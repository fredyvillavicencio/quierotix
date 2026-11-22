<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sorteo de {{ $event->nombre }} | {{ config('app.name', 'QuieroTIX.com') }}</title>
    <meta name="description" content="Participa en el sorteo de un ticket de regalo para {{ $event->nombre }}.">
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
            --brand-gradient: linear-gradient(135deg, var(--brand-start) 0%, var(--brand-mid) 55%, var(--brand-end) 100%);
            --bs-link-color: var(--brand-link);
            --bs-link-color-rgb: 11, 107, 0;
            --bs-link-hover-color: var(--brand-link-hover);
            --bs-link-hover-color-rgb: 6, 77, 0;
            --ink: #102006;
            --muted: #5e6b55;
            --ticket-shadow: rgba(11, 107, 0, .18);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            background: #ffffff;
            color: var(--ink);
            font-family: "Segoe UI", system-ui, -apple-system, "Helvetica Neue", Arial, sans-serif;
        }

        a {
            color: rgba(var(--bs-link-color-rgb), var(--bs-link-opacity, 1));
        }

        a:hover,
        a:focus {
            color: rgba(var(--bs-link-hover-color-rgb), var(--bs-link-opacity, 1));
        }

        .gift-page {
            width: min(100%, 1040px);
            text-align: center;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            max-width: 100%;
            margin-top: 1rem;
            margin-bottom: 20px;
            padding: .55rem 1rem;
            border: 1px solid #d8f7b0;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand-link);
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .14em;
            overflow-wrap: anywhere;
            text-align: center;
            text-transform: uppercase;
        }

        .brand-pill i {
            font-size: 1rem;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.9rem, 5vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.05;
        }

        .gift-subtitle {
            max-width: 600px;
            margin: 1rem auto 0;
            color: var(--muted);
            font-size: clamp(.95rem, 2vw, 1.1rem);
            line-height: 1.6;
        }

        .ticket-stage {
            position: relative;
            width: min(100%, 900px);
            margin: -1.25rem auto 0;
            aspect-ratio: 900 / 420;
            isolation: isolate;
        }

        .ticket-stage::before {
            content: "";
            position: absolute;
            inset: 12% 14%;
            z-index: -1;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(21, 212, 3, .22), rgba(167, 248, 1, 0) 70%);
            opacity: 0;
            transform: scale(.75);
            transition: opacity .35s ease, transform .35s ease;
        }

        .ticket-stage.is-spinning::before {
            opacity: 1;
            transform: scale(1);
            animation: prize-glow 1.1s ease-in-out infinite alternate;
        }

        .ticket-image {
            display: block;
            width: 100%;
            height: 100%;
            filter: drop-shadow(0 24px 30px rgba(11, 107, 0, .12));
            transition: transform .35s ease, filter .35s ease;
        }

        .ticket-stage.is-spinning .ticket-image {
            transform: translateY(-4px);
            filter: drop-shadow(0 28px 36px var(--ticket-shadow));
        }

        .ticket-display {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6% 9%;
            pointer-events: none;
        }

        .ticket-display-label {
            margin-bottom: .4rem;
            color: #5f7a4a;
            font-size: clamp(.62rem, 1.4vw, .8rem);
            font-weight: 800;
            letter-spacing: .22em;
            text-transform: uppercase;
        }

        .ticket-code {
            display: block;
            max-width: 100%;
            color: var(--ink);
            font-family: "SFMono-Regular", ui-monospace, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: clamp(3rem, 11vw, 7.4rem);
            font-weight: 900;
            letter-spacing: .08em;
            line-height: 1;
            text-shadow: 0 2px 0 #ffffff, 0 8px 18px rgba(11, 107, 0, .08);
            overflow-wrap: anywhere;
        }

        .ticket-code.is-spinning {
            color: var(--brand-link);
            animation: code-flicker .11s steps(2, end) infinite;
        }

        .ticket-code.is-revealed {
            color: var(--brand-link);
            animation: code-reveal .55s cubic-bezier(.2, .9, .25, 1.25);
        }

        .btn-spin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            margin-top: 1.4rem;
            padding: .95rem 2.2rem;
            border: 0;
            border-radius: 999px;
            background: var(--brand-gradient);
            box-shadow: 0 14px 30px rgba(11, 107, 0, .22);
            color: var(--brand-ink);
            cursor: pointer;
            font-size: 1.05rem;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        }

        .btn-spin:hover:not(:disabled),
        .btn-spin:focus-visible:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(11, 107, 0, .28);
            filter: brightness(1.03);
        }

        .btn-spin:focus-visible {
            outline: 3px solid rgba(21, 212, 3, .35);
            outline-offset: 4px;
        }

        .btn-spin:disabled {
            cursor: wait;
            opacity: .68;
            transform: none;
        }

        @keyframes code-flicker {
            0% { transform: translateY(-1px); opacity: .72; }
            100% { transform: translateY(1px); opacity: 1; }
        }

        @keyframes code-reveal {
            0% { transform: scale(.72); opacity: .25; }
            65% { transform: scale(1.08); opacity: 1; }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes prize-glow {
            from { opacity: .55; transform: scale(.94); }
            to { opacity: 1; transform: scale(1.06); }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 1.5rem .8rem;
            }

            .ticket-display {
                padding: 7% 6%;
            }

            .ticket-stage {
                margin-top: -.5rem;
            }

            .ticket-display-label {
                margin-bottom: .2rem;
            }

            .btn-spin {
                width: 100%;
                max-width: 320px;
            }

            .gift-subtitle {
                margin-top: .75rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>
<body>
    <main class="gift-page">

        <h1>Tu ticket de la suerte</h1>

        <div class="brand-pill">
            {{ $event->nombre }} · {{ \Carbon\Carbon::parse($event->fecha)->format('d/m/Y') }}
        </div>

        <div class="ticket-stage" id="ticket-stage">
            <img src="{{ asset('public/images/gift-ticket.svg') }}"
                 alt="Ticket de regalo"
                 class="ticket-image">

            <div class="ticket-display">
                <span class="ticket-display-label" id="display-label">Número ganador</span>
                <span class="ticket-code" id="ticket-code" aria-live="polite">---</span>
            </div>
        </div>

        <button type="button" class="btn-spin" id="spin-button">
            <i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i>
            Iniciar sorteo
        </button>
    </main>

    <script>
        (() => {
            const spinButton = document.getElementById('spin-button');
            const ticketStage = document.getElementById('ticket-stage');
            const ticketCode = document.getElementById('ticket-code');
            const displayLabel = document.getElementById('display-label');
            const endpoint = @json(route('gifts.random', ['event_id' => $event->id]));
            const spinDuration = 3000;
            const frameInterval = 55;

            let isSpinning = false;

            const wait = (milliseconds) => new Promise((resolve) => window.setTimeout(resolve, milliseconds));

            const createRandomNumber = (length = 3) => {
                const targetLength = Math.max(1, Number(length) || 1);

                return String(Math.floor(Math.random() * (10 ** targetLength))).padStart(targetLength, '0');
            };

            const fetchRandomTicket = async () => {
                const response = await fetch(endpoint, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok || payload.numero === undefined || payload.numero === null) {
                    throw new Error(payload.message || 'No hay tickets disponibles para sortear.');
                }

                return String(payload.numero);
            };

            spinButton.addEventListener('click', async () => {
                if (isSpinning) {
                    return;
                }

                isSpinning = true;
                spinButton.disabled = true;
                spinButton.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Girando...';
                ticketStage.classList.add('is-spinning');
                ticketCode.classList.remove('is-revealed');
                ticketCode.classList.add('is-spinning');
                ticketCode.textContent = createRandomNumber(3);
                displayLabel.textContent = 'Buscando número';

                let targetLength = 3;

                const request = fetchRandomTicket().then((numero) => {
                    targetLength = Math.max(3, numero.length);

                    return numero;
                });

                const animationInterval = window.setInterval(() => {
                    ticketCode.textContent = createRandomNumber(targetLength);
                }, frameInterval);

                try {
                    const [finalNumber] = await Promise.all([request, wait(spinDuration)]);

                    ticketCode.textContent = finalNumber;
                    ticketCode.classList.remove('is-spinning');
                    ticketCode.classList.add('is-revealed');
                    displayLabel.textContent = '¡Número ganador!';
                } catch {
                    ticketCode.textContent = '---';
                    ticketCode.classList.remove('is-spinning');
                    displayLabel.textContent = 'Sorteo no disponible';
                } finally {
                    window.clearInterval(animationInterval);
                    ticketStage.classList.remove('is-spinning');
                    spinButton.disabled = false;
                    spinButton.innerHTML = '<i class="bi bi-arrow-repeat" aria-hidden="true"></i> Sortear de nuevo';
                    isSpinning = false;
                }
            });
        })();
    </script>
</body>
</html>
