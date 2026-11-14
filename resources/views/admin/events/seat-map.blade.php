<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Locaciones - {{ $event->nombre }} - Sistema de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            color: white;
        }
        
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .sidebar .nav-link.active {
            background-color: #3498db;
            color: white;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .page-header {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .seat-map-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .seat-map-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .seat-map-grid {
            display: flex;
            flex-direction: column;
            gap: 5px;
            overflow-x: auto;
        }
        
        .seat-row {
            display: flex;
            gap: 3px;
            align-items: center;
            min-height: 40px;
        }
        
        .row-label {
            width: 40px;
            font-weight: bold;
            text-align: center;
            color: #495057;
            background: #f8f9fa;
            padding: 8px 5px;
            border-radius: 4px;
            font-size: 0.9rem;
        }
        
        .seats-container {
            display: flex;
            flex: 1;
            gap: 3px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 5px 0;
        }
        
        .seat {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: default;
            transition: all 0.2s;
            border: 1px solid transparent;
            flex-shrink: 0;
        }
        
        .seat:hover {
            transform: scale(1.1);
            z-index: 10;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }
        
        .seat-validated {
            background-color: #198754;
            color: white;
            border-color: #198754;
        }
        
        .seat-assigned {
            background-color: #9bafb1;
            color: white;
            border-color: #9bafb1;
        }
        
        .seat-available {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        
        .block-separator {
            width: 10px;
            background: #e9ecef;
            border-radius: 2px;
            margin: 0 5px;
            flex-shrink: 0;
        }
        
        .block-label {
            text-align: center;
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
            padding: 5px 10px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        
        .block-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0 5px;
        }
        
        .blocks-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .legend {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }
        
        .legend-text {
            font-size: 0.9rem;
            color: #495057;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #3498db;
        }
        
        .stat-card-title {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .stat-card-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 100px;
        }
        
        .stage-indicator {
            text-align: center;
            font-weight: bold;
            color: #2c3e50;
            margin: 20px 0;
            padding: 10px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 8px;
        }
        
        .locations-summary-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .location-summary-card {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e3e8ef;
            border-radius: 12px;
            padding: 22px;
            box-shadow: 0 2px 6px rgba(44, 62, 80, 0.06);
        }

        .location-summary-name {
            font-size: 1.15rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0 0 18px;
        }

        .location-summary-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .location-summary-metric {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
        }

        .location-summary-metric-label {
            display: block;
            color: #7f8c8d;
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .location-summary-metric-value {
            color: #2c3e50;
            font-size: 1.35rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .location-summary-percentage {
            font-weight: 700;
            color: #2c3e50;
        }

        .location-progress {
            height: 14px;
            background: #e9ecef;
            border-radius: 999px;
            overflow: hidden;
        }

        .location-progress .progress-bar {
            background: linear-gradient(90deg, #3498db 0%, #2ecc71 100%);
            border-radius: 999px;
            transition: width 0.6s ease;
        }

        .location-progress-validated .progress-bar {
            background: linear-gradient(90deg, #16a34a 0%, #22c55e 100%);
        }

        @media (max-width: 768px) {
            .locations-summary-list {
                grid-template-columns: 1fr;
            }

            .seat {
                width: 30px;
                height: 30px;
                font-size: 0.7rem;
            }
            
            .row-label {
                width: 35px;
                font-size: 0.8rem;
            }
            
            .blocks-container {
                flex-direction: column;
                gap: 20px;
            }
            
            .block-container {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-pie-chart"></i>
                                Resumen de Locaciones
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

                <!-- Resumen de Locaciones -->
                <div class="seat-map-container">
                    <div class="seat-map-title">
                        <i class="bi bi-pie-chart"></i>
                        Resumen de Locaciones
                    </div>

                    <div class="locations-summary-list">
                        @forelse($locations as $location)
                            @php
                                $percentage = $location['total_asientos'] > 0
                                    ? round(($location['vendidos'] / $location['total_asientos']) * 100, 2)
                                    : 0;
                                $barWidth = min(100, max(0, $percentage));
                                $validatedPercentage = $location['porcentaje_validados'] ?? 0;
                                $validatedBarWidth = min(100, max(0, $validatedPercentage));
                            @endphp

                            <div class="location-summary-card" data-location-card>
                                <h2 class="location-summary-name">{{ $location['nombre'] }}</h2>

                                <div class="location-summary-metrics">
                                    <div class="location-summary-metric">
                                        <span class="location-summary-metric-label">Total de tickets</span>
                                        <span class="location-summary-metric-value">{{ number_format($location['total_asientos'], 0, '.', ',') }}</span>
                                    </div>
                                    <div class="location-summary-metric">
                                        <span class="location-summary-metric-label">Tickets vendidos</span>
                                        <span class="location-summary-metric-value">{{ number_format($location['vendidos'], 0, '.', ',') }}</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted">Porcentaje ocupado</span>
                                    <span class="location-summary-percentage">{{ number_format($percentage, 2, '.', ',') }}%</span>
                                </div>
                                <div class="progress location-progress" role="progressbar"
                                     aria-label="Ocupación de {{ $location['nombre'] }}"
                                     aria-valuenow="{{ $percentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $barWidth }}%"></div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                    <span class="text-muted">Porcentaje validados</span>
                                    <span class="location-summary-percentage">{{ number_format($validatedPercentage, 2, '.', ',') }}%</span>
                                </div>
                                <div class="progress location-progress location-progress-validated" role="progressbar"
                                     aria-label="Validación de {{ $location['nombre'] }}"
                                     aria-valuenow="{{ $validatedPercentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $validatedBarWidth }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info mb-0">
                                No hay locaciones configuradas para este evento.
                            </div>
                        @endforelse
                    </div>
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar el elemento activo en el sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>