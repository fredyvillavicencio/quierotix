@extends('layouts.public')

@section('title', $event->nombre.' | '.config('app.name', 'QuieroTIX.com'))
@section('meta_description', 'Información completa del evento '.$event->nombre.': fecha, hora y lugar'.($event->direccion ? ' y dirección.' : '.'))

@section('content')
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        <i class="bi bi-house-door me-1"></i>Eventos
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ \Illuminate\Support\Str::limit($event->nombre, 60) }}
                </li>
            </ol>
        </nav>

        <section class="detail-hero mb-4">
            @if ($event->imagen_url)
                <img src="{{ $event->imagen_url }}"
                     alt="Portada del evento {{ $event->nombre }}"
                     class="detail-hero-img">
            @endif
            <div class="detail-hero-overlay p-4 p-md-5">
                <span class="badge rounded-pill hero-badge mb-3">
                    <i class="bi bi-calendar2-week me-1"></i>
                    {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
                    &middot;
                    {{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}
                </span>
                <h1 class="display-6 fw-bold mb-2">{{ $event->nombre }}</h1>
                <p class="mb-0 opacity-75">
                    <i class="bi bi-geo-alt me-1"></i>{{ $event->lugar ?: 'Lugar por confirmar' }}
                </p>
            </div>
        </section>

        <div class="row g-4 mb-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="h5 fw-semibold mb-4">
                            <i class="bi bi-info-circle me-2 text-brand"></i>Información del evento
                        </h2>
                        <dl class="row detail-list mb-0">

                            <dt class="col-sm-4">Nombre</dt>
                            <dd class="col-sm-8">{{ $event->nombre }}</dd>

                            <dt class="col-sm-4">Código</dt>
                            <dd class="col-sm-8">{{ $event->codigo_evento }}</dd>

                            <dt class="col-sm-4">Fecha</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}</dd>

                            <dt class="col-sm-4">Hora</dt>
                            <dd class="col-sm-8">{{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}</dd>

                            <dt class="col-sm-4">Lugar</dt>
                            <dd class="col-sm-8">{{ $event->lugar ?: '—' }}</dd>

                            @if ($event->direccion)
                                <dt class="col-sm-4">Dirección</dt>
                                <dd class="col-sm-8">{{ $event->direccion }}</dd>
                            @endif

                            <dt class="col-sm-4">Participantes registrados</dt>
                            <dd class="col-sm-8">{{ number_format($event->participants_count) }}</dd>

                            <dt class="col-sm-4">Tickets generados</dt>
                            <dd class="col-sm-8">{{ number_format($event->tickets_count) }}</dd>

                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">

                        @if ($event->imagen_url)
                            <img src="{{ $event->imagen_url }}"
                                 alt="Imagen para tickets del evento {{ $event->nombre }}"
                                 class="img-fluid rounded-3 border">
                        @else
                            <p class="text-muted small mb-0">Este evento no tiene imagen para tickets.</p>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        <section class="purchase-cta mb-4">
            <div class="p-4 p-md-5 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div>
                    <span class="badge rounded-pill hero-badge mb-3">
                        <i class="bi bi-ticket-perforated me-1"></i>Venta en línea
                    </span>
                    <h2 class="h3 fw-bold mb-2">Compra tus tickets</h2>
                    <p class="mb-2 opacity-75">
                        Elige cuántos tickets necesitas y recíbelos por correo en formato PDF.
                    </p>
                    <p class="mb-0 small opacity-75">
                        <i class="bi bi-people me-1"></i>
                        @if ($availableSeats > 0)
                            {{ number_format($availableSeats) }} {{ $availableSeats === 1 ? 'ticket disponible' : 'tickets disponibles' }}
                        @else
                            Sin tickets disponibles por el momento
                        @endif
                    </p>
                </div>
                <div class="text-lg-end">
                    @if ($availableSeats > 0)
                        <a href="{{ route('events.public.purchase.create', $event) }}"
                           class="btn btn-cta btn-lg px-4 px-lg-5 py-3">
                            <i class="bi bi-ticket-perforated-fill me-2"></i>Comprar Ticket
                        </a>
                    @else
                        <button type="button" class="btn btn-cta btn-lg px-4 px-lg-5 py-3" disabled>
                            <i class="bi bi-x-circle me-2"></i>Agotado
                        </button>
                    @endif
                </div>
            </div>
        </section>

    </div>
@endsection
