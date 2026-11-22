@extends('layouts.public')

@section('title', 'Comprar tickets | '.$event->nombre)
@section('meta_description', 'Compra tus tickets para '.$event->nombre.'. Recibe tus entradas en PDF por correo.')

@section('content')
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
                <div class="alert alert-light border small mt-1 mb-1">

                                    <i class="bi bi-shield-check me-1 text-brand"></i>
                    Completa tus datos y elige cuántos tickets necesitas.<br>
                    <i class="bi bi-shield-check me-1 text-brand"></i>
                    Tus datos se usan únicamente para generar y enviar tus entradas.<br>
                    <i class="bi bi-envelope-check me-1 text-brand"></i>
                                    Recibirás tus tickets por correo. También podrás descargarlos al finalizar la compra.
                </div>


                        @if (session('error'))
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if ($availableSeats < 1)
                            <div class="alert alert-warning mb-0" role="alert">
                                <h2 class="h6 fw-semibold">
                                    <i class="bi bi-ticket-perforated me-1"></i>Este evento está agotado
                                </h2>
                                <p class="mb-0 small">No hay tickets disponibles por el momento.</p>
                            </div>
                            <a href="{{ route('events.public.show', $event) }}" class="btn btn-outline-secondary mt-4">
                                <i class="bi bi-arrow-left me-1"></i>Volver al evento
                            </a>
                        @else
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

                                <div class="row g-3 mb-2">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="nombre"
                                               name="nombre"
                                               value="{{ old('nombre') }}"
                                               class="form-control @error('nombre') is-invalid @enderror"
                                               maxlength="255"
                                               autocomplete="given-name"
                                               required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="apellido"
                                               name="apellido"
                                               value="{{ old('apellido') }}"
                                               class="form-control @error('apellido') is-invalid @enderror"
                                               maxlength="255"
                                               autocomplete="family-name"
                                               required>
                                        @error('apellido')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="email" class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                                        <!--
                                        <div class="form-text">Aquí recibirás tus tickets en PDF.</div>
                                        -->
                                        <input type="email"
                                               id="email"
                                               name="email"
                                               value="{{ old('email') }}"
                                               class="form-control @error('email') is-invalid @enderror"
                                               maxlength="255"
                                               autocomplete="email"
                                               inputmode="email"
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        
                                    </div>

                                    <div class="col-md-6">
                                        <label for="carne" class="form-label">DPI <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="carne"
                                               name="carne"
                                               value="{{ old('carne') }}"
                                               class="form-control @error('carne') is-invalid @enderror"
                                               maxlength="255"
                                               autocomplete="off"
                                               required>
                                        @error('carne')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>


                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="telefono"
                                               name="telefono"
                                               value="{{ old('telefono') }}"
                                               class="form-control @error('telefono') is-invalid @enderror"
                                               maxlength="255"
                                               autocomplete="off"
                                               required>
                                        @error('telefono')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>



                                    <div class="col-md-6">
                                        <label for="locacion" class="form-label">Compra <span class="text-danger">*</span></label>
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

                                    <div class="col-md-6 mb-3">
                                        <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                        <select id="cantidad"
                                                name="cantidad"
                                                class="form-select @error('cantidad') is-invalid @enderror"
                                                data-unit-price="{{ number_format($unitPrice, 2, '.', '') }}"
                                                required>
                                            @for ($i = 1; $i <= $maxTickets; $i++)
                                                <option value="{{ $i }}" @selected($selectedQuantity === $i)>
                                                    {{ $i }} {{ $i === 1 ? 'ticket' : 'tickets' }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('cantidad')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="card bg-light border-0 mb-4">
                                    <div class="card-body py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Precio por ticket</span>
                                            <span class="fw-semibold" id="purchase-unit-price">Q {{ number_format($unitPrice, 2, '.', ',') }}</span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Total</span>
                                            <span class="h4 mb-0 fw-bold text-brand" id="purchase-total" aria-live="polite">
                                                Q {{ number_format($total, 2, '.', ',') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-brand btn-lg w-100 py-3 fw-semibold">
                                    <i class="bi bi-ticket-perforated-fill me-2"></i>Confirmar compra
                                </button>
                            </form>
                        @endif
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
        document.addEventListener('DOMContentLoaded', function () {
            const locationSelect = document.getElementById('locacion');
            const quantitySelect = document.getElementById('cantidad');
            const totalElement = document.getElementById('purchase-total');
            const unitPriceElement = document.getElementById('purchase-unit-price');

            if (!locationSelect || !quantitySelect || !totalElement || !unitPriceElement) {
                return;
            }

            const maxTicketsPerPurchase = {{ $maxTickets > 0 ? \App\Services\SeatAssignmentService::MAX_TICKETS_PER_PURCHASE : 0 }};
            const selectedLocation = () => locationSelect.options[locationSelect.selectedIndex];

            const formatMoney = (amount) => 'Q ' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const currentPrice = () => parseFloat(selectedLocation()?.dataset.precio || '0') || 0;

            const updateTotal = () => {
                const quantity = parseInt(quantitySelect.value, 10) || 0;
                const unitPrice = currentPrice();

                unitPriceElement.textContent = formatMoney(unitPrice);
                totalElement.textContent = formatMoney(unitPrice * quantity);
            };

            const rebuildQuantityOptions = () => {
                const available = parseInt(selectedLocation()?.dataset.disponibles || '0', 10) || 0;
                const maxForLocation = Math.min(maxTicketsPerPurchase, available);
                const previousQuantity = parseInt(quantitySelect.value, 10) || 1;

                quantitySelect.innerHTML = '';
                quantitySelect.disabled = maxForLocation < 1;

                for (let i = 1; i <= maxForLocation; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i + (i === 1 ? ' ticket' : ' tickets');
                    quantitySelect.appendChild(option);
                }

                if (maxForLocation > 0) {
                    quantitySelect.value = Math.min(previousQuantity, maxForLocation);
                }
            };

            locationSelect.addEventListener('change', function () {
                rebuildQuantityOptions();
                updateTotal();
            });

            quantitySelect.addEventListener('change', updateTotal);

            rebuildQuantityOptions();
            updateTotal();
        });
    </script>
@endpush

