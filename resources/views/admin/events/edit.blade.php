<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Evento - Sistema de Tickets</title>
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .form-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            border-color: #4facfe;
            box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
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
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.3);
        }
        
        .event-info-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .info-label {
            font-weight: 600;
        }
        
        .info-value {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @include('layouts.sidebar')

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-pencil-square"></i>
                        Editar Evento
                    </h1>
                    <p class="mb-0 opacity-75">Modifica la información del evento</p>
                </div>
                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i>
                    Volver al Evento
                </a>
            </div>
        </div>


                <!-- Display Session Errors -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Error:</strong> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>¡Éxito!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Advertencia:</strong> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Información:</strong> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Validation Errors -->
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Por favor, corrige los siguientes errores:</strong>
                        <ul class="error-list mt-2 mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Import Errors Summary -->
                @if(session('import_errors'))
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Problemas durante la importación:</strong>
                        <ul class="error-list mt-2 mb-0">
                            @foreach(session('import_errors') as $importError)
                                <li>{{ $importError }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif


        <!-- Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Event Code (Readonly) -->
                        <div class="mb-4">
                            <label for="codigo_evento" class="form-label required">Código del Evento</label>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   id="codigo_evento" 
                                   value="{{ $event->codigo_evento }}"
                                   readonly
                                   disabled>
                        </div>

                        <!-- Recurrente Integration -->
                        <div class="mb-4">
                            <label for="recurrente_product_id" class="form-label">Producto de Recurrente</label>
                            <input type="text"
                                   class="form-control @error('recurrente_product_id') is-invalid @enderror"
                                   id="recurrente_product_id"
                                   name="recurrente_product_id"
                                   value="{{ old('recurrente_product_id', $event->recurrente_product_id) }}"
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
                                   value="{{ old('recurrente_product_url', $event->recurrente_product_url) }}"
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
                                   value="{{ old('nombre', $event->nombre) }}"
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
                                       value="{{ old('fecha', $event->fecha) }}"
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
                                       value="{{ old('hora', $event->hora) }}"
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
                                   value="{{ old('lugar', $event->lugar) }}"
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
                                rows="3">{{ old('direccion', $event->direccion) }}</textarea>
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
                                       value="{{ old('precio', $event->precio) }}"
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
                                    $locationRows = $event->locations->map(fn ($location) => [
                                        'id' => $location->id,
                                        'nombre' => $location->nombre,
                                        'precio' => $location->precio,
                                        'total_asientos' => $location->total_asientos,
                                        'vendidos' => $location->vendidos ?? 0,
                                    ])->all();
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
                                Si no configuras locaciones, se usará una locación General con 714 asientos y el precio general del evento.
                            </div>
                        </div>

                        <template id="location-row-template">
                            @include('admin.events.partials.location-row', ['index' => '__INDEX__', 'location' => []])
                        </template>

                        <!-- Imagen Principal -->
                        <div class="mb-4">
                            <label for="imagen" class="form-label">Imagen del Evento</label>
                            
                            @if($event->imagen)
                                <div class="mb-3">
                                    <p class="mb-1">Imagen actual:</p>
                                    <img src="{{ url('public').'/'.$event->imagen }}" alt="Imagen del evento" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="eliminar_imagen" name="eliminar_imagen" value="1">
                                        <label class="form-check-label text-danger" for="eliminar_imagen">
                                            Eliminar imagen actual
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
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
                            
                            @if($event->imagen_ticket)
                                <div class="mb-3">
                                    <p class="mb-1">Imagen actual para tickets:</p>
                                    <img src="{{ url('public').'/'.$event->imagen_ticket }}" alt="Imagen para tickets" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="eliminar_imagen_ticket" name="eliminar_imagen_ticket" value="1">
                                        <label class="form-check-label text-danger" for="eliminar_imagen_ticket">
                                            Eliminar imagen actual para tickets
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
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
                            
                            @if($event->imagen_cover)
                                <div class="mb-3">
                                    <p class="mb-1">Imagen cover actual:</p>
                                    <img src="{{ url('public').'/'.$event->imagen_cover }}" alt="Imagen cover de la página de compra" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="eliminar_imagen_cover" name="eliminar_imagen_cover" value="1">
                                        <label class="form-check-label text-danger" for="eliminar_imagen_cover">
                                            Eliminar imagen cover actual
                                        </label>
                                    </div>
                                </div>
                            @endif
                            
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
                            <div>
                                <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                    Cancelar
                                </a>
                                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-list"></i>
                                    Ver Todos
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-submit">
                                    <i class="bi bi-check-circle"></i>
                                    Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div class="card mt-4 border-danger">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-exclamation-triangle"></i>
                            Alerta
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">
                            <strong>Advertencia:</strong> Las siguientes acciones son irreversibles.
                        </p>
                        
                        @if($event->participants_count == 0 && $event->tickets_count == 0)
                            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este evento? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i>
                                    Eliminar Evento
                                </button>
                            </form>
                            <small class="text-muted d-block mt-2">
                                Solo puedes eliminar eventos que no tengan participantes ni tickets asociados.
                            </small>
                        @else
                            <button class="btn btn-danger" disabled>
                                <i class="bi bi-trash"></i>
                                Eliminar Evento
                            </button>
                            <small class="text-danger d-block mt-2">
                                No puedes eliminar este evento porque tiene 
                                {{ $event->participants_count }} participantes y 
                                {{ $event->tickets_count }} tickets asociados.
                            </small>
                        @endif
                    </div>
                </div>
            </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const fecha = document.getElementById('fecha').value;
            const hora = document.getElementById('hora').value;
            const precio = document.getElementById('precio').value;
            
            if (!nombre || !fecha || !hora || precio === '') {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
                return false;
            }
            
            return true;
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
    <script>
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
</body>
</html>
