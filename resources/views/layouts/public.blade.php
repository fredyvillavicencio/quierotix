<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'QuieroTIX.com'))</title>
    <meta name="description" content="@yield('meta_description', 'Consulta los próximos eventos y toda su información: fecha, hora y ubicación.')">
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
        body {
            background-color: var(--soft);
            color: var(--ink);
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        a {
            color: rgba(var(--bs-link-color-rgb), var(--bs-link-opacity, 1));
        }
        a:hover,
        a:focus {
            color: rgba(var(--bs-link-hover-color-rgb), var(--bs-link-opacity, 1));
        }
        main { flex: 1 0 auto; }
        .site-navbar {
            background: rgba(255, 255, 255, .95);
            backdrop-filter: blur(10px);
            box-shadow: 0 1px 0 rgba(15, 23, 42, .07);
        }
        .navbar-brand-logo {
            display: block;
            height: 50px;
            width: auto;
            max-width: 180px;
            object-fit: contain;
        }

        .navbar {
            --bs-navbar-padding-x: 0;
            --bs-navbar-padding-y: 0;
        }

        @media (max-width: 575.98px) {
            .navbar-brand-logo { height: 34px; }
        }
        .btn-brand {
            background: var(--brand-gradient);
            border: 0;
            color: var(--brand-ink);
        }
        .btn-brand:hover, .btn-brand:focus { color: var(--brand-ink); filter: brightness(1.03); }
        .text-brand { color: var(--brand-link) !important; }
        .hero {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            color: var(--brand-ink);
            background:
                radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .38), transparent 45%),
                var(--brand-gradient);
        }
        .hero::after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .24);
            right: -70px;
            bottom: -120px;
        }
        .hero-badge {
            background: rgba(2, 2, 1, .12);
            border: 1px solid rgba(2, 2, 1, .18);
            color: var(--brand-ink);
            font-weight: 500;
        }
        .detail-hero-overlay .hero-badge {
            background: rgba(255, 255, 255, .18);
            border-color: rgba(255, 255, 255, .35);
            color: #fff;
        }
        .hero-stat { position: relative; z-index: 1; }
        .hero-stat-number { font-size: 2.2rem; font-weight: 700; line-height: 1; }
        .event-card {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 24px rgba(31, 41, 55, .08);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .event-card:hover { transform: translateY(-4px); box-shadow: 0 18px 34px var(--brand-shadow); }
        .event-cover { display: block; width: 100%; aspect-ratio: 16 / 9; object-fit: cover; background: var(--brand-soft); }
        .event-cover-placeholder {
            aspect-ratio: 16 / 9;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .35rem;
            color: var(--brand-ink);
            background: var(--brand-gradient);
            font-size: .85rem;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .event-cover-placeholder i { font-size: 2rem; }
        .date-badge { background: var(--brand-soft); color: var(--brand-link); }
        .time-badge { background: var(--brand-soft-yellow); color: #7a5b00; }
        .event-card .card-title a { color: inherit; }
        .event-card .card-title a:hover { color: var(--brand-link); }
        .event-card-cta { position: relative; z-index: 2; }
        .detail-hero {
            position: relative;
            border-radius: 22px;
            overflow: hidden;
            background: var(--brand-gradient);
            min-height: 280px;
            display: flex;
            align-items: flex-end;
        }
        .detail-hero-img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .detail-hero-overlay {
            position: relative;
            width: 100%;
            color: #fff;
            background: linear-gradient(180deg, rgba(17, 24, 39, 0) 0%, rgba(17, 24, 39, .78) 100%);
        }
        .detail-list dt { font-weight: 600; color: #4b5563; }
        .detail-list dd { margin-bottom: 0; border-bottom: 1px solid #eef0f4; padding-bottom: .65rem; }
        .detail-list dd:last-of-type { border-bottom: 0; }
        .detail-list dt, .detail-list dd { padding-top: .15rem; }
        .purchase-cta {
            position: relative;
            overflow: hidden;
            border-radius: 22px;
            color: var(--brand-ink);
            background:
                radial-gradient(circle at 85% 20%, rgba(255, 255, 255, .38), transparent 45%),
                var(--brand-gradient);
        }
        .purchase-cta .btn-cta {
            background: #fff;
            border: 0;
            color: var(--brand-link);
            font-weight: 600;
        }
        .purchase-cta .btn-cta:hover,
        .purchase-cta .btn-cta:focus {
            color: var(--brand-link);
            filter: brightness(.96);
        }
        .purchase-cta .btn-cta:disabled {
            background: rgba(255, 255, 255, .78);
            color: var(--muted);
        }
        .btn-outline-brand {
            color: var(--brand-link);
            border-color: var(--brand-link);
        }
        .btn-outline-brand:hover,
        .btn-outline-brand:focus {
            background: var(--brand-link);
            border-color: var(--brand-link);
            color: #fff;
        }
        .ticket-row { border-bottom: 1px solid #eef0f4; padding: .85rem 0; }
        .ticket-row:last-child { border-bottom: 0; }
        .success-icon { font-size: 3.5rem; color: #198754; line-height: 1; }
        .site-footer {
            flex-shrink: 0;
            background: #fff;
            border-top: 1px solid rgba(15, 23, 42, .07);
            color: var(--muted);
            font-size: .875rem;
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('images/logo_quierotix.png') }}"
                     alt="QuieroTIX.com"
                     class="navbar-brand-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar"
                    aria-controls="publicNavbar" aria-expanded="false" aria-label="Mostrar navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

        </div>
    </nav>

    <main class="py-4 py-lg-5" style="padding-top: 1rem !important;">
        @yield('content')
    </main>

    <footer class="site-footer py-4">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>&copy; {{ date('Y') }} {{ config('app.name', 'QuieroTIX.com') }}</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
