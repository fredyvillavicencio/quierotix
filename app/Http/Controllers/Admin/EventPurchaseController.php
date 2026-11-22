<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Services\EventPurchaseService;
use App\Services\SeatAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class EventPurchaseController extends Controller
{
    public function __construct(
        private readonly SeatAssignmentService $seatAssignmentService,
        private readonly EventPurchaseService $eventPurchaseService,
    ) {}

    /**
     * Muestra el formulario de compra manual desde el panel administrativo.
     */
    public function create(Event $event): View
    {
        return view('admin.events.purchase', array_merge(
            ['event' => $event],
            $this->purchaseFormOptions($event)
        ));
    }

    /**
     * Registra una compra manual, genera los tickets y envía el correo.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'carne' => ['required', 'string', 'max:255'],
            'locacion' => ['required', 'string', 'max:100'],
            'cantidad' => [
                'required',
                'integer',
                'min:1',
                'max:'.SeatAssignmentService::MAX_TICKETS_PER_PURCHASE,
            ],
        ], [
            'cantidad.max' => 'Puedes crear un máximo de '.SeatAssignmentService::MAX_TICKETS_PER_PURCHASE.' tickets por compra.',
        ]);

        $quantity = (int) $validated['cantidad'];
        $requestedLocation = trim((string) $validated['locacion']);

        try {
            $participant = $this->eventPurchaseService->purchase(
                $event,
                $validated,
                $quantity,
                $requestedLocation
            );
        } catch (Throwable $exception) {
            Log::error('Error al crear compra desde administración', [
                'event_id' => $event->id,
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.events.purchase.create', $event)
                ->withInput()
                ->with('error', $this->purchaseErrorMessage(
                    $exception,
                    'No pudimos crear la compra. Intenta nuevamente.'
                ));
        }

        $pdfDirectory = "pdfs/event_{$event->id}/participant_{$participant->id}";

        try {
            $delivery = $this->eventPurchaseService->deliver($participant);
        } catch (Throwable $exception) {
            Storage::disk('local')->deleteDirectory($pdfDirectory);
            $participant->tickets()->delete();
            $participant->delete();

            Log::error('Error al generar PDFs de compra administrativa', [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('admin.events.purchase.create', $event)
                ->withInput()
                ->with('error', 'No pudimos generar los tickets. Intenta nuevamente.');
        }

        $participant->refresh();

        $ticketsCount = $participant->tickets()->count();
        $emailSent = (bool) ($delivery['email_sent'] ?? false);

        Log::info('Compra administrativa de tickets completada', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'tickets_count' => $ticketsCount,
            'email_sent' => $emailSent,
        ]);

        return redirect()
            ->route('admin.events.purchase.success', [$event, $participant])
            ->with('success', 'Compra creada exitosamente. Se generaron '.$ticketsCount.' '
                .($ticketsCount === 1 ? 'ticket' : 'tickets')
                .' para '.$participant->full_name.'.');
    }

    /**
     * Muestra los tickets generados y sus enlaces de descarga.
     */
    public function success(Event $event, Participant $participant): View
    {
        abort_unless($participant->event_id === $event->id, 404);

        $participant->load(['event', 'tickets']);

        return view('admin.events.purchase-success', [
            'event' => $event,
            'participant' => $participant,
        ]);
    }

    /**
     * Prepara las locaciones y precios usados por el formulario de compra.
     *
     * @return array{
     *     locations: array<int, array{id: int|null, nombre: string, precio: float, total_asientos: int, vendidos: int, disponibles: int}>,
     *     availableSeats: int,
     *     maxTickets: int,
     *     unitPrice: float,
     *     selectedLocation: array{id: int|null, nombre: string, precio: float, total_asientos: int, vendidos: int, disponibles: int}|null
     * }
     */
    private function purchaseFormOptions(Event $event): array
    {
        $locations = $this->seatAssignmentService->getLocationOptions($event);
        $availableSeats = (int) array_sum(array_column($locations, 'disponibles'));
        $largestLocationAvailability = $locations === []
            ? 0
            : (int) max(array_column($locations, 'disponibles'));
        $maxTickets = min(SeatAssignmentService::MAX_TICKETS_PER_PURCHASE, $largestLocationAvailability);

        $defaultLocation = collect($locations)->first(fn (array $location): bool => $location['disponibles'] > 0)
            ?? ($locations[0] ?? null);

        $selectedLocation = $this->eventPurchaseService->findLocationOption(
            $locations,
            (string) old('locacion', $defaultLocation['nombre'] ?? '')
        ) ?? $defaultLocation;

        return [
            'locations' => $locations,
            'availableSeats' => $availableSeats,
            'maxTickets' => $maxTickets,
            'unitPrice' => $selectedLocation['precio'] ?? (float) $event->precio,
            'selectedLocation' => $selectedLocation,
        ];
    }

    /**
     * Devuelve un mensaje seguro para mostrar al administrador.
     */
    private function purchaseErrorMessage(Throwable $exception, string $fallback): string
    {
        if ($exception instanceof \RuntimeException || $exception instanceof \InvalidArgumentException) {
            return $exception->getMessage();
        }

        return $fallback;
    }
}
