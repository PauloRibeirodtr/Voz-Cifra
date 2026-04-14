<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Voz & Cifra' }}</title>
    <meta name="description" content="{{ $description ?? 'Acompanhe missas, consulte igrejas e acesse o sistema Voz & Cifra.' }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <style>
        :root {
            color-scheme: dark;
            --bg: #120d09;
            --bg-soft: #1c1510;
            --panel: rgba(34, 24, 17, 0.88);
            --panel-strong: rgba(45, 31, 21, 0.94);
            --line: rgba(224, 191, 140, 0.16);
            --text: #f5efe6;
            --muted: #ccbba7;
            --gold: #d2aa66;
            --gold-soft: #f1d5a7;
            --brown: #6d4a2f;
            --success: #4f8f67;
            --warning: #a37645;
            --danger: #8b4a3f;
            --shadow: 0 24px 70px rgba(0, 0, 0, 0.34);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 18px;
            --page-width: 1200px;
            --font-display: Georgia, "Times New Roman", serif;
            --font-body: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top, rgba(210, 170, 102, 0.12), transparent 32%),
                linear-gradient(180deg, #1a130e 0%, #120d09 55%, #0f0906 100%);
            color: var(--text);
            font-family: var(--font-body);
            overflow-x: hidden;
        }

        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        .container { width: min(calc(100% - 2rem), var(--page-width)); margin: 0 auto; }
        .site-header {
            position: sticky; top: 0; z-index: 50; backdrop-filter: blur(14px);
            background: rgba(18, 13, 9, 0.82); border-bottom: 1px solid rgba(210, 170, 102, 0.12);
        }
        .site-header__inner {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.9rem 0;
        }
        .brand { display: inline-flex; align-items: center; gap: 0.85rem; }
        .brand img { width: 2.9rem; height: 2.9rem; border-radius: 999px; box-shadow: 0 12px 26px rgba(0, 0, 0, 0.24); }
        .brand__eyebrow {
            margin: 0; color: var(--gold); text-transform: uppercase; letter-spacing: 0.22em; font-size: 0.68rem; font-weight: 800;
        }
        .brand__name { margin: 0.15rem 0 0; font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; }
        .nav-toggle {
            display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; border-radius: 999px;
            border: 1px solid var(--line); background: rgba(255, 255, 255, 0.03); color: var(--text); cursor: pointer;
        }
        .nav-toggle span, .nav-toggle span::before, .nav-toggle span::after {
            display: block; width: 1.15rem; height: 2px; border-radius: 999px; background: currentColor;
            transition: transform 0.24s ease, opacity 0.24s ease; content: "";
        }
        .nav-toggle span::before { transform: translateY(-0.36rem); }
        .nav-toggle span::after { transform: translateY(0.26rem); }
        .nav-toggle[aria-expanded="true"] span { background: transparent; }
        .nav-toggle[aria-expanded="true"] span::before { transform: rotate(45deg); }
        .nav-toggle[aria-expanded="true"] span::after { transform: rotate(-45deg); }
        .site-nav {
            position: absolute; inset: calc(100% + 0.35rem) 1rem auto; display: none; flex-direction: column; gap: 0.4rem; padding: 0.7rem;
            border-radius: 1.2rem; background: rgba(27, 19, 14, 0.98); border: 1px solid var(--line); box-shadow: var(--shadow);
        }
        .site-nav[data-open="true"] { display: flex; }
        .site-nav a { padding: 0.85rem 0.95rem; border-radius: 0.95rem; color: var(--muted); font-weight: 600; }
        .site-nav a:hover, .site-nav a:focus-visible { background: rgba(210, 170, 102, 0.08); color: var(--text); outline: none; }
        .hero { padding: 1.2rem 0 1rem; }
        .hero__panel { position: relative; min-height: 80vh; border-radius: var(--radius-xl); overflow: hidden; box-shadow: var(--shadow); background: #120d09; }
        .hero__image { position: absolute; inset: 0; background-position: center; background-size: cover; transform: scale(1.02); }
        .hero__overlay {
            position: absolute; inset: 0; background:
                linear-gradient(180deg, rgba(7, 5, 4, 0.35), rgba(7, 5, 4, 0.82)),
                linear-gradient(90deg, rgba(7, 5, 4, 0.82), rgba(7, 5, 4, 0.3));
        }
        .hero__content { position: relative; z-index: 1; display: flex; min-height: 80vh; flex-direction: column; justify-content: space-between; padding: 1.4rem; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 0.6rem; width: fit-content; border-radius: 999px;
            border: 1px solid rgba(241, 213, 167, 0.18); background: rgba(25, 18, 13, 0.65); color: var(--gold-soft);
            padding: 0.6rem 0.9rem; text-transform: uppercase; letter-spacing: 0.14em; font-size: 0.68rem; font-weight: 800;
        }
        .hero__title { max-width: 12ch; margin: 1.1rem 0 0; font-family: var(--font-display); font-size: clamp(2.4rem, 8vw, 5.2rem); line-height: 0.96; letter-spacing: -0.04em; }
        .hero__lead { max-width: 42rem; margin: 1rem 0 0; color: #e7dccb; font-size: clamp(1rem, 3.1vw, 1.18rem); line-height: 1.85; }
        .hero__actions { display: flex; flex-direction: column; gap: 0.85rem; margin-top: 1.5rem; }
        .hero__foot { display: grid; gap: 0.9rem; margin-top: 2rem; }
        .hero-stat {
            border-radius: 1.25rem; padding: 1rem 1.05rem; background: rgba(28, 20, 15, 0.72); border: 1px solid rgba(210, 170, 102, 0.12);
        }
        .hero-stat__label { display: block; color: var(--muted); font-size: 0.82rem; }
        .hero-stat__value { display: block; margin-top: 0.35rem; font-size: 1.5rem; font-weight: 800; }
        .section { padding: 1rem 0; }
        .section__header { display: flex; flex-direction: column; gap: 0.65rem; margin-bottom: 1.2rem; }
        .section__title { margin: 0; font-family: var(--font-display); font-size: clamp(1.8rem, 5vw, 3rem); line-height: 1.04; }
        .section__lead { margin: 0; max-width: 46rem; color: var(--muted); line-height: 1.85; }
        .summary-grid, .church-grid, .cards-grid { display: grid; gap: 1rem; }
        .summary-card, .church-card, .filter-card, .missa-card {
            border-radius: var(--radius-lg); border: 1px solid var(--line); background: var(--panel); box-shadow: var(--shadow);
        }
        .summary-card, .church-card, .filter-card { padding: 1.15rem; }
        .summary-card__title, .church-card__title { margin: 0; font-size: 0.95rem; color: var(--muted); }
        .summary-card__value { display: block; margin-top: 0.65rem; font-size: clamp(2rem, 7vw, 2.8rem); line-height: 1; font-weight: 900; color: var(--gold-soft); }
        .summary-card__description, .church-card__meta { margin: 0.75rem 0 0; color: var(--muted); line-height: 1.7; }
        .filters-grid { display: grid; gap: 0.9rem; }
        .field label { display: block; margin-bottom: 0.5rem; color: var(--gold-soft); font-size: 0.82rem; font-weight: 700; letter-spacing: 0.03em; }
        .field input, .field select {
            width: 100%; border: 1px solid rgba(210, 170, 102, 0.18); border-radius: 1rem; background: rgba(255, 255, 255, 0.03);
            color: var(--text); padding: 0.95rem 1rem; font: inherit;
        }
        .field input::placeholder { color: #a99580; }
        .field input:focus, .field select:focus { outline: 2px solid rgba(210, 170, 102, 0.18); border-color: rgba(241, 213, 167, 0.4); }
        .filter-actions { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem; }
        .site-footer { margin-top: 2rem; padding: 2rem 0 2.8rem; border-top: 1px solid rgba(210, 170, 102, 0.12); }
        .site-footer__grid { display: grid; gap: 1rem; }
        .site-footer__title { margin: 0; font-family: var(--font-display); font-size: 1.2rem; }
        .site-footer__text, .site-footer__links a { color: var(--muted); line-height: 1.8; }
        .site-footer__links { display: grid; gap: 0.4rem; }

        @media (min-width: 768px) {
            .site-nav { position: static; display: flex; flex-direction: row; align-items: center; gap: 0.2rem; padding: 0; background: transparent; border: 0; box-shadow: none; }
            .nav-toggle { display: none; }
            .hero__content { padding: 2rem; }
            .hero__actions, .filter-actions { flex-direction: row; flex-wrap: wrap; }
            .hero__foot { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .summary-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filters-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cards-grid, .church-grid, .site-footer__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (min-width: 1080px) {
            .hero__content { padding: 2.4rem; }
            .summary-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .filters-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .cards-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .church-grid, .site-footer__grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{ $slot }}

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const navToggle = document.querySelector('[data-nav-toggle]');
            const nav = document.querySelector('[data-site-nav]');

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
