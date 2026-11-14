<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets - Sistema de Tickets</title>
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
        
        .badge-validated {
            background-color: #28a745;
            color: white;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 80px;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        .pagination-container {
            background: #f8f9fa;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
        
        .qr-code {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #495057;
        }
        
        .seat-badge {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .ticket-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #495057;
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
                                <i class="bi bi-ticket-perforated"></i>
                                Tickets
                            </h1>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('admin.tickets.index') }}" class="input-group search-box">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   placeholder="Nombre o apellido" 
                                   id="searchInput"
                                   name="search"
                                   value="{{ $searchTerm ?? '' }}">
                            <button class="btn btn-outline-primary" type="submit" id="searchButton">
                                Buscar
                            </button>
                            @if(isset($searchTerm) && $searchTerm)
                                <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary" type="button">
                                    Limpiar
                                </a>
                            @endif
                        </form>
                        @if(isset($searchTerm) && $searchTerm)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    Mostrando resultados para: "<strong>{{ $searchTerm }}</strong>"
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
                                <li><a class="dropdown-item" href="#">Validados</a></li>
                                <li><a class="dropdown-item" href="#">Pendientes</a></li>
                                <li><a class="dropdown-item" href="#">Por Evento</a></li>
                                <li><a class="dropdown-item" href="#">Por Participante</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-download"></i>
                            Exportar
                        </button>
                    </div>
                    -->
                </div>

                <!-- Tickets Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <!--
                                    <th>Código QR <i class="bi bi-qr-code"></i></th>
                                    -->
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Ticket</th>
                                    <th>Estado</th>
                                    <th>Validado</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ticket->participant->nombre }} {{ $ticket->participant->apellido }}</div>
                                            <small class="text-muted">ID: {{ $ticket->participant->id }}</small>
                                        </td>
                                        <td>
                                            
                                            {{ $ticket->event->nombre }}<br>
                                            {{ \Carbon\Carbon::parse($ticket->event->fecha)->format('d/m/Y') }} - {{ $ticket->event->hora }}
                                            
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $ticket->location_label }}</div>
                                        </td>
                                        <td>
                                            @if($ticket->validado)
                                                <span class="badge badge-validated">
                                                    <i class="bi bi-check-circle"></i>
                                                    Validado
                                                </span>
                                            @else
                                                <span class="badge badge-pending">
                                                    <i class="bi bi-clock"></i>
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $ticket->contador_validaciones }}
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            @if(!empty($ticket->archivo_pdf))

                                            <!--
                                                <a href="{{ route('admin.tickets.preview.pdf', $ticket->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Descargar PDF"
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            -->

                                                <a href="{{ route('admin.tickets.download.pdfadmin', $ticket->id) }}" 
                                                   class="btn btn-sm btn-secondary" 
                                                   title="Descargar PDF"
                                                   target="_blank">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-ticket-perforated display-4"></i>
                                                <h5 class="mt-3">No hay tickets generados</h5>
                                                <p class="mb-0">Los tickets se generan automáticamente al importar participantes</p>
                                                <a href="{{ route('admin.events.index') }}" class="btn btn-primary mt-3">
                                                    <i class="bi bi-calendar-event"></i>
                                                    Ver Eventos para Importar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tickets->hasPages())
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                Mostrando {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} de {{ $tickets->total() }} tickets
                            </div>
                            <nav>
                                {{ $tickets->links() }}
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Focus on search input when page loads if there's a search term
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = "{{ $searchTerm ?? '' }}";
            
            // Focus on search input if there's a search term
            if (searchTerm && searchInput) {
                searchInput.focus();
                searchInput.setSelectionRange(searchTerm.length, searchTerm.length);
            }
            
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
            
            // Clear search with Escape key
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && this.value) {
                        this.value = '';
                        // Optionally submit the form to clear search immediately
                        // this.form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>
