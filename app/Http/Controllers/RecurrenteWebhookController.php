<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\RecurrenteWebhookEvent;
use App\Services\EventPurchaseService;
use App\Services\SeatAssignmentService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RecurrenteWebhookController extends Controller
{
    /**
     * Eventos de Recurrente que representan un pago exitoso.
     *
     * @var array<int, string>
     */
    private const SUCCESS_EVENT_TYPES = [
        'intent.succeeded',
        'payment_intent.succeeded',
    ];

    public function __construct(
        private readonly EventPurchaseService $eventPurchaseService,
        private readonly SeatAssignmentService $seatAssignmentService,
    ) {}

    /**
     * Procesa el webhook de pagos de Recurrente.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);

        if (! is_array($payload)) {
            Log::warning('Webhook Recurrente con JSON inválido', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'JSON inválido.',
            ], 400);
        }

        if (! $this->isSuccessfulPayment($payload)) {
            return response()->json(['status' => 'ignored']);
        }

        $paymentKey = $this->resolvePaymentKey($payload);

        if ($paymentKey === null) {
            Log::warning('Webhook Recurrente sin identificador de pago', [
                'event_type' => $payload['event_type'] ?? $payload['type'] ?? null,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'El pago no incluye un identificador.',
            ], 400);
        }

        $webhook = $this->findOrCreateWebhook($request, $payload, $paymentKey);
        $lock = Cache::lock('recurrente-webhook:'.$webhook->id, 120);

        try {
            return $lock->block(10, fn (): JsonResponse => $this->processPayment($webhook, $payload));
        } catch (LockTimeoutException $exception) {
            Log::warning('Webhook Recurrente en proceso por otra petición', [
                'webhook_id' => $webhook->id,
                'payment_key' => $paymentKey,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'El pago está siendo procesado.',
            ], 500);
        } catch (Throwable $exception) {
            Log::error('Error inesperado procesando webhook Recurrente', [
                'webhook_id' => $webhook->id,
                'payment_key' => $paymentKey,
                'error' => $exception->getMessage(),
            ]);

            $webhook->update(['last_error' => $exception->getMessage()]);

            return response()->json([
                'status' => 'error',
                'message' => 'No se pudo procesar el pago.',
            ], 500);
        }
    }

    /**
     * Ejecuta la compra y entrega de tickets para un pago exitoso.
     */
    private function processPayment(RecurrenteWebhookEvent $webhook, array $payload): JsonResponse
    {
        $webhook->refresh();

        if ($webhook->isProcessed()) {
            return response()->json([
                'status' => 'ok',
                'duplicate' => true,
                'participant_id' => $webhook->participant_id,
            ]);
        }

        $productId = trim((string) data_get($payload, 'product.id', ''));
        $event = $productId !== ''
            ? Event::where('recurrente_product_id', $productId)->first()
            : null;

        if ($event === null) {
            return $this->fail(
                $webhook,
                $productId === ''
                    ? 'El webhook no incluye product.id.'
                    : "No existe un evento con recurrente_product_id {$productId}.",
                500
            );
        }

        if ($webhook->event_id !== $event->id) {
            $webhook->update(['event_id' => $event->id]);
        }

        $quantity = $this->resolveQuantity($payload);

        if ($quantity < 1 || $quantity > SeatAssignmentService::MAX_TICKETS_PER_PURCHASE) {
            return $this->fail(
                $webhook,
                'Cantidad de tickets inválida: '.$quantity.'.',
                400
            );
        }

        $buyer = $this->mapBuyer($payload);

        if ($buyer['email'] === '' || ! filter_var($buyer['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->fail($webhook, 'El pago no incluye un correo válido.', 400);
        }

        $locationName = $this->resolveLocationName(
            $event,
            $quantity,
            (int) data_get($payload, 'amount_in_cents', 0)
        );

        if ($locationName === null) {
            return $this->fail(
                $webhook,
                'No hay asientos disponibles para la cantidad de tickets pagada.',
                500
            );
        }

        try {
            $participant = DB::transaction(function () use ($webhook, $event, $buyer, $quantity, $locationName): Participant {
                $lockedWebhook = RecurrenteWebhookEvent::whereKey($webhook->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedWebhook->participant_id !== null) {
                    $existingParticipant = Participant::find($lockedWebhook->participant_id);

                    if ($existingParticipant !== null) {
                        return $existingParticipant;
                    }
                }

                $participant = $this->eventPurchaseService->purchase(
                    $event,
                    $buyer,
                    $quantity,
                    $locationName
                );

                $lockedWebhook->update([
                    'event_id' => $event->id,
                    'participant_id' => $participant->id,
                ]);

                return $participant;
            }, 3);
        } catch (Throwable $exception) {
            return $this->fail(
                $webhook,
                'Error creando participante y tickets: '.$exception->getMessage(),
                500
            );
        }

        try {
            $delivery = $this->eventPurchaseService->deliver($participant);
        } catch (Throwable $exception) {
            return $this->fail(
                $webhook,
                'Error generando PDFs: '.$exception->getMessage(),
                500
            );
        }

        $webhook->update([
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'processed_at' => now(),
            'last_error' => $delivery['email_sent']
                ? null
                : 'No se pudo enviar el correo de confirmación.',
        ]);

        $participant->refresh();

        Log::info('Webhook Recurrente procesado', [
            'webhook_id' => $webhook->id,
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'tickets_count' => $participant->tickets()->count(),
            'email_sent' => $delivery['email_sent'],
        ]);

        return response()->json([
            'status' => 'ok',
            'participant_id' => $participant->id,
            'tickets' => $participant->tickets()->count(),
            'email_sent' => $delivery['email_sent'],
        ]);
    }

    /**
     * Determina si el payload corresponde a un pago exitoso.
     */
    private function isSuccessfulPayment(array $payload): bool
    {
        $type = $payload['type'] ?? null;
        $eventType = $payload['event_type'] ?? null;
        $status = $payload['status'] ?? null;

        $typeIsPayment = $type === 'payment'
            || in_array($type, self::SUCCESS_EVENT_TYPES, true);

        if (! $typeIsPayment) {
            return false;
        }

        if ($eventType !== null && ! in_array($eventType, self::SUCCESS_EVENT_TYPES, true)) {
            return false;
        }

        return $status === 'succeeded';
    }

    /**
     * Obtiene una clave estable para identificar el pago.
     */
    private function resolvePaymentKey(array $payload): ?string
    {
        $candidates = [
            $payload['legacy_id'] ?? null,
            $payload['id'] ?? null,
            data_get($payload, 'payment.id'),
            data_get($payload, 'payment_intent.id'),
            $payload['receipt_number'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_scalar($candidate) && trim((string) $candidate) !== '') {
                return trim((string) $candidate);
            }
        }

        return null;
    }

    /**
     * Busca el registro del webhook o lo crea de forma idempotente.
     */
    private function findOrCreateWebhook(Request $request, array $payload, string $paymentKey): RecurrenteWebhookEvent
    {
        $svixId = trim((string) $request->header('svix-id', ''));
        $svixId = $svixId !== '' ? $svixId : null;

        if ($svixId !== null) {
            $existing = RecurrenteWebhookEvent::where('svix_id', $svixId)->first();

            if ($existing !== null) {
                return $existing;
            }
        }

        $attributes = $this->webhookAttributes($payload, $paymentKey, $svixId);

        try {
            $webhook = RecurrenteWebhookEvent::firstOrCreate(
                [
                    'provider' => RecurrenteWebhookEvent::PROVIDER,
                    'payment_key' => $paymentKey,
                ],
                $attributes
            );
        } catch (UniqueConstraintViolationException $exception) {
            $webhook = $svixId !== null
                ? RecurrenteWebhookEvent::where('svix_id', $svixId)->first()
                : null;

            $webhook ??= RecurrenteWebhookEvent::where('provider', RecurrenteWebhookEvent::PROVIDER)
                ->where('payment_key', $paymentKey)
                ->firstOrFail();
        }

        if (! $webhook->wasRecentlyCreated && ! $webhook->isProcessed()) {
            $existingSvixId = $webhook->svix_id;
            $webhook->fill($attributes);
            $webhook->svix_id = $existingSvixId ?: $svixId;
            $webhook->save();
        }

        return $webhook;
    }

    /**
     * Construye los datos persistibles del webhook.
     *
     * @return array<string, mixed>
     */
    private function webhookAttributes(array $payload, string $paymentKey, ?string $svixId): array
    {
        $eventType = (string) ($payload['event_type'] ?? $payload['type'] ?? '');
        $receiptNumber = $payload['receipt_number'] ?? null;
        $amountInCents = $payload['amount_in_cents'] ?? data_get($payload, 'payment.amount_in_cents');

        return [
            'payment_key' => $paymentKey,
            'svix_id' => $svixId,
            'intent_id' => data_get($payload, 'payment_intent.id')
                ?? (str_contains($eventType, 'payment_intent') ? data_get($payload, 'id') : null),
            'legacy_id' => $payload['legacy_id'] ?? null,
            'event_type' => $eventType,
            'status' => $payload['status'] ?? null,
            'raw_status' => $payload['status'] ?? null,
            'receipt_number' => is_numeric($receiptNumber) ? (int) $receiptNumber : null,
            'amount_in_cents' => is_numeric($amountInCents) ? (int) $amountInCents : null,
            'currency' => $payload['currency'] ?? null,
            'customer_email' => data_get($payload, 'customer.email'),
            'customer_name' => data_get($payload, 'customer.full_name'),
            'checkout_id' => data_get($payload, 'checkout.id'),
            'payload' => $payload,
        ];
    }


    /**
     * Suma la cantidad de tickets incluida en el pago.
     */
    private function resolveQuantity(array $payload): int
    {
        $products = data_get($payload, 'details.products');

        if (! is_array($products) || $products === []) {
            return 1;
        }

        $quantity = 0;

        foreach ($products as $product) {
            $productQuantity = $product['quantity'] ?? 0;

            if (! is_numeric($productQuantity)) {
                return 0;
            }

            $quantity += (int) $productQuantity;
        }

        return $quantity;
    }

    /**
     * Resuelve la locación por precio unitario o usa la primera con disponibilidad.
     */
    private function resolveLocationName(Event $event, int $quantity, int $amountInCents): ?string
    {
        $locations = $this->seatAssignmentService->getLocationOptions($event);
        $availableLocations = array_values(array_filter(
            $locations,
            fn (array $location): bool => $location['disponibles'] >= $quantity
        ));

        if ($availableLocations === []) {
            return null;
        }

        if (count($locations) === 1) {
            return $availableLocations[0]['nombre'];
        }

        if ($amountInCents > 0) {
            $unitPrice = ($amountInCents / 100) / $quantity;
            $matchingLocations = array_values(array_filter(
                $availableLocations,
                fn (array $location): bool => abs($location['precio'] - $unitPrice) < 0.01
            ));

            if (count($matchingLocations) === 1) {
                return $matchingLocations[0]['nombre'];
            }
        }

        return $availableLocations[0]['nombre'];
    }

    /**
     * Convierte los datos del cliente de Recurrente al formato del participante.
     *
     * @return array{nombre: string, apellido: string, email: string, telefono: string, carne: string}
     */
    private function mapBuyer(array $payload): array
    {
        $fullName = trim((string) data_get($payload, 'customer.full_name', ''));
        $nameParts = preg_split('/\s+/u', $fullName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $apellido = count($nameParts) > 1 ? array_pop($nameParts) : '';
        $nombre = $nameParts === [] ? ($fullName !== '' ? $fullName : 'Cliente') : implode(' ', $nameParts);

        return [
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => trim((string) data_get($payload, 'customer.email', '')),
            'telefono' => trim((string) data_get($payload, 'customer.phone', '')),
            'carne' => trim((string) data_get($payload, 'customer.nit', '')) ?: 'CF',
        ];
    }

    /**
     * Registra el error y responde con un mensaje genérico.
     */
    private function fail(RecurrenteWebhookEvent $webhook, string $message, int $status): JsonResponse
    {
        $webhook->update(['last_error' => $message]);

        Log::error('Error procesando webhook Recurrente', [
            'webhook_id' => $webhook->id,
            'payment_key' => $webhook->payment_key,
            'error' => $message,
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'No se pudo procesar el pago.',
        ], $status);
    }
}

