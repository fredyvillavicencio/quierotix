<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDFs Generados - Sistema de Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-start: #15d403;
            --brand-mid: #a7f801;
            --brand-end: #fcef03;
            --brand-ink: #020201;
            --brand-link: #0b6b00;
            --brand-link-hover: #064d00;
            --brand-soft: #f4ffe0;
            --brand-border: #dcefc2;
            --brand-gradient: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
        }

        body {
            background-color: var(--brand-soft);
            color: #1f2937;
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #0b3d0b 0%, #042104 100%);
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
            background: var(--brand-gradient);
            color: var(--brand-ink);
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .page-header {
            background:
                radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .38), transparent 45%),
                var(--brand-gradient);
            color: var(--brand-ink);
            padding: 20px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 10px 24px rgba(11, 107, 0, .18);
        }
        
        .table-container {
            background: white;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(31, 41, 55, .08);
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--brand-soft);
            border-bottom: 2px solid var(--brand-border);
            font-weight: 600;
            color: var(--brand-link);
        }
        
        .badge-sent {
            background-color: var(--brand-link);
            color: white;
        }
        
        .badge-pending {
            background-color: var(--brand-end);
            color: var(--brand-ink);
        }
        
        .action-buttons .btn {
            margin: 2px;
            min-width: 80px;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        .pagination-container {
            background: #f8fff2;
            padding: 15px;
            border-top: 1px solid var(--brand-border);
        }
        
        .pdf-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .file-size {
            font-size: 12px;
            color: #6c757d;
        }
        
        .pdf-preview {
            width: 100px;
            height: 140px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #dc3545;
        }
        
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--brand-gradient);
            border-radius: 4px;
        }

        .btn-primary,
        .btn-success {
            background: var(--brand-gradient);
            border-color: transparent;
            color: var(--brand-ink);
            font-weight: 600;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-success:hover,
        .btn-success:focus {
            background: var(--brand-gradient);
            border-color: transparent;
            color: var(--brand-ink);
            filter: brightness(1.03);
        }

        .btn-info {
            background: var(--brand-link);
            border-color: var(--brand-link);
            color: #fff;
        }

        .btn-info:hover,
        .btn-info:focus {
            background: var(--brand-link-hover);
            border-color: var(--brand-link-hover);
            color: #fff;
        }

        .btn-warning {
            background: var(--brand-end);
            border-color: var(--brand-end);
            color: var(--brand-ink);
        }

        body a {
            color: var(--brand-link);
        }

        body a:hover,
        body a:focus {
            color: var(--brand-link-hover);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-ticket-perforated"></i>
                            Sistema de Tickets
                        </h4>
                        <p class="text-muted small">Panel de Administración</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.events.index') }}">
                                <i class="bi bi-calendar-event"></i>
                                Eventos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.participants.index') }}">
                                <i class="bi bi-people"></i>
                                Participantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.tickets.index') }}">
                                <i class="bi bi-ticket-perforated"></i>
                                Tickets
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="#">
                                <i class="bi bi-file-pdf"></i>
                                PDFs Generados
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-bar-chart"></i>
                                Estadísticas
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="{{ route('dashboard') }}">
                                <i class="bi bi-arrow-left"></i>
                                Volver al Usuario
                            </a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100">
                                    <i class="bi bi-box-arrow-right"></i>
                                    Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                    
                    <div class="mt-5 text-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i>
                            Sistema v1.0.0
                        </small>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-file-pdf"></i>
                                PDFs Generados
                            </h1>
                            <p class="mb-0 opacity-75">Administra todos los PDFs generados del sistema</p>
                        </div>
                        <a href="#" class="btn btn-light">
                            <i class="bi bi-plus-circle"></i>
                            Generar PDFs
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
                            <input type="text" class="form-control" placeholder="Buscar PDFs..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                Buscar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-filter"></i>
                                Filtros
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Todos</a></li>
                                <li><a class="dropdown-item" href="#">Enviados</a></li>
                                <li><a class="dropdown-item" href="#">Pendientes</a></li>
                                <li><a class="dropdown-item" href="#">Por Evento</a></li>
                                <li><a class="dropdown-item" href="#">Por Participante</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-download"></i>
                            Exportar Lista
                        </button>
                    </div>
                </div>

                <!-- PDFs Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>PDF</th>
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Tipo</th>
                                    <th>Tamaño</th>
                                    <th>Generado</th>
                                    <th>Estado</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    // Datos de ejemplo para demostración
                                    $pdfs = [
                                        (object)[
                                            'id' => 1,
                                            'nombre' => 'ticket_juan_perez_1.pdf',
                                            'participant' => (object)['nombre' => 'Juan', 'apellido' => 'Pérez'],
                                            'event' => (object)['nombre' => 'Conferencia Tech 2024'],
                                            'tipo' => 'Ticket 1',
                                            'tamano' => '2.4 MB',
                                            'generado' => now()->subDays(3),
                                            'enviado' => true,
                                            'ruta' => '/pdfs/ticket_001.pdf'
                                        ],
                                        (object)[
                                            'id' => 2,
                                            'nombre' => 'ticket_maria_gonzalez_1.pdf',
                                            'participant' => (object)['nombre' => 'María', 'apellido' => 'González'],
                                            'event' => (object)['nombre' => 'Conferencia Tech 2024'],
                                            'tipo' => 'Ticket 1',
                                            'tamano' => '2.3 MB',
                                            'generado' => now()->subDays(2),
                                            'enviado' => true,
                                            'ruta' => '/pdfs/ticket_002.pdf'
                                        ],
                                        (object)[
                                            'id' => 3,
                                            'nombre' => 'ticket_carlos_rodriguez_1.pdf',
                                            'participant' => (object)['nombre' => 'Carlos', 'apellido' => 'Rodríguez'],
                                            'event' => (object)['nombre' => 'Conferencia Tech 2024'],
                                            'tipo' => 'Ticket 1',
                                            'tamano' => '2.5 MB',
                                            'generado' => now()->subDays(1),
                                            'enviado' => false,
                                            'ruta' => '/pdfs/ticket_003.pdf'
                                        ],
                                        (object)[
                                            'id' => 4,
                                            'nombre' => 'ticket_ana_martinez_1.pdf',
                                            'participant' => (object)['nombre' => 'Ana', 'apellido' => 'Martínez'],
                                            'event' => (object)['nombre' => 'Conferencia Tech 2024'],
                                            'tipo' => 'Ticket 1',
                                            'tamano' => '2.2 MB',
                                            'generado' => now()->subHours(12),
                                            'enviado' => true,
                                            'ruta' => '/pdfs/ticket_004.pdf'
                                        ],
                                        (object)[
                                            'id' => 5,
                                            'nombre' => 'ticket_luis_hernandez_1.pdf',
                                            'participant' => (object)['nombre' => 'Luis', 'apellido' => 'Hernández'],
                                            'event' => (object)['nombre' => 'Conferencia Tech 2024'],
                                            'tipo' => 'Ticket 1',
                                            'tamano' => '2.6 MB',
                                            'generado' => now()->subHours(6),
                                            'enviado' => false,
                                            'ruta' => '/pdfs/ticket_005.pdf'
                                        ],
                                    ];
                                @endphp
                                
                                @forelse($pdfs as $pdf)
                                    <tr>
                                        <td>{{ $pdf->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="pdf-icon me-3">
                                                    <i class="bi bi-file-pdf"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $pdf->nombre }}</div>
                                                    <small class="text-muted">ID: {{ $pdf->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $pdf->participant->nombre }} {{ $pdf->participant->apellido }}</div>
                                            <small class="text-muted">Participante ID: {{ $pdf->id }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $pdf->event->nombre }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $pdf->tipo }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="file-size">{{ $pdf->tamano }}</div>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($pdf->generado)->format('d/m/Y H:i') }}
                                        </td>
                                        <td>
                                            @if($pdf->enviado)
                                                <span class="badge badge-sent">
                                                    <i class="bi bi-check-circle"></i>
                                                    Enviado
                                                </span>
                                            @else
                                                <span class="badge badge-pending">
                                                    <i class="bi bi-clock"></i>
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="{{ $pdf->ruta }}" target="_blank" class="btn btn-sm btn-danger" title="Ver PDF">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ $pdf->ruta }}" download class="btn btn-sm btn-primary" title="Descargar">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            @if(!$pdf->enviado)
                                                <a href="#" class="btn btn-sm btn-success" title="Enviar por Email">
                                                    <i class="bi bi-envelope"></i>
                                                </a>
                                            @else
                                                <a href="#" class="btn btn-sm btn-secondary" title="Reenviar">
                                                    <i class="bi bi-envelope-arrow-up"></i>
                                                </a>
                                            @endif
                                            <form action="#" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este PDF?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning" title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-file-pdf display-4"></i>
                                                <h5 class="mt-3">No hay PDFs generados</h5>
                                                <p class="mb-0">Los PDFs se generan automáticamente al crear tickets</p>
                                                <a href="#" class="btn btn-primary mt-3">
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                    Generar PDFs
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                Mostrando 1 - 5 de 35 PDFs
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">Anterior</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">4</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">5</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Total PDFs</h6>
                                        <h2 class="card-title">35</h2>
                                    </div>
                                    <i class="bi bi-file-pdf display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">PDFs Enviados</h6>
                                        <h2 class="card-title">25</h2>
                                    </div>
                                    <i class="bi bi-check-circle display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">PDFs Pendientes</h6>
                                        <h2 class="card-title">10</h2>
                                    </div>
                                    <i class="bi bi-clock display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Espacio Usado</h6>
                                        <h2 class="card-title">84 MB</h2>
                                    </div>
                                    <i class="bi bi-hdd display-6 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
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
                                <a href="#" class="btn btn-primary w-100">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    Generar Todos
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-success w-100">
                                    <i class="bi bi-envelope"></i>
                                    Enviar Todos
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-warning w-100">
                                    <i class="bi bi-download"></i>
                                    Descargar ZIP
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-info w-100">
                                    <i class="bi bi-printer"></i>
                                    Imprimir Lote
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                // Implement search logic here
                console.log('Searching for:', searchTerm);
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
