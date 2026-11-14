@extends('layouts.public')

@section('title', 'Comprar tickets | '.$event->nombre)
@section('meta_description', 'Compra tus tickets para '.$event->nombre.'. Recibe tus entradas en PDF por correo.')

@section('content')

<style>
#recurrente-checkout-iframe {
  min-height: 1200px !important;
  width: 100%;
  border: none;
  overflow: hidden;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/recurrente-checkout@0.0.5/recurrente-checkout.js"></script>

    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('home') }}" class="text-decoration-none">
                        Eventos
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Comprar tickets</li>
            </ol>
        </nav>

        <section class="detail-hero mb-4">
            @php
                $heroImage = $event->imagen_cover_url ?: $event->imagen_url;
            @endphp
            @if ($heroImage)
                <img src="{{ $heroImage }}"
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

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-md-5">
                        <h1 class="h4 fw-semibold mb-1">Compra tus tickets</h1>
                
                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                            @php
                                $selectedQuantity = max(1, min((int) old('cantidad', 1), $maxTickets));
                                $defaultLocation = collect($locations)->first(fn (array $location): bool => $location['disponibles'] > 0)
                                    ?? ($locations[0] ?? null);
                                $selectedLocationName = old('locacion', $defaultLocation['nombre'] ?? 'General');
                                $selectedLocation = collect($locations)->first(
                                    fn (array $location): bool => mb_strtolower($location['nombre']) === mb_strtolower((string) $selectedLocationName)
                                ) ?? ($locations[0] ?? null);
                                $unitPrice = $selectedLocation['precio'] ?? $unitPrice;
                                $total = $unitPrice * $selectedQuantity;
                            @endphp
                            <form method="POST" action="{{ route('events.public.purchase.store', $event) }}">
                                @csrf

                                    <div class="col-md-6">
                                        <label for="locacion" class="form-label">Locación <span class="text-danger">*</span></label>
                                        <select id="locacion"
                                                name="locacion"
                                                class="form-select @error('locacion') is-invalid @enderror"
                                                required>
                                            @foreach ($locations as $location)
                                                <option value="{{ $location['nombre'] }}"
                                                        data-precio="{{ number_format($location['precio'], 2, '.', '') }}"
                                                        data-disponibles="{{ $location['disponibles'] }}"
                                                        @selected($selectedLocation && strcasecmp($selectedLocation['nombre'], $location['nombre']) === 0)
                                                        @disabled($location['disponibles'] < 1)>
                                                    {{ $location['nombre'] }} — Q {{ number_format($location['precio'], 2, '.', ',') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('locacion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                            </form>

                            <div id="recurrente-checkout-container" style="overflow: hidden;"></div>


                    </div>    
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        @if ($event->imagen_url)
                            <img src="{{ $event->imagen_url }}"
                                 alt="Portada del evento {{ $event->nombre }}"
                                 class="img-fluid rounded-3 border mb-4">
                        @endif

                        <h3 class="h5 fw-semibold">{{ $event->nombre }}</h3>
                        <ul class="list-unstyled small text-muted mb-0">
                            <li class="mb-2">
                                <i class="bi bi-calendar-event me-2 text-brand"></i>
                                {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
                                &middot;
                                {{ \Carbon\Carbon::parse($event->hora)->format('H:i') }}
                            </li>
                            <li class="mb-2">
                                {{ $event->lugar ?: 'Lugar por confirmar' }}
                            </li>
                            @if ($event->direccion)
                                <li class="mb-2">
                                    <i class="bi bi-geo-alt me-2 text-brand"></i>{{ $event->direccion }}
                                </li>
                            @endif

                            <!--
                            <li>
                                <i class="bi bi-ticket-perforated me-2 text-brand"></i>
                                {{ number_format($availableSeats) }} {{ $availableSeats === 1 ? 'ticket disponible' : 'tickets disponibles' }}
                            </li>
-->
                        </ul>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection

@push('scripts')

    <script>
        RecurrenteCheckout.load({
            url: "https://app.recurrente.com/s/quierotix/ticket-q100",
            onSuccess: function(paymentData) {
                console.log('Payment completed successfully!', paymentData.checkoutId);
                alert(`Payment completed successfully! ${paymentData.checkoutId}`);
                // Add a delay before redirecting to success page
                setTimeout(function() {
                    window.location.href = `/success?checkoutId=${paymentData.checkoutId}&email=${paymentData.checkoutId}`;
                }, 2000); // 2 second delay
            },
            onFailure: function(data) {
                console.log('Payment failed with the following error:', data.error);
                alert(`Payment failed with the following error: ${data.error}`);
            },
            onPaymentInProgress: function(data) {
                console.log('Payment in progress:', data);
                alert(`Payment in progress: ${data.checkoutId}`);
            }

        });
    </script>


@endpush

