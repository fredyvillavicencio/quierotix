<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Services\EventPurchaseService;
use App\Services\SeatAssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Throwable;

class EventPurchaseController extends Controller
{
    /**
     * Cantidad máxima de tickets por compra.
     */
    public const MAX_TICKETS_PER_PURCHASE = SeatAssignmentService::MAX_TICKETS_PER_PURCHASE;

    public function __construct(
        private readonly SeatAssignmentService $seatAssignmentService,
        private readonly EventPurchaseService $eventPurchaseService,
    ) {}

    /**
     * Muestra el formulario público para comprar tickets de un evento.
     */
    public function create(Event $event): View
    {
        $locations = $this->seatAssignmentService->getLocationOptions($event);
        $availableSeats = array_sum(array_column($locations, 'disponibles'));
        $largestLocationAvailability = $locations === []
            ? 0
            : max(array_column($locations, 'disponibles'));
        $maxTickets = min(self::MAX_TICKETS_PER_PURCHASE, $largestLocationAvailability);

        $defaultLocation = collect($locations)->first(fn (array $location): bool => $location['disponibles'] > 0)
            ?? ($locations[0] ?? null);

        $selectedLocation = $this->eventPurchaseService->findLocationOption(
            $locations,
            (string) old('locacion', $defaultLocation['nombre'] ?? '')
        );

        return view('public.events.purchase', [
            'event' => $event,
            'locations' => $locations,
            'availableSeats' => $availableSeats,
            'maxTickets' => $maxTickets,
            'unitPrice' => $selectedLocation['precio'] ?? (float) $event->precio,
        ]);
    }

    /**
     * Registra al comprador, asigna asientos, genera PDFs y envía el correo.
     */
    public function store(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:255'],
            'carne' => ['required', 'string', 'max:255'],
            'locacion' => ['nullable', 'string', 'max:100'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:'.self::MAX_TICKETS_PER_PURCHASE],
        ], [
            'cantidad.max' => 'Puedes comprar un máximo de '.self::MAX_TICKETS_PER_PURCHASE.' tickets por compra.',
        ]);

        $quantity = (int) $validated['cantidad'];
        $requestedLocation = trim((string) ($validated['locacion'] ?? ''));

        try {
            $participant = $this->eventPurchaseService->purchase(
                $event,
                $validated,
                $quantity,
                $requestedLocation
            );
        } catch (Throwable $exception) {
            Log::error('Error al registrar compra pública', [
                'event_id' => $event->id,
                'email' => $validated['email'],
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('events.public.purchase.create', $event)
                ->withInput()
                ->with('error', $exception instanceof \RuntimeException
                    ? $exception->getMessage()
                    : 'No pudimos completar tu compra. Intenta nuevamente.');
        }

        $pdfDirectory = "pdfs/event_{$event->id}/participant_{$participant->id}";

        try {
            $delivery = $this->eventPurchaseService->deliver($participant);
        } catch (Throwable $exception) {
            Storage::disk('local')->deleteDirectory($pdfDirectory);
            $participant->tickets()->delete();
            $participant->delete();

            Log::error('Error al generar PDFs de compra pública', [
                'event_id' => $event->id,
                'participant_id' => $participant->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('events.public.purchase.create', $event)
                ->withInput()
                ->with('error', 'No pudimos generar tus tickets. Intenta nuevamente.');
        }

        $emailSent = (bool) $delivery['email_sent'];
        $participant->refresh();

        Log::info('Compra pública de tickets completada', [
            'event_id' => $event->id,
            'participant_id' => $participant->id,
            'tickets_count' => $participant->tickets()->count(),
            'email_sent' => $emailSent,
        ]);

        $successUrl = URL::temporarySignedRoute(
            'events.public.purchase.success',
            now()->addDays(7),
            [
                'event' => $event->id,
                'participant' => $participant->id,
            ]
        );

        return redirect()->to($successUrl);
    }

    /**
     * Muestra la confirmación de la compra y los enlaces de descarga.
     */
    public function success(Event $event, Participant $participant): View
    {
        abort_unless($participant->event_id === $event->id, 404);

        $participant->load(['event', 'tickets']);

        return view('public.events.purchase-success', [
            'event' => $event,
            'participant' => $participant,
        ]);
    }
}
