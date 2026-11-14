<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GiftController extends Controller
{
    /**
     * Display the public gift ticket raffle page.
     */
    public function index(): View
    {
        return view('gifts.index');
    }

    /**
     * Return a random ticket code for the raffle.
     */
    public function randomTicket(): JsonResponse
    {
        $ticket = Ticket::query()->inRandomOrder()->first();

        if (! $ticket) {
            return response()->json([
                'message' => 'No hay tickets registrados para sortear.',
            ], 404);
        }

        return response()->json([
            'code' => $ticket->ticket_file_code,
        ])->header('Cache-Control', 'no-store, max-age=0');
    }
}
