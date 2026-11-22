<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->nombre }} - Sistema de Tickets</title>
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
        
        .card-participants {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .card-tickets {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        
        .card-pdfs {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }
        
        .card-validation {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 100px;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        
        .tab-content {
            background: white;
            border-radius: 0 0 10px 10px;
            border: 1px solid #dee2e6;
            border-top: none;
            padding: 20px;
        }
        
        .nav-tabs .nav-link {
            border: 1px solid transparent;
            border-radius: 10px 10px 0 0;
            padding: 12px 20px;
            font-weight: 600;
        }
        
        .nav-tabs .nav-link.active {
            background-color: white;
            border-color: #dee2e6 #dee2e6 white;
            color: #495057;
        }
        
        .event-details {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
        }
        
        .detail-value {
            color: #6c757d;
            text-align: right;
        }
        
        .badge-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
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
                        <i class="bi bi-calendar-event"></i>
                        {{ $event->nombre }}
                    </h1>
                    <p class="mb-0 opacity-75">
                        Fecha: {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} a las {{ $event->hora }}
                    </p>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-light">
                        <i class="bi bi-pencil"></i>
                        Editar
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>

              <!-- Display Session Errors -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>¡Éxito!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Advertencia:</strong> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Información:</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="error-list mt-2 mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

        <div class="row mb-4">
            <div class="col-md-3">
                            <a href="{{ route('admin.events.purchase.create', $event) }}" class="btn btn-success w-100">
                                <i class="bi bi-cart-plus"></i>
                                Crear Compra
                            </a>
            </div>
            <div class="col-md-3">
                            <form method="POST" action="{{ route('admin.events.send.emails', $event) }}" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-secondary w-100" onclick="return confirm('¿Estás seguro de que deseas enviar emails a todos los participantes de este evento?')">
                                    <i class="bi bi-envelope"></i>
                                    Enviar Emails
                                </button>
                            </form>
            </div>
            <div class="col-md-3">
                            <a href="{{ route('admin.events.statistics', $event) }}" class="btn btn-info w-100">
                                <i class="bi bi-graph-up"></i>
                                Estadísticas
                            </a>
            </div>
            <div class="col-md-3">
                            <a href="{{ route('admin.events.seat.map', $event) }}" class="btn btn-primary w-100">
                                <i class="bi bi-map"></i>
                                Mapa
                            </a>
            </div>
        </div>


        <!-- Main Content -->
        <div class="row">
            <!-- Left Column: Event Details -->
            <div class="col-lg-4 mb-4">
                <div class="event-details">
                    <h5 class="mb-4">
                        <i class="bi bi-info-circle"></i>
                        Detalles del Evento
                    </h5>
                    
                    <div class="detail-item">
                        <span class="detail-label">Estado</span>
                        <span class="detail-value">
                            @php
                                $eventDate = \Carbon\Carbon::parse($event->fecha . ' ' . $event->hora);
                                $isPast = $eventDate->isPast();
                            @endphp
                            <span class="small badge-status {{ $isPast ? 'bg-secondary' : 'bg-success' }} text-white">
                                {{ $isPast ? 'Finalizado' : 'Activo' }}
                            </span>
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">PDFs Generados</span>
                        <span class="detail-value">
                            @php

                            $bg_pdf = match(true) {
                                $pdfsGenerated == $tickets->total() => "bg-success", 
                                $pdfsGenerated == 0 => "bg-danger",
                                default => "bg-warning"
                            }

                            @endphp
                            <span class="small badge-status {{ $bg_pdf }} text-white">
                                {{ $pdfsGenerated }}
                            </span>
                        </span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Emails Enviados</span>
                        <span class="detail-value">
                            @php

                            $bg_email = match(true) {
                                $totalEmailSent == $participants->total() => "bg-success", 
                                $totalEmailSent == 0 => "bg-danger",
                                default => "bg-warning"
                            }

                            @endphp
                            <span class="small badge-status {{ $bg_email }} text-white">
                                {{ $totalEmailSent }}
                            </span>
                        </span>
                    </div>



                    
                </div>
            </div>

            <!-- Right Column: Tabs -->
            <div class="col-lg-8">
                <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="participants-tab" data-bs-toggle="tab" data-bs-target="#participants" type="button" role="tab">
                            <i class="bi bi-people"></i>
                            Participantes ({{ $participants->total() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tickets-tab" data-bs-toggle="tab" data-bs-target="#tickets" type="button" role="tab">
                            <i class="bi bi-ticket-perforated"></i>
                            Tickets ({{ $tickets->total() }})
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="eventTabsContent">
                    <!-- Participants Tab -->
                    <div class="tab-pane fade show active" id="participants" role="tabpanel">
                        @if($participants->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Email Enviado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($participants as $participant)
                                            <tr>
                                                <td>{{ $participant->id }}</td>
                                                <td>{{ $participant->nombre }} {{ $participant->apellido }}</td>
                                                <td>{{ $participant->email }}</td>
                                                <td>
                                                    @if($participant->email_sent)
                                                        <span class="badge bg-success">Enviado</span>
                                                    @else
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            @if($participants->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="small text-muted">
                                        Mostrando {{ $participants->firstItem() }} - {{ $participants->lastItem() }} de {{ $participants->total() }} participantes
                                    </div>
                                    <nav>
                                        {{ $participants->links() }}
                                    </nav>
                                </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-people"></i>
                                </div>
                                <h5 class="mt-3">No hay participantes registrados</h5>
                                <p class="text-muted mb-4">Importa un archivo CSV para agregar participantes a este evento</p>
                                <a href="{{ route('admin.events.import.form', $event) }}" class="btn btn-primary">
                                    <i class="bi bi-upload"></i>
                                    Importar CSV
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Tickets Tab -->
                    <div class="tab-pane fade" id="tickets" role="tabpanel">
                        @if($tickets->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Participante</th>
                                            <th>Ubicación</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tickets as $ticket)
                                            <tr>
                                                <td>
                                                    {{ $ticket->participant->nombre ?? 'N/A' }}
                                                </td>
                                                <td>
                                                    {{ $ticket->locacion }} - Asiento {{ $ticket->asiento }}
                                                </td>
                                                <td>
                                                    @if($ticket->validado)
                                                        <span class="badge bg-success">Validado</span>
                                                    @else
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            @if($tickets->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="small text-muted">
                                        Mostrando {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} de {{ $tickets->total() }} tickets
                                    </div>
                                    <nav>
                                        {{ $tickets->links() }}
                                    </nav>
                                </div>
                            @endif
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="bi bi-ticket-perforated"></i>
                                </div>
                                <h5 class="mt-3">No hay tickets generados</h5>
                                <p class="text-muted mb-4">Los tickets se generan automáticamente al importar participantes</p>
                                <a href="{{ route('admin.events.import.form', $event) }}" class="btn btn-primary">
                                    <i class="bi bi-upload"></i>
                                    Importar CSV
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tabs
        const triggerTabList = document.querySelectorAll('#eventTabs button')
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', event => {
                event.preventDefault()
                tabTrigger.show()
            })
        });

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
