@extends('layouts.admin')

@section('title', 'Estadísticas')

@push('styles')
<style>
.stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.3s;
            height: 100%;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .chart-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .filter-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .date-range {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .metric-badge {
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .trend-up {
            background-color: #d4edda;
            color: #155724;
        }
        
        .trend-down {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .trend-neutral {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 5px;
        }
</style>
@endpush

@section('content')
                <!-- Header -->
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-0">
                                <i class="bi bi-bar-chart"></i>
                                Estadísticas de Validación
                            </h1>
                            <p class="mb-0 opacity-75">Tickets validados por usuario</p>
                        </div>
                    </div>
                </div>



    <!-- Filtro -->


                <!-- Date Range Filter -->
                <div class="filter-card">
                    <h5 class="mb-3">
                        <i class="bi bi-filter"></i>
                        Filtros de Fecha
                    </h5>
                    <div class="row">

                    <form method="GET"
                          action="{{ route('admin.statistics') }}"
                          class="row align-items-end">

                        <div class="col-md-3">
                            <select
                                name="fecha"
                                id="fecha"
                                class="form-select"
                                onchange="this.form.submit()"
                            >

                                <option value="">
                                    Seleccione una fecha
                                </option>

                                @foreach($eventDates as $date)

                                    <option
                                        value="{{ $date }}"
                                        {{ $selectedDate == $date ? 'selected' : '' }}
                                    >
                                        {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
                                    </option>

                                @endforeach

                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit"
                                    class="btn btn-primary w-100">

                                <i class="bi bi-search"></i>
                                Filtrar

                            </button>
                        </div>

                        @if($selectedDate)

                            <div class="col-md-2">

                                <a href="{{ route('admin.statistics') }}"
                                   class="btn btn-outline-secondary w-100">

                                    <i class="bi bi-x-circle"></i>
                                    Limpiar

                                </a>

                            </div>

                        @endif

                    </div>

                </form>

                </div>






    @if($selectedDate)

        <!-- Resumen -->
        <div class="row mb-4">

            <div class="col-md-4">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h6 class="text-muted">
                                    Total de Tickets
                                </h6>

                                <h2 class="mb-0">
                                    {{ number_format($totalTickets) }}
                                </h2>

                            </div>

                            <div class="fs-1 text-primary">
                                <i class="bi bi-ticket-perforated"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h6 class="text-muted">
                                    Tickets Validados
                                </h6>

                                <h2 class="mb-0 text-success">
                                    {{ number_format($totalValidated) }}
                                </h2>

                            </div>

                            <div class="fs-1 text-success">
                                <i class="bi bi-check-circle"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="col-md-4">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <div class="d-flex justify-content-between">

                            <div>

                                <h6 class="text-muted">
                                    Pendientes
                                </h6>

                                <h2 class="mb-0 text-warning">
                                    {{ number_format($totalTickets - $totalValidated) }}
                                </h2>

                            </div>

                            <div class="fs-1 text-warning">
                                <i class="bi bi-clock"></i>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

                <!-- Detailed Statistics -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5 class="chart-title">
                                <i class="bi bi-person-check"></i>
                                Tickets Validados
                            </h5>

                                <div class="table-responsive">

                                    <table class="table table-hover mb-0">

                                        <thead>

                                            <tr>

                                                <th width="80">
                                                    #
                                                </th>

                                                <th>
                                                    Usuario
                                                </th>

                                                <th>
                                                    Correo
                                                </th>

                                                <th class="text-center">
                                                    Tickets Validados
                                                </th>

                                                <th>
                                                    % del Total
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            @forelse($usersStatistics as $index => $user)

                                                <tr>

                                                    <td>

                                                        @if($index === 0)

                                                            <span class="badge bg-warning text-dark">
                                                                <i class="bi bi-trophy-fill"></i>
                                                                1
                                                            </span>

                                                        @elseif($index === 1)

                                                            <span class="badge bg-secondary">
                                                                2
                                                            </span>

                                                        @elseif($index === 2)

                                                            <span class="badge bg-dark">
                                                                3
                                                            </span>

                                                        @else

                                                            <span class="text-muted">
                                                                {{ $index + 1 }}
                                                            </span>

                                                        @endif

                                                    </td>


                                                    <td>

                                                        <strong>
                                                            {{ $user->validatedBy->name ?? 'Usuario eliminado' }}
                                                        </strong>

                                                    </td>


                                                    <td>

                                                        <span class="text-muted">

                                                            {{ $user->validatedBy->email ?? '-' }}

                                                        </span>

                                                    </td>


                                                    <td class="text-center">

                                                        <span class="badge bg-success fs-6">

                                                            {{ number_format($user->validated_tickets) }}

                                                        </span>

                                                    </td>


                                                    <td>

                                                        <div class="d-flex align-items-center">

                                                            <div class="me-3"
                                                                 style="min-width:55px;">

                                                                <strong>
                                                                    {{ $user->percentage }}%
                                                                </strong>

                                                            </div>


                                                            <div
                                                                style="
                                                                    width: 150px;
                                                                    height: 8px;
                                                                    background: #e9ecef;
                                                                    border-radius: 4px;
                                                                    overflow: hidden;
                                                                "
                                                            >

                                                                <div
                                                                    style="
                                                                        width: {{ $user->percentage }}%;
                                                                        height: 100%;
                                                                        background: linear-gradient(
                                                                            135deg,
                                                                            #a7f801 0%,
                                                                            #fcef03 100%
                                                                        );
                                                                    "
                                                                ></div>

                                                            </div>

                                                        </div>

                                                    </td>

                                                </tr>

                                            @empty

                                                <tr>

                                                    <td colspan="5"
                                                        class="text-center py-5">

                                                        <div class="text-muted">

                                                            <i class="bi bi-person-x display-4"></i>

                                                            <h5 class="mt-3">
                                                                No hay validaciones
                                                            </h5>

                                                            <p class="mb-0">
                                                                No se encontraron tickets
                                                                validados para esta fecha.
                                                            </p>

                                                        </div>

                                                    </td>

                                                </tr>

                                            @endforelse

                                        </tbody>

                                    </table>

                                </div>

                        </div>
                    </div>
                </div>

                <!-- end rod -->


    @else

        <!-- Sin fecha seleccionada -->

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i class="bi bi-calendar3 display-3 text-muted"></i>

                <h4 class="mt-3">
                    Seleccione una fecha
                </h4>

                <p class="text-muted mb-0">
                    Seleccione la fecha de un evento para
                    consultar las estadísticas de validación.
                </p>

            </div>

        </div>

    @endif


            
@endsection
