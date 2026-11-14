<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SeatAssignmentService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(
        private readonly SeatAssignmentService $seatAssignmentService,
    ) {}

    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Obtener estadísticas generales
        $stats = [
            'events_count' => Event::count(),
            'participants_count' => Participant::count(),
            'tickets_count' => Ticket::count(),
            'active_users' => User::where('is_admin', false)->count(),
            'validated_tickets' => Ticket::where('validado', true)->count(),
            'total_validations' => Ticket::sum('contador_validaciones'),
        ];

        // Obtener estadísticas detalladas por evento
        $eventsWithStats = Event::withCount(['participants', 'tickets'])
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($event) {
                $locations = $this->seatAssignmentService->getLocationOptions($event);

                $locationBreakdown = collect($locations)
                    ->filter(fn (array $location): bool => $location['vendidos'] > 0)
                    ->map(fn (array $location): array => [
                        'nombre' => $location['nombre'],
                        'tickets' => $location['vendidos'],
                        'total_sales' => round($location['vendidos'] * $location['precio'], 2),
                    ])
                    ->values()
                    ->all();

                $totalSales = array_sum(array_column($locationBreakdown, 'total_sales'));

                return [
                    'id' => $event->id,
                    'nombre' => $event->nombre,
                    'codigo_evento' => $event->codigo_evento,
                    'fecha' => $event->fecha,
                    'hora' => $event->hora,
                    'participants_count' => $event->participants_count,
                    'tickets_count' => $event->tickets_count,
                    'location_breakdown' => $locationBreakdown,
                    'total_sales' => round($totalSales, 2),
                ];
            });

        // Calcular totales
        $totalStats = [
            'total_participants' => $eventsWithStats->sum('participants_count'),
            'total_tickets' => $eventsWithStats->sum('tickets_count'),
            'total_sales' => round((float) $eventsWithStats->sum('total_sales'), 2),
        ];

        // Obtener actividad reciente
        $recentActivities = $this->getRecentActivities();

        // Obtener eventos próximos
        $upcomingEvents = Event::where('fecha', '>=', now())
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        // Obtener participantes recientes
        $recentParticipants = Participant::with('event')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'eventsWithStats',
            'totalStats',
            'recentActivities',
            'upcomingEvents',
            'recentParticipants'
        ));
    }

    /**
     * Get recent activities for the dashboard.
     */
    private function getRecentActivities(): array
    {
        $activities = [];

        // Actividad reciente de eventos
        $recentEvents = Event::orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentEvents as $event) {
            $activities[] = [
                'title' => 'Nuevo Evento Creado',
                'description' => "Evento: {$event->nombre}",
                'time' => $event->created_at->diffForHumans(),
                'icon' => 'bi-calendar-event',
                'color' => 'primary',
            ];
        }

        // Actividad reciente de participantes
        $recentParticipants = Participant::with('event')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentParticipants as $participant) {
            $activities[] = [
                'title' => 'Nuevo Participante Registrado',
                'description' => "{$participant->full_name} - {$participant->event->nombre}",
                'time' => $participant->created_at->diffForHumans(),
                'icon' => 'bi-person-plus',
                'color' => 'success',
            ];
        }

        // Actividad reciente de tickets
        $recentTickets = Ticket::with(['participant', 'event'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        foreach ($recentTickets as $ticket) {
            $activities[] = [
                'title' => 'Ticket Generado',
                'description' => "Asiento: {$ticket->seat_code} - {$ticket->participant->full_name}",
                'time' => $ticket->created_at->diffForHumans(),
                'icon' => 'bi-ticket-perforated',
                'color' => 'warning',
            ];
        }

        // Ordenar por fecha (más reciente primero)
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 10); // Limitar a 10 actividades
    }

    /**
     * Get system statistics for charts.
     */
    public function getStatistics()
    {
        // Estadísticas por mes
        $monthlyStats = Event::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Rellenar meses faltantes
        $completeMonthlyStats = [];
        for ($i = 1; $i <= 12; $i++) {
            $completeMonthlyStats[$i] = $monthlyStats[$i] ?? 0;
        }

        // Estadísticas de validación
        $validationStats = [
            'validated' => Ticket::where('validado', true)->count(),
            'pending' => Ticket::where('validado', false)->count(),
        ];

        // Estadísticas de PDFs
        $pdfStats = [
            'generated' => Ticket::where('archivo_pdf', '!=', '')->count(),
            'not_generated' => Ticket::where('archivo_pdf', '')->count(),
        ];

        return response()->json([
            'monthly_stats' => $completeMonthlyStats,
            'validation_stats' => $validationStats,
            'pdf_stats' => $pdfStats,
        ]);
    }

    /**
     * Get quick stats for the dashboard widgets.
     */
    public function getQuickStats()
    {
        $today = now()->format('Y-m-d');

        $stats = [
            'today_events' => Event::whereDate('fecha', $today)->count(),
            'today_participants' => Participant::whereDate('created_at', $today)->count(),
            'today_tickets' => Ticket::whereDate('created_at', $today)->count(),
            'today_validations' => Ticket::whereDate('updated_at', $today)
                ->where('validado', true)
                ->count(),
        ];

        return response()->json($stats);
    }
}
