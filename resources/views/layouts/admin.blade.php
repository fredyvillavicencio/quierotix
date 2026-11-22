<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Administración') | {{ config('app.name', 'QuieroTIX.com') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icono_quierotix.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --brand-start: #15d403;
            --brand-mid: #a7f801;
            --brand-end: #fcef03;
            --brand-ink: #020201;
            --brand-link: #0b6b00;
            --brand-link-hover: #064d00;
            --brand-soft: #f4ffe0;
            --brand-soft-yellow: #fffde0;
            --brand-border: #dcefc2;
            --brand-shadow: rgba(11, 107, 0, .18);
            --brand-gradient: linear-gradient(135deg, var(--brand-start) 0%, var(--brand-mid) 55%, var(--brand-end) 100%);
            --bs-link-color: var(--brand-link);
            --bs-link-color-rgb: 11, 107, 0;
            --bs-link-hover-color: var(--brand-link-hover);
            --bs-link-hover-color-rgb: 6, 77, 0;
            --ink: #1f2937;
            --muted: #6b7280;
            --soft: #f7fff0;
        }

        body.admin-body {
            background-color: var(--soft);
            color: var(--ink);
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
        }

        body.admin-body a {
            color: rgba(var(--bs-link-color-rgb), var(--bs-link-opacity, 1));
        }

        body.admin-body a:hover,
        body.admin-body a:focus {
            color: rgba(var(--bs-link-hover-color-rgb), var(--bs-link-opacity, 1));
        }

        .admin-shell {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 260px;
            flex: 0 0 260px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            padding: 1rem;
            color: #fff;
            background: linear-gradient(180deg, #0b3d0b 0%, #042104 100%);
        }

        .admin-sidebar .admin-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .65rem .9rem;
            margin-bottom: .75rem;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
        }

        .admin-sidebar .admin-brand img {
            display: block;
            width: auto;
            max-width: 100%;
            height: 42px;
            object-fit: contain;
        }

        .admin-brand-subtitle {
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, .65);
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-align: center;
            text-transform: uppercase;
        }

        .admin-sidebar .admin-nav {
            gap: .15rem;
        }

        .admin-sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: .65rem;
            padding: .65rem .8rem;
            color: rgba(255, 255, 255, .82);
            border-radius: 10px;
            font-weight: 500;
            transition: background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }

        .admin-sidebar .nav-link i {
            width: 20px;
            text-align: center;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link:focus {
            color: #fff;
            background: rgba(255, 255, 255, .1);
        }

        .admin-sidebar .nav-link.active {
            color: var(--brand-ink);
            background: var(--brand-gradient);
            font-weight: 600;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .18);
        }

        .admin-sidebar .admin-nav-footer {
            border-top: 1px solid rgba(255, 255, 255, .12);
        }

        .admin-sidebar .nav-link.btn-link {
            color: rgba(255, 255, 255, .82);
            text-decoration: none;
        }

        .admin-sidebar-offcanvas {
            --bs-offcanvas-width: 280px;
            color: #fff;
            background: linear-gradient(180deg, #0b3d0b 0%, #042104 100%);
        }

        .admin-sidebar-offcanvas .offcanvas-header {
            border-bottom: 1px solid rgba(255, 255, 255, .12);
        }

        .admin-sidebar-offcanvas .admin-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: .65rem .9rem;
            margin-bottom: .75rem;
            background: #fff;
            border-radius: 14px;
        }

        .admin-sidebar-offcanvas .admin-brand img {
            display: block;
            width: auto;
            max-width: 100%;
            height: 42px;
            object-fit: contain;
        }

        .admin-main {
            display: flex;
            flex: 1 1 auto;
            flex-direction: column;
            min-width: 0;
            min-height: 100vh;
        }

        .admin-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .65rem 1rem;
            background: rgba(255, 255, 255, .95);
            border-bottom: 1px solid rgba(15, 23, 42, .07);
            backdrop-filter: blur(10px);
        }

        .admin-user-btn {
            border: 1px solid var(--brand-border);
            background: #fff;
            color: var(--brand-link);
        }

        .admin-user-btn:hover,
        .admin-user-btn:focus {
            border-color: var(--brand-link);
            background: var(--brand-soft);
            color: var(--brand-link-hover);
        }

        .admin-content {
            flex: 1 1 auto;
            padding: 1.25rem;
        }

        .admin-footer {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: .5rem;
            padding: .85rem 1.25rem;
            color: var(--muted);
            font-size: .875rem;
            background: #fff;
            border-top: 1px solid rgba(15, 23, 42, .07);
        }

        @media (min-width: 992px) {
            .admin-content {
                padding: 1.75rem 2rem;
            }

            .admin-topbar {
                padding: .75rem 2rem;
            }
        }

        .page-header {
            padding: 20px;
            margin-bottom: 24px;
            color: var(--brand-ink);
            background:
                radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .38), transparent 45%),
                var(--brand-gradient);
            border-radius: 16px;
            box-shadow: 0 10px 24px var(--brand-shadow);
        }

        .page-header h1,
        .page-header h2,
        .page-header h3 {
            color: var(--brand-ink);
        }

        .form-container,
        .purchase-card,
        .event-summary,
        .success-card,
        .table-container,
        .filter-card,
        .import-container,
        .instructions-card {
            background: #fff;
            border: 0;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(31, 41, 55, .08);
        }

        .form-container {
            padding: 30px;
        }

        .purchase-card {
            padding: 30px;
        }

        .event-summary {
            padding: 25px;
        }

        .btn-brand {
            background: var(--brand-gradient);
            border: 0;
            color: var(--brand-ink);
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-brand:hover,
        .btn-brand:focus {
            color: var(--brand-ink);
            filter: brightness(1.03);
        }

        .btn-primary,
        .btn-success {
            background: var(--brand-gradient);
            border-color: transparent;
            color: var(--brand-ink);
            font-weight: 600;
        }

        .btn-primary:hover,
        .btn-primary:focus,
        .btn-success:hover,
        .btn-success:focus {
            background: var(--brand-gradient);
            border-color: transparent;
            color: var(--brand-ink);
            filter: brightness(1.03);
        }

        .btn-outline-primary {
            color: var(--brand-link);
            border-color: var(--brand-link);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            color: #fff;
            background: var(--brand-link);
            border-color: var(--brand-link);
        }

        .btn-info {
            background: var(--brand-link);
            border-color: var(--brand-link);
            color: #fff;
        }

        .btn-info:hover,
        .btn-info:focus {
            background: var(--brand-link-hover);
            border-color: var(--brand-link-hover);
            color: #fff;
        }

        .btn-warning {
            background: var(--brand-end);
            border-color: var(--brand-end);
            color: var(--brand-ink);
        }

        .btn-warning:hover,
        .btn-warning:focus {
            background: var(--brand-end);
            border-color: var(--brand-end);
            color: var(--brand-ink);
            filter: brightness(1.03);
        }

        .btn-outline-brand {
            color: var(--brand-link);
            border-color: var(--brand-link);
        }

        .btn-outline-brand:hover,
        .btn-outline-brand:focus {
            color: #fff;
            background: var(--brand-link);
            border-color: var(--brand-link);
        }

        .btn-submit {
            padding: 12px 30px;
            color: var(--brand-ink);
            background: var(--brand-gradient);
            border: 0;
            border-radius: 8px;
            font-weight: 600;
            transition: all .3s ease;
        }

        .btn-submit:hover,
        .btn-submit:focus {
            color: var(--brand-ink);
            box-shadow: 0 4px 12px rgba(21, 212, 3, .3);
            transform: translateY(-2px);
        }

        .text-brand {
            color: var(--brand-link) !important;
        }

        .badge-brand {
            background: var(--brand-gradient);
            color: var(--brand-ink);
            font-weight: 600;
        }

        .bg-primary {
            background-color: var(--brand-link) !important;
            color: #fff !important;
        }

        .bg-success {
            background-color: var(--brand-link) !important;
            color: #fff !important;
        }

        .bg-info {
            background-color: var(--brand-start) !important;
            color: var(--brand-ink) !important;
        }

        .bg-warning {
            background-color: var(--brand-end) !important;
            color: var(--brand-ink) !important;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control,
        .form-select {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 10px 15px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-start);
            box-shadow: 0 0 0 .2rem rgba(21, 212, 3, .25);
        }

        .required::after,
        .form-label.required::after {
            content: " *";
            color: #dc3545;
        }

        .help-text {
            margin-top: 5px;
            color: #6c757d;
            font-size: .875rem;
        }

        .table-container {
            overflow: hidden;
        }

        .table th {
            background-color: var(--brand-soft);
            border-bottom: 2px solid var(--brand-border);
            color: var(--brand-link);
            font-weight: 600;
        }

        .action-buttons .btn {
            margin: 2px;
            min-width: 80px;
        }

        .btn-pdf {
            --bs-btn-bg: #b30b00;
            --bs-btn-border-color: #b30b00;
            --bs-btn-color: #fff;
            --bs-btn-hover-bg: #8f0900;
            --bs-btn-hover-border-color: #8f0900;
            --bs-btn-hover-color: #fff;
            --bs-btn-active-bg: #8f0900;
            --bs-btn-active-border-color: #8f0900;
            --bs-btn-active-color: #fff;
        }

        .search-box {
            max-width: 300px;
        }

        .pagination-container {
            padding: 15px;
            background: #f8fff2;
            border-top: 1px solid var(--brand-border);
        }

        .pagination {
            display: flex;
            flex-wrap: wrap;
            padding-left: 0;
            margin-bottom: 0;
            list-style: none;
        }

        .pagination .page-item {
            margin: 0 2px;
        }

        .pagination .page-link {
            position: relative;
            display: block;
            padding: .375rem .75rem;
            margin-left: -1px;
            line-height: 1.25;
            color: var(--brand-link);
            background-color: #fff;
            border: 1px solid var(--brand-border);
            border-radius: .25rem;
            text-decoration: none;
            transition: all .2s ease;
        }

        .pagination .page-link:hover {
            z-index: 2;
            color: var(--brand-link-hover);
            background-color: var(--brand-soft);
            border-color: #cdeca6;
            transform: translateY(-1px);
        }

        .pagination .page-item.active .page-link {
            z-index: 3;
            color: var(--brand-ink);
            background: var(--brand-gradient);
            border-color: var(--brand-start);
        }

        .pagination .page-item.disabled .page-link {
            color: var(--muted);
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .pagination svg[class*="h-5"] {
            width: 16px;
            height: 16px;
        }

        .pagination .page-item .page-link svg {
            width: 14px;
            height: 14px;
        }

        .pagination .page-link .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .pagination .page-link:has(svg) {
            padding: .375rem .5rem;
        }

        @media (max-width: 768px) {
            .pagination {
                justify-content: center;
            }

            .pagination .page-item {
                margin: 2px;
            }

            .pagination .page-link {
                padding: .25rem .5rem;
                font-size: .875rem;
            }
        }

        .stat-card {
            border: 0;
            border-radius: 12px;
            transition: transform .3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: .8;
        }

        .progress-bar-custom {
            width: 100%;
            height: 6px;
            overflow: hidden;
            background-color: #e9ecef;
            border-radius: 3px;
        }

        .progress-fill {
            height: 100%;
            border-radius: 3px;
            transition: width .3s ease;
        }

        @media (max-width: 575.98px) {
            .admin-content {
                padding: 1rem;
            }

            .page-header {
                padding: 16px;
                border-radius: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar d-none d-md-flex flex-column">
            @include('layouts.partials.admin.sidebar')
        </aside>

        <div class="offcanvas offcanvas-start admin-sidebar-offcanvas" tabindex="-1" id="adminSidebarMobile" aria-labelledby="adminSidebarMobileLabel">
            <div class="offcanvas-header">
                <span class="fw-semibold" id="adminSidebarMobileLabel">Menú</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-3">
                @include('layouts.partials.admin.sidebar')
            </div>
        </div>

        <div class="admin-main">
            @include('layouts.partials.admin.topbar')

            <main class="admin-content flex-grow-1">
                @include('layouts.partials.admin.flash')
                @yield('content')
            </main>

            <footer class="admin-footer">
                <span>&copy; {{ date('Y') }} {{ config('app.name', 'QuieroTIX.com') }}</span>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
