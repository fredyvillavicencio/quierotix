<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\SeatAssignmentService;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the public homepage with the list of events.
     */
    public function index(): View
    {
        $events = Event::withCount(['participants', 'tickets'])
            ->orderBy('fecha')
            ->orderBy('hora')
            ->get();

        return view('public.home', compact('events'));
    }

    /**
     * Display the public detail of a single event.
     */
    public function show(Event $event, SeatAssignmentService $seatAssignmentService): View
    {
        $event->loadCount(['participants', 'tickets']);

        $availableSeats = $seatAssignmentService->getAvailableSeatCount($event);
        $maxTickets = min(SeatAssignmentService::MAX_TICKETS_PER_PURCHASE, $availableSeats);

        return view('public.events.show', compact('event', 'availableSeats', 'maxTickets'));
    }
}
