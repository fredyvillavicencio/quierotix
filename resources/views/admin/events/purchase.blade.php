<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Compra - {{ $event->nombre }} - Sistema de Tickets</title>
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

        .purchase-card,
        .event-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .purchase-card {
            padding: 30px;
        }

        .event-summary {
            padding: 25px;
        }

        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }

        .price-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 18px;
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
                                <i class="bi bi-cart-plus"></i>
                                Crear Compra
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

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="mt-2 mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($availableSeats < 1)
                    <div class="alert alert-warning mb-0" role="alert">
                        <h2 class="h6 fw-semibold">
                            <i class="bi bi-ticket-perforated me-1"></i>
                            Este evento está agotado
                        </h2>
                        <p class="mb-0 small">No hay tickets disponibles por el momento.</p>
                    </div>
                @else
                    <div class="row g-4">
                        <div class="col-lg-7">
                            <div class="purchase-card">
                                <h2 class="h4 mb-1">Datos de la compra</h2>
                                <p class="text-muted mb-4">
                                    Completa la información del participante. Los tickets se generarán y enviarán automáticamente.
                                </p>

                                @php
                                    $selectedQuantity = max(1, min((int) old('cantidad', 1), max(1, $maxTickets)));
                                    $total = $unitPrice * $selectedQuantity;
                                @endphp

                                <form method="POST" action="{{ route('admin.events.purchase.store', $event) }}">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="nombre" class="form-label required">Nombre</label>
                                            <input
                                                type="text"
                                                class="form-control @error('nombre') is-invalid @enderror"
                                                id="nombre"
                                                name="nombre"
                                                value="{{ old('nombre') }}"
                                                required
                                            >
                                            @error('nombre')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="apellido" class="form-label required">Apellido</label>
                                            <input
                                                type="text"
                                                class="form-control @error('apellido') is-invalid @enderror"
                                                id="apellido"
                                                name="apellido"
                                                value="{{ old('apellido') }}"
                                                required
                                            >
                                            @error('apellido')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="email" class="form-label required">Correo electrónico</label>
                                            <input
                                                type="email"
                                                class="form-control @error('email') is-invalid @enderror"
                                                id="email"
                                                name="email"
                                                value="{{ old('email') }}"
                                                required
                                            >
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="telefono" class="form-label required">Teléfono</label>
                                            <input
                                                type="tel"
                                                class="form-control @error('telefono') is-invalid @enderror"
                                                id="telefono"
                                                name="telefono"
                                                value="{{ old('telefono') }}"
                                                required
                                            >
                                            @error('telefono')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="carne" class="form-label required">DPI / Carné</label>
                                            <input
                                                type="text"
                                                class="form-control @error('carne') is-invalid @enderror"
                                                id="carne"
                                                name="carne"
                                                value="{{ old('carne') }}"
                                                required
                                            >
                                            @error('carne')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>


                                        <div class="col-md-6">
                                            <label for="locacion" class="form-label required">Locación</label>
                                            <select
                                                class="form-select @error('locacion') is-invalid @enderror"
                                                id="locacion"
                                                name="locacion"
                                                required
                                            >
                                                @foreach ($locations as $location)
                                                    <option
                                                        value="{{ $location['nombre'] }}"
                                                        data-precio="{{ $location['precio'] }}"
                                                        data-disponibles="{{ $location['disponibles'] }}"
                                                        @selected(mb_strtolower($selectedLocation['nombre'] ?? '') === mb_strtolower($location['nombre']))
                                                        @disabled($location['disponibles'] < 1)
                                                    >
                                                        {{ $location['nombre'] }} - Q{{ number_format($location['precio'], 2) }}
                                                        ({{ $location['disponibles'] }} disponibles)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('locacion')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="cantidad" class="form-label required">Cantidad de tickets</label>
                                            <select
                                                class="form-select @error('cantidad') is-invalid @enderror"
                                                id="cantidad"
                                                name="cantidad"
                                                required
                                            >
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

                                    <div class="price-box mt-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted">Precio por ticket</span>
                                            <strong id="precio-unitario">Q{{ number_format($unitPrice, 2) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold">Total</span>
                                            <span class="h4 mb-0" id="total-display">Q{{ number_format($total, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center gap-3 mt-4">
                                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-secondary">
                                            Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-brand px-4">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Confirmar compra
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-lg-5">

                            @php
                                $heroImage = $event->imagen_cover_url ?: $event->imagen_url;
                            @endphp

                            <div class="event-summary">
                                <h2 class="h5 mb-3">Resumen del evento</h2>

                                @if ($heroImage)
                                    <img
                                        src="{{ $heroImage }}"
                                        alt="{{ $event->nombre }}"
                                        class="img-fluid rounded mb-3 w-100"
                                        style="max-height: 220px; object-fit: cover;"
                                    >
                                @endif

                                <ul class="summary-list">
                                    <li>
                                        <span class="label">Evento</span>
                                        <span class="value">{{ $event->nombre }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Código</span>
                                        <span class="value">{{ $event->codigo_evento }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Fecha</span>
                                        <span class="value">
                                            {{ $event->fecha ? \Illuminate\Support\Carbon::parse($event->fecha)->format('d/m/Y') : 'Por definir' }}
                                        </span>
                                    </li>
                                    <li>
                                        <span class="label">Hora</span>
                                        <span class="value">{{ $event->hora ? \Illuminate\Support\Carbon::parse($event->hora)->format('H:i') : 'Por definir' }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Lugar</span>
                                        <span class="value">{{ $event->lugar ?: 'Por definir' }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Dirección</span>
                                        <span class="value">{{ $event->direccion ?: 'Por definir' }}</span>
                                    </li>
                                    <li>
                                        <span class="label">Disponibles</span>
                                        <span class="value">{{ number_format($availableSeats) }} tickets</span>
                                    </li>
                                </ul>

                                <hr>

                                <h3 class="h6 mb-2">Locaciones</h3>
                                <ul class="summary-list">
                                    @foreach ($locations as $location)
                                        <li>
                                            <span class="label">
                                                {{ $location['nombre'] }}
                                                <small class="d-block text-muted">
                                                    {{ $location['disponibles'] }} disponibles
                                                </small>
                                            </span>
                                            <span class="value">Q{{ number_format($location['precio'], 2) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif
            </main>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locationSelect = document.getElementById('locacion');
            const quantitySelect = document.getElementById('cantidad');
            const unitPriceElement = document.getElementById('precio-unitario');
            const totalElement = document.getElementById('total-display');
            const maxTicketsPerPurchase = {{ (int) $maxTickets }};

            if (!locationSelect || !quantitySelect || !unitPriceElement || !totalElement) {
                return;
            }

            function formatMoney(value) {
                return 'Q' + Number(value).toLocaleString('es-GT', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function selectedLocationData() {
                const option = locationSelect.options[locationSelect.selectedIndex];

                if (!option) {
                    return { price: 0, available: 0 };
                }

                return {
                    price: parseFloat(option.dataset.precio || '0'),
                    available: parseInt(option.dataset.disponibles || '0', 10)
                };
            }

            function updateTotal() {
                const data = selectedLocationData();
                const quantity = parseInt(quantitySelect.value || '1', 10);

                unitPriceElement.textContent = formatMoney(data.price);
                totalElement.textContent = formatMoney(data.price * quantity);
            }

            function rebuildQuantityOptions(availability) {
                const previousQuantity = parseInt(quantitySelect.value || '1', 10);
                const maxQuantity = Math.max(1, Math.min(maxTicketsPerPurchase, availability));

                quantitySelect.innerHTML = '';

                for (let quantity = 1; quantity <= maxQuantity; quantity++) {
                    const option = document.createElement('option');
                    option.value = quantity;
                    option.textContent = quantity + (quantity === 1 ? ' ticket' : ' tickets');
                    quantitySelect.appendChild(option);
                }

                quantitySelect.value = String(Math.min(Math.max(previousQuantity, 1), maxQuantity));
            }

            locationSelect.addEventListener('change', function () {
                rebuildQuantityOptions(selectedLocationData().available);
                updateTotal();
            });

            quantitySelect.addEventListener('change', updateTotal);

            rebuildQuantityOptions(selectedLocationData().available);
            updateTotal();
        });
    </script>
</body>
</html>

