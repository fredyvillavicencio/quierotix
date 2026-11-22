<header class="admin-topbar">
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-sm d-md-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#adminSidebarMobile"
                aria-controls="adminSidebarMobile" aria-label="Abrir menú">
            <i class="bi bi-list"></i>
        </button>
        <span class="fw-semibold text-brand d-none d-sm-inline">Administración</span>
    </div>

    <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle admin-user-btn" type="button"
                data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-1"></i>
            {{ auth()->user()?->name ?? 'Invitado' }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            @auth
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="bi bi-person me-2"></i>Mi perfil
                    </a>
                </li>
            @endauth
            @auth
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                        </button>
                    </form>
                </li>
            @endauth
        </ul>
    </div>
</header>
