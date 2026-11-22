@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
.stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .card-eventos {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
        }
        
        .card-participantes {
            background: linear-gradient(135deg, #0b6b00 0%, #15d403 100%);
            color: white;
        }
        
        .card-tickets {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 100%);
            color: var(--brand-ink);
        }
        
        .card-ventas {
            background: linear-gradient(135deg, #a7f801 0%, #fcef03 100%);
            color: var(--brand-ink);
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            padding: 10px 15px;
            border-left: 3px solid #15d403;
            margin-bottom: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .activity-time {
            font-size: 0.85rem;
            color: #7f8c8d;
        }
        
        .quick-actions .btn {
            margin: 5px;
            min-width: 150px;
        }
        
        .progress-bar-custom {
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            width: 100%;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.3s ease;
        }
</style>
@endpush

@section('content')
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </h1>
                            <p class="mb-0 opacity-75">Resumen general del sistema</p>
                        </div>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <span class="badge badge-brand">
                                <i class="bi bi-person-circle"></i>
                                {{ Auth::user()->name }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-5">
                    <div class="col-md-3">
                        <div class="card stat-card card-eventos">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Eventos Activos</h6>
                                        <h2 class="card-title">{{ $stats['events_count'] ?? 0 }}</h2>
                                        <p class="card-text small">Total de eventos</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-calendar-event"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-participantes">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Participantes</h6>
                                        <h2 class="card-title">{{ $stats['participants_count'] ?? 0 }}</h2>
                                        <p class="card-text small">Total registrados</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-people"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-tickets">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Tickets</h6>
                                        <h2 class="card-title">{{ $stats['tickets_count'] ?? 0 }}</h2>
                                        <p class="card-text small">Total generados</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-ticket-perforated"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-ventas">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Total de Ventas</h6>
                                        <h2 class="card-title">Q {{ number_format($totalStats['total_sales'] ?? 0, 2, '.', ',') }}</h2>
                                        <p class="card-text small">Ingresos totales</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-currency-dollar"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <!-- Detailed Statistics -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="bi bi-table"></i>
                                Estadísticas
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="260px">Evento</th>
                                            <th>Participantes</th>
                                            <th>Tickets</th>
                                            <th>Total de Venta</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($eventsWithStats as $event)
                                            <tr>
                                                <td>
                                                    <strong>{{ $event['nombre'] }}</strong>
                                                    <div class="text-muted small">
                                                        {{ \Carbon\Carbon::parse($event['fecha'])->format('d/m/Y') }} 
                                                        @if($event['hora'])
                                                            a las {{ $event['hora'] }}
                                                        @endif
                                                    </div>

                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $event['participants_count'] }}</div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">{{ $event['tickets_count'] }} TOTAL</div>
                                                    @foreach($event['location_breakdown'] as $location)
                                                        <div class="text-muted small">
                                                            {{ $location['tickets'] }} {{ $location['nombre'] }}
                                                        </div>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    <div class="fw-semibold">Q {{ number_format($event['total_sales'], 2, '.', ',') }} TOTAL</div>
                                                    @foreach($event['location_breakdown'] as $location)
                                                        <div class="text-muted small">
                                                            Q {{ number_format($location['total_sales'], 2, '.', ',') }} {{ $location['nombre'] }}
                                                        </div>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4">
                                                    <i class="bi bi-calendar-x display-4 text-muted"></i>
                                                    <p class="mt-3">No hay eventos registrados</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <td>
                                                <strong>Totales</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $totalStats['total_participants'] ?? 0 }}</strong>
                                            </td>
                                            <td>
                                                <strong>{{ $totalStats['total_tickets'] ?? 0 }}</strong>
                                            </td>
                                            <td>
                                                <strong>Q {{ number_format($totalStats['total_sales'] ?? 0, 2, '.', ',') }}</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity 
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history"></i>
                                    Actividad Reciente
                                </h5>
                            </div>
                            <div class="card-body recent-activity">
                                @if(isset($recentActivities) && count($recentActivities) > 0)
                                    @foreach($recentActivities as $activity)
                                        <div class="activity-item">
                                            <div class="d-flex justify-content-between">
                                                <strong>{{ $activity['title'] }}</strong>
                                                <span class="activity-time">{{ $activity['time'] }}</span>
                                            </div>
                                            <p class="mb-0">{{ $activity['description'] }}</p>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4">
                                        <i class="bi bi-info-circle display-4 text-muted"></i>
                                        <p class="mt-3">No hay actividad reciente</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                -->

            
@endsection
