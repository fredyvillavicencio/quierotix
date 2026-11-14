            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="bi bi-ticket-perforated"></i>
                            Sistema de Tickets
                        </h4>
                        <p class="small">Panel de Administración</p>
                    </div>

                    <ul class="nav flex-column">

@php
    if (Auth::user()->is_admin) {
@endphp
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                <i class="bi bi-speedometer2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.events.index') }}">
                                <i class="bi bi-calendar-event"></i>
                                Eventos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.participants.index') }}">
                                <i class="bi bi-people"></i>
                                Participantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.tickets.index') }}">
                                <i class="bi bi-ticket-perforated"></i>
                                Tickets
                            </a>
                        </li>

                        <!--
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="bi bi-file-pdf"></i>
                                PDFs Generados
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="#">
                                <i class="bi bi-bar-chart"></i>
                                Estadísticas
                            </a>
                        </li>
                        -->
@php
    }
@endphp
                        <li class="nav-item mt-4">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100">
                                    <i class="bi bi-box-arrow-left"></i>
                                    Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>                    

                </div>
            </nav>