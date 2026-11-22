<div class="admin-sidebar-inner d-flex flex-column h-100">
    <a href="{{ route('home') }}" class="admin-brand" title="Ir al sitio público">
        <img src="{{ asset('images/logo_quierotix.png') }}" alt="{{ config('app.name', 'QuieroTIX.com') }}">
    </a>
    <p class="admin-brand-subtitle">Panel de Administración</p>

    @if(auth()->user()?->is_admin)
        <nav class="nav flex-column admin-nav" aria-label="Navegación de administración">
            <a class="nav-link {{ request()->routeIs('admin.dashboard', 'admin') ? 'active' : '' }}"
               href="{{ route('admin.dashboard') }}"
               @if(request()->routeIs('admin.dashboard', 'admin')) aria-current="page" @endif>
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}"
               href="{{ route('admin.events.index') }}"
               @if(request()->routeIs('admin.events.*')) aria-current="page" @endif>
                <i class="bi bi-calendar-event"></i>
                <span>Eventos</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.participants.*') ? 'active' : '' }}"
               href="{{ route('admin.participants.index') }}"
               @if(request()->routeIs('admin.participants.*')) aria-current="page" @endif>
                <i class="bi bi-people"></i>
                <span>Participantes</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}"
               href="{{ route('admin.tickets.index') }}"
               @if(request()->routeIs('admin.tickets.*')) aria-current="page" @endif>
                <i class="bi bi-ticket-perforated"></i>
                <span>Tickets</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.statistics*') ? 'active' : '' }}"
               href="{{ route('admin.statistics') }}"
               @if(request()->routeIs('admin.statistics*')) aria-current="page" @endif>
                <i class="bi bi-bar-chart-line"></i>
                <span>Estadísticas</span>
            </a>
        </nav>

        <div class="admin-nav-footer mt-auto pt-3">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    @else
        <nav class="nav flex-column admin-nav" aria-label="Navegación pública">
            <a class="nav-link" href="{{ route('home') }}">
                <i class="bi bi-house-door"></i>
                <span>Inicio</span>
            </a>
        </nav>
    @endif
</div>
