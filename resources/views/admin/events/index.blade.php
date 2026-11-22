@extends('layouts.admin')

@section('title', 'Eventos')

@push('styles')
<style>
.badge-active {
            background-color: var(--brand-link);
            color: #fff;
        }
        
        .badge-inactive {
            background-color: #6c757d;
            color: white;
        }
</style>
@endpush

@section('content')
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="bi bi-calendar-event"></i>
                        Eventos
                    </h1>
                </div>
                <a href="{{ route('admin.events.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i>
                    Nuevo Evento
                </a>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="input-group search-box">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" placeholder="Buscar eventos..." id="searchInput">
                    <button class="btn btn-outline-secondary" type="button" id="searchButton">
                        Buscar
                    </button>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Hora</th>
                            <th>Participantes</th>
                            <th>Tickets</th>
                            <th>PDFs</th>
                            <th>Emails</th>
                            <th>Estado</th>
                            <th width="200" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ $event->id }}</td>
                                <td>
                                    <a href="{{ route('admin.events.show', $event) }}" class="nav-link fw-semibold active" title="Ver">{{ $event->nombre }}</a>
                                    <span class="text-muted small">
                                        {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }} - {{ $event->hora }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $event->participants_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ $event->tickets_count ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                    
                                @php
    
                                $bg_pdf = match(true) {
                                    $event->pdfs_generated == $event->tickets_count => "bg-success", 
                                    $event->pdfs_generated == 0 => "bg-danger",
                                    default => "bg-warning"
                                }
    
                                @endphp                                    
                                    <span class="badge {{ $bg_pdf }}">
                                        {{ $event->pdfs_generated ?? 0 }}
                                    </span>
                                </td>
                                <td>
                                @php
    
                                $bg_email = match(true) {
                                    $event->emails_sent == $event->participants_count => "bg-success", 
                                    $event->emails_sent == 0 => "bg-danger",
                                    default => "bg-warning"
                                }
    
                                @endphp                                    
                                    <span class="badge {{ $bg_email }}">
                                        {{ $event->emails_sent ?? 0 }}
                                    </span>
                                </td>
                                
                                <td>
                                    @php
                                        $eventDate = \Carbon\Carbon::parse($event->fecha . ' ' . $event->hora);
                                        $isPast = $eventDate->isPast();
                                    @endphp
                                    <span class="badge {{ $isPast ? 'bg-secondary' : 'bg-success' }}">
                                        {{ $isPast ? 'Finalizado' : 'Activo' }}
                                    </span>
                                </td>
                                <td class="text-center action-buttons">
                                    <!--
                                    <a href="{{ route('admin.events.show', $event) }}" class="btn btn-sm btn-info" title="Ver">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.events.statistics', $event) }}" class="btn btn-sm btn-primary" title="Estadísticas">
                                        <i class="bi bi-bar-chart"></i>
                                    </a>
                                    -->
                                    <a href="{{ route('admin.events.seat.map', $event) }}" class="btn btn-sm btn-primary" title="Mapa">
                                        <i class="bi bi-map"></i>
                                    </a>
                                    
                                    <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-sm btn-warning" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!--
                                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este evento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-calendar-x display-4"></i>
                                        <h5 class="mt-3">No hay eventos registrados</h5>
                                        <p class="mb-0">Comienza creando tu primer evento</p>
                                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary mt-3">
                                            <i class="bi bi-plus-circle"></i>
                                            Crear Primer Evento
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($events->hasPages())
                <div class="pagination-container">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="small text-muted">
                            Mostrando {{ $events->firstItem() }} - {{ $events->lastItem() }} de {{ $events->total() }} eventos
                        </div>
                        <nav>
                            {{ $events->links() }}
                        </nav>
                    </div>
                </div>
            @endif
        </div>
@endsection

@push('scripts')
<script>
// Search functionality
        document.getElementById('searchButton').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value;
            if (searchTerm.trim()) {
                window.location.href = '{{ route("admin.events.index") }}?search=' + encodeURIComponent(searchTerm);
            }
        });

        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        // Filter functionality
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                const filter = this.textContent;
                // Implement filter logic here
                console.log('Filter by:', filter);
            });
        });
</script>
@endpush
