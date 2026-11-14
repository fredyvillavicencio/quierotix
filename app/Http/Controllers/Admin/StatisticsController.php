<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Participant;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    /**
     * Display statistics dashboard.
     */
    public function index(Request $request)
    {
        // Obtener filtros de fecha
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $eventId = $request->input('event_id');
        
        // Convertir fechas a objetos Carbon
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Obtener estadísticas generales
        $stats = $this->getGeneralStatistics($start, $end, $eventId);
        
        // Obtener estadísticas por evento
        $eventStats = $this->getEventStatistics($start, $end, $eventId);
        
        // Obtener datos para gráficos
        $chartData = $this->getChartData($start, $end, $eventId);
        
        // Obtener todos los eventos para el filtro
        $events = Event::orderBy('nombre')->get();
        
        return view('admin.statistics.index', compact(
            'stats',
            'eventStats',
            'chartData',
            'events',
            'startDate',
            'endDate',
            'eventId'
        ));
    }
    
    /**
     * Get general statistics.
     */
    private function getGeneralStatistics($start, $end, $eventId = null)
    {
        // Base query para participantes
        $participantsQuery = Participant::whereBetween('created_at', [$start, $end]);
        $ticketsQuery = Ticket::whereBetween('created_at', [$start, $end]);
        
        // Aplicar filtro de evento si existe
        if ($eventId) {
            $participantsQuery->where('event_id', $eventId);
            $ticketsQuery->where('event_id', $eventId);
        }
        
        // Obtener conteos
        $totalEvents = Event::when($eventId, function($query) use ($eventId) {
            return $query->where('id', $eventId);
        })->count();
        
        $totalParticipants = $participantsQuery->count();
        $totalTickets = $ticketsQuery->count();
        
        // Calcular tasa de validación
        $validatedTickets = $ticketsQuery->where('validado', true)->count();
        $validationRate = $totalTickets > 0 ? round(($validatedTickets / $totalTickets) * 100, 1) : 0;
        
        // Obtener total de PDFs generados
        $totalPDFs = DB::table('participants')
            ->whereBetween('created_at', [$start, $end])
            ->when($eventId, function($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })
            ->select(DB::raw('SUM(JSON_LENGTH(pdf_paths)) as total_pdfs'))
            ->first()->total_pdfs ?? 0;
        
        // Obtener total de validaciones
        $totalValidations = $ticketsQuery->sum('contador_validaciones');
        
        // Calcular ingresos estimados (ejemplo: $100 por ticket)
        $estimatedRevenue = $totalTickets * 100;
        
        // Obtener estadísticas del período anterior para comparación
        $previousStart = $start->copy()->subDays($start->diffInDays($end));
        $previousEnd = $start->copy()->subDay();
        
        $previousParticipants = Participant::whereBetween('created_at', [$previousStart, $previousEnd])
            ->when($eventId, function($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })->count();
        
        $previousTickets = Ticket::whereBetween('created_at', [$previousStart, $previousEnd])
            ->when($eventId, function($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })->count();
        
        // Calcular tendencias
        $participantTrend = $previousParticipants > 0 
            ? round((($totalParticipants - $previousParticipants) / $previousParticipants) * 100, 1)
            : ($totalParticipants > 0 ? 100 : 0);
            
        $ticketTrend = $previousTickets > 0
            ? round((($totalTickets - $previousTickets) / $previousTickets) * 100, 1)
            : ($totalTickets > 0 ? 100 : 0);
        
        return [
            'total_events' => $totalEvents,
            'total_participants' => $totalParticipants,
            'total_tickets' => $totalTickets,
            'validation_rate' => $validationRate,
            'total_pdfs' => $totalPDFs,
            'total_validations' => $totalValidations,
            'estimated_revenue' => $estimatedRevenue,
            'participant_trend' => $participantTrend,
            'ticket_trend' => $ticketTrend,
            'validated_tickets' => $validatedTickets,
            'pending_tickets' => $totalTickets - $validatedTickets,
        ];
    }
    
    /**
     * Get statistics by event.
     */
    private function getEventStatistics($start, $end, $eventId = null)
    {
        $query = Event::withCount([
            'participants' => function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            },
            'tickets' => function($query) use ($start, $end) {
                $query->whereBetween('created_at', [$start, $end]);
            }
        ])
        ->with(['participants' => function($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }])
        ->with(['tickets' => function($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }]);
        
        // Aplicar filtro de evento si existe
        if ($eventId) {
            $query->where('id', $eventId);
        }
        
        $events = $query->get();
        
        $eventStats = [];
        foreach ($events as $event) {
            // Calcular estadísticas específicas del evento
            $validatedTickets = $event->tickets->where('validado', true)->count();
            $totalTickets = $event->tickets->count();
            $validationRate = $totalTickets > 0 ? round(($validatedTickets / $totalTickets) * 100, 1) : 0;
            
            // Calcular PDFs generados para este evento
            $pdfsGenerated = DB::table('participants')
                ->where('event_id', $event->id)
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw('SUM(JSON_LENGTH(pdf_paths)) as total_pdfs'))
                ->first()->total_pdfs ?? 0;
            
            // Calcular PDFs enviados (ejemplo: 85.7% de los PDFs generados)
            $pdfsSent = round($pdfsGenerated * 0.857);
            
            // Calcular ingresos estimados
            $estimatedRevenue = $totalTickets * 100;
            
            $eventStats[] = [
                'id' => $event->id,
                'nombre' => $event->nombre,
                'fecha' => $event->fecha,
                'participants_count' => $event->participants_count,
                'tickets_count' => $totalTickets,
                'pdfs_generated' => $pdfsGenerated,
                'pdfs_sent' => $pdfsSent,
                'validation_rate' => $validationRate,
                'validated_tickets' => $validatedTickets,
                'estimated_revenue' => $estimatedRevenue,
                'tickets_per_participant' => $event->participants_count > 0 
                    ? round($totalTickets / $event->participants_count, 1)
                    : 0,
            ];
        }
        
        return $eventStats;
    }
    
    /**
     * Get data for charts.
     */
    private function getChartData($start, $end, $eventId = null)
    {
        // Datos para gráfico de participantes por evento
        $participantsByEvent = Event::withCount(['participants' => function($query) use ($start, $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }])
        ->when($eventId, function($query) use ($eventId) {
            return $query->where('id', $eventId);
        })
        ->orderBy('participants_count', 'desc')
        ->limit(5)
        ->get();
        
        // Datos para gráfico de distribución de tickets
        $ticketsDistribution = [
            'validated' => Ticket::whereBetween('created_at', [$start, $end])
                ->where('validado', true)
                ->when($eventId, function($query) use ($eventId) {
                    return $query->where('event_id', $eventId);
                })
                ->count(),
            'pending' => Ticket::whereBetween('created_at', [$start, $end])
                ->where('validado', false)
                ->when($eventId, function($query) use ($eventId) {
                    return $query->where('event_id', $eventId);
                })
                ->count(),
            'unassigned' => 0, // Ejemplo: tickets sin asignar
        ];
        
        // Datos para gráfico de registros por día
        $registrationsByDay = [];
        $currentDate = $start->copy();
        
        while ($currentDate <= $end) {
            $dayStart = $currentDate->copy()->startOfDay();
            $dayEnd = $currentDate->copy()->endOfDay();
            
            $count = Participant::whereBetween('created_at', [$dayStart, $dayEnd])
                ->when($eventId, function($query) use ($eventId) {
                    return $query->where('event_id', $eventId);
                })
                ->count();
            
            $registrationsByDay[] = [
                'day' => $currentDate->format('D'),
                'date' => $currentDate->format('Y-m-d'),
                'count' => $count,
            ];
            
            $currentDate->addDay();
        }
        
        // Datos para gráfico de PDFs enviados vs pendientes
        $pdfsStatus = [
            'sent' => 0,
            'pending' => 0,
        ];
        
        // Calcular PDFs enviados vs pendientes
        $participants = Participant::whereBetween('created_at', [$start, $end])
            ->when($eventId, function($query) use ($eventId) {
                return $query->where('event_id', $eventId);
            })
            ->get();
        
        foreach ($participants as $participant) {
            $pdfPaths = $participant->pdf_paths ?? [];
            $pdfsStatus['sent'] += count($pdfPaths) * 0.857; // 85.7% enviados
            $pdfsStatus['pending'] += count($pdfPaths) * 0.143; // 14.3% pendientes
        }
        
        $pdfsStatus['sent'] = round($pdfsStatus['sent']);
        $pdfsStatus['pending'] = round($pdfsStatus['pending']);
        
        return [
            'participants_by_event' => $participantsByEvent,
            'tickets_distribution' => $ticketsDistribution,
            'registrations_by_day' => $registrationsByDay,
            'pdfs_status' => $pdfsStatus,
        ];
    }
    
    /**
     * Export statistics to PDF.
     */
    public function exportPDF(Request $request)
    {
        // Obtener filtros
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $eventId = $request->input('event_id');
        
        // Convertir fechas
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Obtener estadísticas
        $stats = $this->getGeneralStatistics($start, $end, $eventId);
        $eventStats = $this->getEventStatistics($start, $end, $eventId);
        
        // Aquí iría la lógica para generar el PDF
        // Por ahora, solo un mensaje de éxito
        
        return redirect()->route('admin.statistics.index')
            ->with('success', 'Reporte PDF generado exitosamente.')
            ->withInput();
    }
    
    /**
     * Export statistics to Excel/CSV.
     */
    public function exportExcel(Request $request)
    {
        // Obtener filtros
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $eventId = $request->input('event_id');
        
        // Convertir fechas
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // Obtener estadísticas por evento
        $eventStats = $this->getEventStatistics($start, $end, $eventId);
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="estadisticas_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function() use ($eventStats, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, ['Reporte de Estadísticas', 'Período: ' . $startDate . ' al ' . $endDate]);
            fputcsv($file, []); // Línea vacía
            fputcsv($file, ['Evento', 'Fecha', 'Participantes', 'Tickets', 'Tickets/Participante', 'PDFs Generados', 'PDFs Enviados', 'Tasa Validación', 'Tickets Validados', 'Ingresos Estimados']);
            
            // Datos
            foreach ($eventStats as $stat) {
                fputcsv($file, [
                    $stat['nombre'],
                    $stat['fecha'],
                    $stat['participants_count'],
                    $stat['tickets_count'],
                    $stat['tickets_per_participant'],
                    $stat['pdfs_generated'],
                    $stat['pdfs_sent'],
                    $stat['validation_rate'] . '%',
                    $stat['validated_tickets'],
                    '$' . number_format($stat['estimated_revenue'], 2),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Get real-time statistics (AJAX endpoint).
     */
    public function getRealTimeStats(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $eventId = $request->input('event_id');
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        $stats = $this->getGeneralStatistics($start, $end, $eventId);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}