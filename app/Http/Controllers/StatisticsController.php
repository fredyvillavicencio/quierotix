<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Fecha seleccionada
        |--------------------------------------------------------------------------
        */

        $selectedDate = $request->get('fecha');

        /*
        |--------------------------------------------------------------------------
        | Eventos disponibles para el filtro
        |--------------------------------------------------------------------------
        */

        $eventDates = Event::query()
            ->select('fecha')
            ->distinct()
            ->orderByDesc('fecha')
            ->pluck('fecha');

        /*
        |--------------------------------------------------------------------------
        | Estadísticas
        |--------------------------------------------------------------------------
        */

        $usersStatistics = collect();

        $totalValidated = 0;
        $totalTickets = 0;

        if ($selectedDate) {

            /*
            |--------------------------------------------------------------------------
            | Total de tickets de los eventos de la fecha seleccionada
            |--------------------------------------------------------------------------
            */

            $totalTickets = Ticket::whereHas('event', function ($query) use ($selectedDate) {
                    $query->whereDate('fecha', $selectedDate);
                })
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Total de tickets validados
            |--------------------------------------------------------------------------
            */

            $totalValidated = Ticket::whereHas('event', function ($query) use ($selectedDate) {
                    $query->whereDate('fecha', $selectedDate);
                })
                ->where('validado', 1)
                ->whereNotNull('validado_por')
                ->count();

            /*
            |--------------------------------------------------------------------------
            | Estadísticas por usuario
            |--------------------------------------------------------------------------
            */

            $usersStatistics = Ticket::query()
                ->select(
                    'validado_por',
                    DB::raw('COUNT(*) as validated_tickets')
                )
                ->whereHas('event', function ($query) use ($selectedDate) {
                    $query->whereDate('fecha', $selectedDate);
                })
                ->where('validado', 1)
                ->whereNotNull('validado_por')
                ->groupBy('validado_por')
                ->orderByDesc('validated_tickets')
                ->with('validatedBy')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Porcentaje
            |--------------------------------------------------------------------------
            */

            $usersStatistics->transform(function ($user) use ($totalValidated) {

                $user->percentage = $totalValidated > 0
                    ? round(($user->validated_tickets / $totalValidated) * 100, 1)
                    : 0;

                return $user;
            });
        }

        return view('admin.statistics.index', compact(
            'selectedDate',
            'eventDates',
            'usersStatistics',
            'totalValidated',
            'totalTickets'
        ));
    }
}