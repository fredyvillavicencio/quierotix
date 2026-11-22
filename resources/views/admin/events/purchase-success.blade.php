<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tickets generados - {{ $event->nombre }} - Sistema de Tickets</title>
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

        .success-card,
        .purchase-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .success-card {
            padding: 30px;
        }

        .purchase-summary {
            padding: 25px;
        }

        .ticket-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .ticket-row:last-of-type {
            border-bottom: none;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-list li {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.925rem;
        }

        .summary-list li:last-child {
            border-bottom: none;
        }

        .summary-list .label {
            color: #6c757d;
        }

        .summary-list .value {
            text-align: right;
            font-weight: 600;
            word-break: break-word;
        }

        .btn-brand {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            border: 0;
            color: #020201;
        }

        .btn-brand:hover,
        .btn-brand:focus {
            color: #020201;
            filter: brightness(1.03);
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            @include('layouts.sidebar')

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-check-circle"></i>
                                Compra Registrada
                            </h1>
                            <p class="mb-0 opacity-75">
                                Evento: {{ $event->nombre }} ({{ $event->codigo_evento }})
                            </p>
                        </div>
                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i>
                            Volver al Evento
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="success-card">
                            <h2 class="h4 mb-1">¡Los tickets están listos!</h2>
                            <p class="text-muted mb-4">
                                Generamos {{ number_format($participant->tickets->count()) }}
                                {{ $participant->tickets->count() === 1 ? 'ticket' : 'tickets' }}
                                para {{ $participant->full_name }}.
                            </p>

                            @if ($participant->email_sent)
                                <div class="alert alert-success small">
                                    <i class="bi bi-envelope-check me-1"></i>
                                    Enviamos una copia de los tickets a {{ $participant->email }}.
                                </div>
                            @else
                                <div class="alert alert-warning small">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Los tickets se generaron correctamente, pero no pudimos enviarlos por correo.
                                    Descárgalos desde esta página.
                                </div>
                            @endif

                            <h3 class="h6 text-uppercase text-muted fw-semibold mb-3">Descargar tickets</h3>

                            @forelse ($participant->tickets as $index => $ticket)
                                <div class="ticket-row">
                                    <div>
                                        <span class="badge bg-secondary me-2">{{ $index + 1 }}</span>
                                        <strong>{{ $ticket->download_label }}</strong>
                                    </div>
                                    <a href="{{ route('tickets.download.pdf', $ticket->codigo) }}"
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-download me-1"></i>Descargar PDF
                                    </a>
                                </div>
                            @empty
                                <div class="alert alert-warning mb-0">
                                    No se encontraron tickets generados. Contacta al organizador del evento.
                                </div>
                            @endforelse

                            @if ($participant->tickets->isNotEmpty())
                                <a href="{{ route('admin.participants.download.pdfs', $participant) }}"
                                   class="btn btn-brand w-100 mt-4 py-3 fw-semibold">
                                    <i class="bi bi-file-earmark-zip me-2"></i>Descargar todos en ZIP
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="purchase-summary">
                            <h2 class="h5 mb-3">Resumen de la compra</h2>

                            <ul class="summary-list">
                                <li>
                                    <span class="label">Nombre</span>
                                    <span class="value">{{ $participant->full_name }}</span>
                                </li>
                                <li>
                                    <span class="label">Correo</span>
                                    <span class="value">{{ $participant->email }}</span>
                                </li>
                                <li>
                                    <span class="label">Número ID</span>
                                    <span class="value">{{ $participant->carne ?: '—' }}</span>
                                </li>
                                <li>
                                    <span class="label">Evento</span>
                                    <span class="value">{{ $event->nombre }}</span>
                                </li>
                                <li>
                                    <span class="label">Fecha</span>
                                    <span class="value">
                                        {{ $event->fecha ? \Illuminate\Support\Carbon::parse($event->fecha)->format('d/m/Y') : 'Por definir' }}
                                    </span>
                                </li>
                                <li>
                                    <span class="label">Hora</span>
                                    <span class="value">
                                        {{ $event->hora ? \Illuminate\Support\Carbon::parse($event->hora)->format('H:i') : 'Por definir' }}
                                    </span>
                                </li>
                                <li>
                                    <span class="label">Lugar</span>
                                    <span class="value">{{ $event->lugar ?: 'Por definir' }}</span>
                                </li>
                                <li>
                                    <span class="label">Tickets</span>
                                    <span class="value">{{ $participant->tickets->count() }}</span>
                                </li>
                            </ul>

                            <div class="d-grid gap-2 mt-4">
                                <a href="{{ route('admin.events.purchase.create', $event) }}" class="btn btn-outline-success">
                                    <i class="bi bi-cart-plus me-1"></i>Crear otra compra
                                </a>
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>Volver al Evento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

