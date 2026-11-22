@extends('layouts.admin')

@section('title', 'Participantes')

@push('styles')
<style>
.badge-event {
            background-color: #15d403;
            color: #020201;
        }
        
        .badge-tickets {
            background-color: var(--brand-link);
            color: #fff;
        }
        
        .participant-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
        }
        
        .email-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
</style>
@endpush

@section('content')
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-people"></i>
                                Participantes
                            </h1>
                        </div>
                    </div>
                </div>



              <!-- Display Session Errors -->




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


                <!-- Search and Filters -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('admin.participants.index') }}" class="search-form">
                            <div class="input-group search-box">
                                <span class="input-group-text">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" 
                                       class="form-control" 
                                       name="search" 
                                       id="searchInput" 
                                       placeholder="Nombre apellido o email..." 
                                       value="{{ $searchTerm ?? '' }}"
                                       autocomplete="off">
                                <select name="event_id" id="eventFilter" class="form-select" style="max-width: 240px;">
                                    <option value="">Todos los eventos</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected((int) $eventId === $event->id)>
                                            {{ $event->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-secondary" type="submit" id="searchButton">
                                    Buscar
                                </button>
                                @if(!empty($searchTerm) || $eventId)
                                <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-danger" title="Limpiar filtros">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                                @endif
                            </div>
                        </form>
                        
                        @if(!empty($searchTerm) || $eventId)
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i>
                                @if($eventId)
                                    Evento: "<strong>{{ $events->firstWhere('id', $eventId)?->nombre ?? 'Desconocido' }}</strong>"
                                @endif
                                @if(!empty($searchTerm))
                                    @if($eventId) &middot; @endif
                                    Buscando: "<strong>{{ $searchTerm }}</strong>"
                                @endif
                                @if($participants->total() > 0)
                                    - Encontrados: {{ $participants->total() }} participante(s)
                                @else
                                    - Sin resultados
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>

                    <!--
                    <div class="col-md-4 text-end">
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-filter"></i>
                                Filtros
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Todos</a></li>
                                <li><a class="dropdown-item" href="#">Con Tickets</a></li>
                                <li><a class="dropdown-item" href="#">Sin Tickets</a></li>
                                <li><a class="dropdown-item" href="#">Por Evento</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-download"></i>
                            Exportar
                        </button>
                    </div>
                    -->

                </div>

                <!-- Participants Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Tickets</th>
                                    <th>PDFs</th>
                                    <th>Email</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($participants as $participant)
                                    <tr>
                                        <td>{{ $participant->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-semibold">{{ $participant->nombre }} {{ $participant->apellido }}</div>
                                                    <small class="text-muted">{{ $participant->email }}</small><br>
                                                    <small class="text-muted">{{ $participant->carne }}</small><br>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ $participant->event->nombre }}<br>
                                            {{ \Carbon\Carbon::parse($participant->event->fecha)->format('d/m/Y') }} - {{ $participant->event->hora }}
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $participant->tickets_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ is_array($participant->pdf_paths) ? count($participant->pdf_paths) : 0 }}
                                            </span>
                                        </td>
                                        <td>
                                                    @if($participant->email_sent)
                                                        <span class="badge bg-success">Enviado</span>
                                                    @else
                                                        <span class="badge bg-warning">Pendiente</span>
                                                    @endif
                                        </td>
                                        <td class="text-center action-buttons">
                                            <a href="{{ route('admin.participants.download.pdfsadmin', $participant->id) }}" class="btn btn-sm btn-secondary" title="PDFs">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            
                                            @if($participant->tickets_count > 0 && !empty($participant->pdf_paths))
                                                <form method="POST" action="{{ route('admin.participants.send.email', $participant->id) }}" 
                                                      class="d-inline" onsubmit="return confirm('¿Enviar correo a {{ $participant->email }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" 
                                                            title="Enviar correo con tickets">
                                                        <i class="bi bi-envelope"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            @if(!empty($searchTerm) || $eventId)
                                                <div class="text-muted">
                                                    <i class="bi bi-search display-4"></i>
                                                    <h5 class="mt-3">No hay participantes que coincidan con los filtros</h5>
                                                    <p class="mb-0">Prueba con otro apellido o selecciona otro evento</p>
                                                    <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-secondary mt-3">
                                                        <i class="bi bi-x-circle"></i>
                                                        Limpiar filtros
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-people display-4"></i>
                                                    <h5 class="mt-3">No hay participantes registrados</h5>
                                                    <p class="mb-0">Los participantes se importan desde archivos CSV</p>
                                                    <a href="{{ route('admin.events.index') }}" class="btn btn-primary mt-3">
                                                        <i class="bi bi-calendar-event"></i>
                                                        Ver Eventos para Importar
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($participants->hasPages())
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                Mostrando {{ $participants->firstItem() }} - {{ $participants->lastItem() }} de {{ $participants->total() }} participantes
                            </div>
                            <nav>
                                {{ $participants->links() }}
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions 
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning-charge"></i>
                            Acciones Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.events.index') }}" class="btn btn-primary w-100">
                                    <i class="bi bi-upload"></i>
                                    Importar CSV
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.participants.export') }}" class="btn btn-success w-100">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                    Exportar PDFs
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="#" class="btn btn-warning w-100">
                                    <i class="bi bi-envelope"></i>
                                    Enviar Emails
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.participants.export') }}" class="btn btn-info w-100">
                                    <i class="bi bi-download"></i>
                                    Exportar Datos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                -->

            
@endsection

@push('scripts')
<script>
// Search functionality - Mejorada para formulario
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.querySelector('.search-form');
            const searchInput = document.getElementById('searchInput');
            const searchButton = document.getElementById('searchButton');
            
            const eventFilter = document.getElementById('eventFilter');

            // Recargar el listado automáticamente al cambiar de evento
            if (eventFilter) {
                eventFilter.addEventListener('change', function() {
                    if (searchForm) {
                        searchForm.submit();
                    }
                });
            }

            // Enviar formulario al hacer clic en buscar
            if (searchButton) {
                searchButton.addEventListener('click', function(e) {
                    const hasSearch = searchInput ? searchInput.value.trim() !== '' : false;
                    const hasEvent = eventFilter ? eventFilter.value !== '' : false;

                    if (!hasSearch && !hasEvent) {
                        e.preventDefault();
                        if (searchInput) {
                            searchInput.focus();
                        }
                    }
                });
            }
            
            // Enviar formulario al presionar Enter
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const hasEvent = eventFilter ? eventFilter.value !== '' : false;

                        if (!this.value.trim() && !hasEvent) {
                            e.preventDefault();
                            return;
                        }
                        // El formulario se enviará automáticamente
                    }
                });
            }
            
            // Auto-focus en el campo de búsqueda si hay término de búsqueda
            @if(!empty($searchTerm))
                if (searchInput) {
                    searchInput.focus();
                    // Mover el cursor al final del texto
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            @endif
            
// Mejorar experiencia de usuario: mostrar loading al buscar
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    const button = this.querySelector('button[type="submit"]');
                    if (button) {
                        const originalText = button.innerHTML;
                        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Buscando...';
                        button.disabled = true;
                        
                        // Restaurar después de 2 segundos (por si hay error)
                        setTimeout(function() {
                            button.innerHTML = originalText;
                            button.disabled = false;
                        }, 2000);
                    }
                });
            }
        });
</script>
@endpush
