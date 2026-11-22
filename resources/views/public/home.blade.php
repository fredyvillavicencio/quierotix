@extends('layouts.public')

@section('title', 'Eventos | '.config('app.name', 'QuieroTIX.com'))
@section('meta_description', 'Consulta el listado de eventos disponibles: fecha, hora, lugar y toda su información.')

@section('content')
    <div class="container">

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
            <div>
                <h2 class="h4 fw-semibold mb-1">Eventos</h2>
            </div>
        </div>

        @if ($events->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-calendar-x display-5 text-muted"></i>
                    <h2 class="h5 fw-semibold mt-3">No hay eventos disponibles</h2>
                    <p class="text-muted mb-0">Vuelve a consultar más adelante para conocer los próximos eventos.</p>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach ($events as $event)
                    <div class="col">
                        <article class="card event-card h-100">
                            @if ($event->imagen_url)
                                <img src="{{ $event->imagen_url }}"
                                     alt="Portada del evento {{ $event->nombre }}"
                                     class="event-cover" loading="lazy">
                            @else
                                <div class="event-cover-placeholder">
                                    <i class="bi bi-image"></i>
                                    <span>Sin portada</span>
                                </div>
                            @endif
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <span class="badge date-badge">
                                        <i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
                                    </span>
                                    <span class="badge time-badge">
                                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}
                                    </span>
                                </div>
                                <h3 class="h5 card-title fw-semibold mb-2">
                                    <a href="{{ route('events.public.purchase.create', $event) }}" class="stretched-link text-decoration-none">
                                        {{ $event->nombre }}
                                    </a>
                                </h3>
                                <p class="text-muted small mb-1">
                                    {{ $event->lugar ?: 'Lugar por confirmar' }}
                                </p>
                                @if ($event->direccion)
                                    <p class="text-muted small mb-3">
                                        <i class="bi bi-geo-alt me-1"></i>{{ \Illuminate\Support\Str::limit($event->direccion, 80) }}
                                    </p>
                                @endif
                                <div class="mt-auto pt-3 border-top">
                                    <a href="{{ route('events.public.purchase.create', $event) }}"
                                       class="btn btn-brand w-100 py-2 fw-semibold event-card-cta">
                                        <i class="bi bi-ticket-perforated-fill me-2"></i>Comprar tickets
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
