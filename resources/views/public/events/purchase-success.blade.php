@extends('layouts.public')

@section('title', 'Tus tickets | '.$event->nombre)
@section('meta_description', 'Descarga los tickets generados para '.$event->nombre.'.')

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        Eventos
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Tus tickets</li>
            </ol>
        </nav>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-md-5 text-center">
                <div class="success-icon mb-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="h3 fw-bold mb-2">¡Tus tickets están listos!</h1>
                <p class="text-muted mb-0">
                    Generamos {{ number_format($participant->tickets->count()) }}
                    {{ $participant->tickets->count() === 1 ? 'ticket' : 'tickets' }}
                    para {{ $participant->full_name }}.
                </p>
                        @if ($participant->email_sent)
                            <div class="alert alert-success small mt-4 mb-0">
                                <i class="bi bi-envelope-check me-1"></i>
                                Enviamos una copia de tus tickets a {{ $participant->email }}. Si no lo ves en tu bandeja de entrada en unos minutos, revisa la carpeta de spam o correo no deseado.
                            </div>
                        @else
                            <div class="alert alert-warning small mt-4 mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Tus tickets se generaron correctamente, pero no pudimos enviarlos por correo.
                                Descárgalos desde esta página.
                            </div>
                        @endif
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-1">Descarga tus Tickets</h2>

                        @forelse ($participant->tickets as $index => $ticket)
                            <div class="ticket-row border-0 d-flex flex-wrap justify-content-between align-items-center gap-2" style="padding: .425rem 0;">
                                <a href="{{ route('tickets.download.pdf', $ticket->codigo) }}"
                                   class="btn btn-brand w-100 py-2 fw-semibold">
                                    <i class="bi bi-download me-2"></i>Descargar {{ $ticket->download_label }}
                                </a>
                            </div>
                        @empty
                            <div class="alert alert-warning mb-0">
                                No se encontraron tickets generados. Contacta al organizador del evento.
                            </div>
                        @endforelse

                        @if ($participant->tickets->count() > 2)
                            <a href="{{ route('admin.participants.download.pdfs', $participant) }}"
                               class="btn btn-brand w-100 mt-5 py-2 fw-semibold">
                                <i class="bi bi-file-earmark-zip me-2"></i>Descargar todos en ZIP
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h6 text-muted fw-semibold mb-3">Resumen de la compra</h2>
                        <dl class="row small mb-0">

                            <dt class="col-5 mb-0">Tickets</dt>
                            <dd class="col-7 mb-0">{{ $participant->tickets->count() }}</dd>

                            <dt class="col-5">Evento</dt>
                            <dd class="col-7">{{ $event->nombre }}</dd>

                            <dt class="col-5">Fecha</dt>
                            <dd class="col-7">
                                {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
                                &middot;
                                {{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}
                            </dd>

                            <dt class="col-5">Lugar</dt>
                            <dd class="col-7">{{ $event->lugar ?: 'Por confirmar' }}</dd>

                            <dt class="col-5">&nbsp;</dt>
                            <dd class="col-7">&nbsp;</dd>                           

                            <dt class="col-5">Nombre</dt>
                            <dd class="col-7">{{ $participant->full_name }}</dd>

                            <dt class="col-5">Correo</dt>
                            <dd class="col-7 text-break">{{ $participant->email }}</dd>

                            @if ($participant->carne)
                                <dt class="col-5">DPI</dt>
                                <dd class="col-7">{{ $participant->carne }}</dd>
                            @endif

                            <dt class="col-5">Teléfono</dt>
                            <dd class="col-7 text-break">{{ $participant->telefono }}</dd>

                        </dl>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Volver a Eventos
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

