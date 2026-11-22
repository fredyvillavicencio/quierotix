@extends('layouts.admin')

@section('title', 'Tickets')

@push('styles')
<style>
.badge-validated {
            background-color: var(--brand-link);
            color: #fff;
        }
        
        .badge-pending {
            background-color: var(--brand-end);
            color: var(--brand-ink);
        }
        
        .qr-code {
            width: 60px;
            height: 60px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #495057;
        }
        
        .seat-badge {
            background: linear-gradient(135deg, #15d403 0%, #a7f801 55%, #fcef03 100%);
            color: #020201;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 12px;
        }
        
        .ticket-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #495057;
        }
</style>
@endpush

@section('content')
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-ticket-perforated"></i>
                                Tickets
                            </h1>
                        </div>
                    </div>
                </div>

                <!-- Search and Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <form method="GET" action="{{ route('admin.tickets.index') }}" class="search-form">
                            <div class="d-grid gap-2 d-lg-flex">
                                <div class="input-group flex-grow-1">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text"
                                           class="form-control"
                                           placeholder="Nombre o apellido"
                                           id="searchInput"
                                           name="search"
                                           value="{{ $searchTerm ?? '' }}">
                                </div>
                                <select name="event_id" id="eventFilter" class="form-select" style="max-width: 360px;">
                                    <option value="">Todos los eventos</option>
                                    @foreach($events as $event)
                                        <option value="{{ $event->id }}" @selected((int) $eventId === $event->id)>
                                            {{ $event->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="d-grid d-sm-flex gap-2">
                                    <button class="btn btn-outline-primary" type="submit" id="searchButton">
                                        Buscar
                                    </button>
                                    @if((isset($searchTerm) && $searchTerm) || $eventId)
                                        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary" type="button">
                                            Limpiar
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </form>
                        @if((isset($searchTerm) && $searchTerm) || $eventId)
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i>
                                    @if($eventId)
                                        Evento: "<strong>{{ $events->firstWhere('id', $eventId)?->nombre ?? 'Desconocido' }}</strong>"
                                    @endif
                                    @if(isset($searchTerm) && $searchTerm)
                                        @if($eventId) &middot; @endif
                                        Mostrando resultados para: "<strong>{{ $searchTerm }}</strong>"
                                    @endif
                                    - Total: {{ $tickets->total() }} ticket(s)
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
                                <li><a class="dropdown-item" href="#">Validados</a></li>
                                <li><a class="dropdown-item" href="#">Pendientes</a></li>
                                <li><a class="dropdown-item" href="#">Por Evento</a></li>
                                <li><a class="dropdown-item" href="#">Por Participante</a></li>
                            </ul>
                        </div>
                        <button class="btn btn-outline-secondary ms-2">
                            <i class="bi bi-download"></i>
                            Exportar
                        </button>
                    </div>
                    -->
                </div>

                <!-- Tickets Table -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <!--
                                    <th>Código QR <i class="bi bi-qr-code"></i></th>
                                    -->
                                    <th>Participante</th>
                                    <th>Evento</th>
                                    <th>Ticket</th>
                                    <th>Estado</th>
                                    <th>Validado</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td>{{ $ticket->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $ticket->participant->nombre }} {{ $ticket->participant->apellido }}</div>
                                            <small class="text-muted">ID: {{ $ticket->participant->id }}</small>
                                        </td>
                                        <td>
                                            
                                            {{ $ticket->event->nombre }}<br>
                                            {{ \Carbon\Carbon::parse($ticket->event->fecha)->format('d/m/Y') }} - {{ $ticket->event->hora }}
                                            
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $ticket->location_label }}</div>
                                        </td>
                                        <td>
                                            @if($ticket->validado)
                                                <span class="badge badge-validated">
                                                    <i class="bi bi-check-circle"></i>
                                                    Validado
                                                </span>
                                            @else
                                                <span class="badge badge-pending">
                                                    <i class="bi bi-clock"></i>
                                                    Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $ticket->contador_validaciones }}
                                            </span>
                                        </td>
                                        <td class="text-center action-buttons">
                                            @if(!empty($ticket->archivo_pdf))

                                            <!--
                                                <a href="{{ route('admin.tickets.preview.pdf', $ticket->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Descargar PDF"
                                                   target="_blank">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            -->

                                                <a href="{{ route('admin.tickets.download.pdfadmin', $ticket->codigo) }}" 
                                                   class="btn btn-sm btn-pdf text-white"
                                                   title="Descargar PDF"
                                                   target="_blank">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            @if((isset($searchTerm) && $searchTerm) || $eventId)
                                                <div class="text-muted">
                                                    <i class="bi bi-search display-4"></i>
                                                    <h5 class="mt-3">No hay tickets que coincidan con los filtros</h5>
                                                    <p class="mb-0">Prueba con otro apellido o selecciona otro evento</p>
                                                    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary mt-3">
                                                        <i class="bi bi-x-circle"></i>
                                                        Limpiar filtros
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-ticket-perforated display-4"></i>
                                                    <h5 class="mt-3">No hay tickets generados</h5>
                                                    <p class="mb-0">Los tickets se generan automáticamente al importar participantes</p>
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
                    @if($tickets->hasPages())
                    <div class="pagination-container">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">
                                Mostrando {{ $tickets->firstItem() }} - {{ $tickets->lastItem() }} de {{ $tickets->total() }} tickets
                            </div>
                            <nav>
                                {{ $tickets->links() }}
                            </nav>
                        </div>
                    </div>
                    @endif
                </div>

            
@endsection

@push('scripts')
<script>
// Focus on search input when page loads if there's a search term
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = "{{ $searchTerm ?? '' }}";
            
            // Focus on search input if there's a search term
            if (searchTerm && searchInput) {
                searchInput.focus();
                searchInput.setSelectionRange(searchTerm.length, searchTerm.length);
            }
            
            // Recargar el listado automáticamente al cambiar de evento
            const eventFilter = document.getElementById('eventFilter');
            if (eventFilter) {
                eventFilter.addEventListener('change', function() {
                    if (this.form) {
                        this.form.submit();
                    }
                });
            }

// Clear search with Escape key
            if (searchInput) {
                searchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && this.value) {
                        this.value = '';
                        // Optionally submit the form to clear search immediately
                        // this.form.submit();
                    }
                });
            }
        });
</script>
@endpush
