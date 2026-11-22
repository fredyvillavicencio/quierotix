@extends('layouts.admin')

@section('title', 'Estadísticas - ' . $event->nombre)

@push('styles')
<style>
.validation-bar-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .validation-bar {
            height: 30px;
            background: #e9ecef;
            border-radius: 15px;
            overflow: hidden;
            margin-top: 10px;
        }
        
        .validation-progress {
            height: 100%;
            background: linear-gradient(135deg, #a7f801 0%, #fcef03 100%);
            border-radius: 15px;
            transition: width 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand-ink);
            font-weight: bold;
            font-size: 14px;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .card-total-tickets {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 100%);
            color: var(--brand-ink);
        }
        
        .card-validated-tickets {
            background: linear-gradient(135deg, #a7f801 0%, #fcef03 100%);
            color: var(--brand-ink);
        }
        
        .card-pending-tickets {
            background: linear-gradient(135deg, #064d00 0%, #0b6b00 100%);
            color: white;
        }
        
        .card-pdfs {
            background: linear-gradient(135deg, #0b6b00 0%, #15d403 100%);
            color: white;
        }
        
        .percentage-badge {
            font-size: 1.2rem;
            font-weight: bold;
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .block-badge {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .validation-badge {
            background: linear-gradient(135deg, #a7f801 0%, #fcef03 100%);
            color: var(--brand-ink);
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .pending-badge {
            background: linear-gradient(135deg, #064d00 0%, #0b6b00 100%);
            color: #fff;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
</style>
@endpush

@section('content')
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-graph-up"></i>
                                Estadísticas
                            </h1>
                            <p class="mb-0 opacity-75">
                                <strong>{{ $event->nombre }}</strong><br>
                                Fecha: {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} a las {{ $event->hora }}
                            </p>
                        </div>
                        <div class="action-buttons">
                            <a href="{{ route('admin.events.show', $event) }}" class="btn btn-light">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Evento
                            </a>
                        </div>
                    </div>
                </div>


                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card card-total-tickets">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Total Tickets</h6>
                                        <h2 class="card-title">{{ $totalTickets }}</h2>
                                        <p class="card-text small">Todos los tickets</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-ticket-perforated"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-validated-tickets">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Tickets Validados</h6>
                                        <h2 class="card-title">{{ $totalValidatedTickets }}</h2>
                                        <p class="card-text small">Validados exitosamente</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-pending-tickets">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Tickets Pendientes</h6>
                                        <h2 class="card-title">{{ $totalTickets - $totalValidatedTickets }}</h2>
                                        <p class="card-text small">Por validar</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card stat-card card-pdfs">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">PDFs Generados</h6>
                                        <h2 class="card-title">{{ $pdfsGenerated }}</h2>
                                        <p class="card-text small">Archivos PDF</p>
                                    </div>
                                    <div class="stat-icon">
                                        <i class="bi bi-file-pdf"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Barra de Validación Total -->
                <div class="validation-bar-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-1">
                                <i class="bi bi-check-circle"></i>
                                Porcentaje de Validación Total
                            </h5>
                            <p class="text-muted mb-0">
                                {{ $totalValidatedTickets }} de {{ $totalTickets }} tickets validados
                            </p>
                        </div>
                        <div>
                            <span class="percentage-badge bg-success text-white">
                                {{ $validationPercentage }}%
                            </span>
                        </div>
                    </div>
                    <div class="validation-bar">
                        <div class="validation-progress" style="width: {{ $validationPercentage }}%;">
                            {{ $validationPercentage }}%
                        </div>
                    </div>
                </div>

                <!-- Tabla de Estadísticas por Locación -->
                <div class="table-container">
                    <div class="p-4 border-bottom">
                        <h5 class="mb-0">
                            <i class="bi bi-grid-3x3-gap"></i>
                            Estadísticas por Locación
                        </h5>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="180">Locación</th>
                                    <th>Total Tickets</th>
                                    <th>Tickets Validados</th>
                                    <th>Tickets Pendientes</th>
                                    <th>% Validación</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locationsStatistics as $location)
                                    @php
                                        $pendingTickets = $location->total_tickets - $location->validated_tickets;
                                        $locationPercentage = $location->total_tickets > 0 
                                            ? round(($location->validated_tickets / $location->total_tickets) * 100, 1)
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="block-badge">
                                                {{ $location->locacion }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $location->total_tickets }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ $location->validated_tickets }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $pendingTickets }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">{{ $locationPercentage }}%</div>
                                                <div style="width: 100px; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                                                    <div style="width: {{ $locationPercentage }}%; height: 100%; background: linear-gradient(135deg, #a7f801 0%, #fcef03 100%);"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($locationPercentage == 100)
                                                <span class="badge bg-success">
                                                    <i class="bi bi-check-circle"></i>
                                                    Completado
                                                </span>
                                            @elseif($locationPercentage >= 1)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-clock"></i>
                                                    En Progreso
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="bi bi-exclamation-circle"></i>
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-grid-3x3-gap display-4"></i>
                                                <h5 class="mt-3">No hay datos de locaciones</h5>
                                                <p class="mb-0">No se encontraron tickets para este evento</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Totales 
                    @if($locationsStatistics->count() > 0)
                        <div class="p-3 bg-light border-top">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Total Locaciones:</span>
                                        <span class="fw-bold">{{ $locationsStatistics->count() }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Total Tickets:</span>
                                        <span class="fw-bold">{{ $totalTickets }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-semibold">Validación Promedio:</span>
                                        <span class="fw-bold">{{ $validationPercentage }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    -->

                </div>

            
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
// Animación para la barra de progreso
            const progressBar = document.querySelector('.validation-progress');
            if (progressBar) {
                // Reiniciar la animación
                progressBar.style.width = '0%';
                setTimeout(() => {
                    progressBar.style.width = '{{ $validationPercentage }}%';
                }, 100);
            }
        });
</script>
@endpush
