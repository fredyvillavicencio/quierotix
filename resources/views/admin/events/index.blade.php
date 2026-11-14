<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos - Sistema de Tickets</title>
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
        
        .badge-active {
            background-color: #28a745;
            color: white;
        }
        
        .badge-inactive {
            background-color: #6c757d;
            color: white;
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
                        <i class="bi bi-calendar-event"></i>
                        Eventos
                    </h1>
                </div>
                <a href="{{ route('admin.events.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Evento
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group search-box">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Buscar eventos..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Hora</th>
                            <th>Participantes</th>
                            <th>Tickets</th>
                            <th>PDFs</th>
                            <th>Emails</th>
                            <th>Estado</th>
                            <th width="200" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ $event->id }}</td>
                                <td>
                                    <a href="{{ route('admin.events.show', $event) }}" class="nav-link fw-semibold active" title="Ver">{{ $event->nombre }}</a>
                                    <span class="text-muted small">
                                        {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} - {{ $event->hora }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $event->participants_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $event->tickets_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    
                                @php
    
                                $bg_pdf = match(true) {
                                    $event->pdfs_generated == $event->tickets_count => "bg-success", 
                                    $event->pdfs_generated == 0 => "bg-danger",
                                    default => "bg-warning"
                                }
    
                                @endphp                                    
                                    <span class="badge {{ $bg_pdf }}">
                                        {{ $event->pdfs_generated ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                @php
    
                                $bg_email = match(true) {
                                    $event->emails_sent == $event->participants_count => "bg-success", 
                                    $event->emails_sent == 0 => "bg-danger",
                                    default => "bg-warning"
                                }
    
                                @endphp                                    
                                    <span class="badge {{ $bg_email }}">
                                        {{ $event->emails_sent ?? 0 }}
                                    </span>
                                </td>
                                
                                <td>
                                    @php
                                        $eventDate = \Carbon\Carbon::parse($event->fecha . ' ' . $event->hora);
                                        $isPast = $eventDate->isPast();
                                    @endphp
                                    <span class="badge {{ $isPast ? 'bg-secondary' : 'bg-success' }}">
                                        {{ $isPast ? 'Finalizado' : 'Activo' }}
                                    </span>
                                </td>
                                <td class="text-center action-buttons">
                                    <!--
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.events.statistics', $event) }}" class="btn btn-sm btn-primary" title="Estadísticas">
                                        <i class="bi bi-bar-chart"></i>
                                    </a>
                                    -->
                                    <a href="{{ route('admin.events.seat.map', $event) }}" class="btn btn-sm btn-primary" title="Mapa">
                                        <i class="bi bi-map"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!--
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este evento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-calendar-x display-4"></i>
                                        <h5 class="mt-3">No hay eventos registrados</h5>
                                        <p class="mb-0">Comienza creando tu primer evento</p>
                                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-circle"></i>
                                            Crear Primer Evento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($events->hasPages())
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Mostrando {{ $events->firstItem() }} - {{ $events->lastItem() }} de {{ $events->total() }} eventos
                        </div>
                        <nav>
                            {{ $events->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                window.location.href = '{{ route("admin.events.index") }}?search=' + encodeURIComponent(searchTerm);
            }
        });

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        // Filter functionality
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.textContent;
                // Implement filter logic here
                console.log('Filter by:', filter);
            });
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
