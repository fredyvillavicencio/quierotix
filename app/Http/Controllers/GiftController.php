<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiftController extends Controller
{
    /**
     * Display the event selection page for the gift raffle.
     */
    public function select(): View
    {
        $events = Event::query()
            ->withCount(['tickets' => fn ($query) => $query->whereNotNull('numero')])
            ->whereHas('tickets', fn ($query) => $query->whereNotNull('numero'))
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return view('gifts.select', [
            'events' => $events,
        ]);
    }

    /**
     * Display the public gift ticket raffle page for the selected event.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $eventId = (int) $request->input('event_id');

        if ($eventId < 1) {
            return redirect()->route('gifts.select');
        }

        $event = Event::query()
            ->withCount(['tickets' => fn ($query) => $query->whereNotNull('numero')])
            ->find($eventId);

        if (! $event || $event->tickets_count < 1) {
            return redirect()
                ->route('gifts.select')
                ->with('error', 'El evento seleccionado no tiene tickets disponibles para el sorteo.');
        }

        return view('gifts.index', [
            'event' => $event,
        ]);
    }

    /**
     * Return a random ticket number from the selected event.
     */
    public function randomTicket(Request $request): JsonResponse
    {
        $eventId = (int) $request->input('event_id');

        if ($eventId < 1) {
            return response()->json([
                'message' => 'Selecciona un evento para realizar el sorteo.',
            ], 422);
        }

        $ticket = Ticket::query()
            ->where('event_id', $eventId)
            ->whereNotNull('numero')
            ->inRandomOrder()
            ->first();

        if (! $ticket) {
            return response()->json([
                'message' => 'No hay tickets registrados para sortear.',
            ], 404);
        }

        return response()->json([
            'numero' => (int) $ticket->numero,
        ])->header('Cache-Control', 'no-store, max-age=0');
    }
}
