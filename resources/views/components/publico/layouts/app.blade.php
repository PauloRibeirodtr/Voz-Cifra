@props([
    'title' => 'Voz & Cifra',
    'description' => 'Acompanhe missas, consulte igrejas e acesse o sistema Voz & Cifra.',
    'forcedContrast' => null,
])

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <style>
        :root {
            color-scheme: dark;
            --bg: #140d0d;
            --bg-soft: #241616;
            --panel: rgba(38, 22, 22, 0.92);
            --panel-strong: rgba(56, 32, 28, 0.96);
            --line: rgba(205, 170, 112, 0.24);
            --text: #fff8ed;
            --muted: #e2d2bf;
            --gold: #c9a15f;
            --gold-soft: #f4ddb4;
            --wine: #4a1f24;
            --success: #5f7f57;
            --warning: #a67b43;
            --danger: #8f4d41;
            --shadow: 0 26px 80px rgba(0, 0, 0, 0.4);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 18px;
            --page-width: 1200px;
            --font-display: Georgia, "Times New Roman", serif;
            --font-body: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            --reading-size: 1.08;
        }

        body[data-contrast='high'] {
            --panel: rgba(18, 10, 10, 0.98);
            --panel-strong: rgba(24, 14, 12, 1);
            --line: rgba(255, 222, 162, 0.42);
            --text: #fffdf7;
            --muted: #f5e6cb;
            --gold: #f2c97d;
            --gold-soft: #ffe7bb;
            --shadow: 0 0 0 1px rgba(255, 231, 187, 0.14), 0 26px 80px rgba(0, 0, 0, 0.58);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top, rgba(201, 161, 95, 0.13), transparent 30%),
                radial-gradient(circle at right top, rgba(74, 31, 36, 0.28), transparent 26%),
                linear-gradient(180deg, #261513 0%, #170f0f 52%, #100909 100%);
            color: var(--text);
            font-family: var(--font-body);
            overflow-x: hidden;
            font-size: calc(16px * var(--reading-size));
            transition: background-color .2s ease, color .2s ease;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .container { width: min(calc(100% - 1.5rem), var(--page-width)); margin: 0 auto; }
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(14px);
            background: rgba(20, 13, 13, 0.9);
            border-bottom: 1px solid rgba(205, 170, 112, 0.18);
        }
        .site-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.95rem 0;
        }
        .brand { display: inline-flex; align-items: center; gap: 0.85rem; }
        .brand img { width: 3rem; height: 3rem; border-radius: 999px; box-shadow: 0 12px 26px rgba(0, 0, 0, 0.24); }
        .brand__eyebrow {
            margin: 0;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.22em;
            font-size: 0.7rem;
            font-weight: 800;
        }
        .brand__name {
            margin: 0.18rem 0 0;
            font-family: var(--font-display);
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .site-header__actions {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }
        .toolbar-button,
        .nav-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 3.45rem;
            min-height: 3.45rem;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.03);
            color: var(--text);
            cursor: pointer;
            font: inherit;
            font-weight: 700;
            padding: 0 1rem;
        }
        .toolbar-button {
            min-width: auto;
            font-size: 0.95rem;
        }
        .nav-toggle span, .nav-toggle span::before, .nav-toggle span::after {
            display: block;
            width: 1.15rem;
            height: 2px;
            border-radius: 999px;
            background: currentColor;
            transition: transform 0.24s ease, opacity 0.24s ease;
            content: "";
        }
        .nav-toggle span::before { transform: translateY(-0.36rem); }
        .nav-toggle span::after { transform: translateY(0.26rem); }
        .nav-toggle[aria-expanded="true"] span { background: transparent; }
        .nav-toggle[aria-expanded="true"] span::before { transform: rotate(45deg); }
        .nav-toggle[aria-expanded="true"] span::after { transform: rotate(-45deg); }
        .site-nav {
            position: absolute;
            inset: calc(100% + 0.35rem) 0.75rem auto;
            display: none;
            flex-direction: column;
            gap: 0.4rem;
            padding: 0.7rem;
            border-radius: 1.2rem;
            background: rgba(33, 18, 18, 0.98);
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
        }
        .site-nav[data-open="true"] { display: flex; }
        .site-nav a {
            padding: 1rem 1.05rem;
            border-radius: 0.95rem;
            color: var(--muted);
            font-weight: 700;
        }
        .site-nav__login {
            color: #ddefdd !important;
            background: rgba(95, 127, 87, 0.18);
            border: 1px solid rgba(126, 168, 117, 0.22);
        }
        .site-nav a:hover, .site-nav a:focus-visible,
        .toolbar-button:hover, .toolbar-button:focus-visible,
        .nav-toggle:hover, .nav-toggle:focus-visible {
            background: rgba(201, 161, 95, 0.1);
            color: var(--text);
            outline: none;
        }
        .site-nav__login:hover,
        .site-nav__login:focus-visible {
            background: rgba(95, 127, 87, 0.26) !important;
            color: #f4fff1 !important;
        }
        .hero { padding: 0.65rem 0 0.55rem; }
        .hero__panel {
            position: relative;
            min-height: 58vh;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow);
            background: #120d09;
        }
        .hero__image {
            position: absolute;
            inset: 0;
            background-position: center;
            background-size: cover;
            transform: scale(1.02);
        }
        .hero__overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(12, 7, 7, 0.28), rgba(12, 7, 7, 0.88)),
                linear-gradient(90deg, rgba(16, 10, 10, 0.92), rgba(16, 10, 10, 0.42));
        }
        .hero__content {
            position: relative;
            z-index: 1;
            display: flex;
            min-height: 58vh;
            flex-direction: column;
            justify-content: center;
            padding: 1rem;
        }
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            width: fit-content;
            border-radius: 999px;
            border: 1px solid rgba(241, 213, 167, 0.22);
            background: rgba(43, 21, 22, 0.74);
            color: var(--gold-soft);
            padding: 0.78rem 1.05rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 0.78rem;
            font-weight: 800;
        }
        .hero__title {
            max-width: 15ch;
            margin: 0.75rem 0 0;
            font-family: var(--font-display);
            font-size: clamp(2rem, 6.2vw, 4rem);
            line-height: 1.02;
            letter-spacing: -0.04em;
            text-wrap: balance;
        }
        .hero__lead {
            max-width: 44rem;
            margin: 0.75rem 0 0;
            color: #f0e4d4;
            font-size: clamp(0.98rem, 2.4vw, 1.14rem);
            line-height: 1.55;
        }
        .hero__actions {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            margin-top: 1rem;
        }
        .hero__foot {
            display: grid;
            gap: 0.9rem;
            margin-top: 1.8rem;
        }
        .hero-stat {
            border-radius: 1.25rem;
            padding: 1.15rem;
            background: rgba(42, 24, 24, 0.76);
            border: 1px solid rgba(205, 170, 112, 0.16);
        }
        .hero-stat__label { display: block; color: var(--muted); font-size: 0.98rem; }
        .hero-stat__value { display: block; margin-top: 0.4rem; font-size: 2rem; font-weight: 800; }
        .section { padding: 1rem 0; }
        .section__header { display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1.2rem; }
        .section__title {
            margin: 0;
            font-family: var(--font-display);
            font-size: clamp(2rem, 5vw, 3.2rem);
            line-height: 1.12;
        }
        .section__lead {
            margin: 0;
            max-width: 50rem;
            color: var(--muted);
            line-height: 1.95;
            font-size: 1.08rem;
        }
        .summary-grid, .church-grid, .cards-grid { display: grid; gap: 1rem; }
        .team-grid { display: grid; gap: 1rem; }
        .summary-card, .church-card, .filter-card, .missa-card {
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow);
        }
        .team-card {
            display: grid;
            gap: 0.85rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: linear-gradient(180deg, rgba(54, 31, 29, 0.95), rgba(28, 17, 17, 0.96));
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .team-card__photo {
            aspect-ratio: 4 / 3.7;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.04);
        }
        .team-card__photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .team-card__content {
            display: grid;
            gap: 0.45rem;
            padding: 0 1rem 1rem;
        }
        .team-card__role {
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-size: 0.76rem;
            font-weight: 800;
        }
        .team-card__name {
            margin: 0;
            font-family: var(--font-display);
            font-size: 1.35rem;
            line-height: 1.15;
        }
        .team-card__description {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.75;
        }
        .team-card__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            margin-top: 0.2rem;
        }
        .team-card__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.85rem;
            border-radius: 0.95rem;
            padding: 0.65rem 0.95rem;
            font-weight: 700;
            text-align: center;
        }
        .team-card__link {
            background: rgba(201, 161, 95, 0.14);
            border: 1px solid rgba(201, 161, 95, 0.24);
            color: var(--gold-soft);
        }
        .institution-card {
            display: grid;
            gap: 1rem;
            align-items: center;
            border-radius: var(--radius-lg);
            border: 1px solid var(--line);
            background: linear-gradient(135deg, rgba(54, 31, 29, 0.95), rgba(28, 17, 17, 0.98));
            box-shadow: var(--shadow);
            padding: 1.35rem;
        }
        .institution-card__logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: min(100%, 220px);
            min-height: 90px;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
        }
        .institution-card__logo img {
            max-width: 100%;
            max-height: 72px;
            object-fit: contain;
        }
        .institution-card__title {
            margin: 0;
            font-family: var(--font-display);
            font-size: clamp(1.8rem, 4vw, 2.5rem);
            line-height: 1.12;
        }
        .institution-card__text {
            margin: 0;
            color: var(--muted);
            font-size: 1.02rem;
            line-height: 1.9;
        }
        .institution-card__actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .institution-card__action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3.2rem;
            border-radius: 999px;
            padding: 0 1rem;
            border: 1px solid rgba(201, 161, 95, 0.22);
            background: rgba(201, 161, 95, 0.12);
            color: var(--gold-soft);
            font-weight: 800;
            text-align: center;
        }
        .summary-card, .church-card, .filter-card { padding: 1.35rem; }
        .summary-card__title, .church-card__title { margin: 0; font-size: 1.02rem; color: var(--muted); }
        .summary-card__value {
            display: block;
            margin-top: 0.65rem;
            font-size: clamp(2.2rem, 7vw, 3rem);
            line-height: 1;
            font-weight: 900;
            color: var(--gold-soft);
        }
        .summary-card__description, .church-card__meta {
            margin: 0.75rem 0 0;
            color: var(--muted);
            line-height: 1.9;
            font-size: 1rem;
        }
        .filters-grid { display: grid; gap: 0.9rem; }
        .field label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--gold-soft);
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }
        .field input, .field select {
            width: 100%;
            border: 1px solid rgba(210, 170, 102, 0.18);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.03);
            color: var(--text);
            padding: 1.08rem 1rem;
            font: inherit;
            min-height: 3.55rem;
        }
        .field input::placeholder { color: #b79f83; }
        .field input:focus, .field select:focus {
            outline: 3px solid rgba(210, 170, 102, 0.18);
            border-color: rgba(241, 213, 167, 0.4);
        }
        .filter-actions { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem; }
        .site-footer {
            margin-top: 2rem;
            padding: 2rem 0 2.8rem;
            border-top: 1px solid rgba(210, 170, 102, 0.12);
        }
        .site-footer__grid { display: grid; gap: 1rem; }
        .site-footer__title { margin: 0; font-family: var(--font-display); font-size: 1.35rem; }
        .site-footer__text, .site-footer__links a { color: var(--muted); line-height: 1.9; font-size: 1rem; }
        .site-footer__links { display: grid; gap: 0.4rem; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }

        @media (max-width: 767px) {
            .toolbar-button span:last-child {
                display: none;
            }
        }

        @media (min-width: 768px) {
            .site-nav {
                position: static;
                display: flex;
                flex-direction: row;
                align-items: center;
                gap: 0.2rem;
                padding: 0;
                background: transparent;
                border: 0;
                box-shadow: none;
            }
            .nav-toggle { display: none; }
            .hero__content { padding: 1.65rem; }
            .hero__actions, .filter-actions { flex-direction: row; flex-wrap: wrap; }
            .hero__foot { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cards-grid, .church-grid, .site-footer__grid, .team-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .institution-card {
                grid-template-columns: auto minmax(0, 1fr);
            }
            .institution-card__actions {
                flex-direction: row;
                flex-wrap: wrap;
            }
        }

        @media (min-width: 1080px) {
            .hero__content { padding: 2rem; }
            .summary-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .filters-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .church-grid, .site-footer__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .team-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
    </style>
    @stack('styles')
</head>
<body data-contrast="{{ $forcedContrast === 'high' ? 'high' : 'normal' }}" data-forced-contrast="{{ $forcedContrast }}">
    {{ $slot }}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navToggle = document.querySelector('[data-nav-toggle]');
            const nav = document.querySelector('[data-site-nav]');
            const contrastToggle = document.querySelector('[data-contrast-toggle]');
            const storageKey = 'vozecifra-public-contrast';
            const body = document.body;
            const forcedContrast = body.dataset.forcedContrast || null;

            const aplicarContraste = (modo) => {
                body.dataset.contrast = modo === 'high' ? 'high' : 'normal';

                if (contrastToggle) {
                    contrastToggle.setAttribute('aria-pressed', modo === 'high' ? 'true' : 'false');
                    contrastToggle.querySelector('[data-contrast-label]').textContent = modo === 'high'
                        ? 'Contraste alto ligado'
                        : 'Contraste alto';
                }
            };

            if (forcedContrast === 'high' || forcedContrast === 'normal') {
                aplicarContraste(forcedContrast);
            } else {
                aplicarContraste(localStorage.getItem(storageKey) || 'normal');
            }

            if (contrastToggle && !forcedContrast) {
                contrastToggle.addEventListener('click', () => {
                    const novoModo = body.dataset.contrast === 'high' ? 'normal' : 'high';
                    localStorage.setItem(storageKey, novoModo);
                    aplicarContraste(novoModo);
                });
            }

            if (navToggle && nav) {
                navToggle.addEventListener('click', () => {
                    const expanded = navToggle.getAttribute('aria-expanded') === 'true';
                    navToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    nav.dataset.open = expanded ? 'false' : 'true';
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
