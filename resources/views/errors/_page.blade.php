@php
    $statusCode = (string) ($statusCode ?? '404');
    $title = (string) ($title ?? 'Algo saiu do compasso');
    $eyebrow = (string) ($eyebrow ?? 'Voz & Cifra');
    $message = (string) ($message ?? 'Nao conseguimos abrir esta pagina agora.');
    $hint = (string) ($hint ?? 'Volte para um ponto seguro e tente novamente.');
    $homeUrl = route('root');
    $dashboardRoute = auth()->check() ? auth()->user()?->rotaDestinoAposLogin() : null;
    $dashboardUrl = $dashboardRoute && \Illuminate\Support\Facades\Route::has($dashboardRoute) ? route($dashboardRoute) : null;
@endphp

<x-publico.layouts.app :title="$statusCode . ' | Voz & Cifra'" :description="$message">
    <style>
        .error-page {
            min-height: calc(100vh - 92px);
            display: grid;
            place-items: center;
            padding: clamp(2rem, 6vw, 5rem) 0;
        }

        .error-card {
            position: relative;
            width: min(100%, 980px);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.08), rgba(255, 255, 255, 0.02)),
                var(--panel);
            box-shadow: var(--shadow);
            padding: clamp(1.35rem, 4vw, 3rem);
        }

        .error-card::before {
            content: "";
            position: absolute;
            inset: 1rem;
            border-radius: calc(var(--radius-xl) - 0.7rem);
            border: 1px solid rgba(244, 221, 180, 0.10);
            pointer-events: none;
        }

        .error-grid {
            position: relative;
            z-index: 1;
            display: grid;
            gap: clamp(1.5rem, 4vw, 3rem);
            align-items: center;
        }

        .error-visual {
            position: relative;
            display: grid;
            min-height: 260px;
            place-items: center;
            border-radius: var(--radius-lg);
            background:
                radial-gradient(circle at 50% 42%, rgba(244, 221, 180, 0.22), transparent 34%),
                rgba(255, 255, 255, 0.035);
            border: 1px solid rgba(244, 221, 180, 0.14);
        }

        .error-logo {
            position: relative;
            z-index: 2;
            width: clamp(92px, 18vw, 146px);
            height: clamp(92px, 18vw, 146px);
            display: grid;
            place-items: center;
            border-radius: 32px;
            background: rgba(14, 9, 9, 0.72);
            border: 1px solid rgba(244, 221, 180, 0.22);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.28);
            animation: error-float 4.8s ease-in-out infinite;
        }

        .error-logo img {
            width: 70%;
            height: 70%;
            object-fit: contain;
        }

        .error-staff {
            position: absolute;
            inset: 18%;
            display: grid;
            align-content: center;
            gap: 15px;
            opacity: 0.58;
            transform: rotate(-7deg);
        }

        .error-staff span {
            display: block;
            height: 2px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, rgba(244, 221, 180, 0.58), transparent);
            animation: error-pulse 2.8s ease-in-out infinite;
        }

        .error-staff span:nth-child(2) { animation-delay: 0.16s; }
        .error-staff span:nth-child(3) { animation-delay: 0.32s; }
        .error-staff span:nth-child(4) { animation-delay: 0.48s; }
        .error-staff span:nth-child(5) { animation-delay: 0.64s; }

        .error-note {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: var(--gold-soft);
            box-shadow: 0 0 0 8px rgba(244, 221, 180, 0.09);
            animation: error-note 5.4s ease-in-out infinite;
        }

        .error-note::after {
            content: "";
            position: absolute;
            left: 14px;
            bottom: 10px;
            width: 3px;
            height: 52px;
            border-radius: 999px;
            background: var(--gold-soft);
        }

        .error-note--one { left: 18%; top: 34%; }
        .error-note--two { right: 16%; bottom: 28%; animation-delay: 1.2s; }

        .error-copy {
            max-width: 38rem;
        }

        .error-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            margin: 0 0 1rem;
            color: var(--gold-soft);
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .error-kicker strong {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 3.2rem;
            min-height: 2.2rem;
            border-radius: 999px;
            background: rgba(244, 221, 180, 0.12);
            border: 1px solid rgba(244, 221, 180, 0.18);
            color: var(--gold-soft);
            letter-spacing: 0;
        }

        .error-title {
            margin: 0;
            max-width: 12ch;
            font-family: var(--font-display);
            font-size: clamp(2.4rem, 7vw, 5.2rem);
            line-height: 0.98;
            letter-spacing: -0.04em;
        }

        .error-message {
            margin: 1.15rem 0 0;
            color: var(--muted);
            font-size: clamp(1rem, 2.4vw, 1.16rem);
            line-height: 1.8;
        }

        .error-hint {
            margin: 1rem 0 0;
            color: var(--gold-soft);
            line-height: 1.7;
        }

        .error-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .error-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.15rem;
            border-radius: 999px;
            padding: 0 1.2rem;
            border: 1px solid var(--line);
            color: var(--text);
            font: inherit;
            font-weight: 850;
            cursor: pointer;
        }

        .error-action--primary {
            background: #5f7f57;
            border-color: rgba(126, 168, 117, 0.35);
            color: #f6fff3;
        }

        .error-action--secondary {
            background: rgba(255, 255, 255, 0.04);
        }

        .error-action:hover,
        .error-action:focus-visible {
            transform: translateY(-1px);
            outline: none;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.22);
        }

        @keyframes error-float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes error-pulse {
            0%, 100% { opacity: 0.32; transform: scaleX(0.92); }
            50% { opacity: 0.82; transform: scaleX(1); }
        }

        @keyframes error-note {
            0%, 100% { transform: translateY(0) rotate(-5deg); }
            50% { transform: translateY(-12px) rotate(5deg); }
        }

        @media (min-width: 820px) {
            .error-grid {
                grid-template-columns: minmax(280px, 0.82fr) minmax(0, 1fr);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .error-logo,
            .error-staff span,
            .error-note {
                animation: none;
            }

            .error-action:hover,
            .error-action:focus-visible {
                transform: none;
            }
        }
    </style>

    <main class="container error-page">
        <section class="error-card" aria-labelledby="error-title">
            <div class="error-grid">
                <div class="error-visual" aria-hidden="true">
                    <div class="error-staff">
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <span class="error-note error-note--one"></span>
                    <span class="error-note error-note--two"></span>
                    <div class="error-logo">
                        <img src="{{ asset('logo/final.png') }}" alt="">
                    </div>
                </div>

                <div class="error-copy">
                    <p class="error-kicker"><strong>{{ $statusCode }}</strong> {{ $eyebrow }}</p>
                    <h1 class="error-title" id="error-title">{{ $title }}</h1>
                    <p class="error-message">{{ $message }}</p>
                    <p class="error-hint">{{ $hint }}</p>

                    <div class="error-actions">
                        <a href="{{ $homeUrl }}" class="error-action error-action--primary">Ir para o inicio</a>
                        @if ($dashboardUrl)
                            <a href="{{ $dashboardUrl }}" class="error-action error-action--secondary">Abrir meu painel</a>
                        @endif
                        <button type="button" class="error-action error-action--secondary" onclick="window.history.length > 1 ? window.history.back() : window.location.assign('{{ $homeUrl }}')">
                            Voltar
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-publico.layouts.app>
