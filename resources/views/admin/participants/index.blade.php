<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participantes - Sistema de Tickets</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
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
        
        .badge-event {
            background-color: #15d403;
            color: #020201;
        }
        
        .badge-tickets {
            background-color: #4facfe;
            color: white;
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 80px;
        }
        
        .search-box {
            max-width: 620px;
        }
        
        .pagination-container {
            background: #f8f9fa;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
        
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .email-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        /* Estilos personalizados para la paginación de Laravel - Selectores ultra-específicos */
        .pagination {
            display: flex;
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        
        .pagination .page-item {
            margin: 0 2px;
        }
        
        .pagination .page-link {
            position: relative;
            display: block;
            padding: 0.375rem 0.75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: #0d6efd;
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .pagination .page-link:hover {
            z-index: 2;
            color: #0a58ca;
            background-color: #e9ecef;
            border-color: #dee2e6;
            transform: translateY(-1px);
        }
        
        .pagination .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        
        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
            opacity: 0.6;
        }
        
        /* Selectores ultra-específicos para sobrescribir Tailwind w-5 h-5 */
        nav .pagination svg.w-5,
        nav .pagination svg.h-5,
        .pagination-container nav .pagination svg.w-5,
        .pagination-container nav .pagination svg.h-5,
        .pagination .page-link svg.w-5,
        .pagination .page-link svg.h-5,
        .pagination .page-item .page-link svg.w-5,
        .pagination .page-item .page-link svg.h-5,
        .pagination svg[class*="w-5"],
        .pagination svg[class*="h-5"] {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            vertical-align: middle;
        }
        
        /* Asegurar que se aplique incluso con !important de Tailwind */
        nav .pagination svg,
        .pagination-container nav .pagination svg,
        .pagination .page-link svg,
        .pagination .page-item .page-link svg {
            width: 16px !important;
            height: 16px !important;
        }
        
        /* Estilos específicos para las flechas */
        .pagination .page-link .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
        
        /* Ajustar espaciado de los iconos */
        .pagination .page-link:has(svg) {
            padding: 0.375rem 0.5rem;
        }
        
        /* Mejorar diseño responsive */
        @media (max-width: 768px) {
            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .pagination .page-item {
                margin: 2px;
            }
            
            .pagination .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }
            
            /* Selectores responsive también ultra-específicos */
            @media (max-width: 768px) {
                nav .pagination svg.w-5,
                nav .pagination svg.h-5,
                .pagination-container nav .pagination svg.w-5,
                .pagination-container nav .pagination svg.h-5,
                .pagination .page-link svg.w-5,
                .pagination .page-link svg.h-5,
                .pagination .page-item .page-link svg.w-5,
                .pagination .page-item .page-link svg.h-5 {
                    width: 14px !important;
                    height: 14px !important;
                    max-width: 14px !important;
                    max-height: 14px !important;
                }
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
                                <i class="bi bi-people"></i>
                                Participantes
                            </h1>
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


                <!-- Search and Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('admin.participants.index') }}" class="search-form">
                            <div class="input-group search-box">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       id="searchInput" 
                                       placeholder="Nombre apellido o email..." 
                                       value="{{ $searchTerm ?? '' }}"
                                       autocomplete="off">
                                <select name="event_id" id="eventFilter" class="form-select" style="max-width: 240px;">
                                    <option value="">Todos los eventos</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected((int) $eventId === $event->id)>
                                            {{ $event->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                                    Buscar
                                </button>
                                @if(!empty($searchTerm) || $eventId)
                                <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-danger" title="Limpiar filtros">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                                @endif
                            </div>
                        </form>
                        
                        @if(!empty($searchTerm) || $eventId)
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                @if($eventId)
                                    Evento: "<strong>{{ $events->firstWhere('id', $eventId)?->nombre ?? 'Desconocido' }}</strong>"
                                @endif
                                @if(!empty($searchTerm))
                                    @if($eventId) &middot; @endif
                                    Buscando: "<strong>{{ $searchTerm }}</strong>"
                                @endif
                                @if($participants->total() > 0)
                                    - Encontrados: {{ $participants->total() }} participante(s)
                                @else
                                    - Sin resultados
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>

                    <!--
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-filter"></i>
                                Filtros
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Todos</a></li>
                                <li><a class="dropdown-item" href="#">Con Tickets</a></li>
                                <li><a class="dropdown-item" href="#">Sin Tickets</a></li>
                                <li><a class="dropdown-item" href="#">Por Evento</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-download"></i>
                            Exportar
                        </button>
                    </div>
                    -->

                </div>

                <!-- Participants Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Tickets</th>
                                    <th>PDFs</th>
                                    <th>Email</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($participants as $participant)
                                    <tr>
                                        <td>{{ $participant->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-semibold">{{ $participant->nombre }} {{ $participant->apellido }}</div>
                                                    <small class="text-muted">{{ $participant->email }}</small><br>
                                                    <small class="text-muted">{{ $participant->carne }}</small><br>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $participant->event->nombre }}<br>
                                            {{ \Carbon\Carbon::parse($participant->event->fecha)->format('d/m/Y') }} - {{ $participant->event->hora }}
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $participant->tickets_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ is_array($participant->pdf_paths) ? count($participant->pdf_paths) : 0 }}
                                            </span>
                                        </td>
                                        <td>
                                                    @if($participant->email_sent)
                                                        <span class="badge bg-success">Enviado</span>
                                                    @else
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    @endif
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="{{ route('admin.participants.download.pdfsadmin', $participant->id) }}" class="btn btn-sm btn-secondary" title="PDFs">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            
                                            @if($participant->tickets_count > 0 && !empty($participant->pdf_paths))
                                                <form method="POST" action="{{ route('admin.participants.send.email', $participant->id) }}" 
                                                      class="d-inline" onsubmit="return confirm('¿Enviar correo a {{ $participant->email }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Enviar correo con tickets">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            @if(!empty($searchTerm) || $eventId)
                                                <div class="text-muted">
                                                    <i class="bi bi-search display-4"></i>
                                                    <h5 class="mt-3">No hay participantes que coincidan con los filtros</h5>
                                                    <p class="mb-0">Prueba con otro apellido o selecciona otro evento</p>
                                                    <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-secondary mt-3">
                                                        <i class="bi bi-x-circle"></i>
                                                        Limpiar filtros
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-people display-4"></i>
                                                    <h5 class="mt-3">No hay participantes registrados</h5>
                                                    <p class="mb-0">Los participantes se importan desde archivos CSV</p>
                                                    <a href="{{ route('admin.events.index') }}" class="btn btn-primary mt-3">
                                                        <i class="bi bi-calendar-event"></i>
                                                        Ver Eventos para Importar
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($participants->hasPages())
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                Mostrando {{ $participants->firstItem() }} - {{ $participants->lastItem() }} de {{ $participants->total() }} participantes
                            </div>
                            <nav>
                                {{ $participants->links() }}
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions 
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-charge"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.events.index') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i>
                                    Importar CSV
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.participants.export') }}" class="btn btn-success w-100">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    Exportar PDFs
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-warning w-100">
                                    <i class="bi bi-envelope"></i>
                                    Enviar Emails
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.participants.export') }}" class="btn btn-info w-100">
                                    <i class="bi bi-download"></i>
                                    Exportar Datos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                -->

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality - Mejorada para formulario
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-form');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            
            const eventFilter = document.getElementById('eventFilter');

            // Recargar el listado automáticamente al cambiar de evento
            if (eventFilter) {
                eventFilter.addEventListener('change', function() {
                    if (searchForm) {
                        searchForm.submit();
                    }
                });
            }

            // Enviar formulario al hacer clic en buscar
            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    const hasSearch = searchInput ? searchInput.value.trim() !== '' : false;
                    const hasEvent = eventFilter ? eventFilter.value !== '' : false;

                    if (!hasSearch && !hasEvent) {
                        e.preventDefault();
                        if (searchInput) {
                            searchInput.focus();
                        }
                    }
                });
            }
            
            // Enviar formulario al presionar Enter
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const hasEvent = eventFilter ? eventFilter.value !== '' : false;

                        if (!this.value.trim() && !hasEvent) {
                            e.preventDefault();
                            return;
                        }
                        // El formulario se enviará automáticamente
                    }
                });
            }
            
            // Auto-focus en el campo de búsqueda si hay término de búsqueda
            @if(!empty($searchTerm))
                if (searchInput) {
                    searchInput.focus();
                    // Mover el cursor al final del texto
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            @endif
            
            // Activar el elemento activo en el sidebar
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
            
            // Mejorar experiencia de usuario: mostrar loading al buscar
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...';
                        button.disabled = true;
                        
                        // Restaurar después de 2 segundos (por si hay error)
                        setTimeout(function() {
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }, 2000);
                    }
                });
            }
        });
    </script>
</body>
</html>
