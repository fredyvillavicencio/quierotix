@extends('layouts.admin')

@section('title', 'Nuevo Evento')

@push('styles')
<style>
.form-container {
            background: white;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(31, 41, 55, 0.08);
            padding: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #15d403;
            box-shadow: 0 0 0 0.2rem rgba(21, 212, 3, 0.25);
        }
        
        .required::after {
            content: " *";
            color: #dc3545;
        }
        
        .help-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            border: none;
            color: #020201;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(21, 212, 3, 0.3);
        }
        
        .card-icon {
            font-size: 3rem;
            opacity: 0.8;
            margin-bottom: 15px;
        }
</style>
@endpush

@section('content')
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-plus-circle"></i>
                        Nuevo Evento
                    </h1>
                    <p class="mb-0 opacity-75">Crea un nuevo evento para el sistema de tickets</p>
                </div>
                <a href="{{ route('admin.events.index') }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    Volver a Eventos
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Event Code -->
                        <div class="mb-4">
                            <label for="codigo_evento" class="form-label required">Código del Evento</label>
                            <input type="text" 
                                   class="form-control @error('codigo_evento') is-invalid @enderror" 
                                   id="codigo_evento" 
                                   name="codigo_evento" 
                                   value="{{ old('codigo_evento') }}"
                                   required>
                            @error('codigo_evento')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Recurrente Integration -->
                        <div class="mb-4">
                            <label for="recurrente_product_id" class="form-label">Producto de Recurrente</label>
                            <input type="text"
                                   class="form-control @error('recurrente_product_id') is-invalid @enderror"
                                   id="recurrente_product_id"
                                   name="recurrente_product_id"
                                   value="{{ old('recurrente_product_id') }}"
                                   placeholder="prod_...">
                            <div class="form-text">Identificador del producto en Recurrente. Se usa para asociar los pagos del webhook.</div>
                            @error('recurrente_product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="recurrente_product_url" class="form-label">URL de pago de Recurrente</label>
                            <input type="url"
                                   class="form-control @error('recurrente_product_url') is-invalid @enderror"
                                   id="recurrente_product_url"
                                   name="recurrente_product_url"
                                   value="{{ old('recurrente_product_url') }}"
                                   placeholder="https://app.recurrente.com/...">
                            @error('recurrente_product_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Event Name -->
                        <div class="mb-4">
                            <label for="nombre" class="form-label required">Nombre del Evento</label>
                            <input type="text" 
                                   class="form-control @error('nombre') is-invalid @enderror" 
                                   id="nombre" 
                                   name="nombre" 
                                   value="{{ old('nombre') }}"
                                   required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date and Time -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="fecha" class="form-label required">Fecha del Evento</label>
                                <input type="date" 
                                       class="form-control @error('fecha') is-invalid @enderror" 
                                       id="fecha" 
                                       name="fecha" 
                                       value="{{ old('fecha') }}"
                                       required>
                                @error('fecha')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="hora" class="form-label required">Hora del Evento</label>
                                <input type="time" 
                                       class="form-control @error('hora') is-invalid @enderror" 
                                       id="hora" 
                                       name="hora" 
                                       value="{{ old('hora', '19:00') }}"
                                       required>
                                @error('hora')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Lugar -->
                        <div class="mb-4">
                            <label for="lugar" class="form-label required">Lugar del Evento</label>
                            <input type="text" 
                                   class="form-control @error('lugar') is-invalid @enderror" 
                                   id="lugar" 
                                   name="lugar" 
                                   value="{{ old('lugar') }}"
                                   required>
                            @error('lugar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dirección -->
                        <div class="mb-4">
                            <label for="direccion" class="form-label">Dirección del Evento</label>
                            <textarea 
                                class="form-control @error('direccion') is-invalid @enderror" 
                                id="direccion" 
                                name="direccion" 
                                rows="3">{{ old('direccion') }}</textarea>
                            @error('direccion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Precio -->
                        <div class="mb-4">
                            <label for="precio" class="form-label required">Precio general de respaldo</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">Q</span>
                                <input type="number"
                                       class="form-control @error('precio') is-invalid @enderror"
                                       id="precio"
                                       name="precio"
                                       value="{{ old('precio', 0) }}"
                                       min="0"
                                       max="99999999.99"
                                       step="0.01"
                                       inputmode="decimal"
                                       required>
                                @error('precio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="help-text">Se usa solo cuando el evento no tiene locaciones configuradas.</div>
                        </div>

                        <!-- Locaciones -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                <div>
                                    <label class="form-label mb-1">Locaciones y precios</label>
                                    <div class="help-text">
                                        Define las secciones del evento, su precio y cantidad de asientos.
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="add-location">
                                    <i class="bi bi-plus-circle"></i>
                                    Agregar locación
                                </button>
                            </div>

                            @error('locaciones')
                                <div class="alert alert-danger py-2">{{ $message }}</div>
                            @enderror

                            @php
                                $locationRows = old('locaciones');

                                if ($locationRows === null) {
                                    $locationRows = [];
                                }

                                if (! is_array($locationRows) || $locationRows === []) {
                                    $locationRows = [['id' => '', 'nombre' => '', 'precio' => '', 'total_asientos' => '']];
                                }
                            @endphp

                            <div id="locations-container">
                                @foreach($locationRows as $index => $location)
                                    @include('admin.events.partials.location-row', ['index' => $index, 'location' => $location])
                                @endforeach
                            </div>

                            <div class="help-text">
                                Si no agregas locaciones, se usará una locación General con 714 asientos y el precio general del evento.
                            </div>
                        </div>

                        <template id="location-row-template">
                            @include('admin.events.partials.location-row', ['index' => '__INDEX__', 'location' => []])
                        </template>

                        <!-- Imagen Principal -->
                        <div class="mb-4">
                            <label for="imagen" class="form-label">Imagen del Evento</label>
                            <input type="file" 
                                   class="form-control @error('imagen') is-invalid @enderror" 
                                   id="imagen" 
                                   name="imagen"
                                   accept="image/*">
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Imagen para Tickets -->
                        <div class="mb-4">
                            <label for="imagen_ticket" class="form-label">Imagen para Tickets</label>
                            <input type="file" 
                                   class="form-control @error('imagen_ticket') is-invalid @enderror" 
                                   id="imagen_ticket" 
                                   name="imagen_ticket"
                                   accept="image/*">
                            @error('imagen_ticket')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Imagen Cover de la Página de Compra -->
                        <div class="mb-4">
                            <label for="imagen_cover" class="form-label">Imagen Cover (página de compra)</label>
                            <input type="file" 
                                   class="form-control @error('imagen_cover') is-invalid @enderror" 
                                   id="imagen_cover" 
                                   name="imagen_cover"
                                   accept="image/*">
                            @error('imagen_cover')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-5">
                            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-submit">
                                <i class="bi bi-check-circle"></i>
                                Crear Evento
                            </button>
                        </div>
                    </form>
                </div>

            </div>
            
@endsection

@push('scripts')
<script>
// Set minimum date to today
        document.getElementById('fecha').min = new Date().toISOString().split('T')[0];
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const codigo = document.getElementById('codigo_evento').value.trim();
            const nombre = document.getElementById('nombre').value.trim();
            const fecha = document.getElementById('fecha').value;
            const hora = document.getElementById('hora').value;
            const precio = document.getElementById('precio').value;
            
            if (!codigo || !nombre || !fecha || !hora || precio === '') {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
                return false;
            }
            
            // Validate event code format
            if (!/^[A-Z0-9\-_]+$/.test(codigo)) {
                e.preventDefault();
                alert('El código del evento solo puede contener letras mayúsculas, números, guiones y guiones bajos.');
                return false;
            }
            
            return true;
        });
document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('locations-container');
            const addButton = document.getElementById('add-location');
            const template = document.getElementById('location-row-template');

            if (!container || !addButton || !template) {
                return;
            }

            let nextIndex = {{ count($locationRows) }};

            addButton.addEventListener('click', function () {
                container.insertAdjacentHTML(
                    'beforeend',
                    template.innerHTML.replaceAll('__INDEX__', String(nextIndex++))
                );
            });

            container.addEventListener('click', function (event) {
                const removeButton = event.target.closest('[data-remove-location]');

                if (!removeButton) {
                    return;
                }

                removeButton.closest('[data-location-row]')?.remove();
            });
        });
</script>
@endpush
