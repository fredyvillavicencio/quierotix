@extends('layouts.public')

@section('title', 'Sorteo de regalos | '.config('app.name', 'QuieroTIX.com'))
@section('meta_description', 'Selecciona el evento para realizar el sorteo de un ticket de regalo.')

@section('content')
    <div class="container">
        @if (session('error'))
            <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill mt-1" aria-hidden="true"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-3">
            <div>
                <span class="badge date-badge mb-2">
                    <i class="bi bi-gift me-1" aria-hidden="true"></i>Sorteo de regalos
                </span>
                <h1 class="h3 fw-semibold mb-1">Elige un evento</h1>
                <p class="text-muted mb-0">
                    Selecciona el evento para el que quieres sortear un ticket de regalo.
                </p>
            </div>
            <a href="{{ route('home') }}" class="btn btn-outline-brand">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a eventos
            </a>
        </div>

        @if ($events->isEmpty())
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-ticket-perforated display-5 text-muted" aria-hidden="true"></i>
                    <h2 class="h5 fw-semibold mt-3">No hay eventos con tickets</h2>
                    <p class="text-muted mb-3">
                        Cuando existan tickets registrados podrás realizar el sorteo de regalos.
                    </p>
                    <a href="{{ route('home') }}" class="btn btn-brand">
                        <i class="bi bi-calendar-event me-2" aria-hidden="true"></i>Ver eventos
                    </a>
                </div>
            </div>
        @else
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                @foreach ($events as $event)
                    <div class="col">
                        <form method="POST" action="{{ route('gifts.index') }}" class="h-100">
                            @csrf
                            <input type="hidden" name="event_id" value="{{ $event->id }}">

                            <article class="card event-card h-100">
                                @if ($event->imagen_url)
                                    <img src="{{ $event->imagen_url }}"
                                         alt="Portada del evento {{ $event->nombre }}"
                                         class="event-cover" loading="lazy">
                                @else
                                    <div class="event-cover-placeholder">
                                        <i class="bi bi-image" aria-hidden="true"></i>
                                        <span>Sin portada</span>
                                    </div>
                                @endif

                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        <span class="badge date-badge">
                                            <i class="bi bi-calendar-event me-1" aria-hidden="true"></i>{{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
                                        </span>
                                        <span class="badge time-badge">
                                            <i class="bi bi-clock me-1" aria-hidden="true"></i>{{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}
                                        </span>
                                    </div>

                                    <h2 class="h5 card-title fw-semibold mb-2">{{ $event->nombre }}</h2>

                                    <p class="text-muted small mb-1">
                                        {{ $event->lugar ?: 'Lugar por confirmar' }}
                                    </p>

                                    @if ($event->direccion)
                                        <p class="text-muted small mb-3">
                                            <i class="bi bi-geo-alt me-1" aria-hidden="true"></i>{{ \Illuminate\Support\Str::limit($event->direccion, 80) }}
                                        </p>
                                    @endif

                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-ticket-perforated me-1" aria-hidden="true"></i>
                                        {{ $event->tickets_count }} {{ $event->tickets_count === 1 ? 'ticket registrado' : 'tickets registrados' }}
                                    </p>

                                    <div class="mt-auto pt-3 border-top">
                                        <button type="submit"
                                                class="btn btn-brand w-100 py-2 fw-semibold"
                                                aria-label="Continuar con el sorteo de {{ $event->nombre }}">
                                            <i class="bi bi-shuffle me-2" aria-hidden="true"></i>Continuar
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection