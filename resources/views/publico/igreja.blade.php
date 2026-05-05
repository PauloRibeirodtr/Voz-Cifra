<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz &amp; Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <style>
        :root {
            color-scheme: dark;
            --bg-1: #160c0d;
            --bg-2: #241112;
            --bg-3: #4a2e20;
            --panel: rgba(31, 16, 17, 0.94);
            --panel-soft: rgba(46, 24, 24, 0.94);
            --panel-strong: rgba(62, 33, 28, 0.96);
            --text: #fff7ee;
            --muted: #e8d8c4;
            --accent: #e3be84;
            --line: rgba(227, 190, 132, 0.18);
            --shadow: 0 22px 60px rgba(0, 0, 0, 0.34);
            --public-font-scale: 1.02;
        }

        body[data-contrast='high'] {
            --panel: rgba(15, 9, 9, 0.98);
            --panel-soft: rgba(21, 12, 12, 0.98);
            --panel-strong: rgba(35, 18, 18, 0.98);
            --text: #fffdf9;
            --muted: #f5ead9;
            --accent: #ffd99d;
            --line: rgba(255, 217, 157, 0.28);
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(227, 190, 132, 0.10), transparent 22%),
                radial-gradient(circle at left bottom, rgba(138, 90, 54, 0.16), transparent 26%),
                linear-gradient(145deg, var(--bg-1), var(--bg-2) 46%, var(--bg-3));
            color: var(--text);
        }

        body {
            -webkit-text-size-adjust: 100%;
            line-height: 1.65;
        }

        a {
            color: inherit;
        }

        .page {
            min-height: 100vh;
            padding: 14px 12px 32px;
        }

        .shell {
            width: min(100%, 980px);
            margin: 0 auto;
        }

        .section {
            margin-top: 10px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow);
            padding: 14px;
            backdrop-filter: blur(12px);
        }

        .hero {
            padding: 16px;
        }

        .home-floating {
            position: fixed;
            left: 14px;
            bottom: 14px;
            z-index: 60;
        }

        .home-floating__link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 54px;
            border-radius: 999px;
            border: 1px solid rgba(227, 190, 132, 0.22);
            background: rgba(13, 8, 8, 0.96);
            color: var(--accent);
            box-shadow: var(--shadow);
            font-size: 13px;
            font-weight: 900;
            padding: 0 16px;
            text-decoration: none;
        }

        .home-floating__link:hover,
        .home-floating__link:focus-visible {
            background: rgba(227, 190, 132, 0.12);
            outline: none;
        }

        .brand {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            text-decoration: none;
        }

        .brand img {
            width: 138px;
            height: 138px;
            object-fit: cover;
            flex-shrink: 0;
            border-radius: 26px;
        }

        .brand img.brand-image--fallback {
            object-fit: contain;
            padding: 12px;
            background:
                radial-gradient(circle at center, rgba(255, 217, 157, 0.14), transparent 60%),
                rgba(255, 255, 255, 0.04);
        }

        .brand-kicker,
        .section-kicker {
            margin: 0;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.20em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .section-title,
        .card-title,
        .celebration-title {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-weight: 900;
            letter-spacing: -0.03em;
            color: var(--text);
        }

        .hero-church {
            margin: 4px 0 0;
            font-size: clamp(calc(26px * var(--public-font-scale)), calc(6.5vw * var(--public-font-scale)), calc(42px * var(--public-font-scale)));
            line-height: 1.15;
        }

        .hero-city {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.5vw * var(--public-font-scale)), calc(18px * var(--public-font-scale)));
            font-weight: 700;
        }

        .cards,
        .history-list,
        .celebration-list {
            display: grid;
            gap: 12px;
        }

        .schedule-shell {
            position: relative;
        }

        .schedule-carousel {
            display: grid;
            grid-auto-columns: minmax(235px, 82%);
            grid-auto-flow: column;
            gap: 12px;
            overflow-x: auto;
            padding: 2px 2px 8px;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
        }

        .schedule-carousel .card {
            min-height: 132px;
            scroll-snap-align: start;
        }

        .schedule-nav {
            display: none;
        }

        .card,
        .history-item,
        .celebration-item,
        .access-bar,
        .empty-state,
        .history-form {
            border-radius: 22px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: var(--panel-soft);
        }

        .card,
        .history-item,
        .celebration-item,
        .empty-state {
            padding: 18px;
        }

        .card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .card-link {
            color: inherit;
            text-decoration: none;
            cursor: pointer;
            transition: border-color 0.16s ease, background 0.16s ease, transform 0.16s ease;
        }

        .card-link:hover,
        .card-link:focus-visible {
            border-color: rgba(227, 190, 132, 0.38);
            background: rgba(62, 33, 28, 0.98);
            outline: none;
            transform: translateY(-1px);
        }

        .card-main {
            min-width: 0;
        }

        .card-hour {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 84px;
            padding: 8px 12px;
            border-radius: 16px;
            background: rgba(227, 190, 132, 0.12);
            color: var(--accent);
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .card-title {
            margin-top: 8px;
            font-size: clamp(calc(19px * var(--public-font-scale)), calc(4.3vw * var(--public-font-scale)), calc(25px * var(--public-font-scale)));
            line-height: 1.1;
        }

        .card-meta,
        .section-copy,
        .history-meta,
        .empty-copy,
        .celebration-meta-text {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: clamp(calc(16px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(18px * var(--public-font-scale)));
            line-height: 1.5;
        }

        .card-action,
        .empty-action,
        .history-form button,
        .history-form a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 46px;
            border-radius: 16px;
            padding: 0 18px;
            border: 1px solid transparent;
            text-decoration: none;
            font-size: 15px;
            font-weight: 800;
            font-family: inherit;
            cursor: pointer;
        }

        .card-action {
            background: linear-gradient(135deg, #2f6b4f, #4f8a63);
            color: #fff8ef;
            box-shadow: 0 12px 24px rgba(47, 107, 79, 0.26);
        }

        .celebration-section[hidden] {
            display: none;
        }

        .empty-action,
        .history-form button {
            background: rgba(160, 107, 53, 0.18);
            border-color: rgba(227, 190, 132, 0.34);
            color: var(--accent);
        }

        .history-form a {
            background: rgba(255, 255, 255, 0.03);
            border-color: rgba(255, 255, 255, 0.08);
            color: var(--muted);
        }

        .empty-state {
            background: linear-gradient(180deg, rgba(44, 24, 22, 0.96), rgba(62, 33, 28, 0.96));
        }

        .empty-state--compact {
            padding: 18px;
        }

        .empty-title {
            margin-top: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(calc(28px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(36px * var(--public-font-scale)));
            line-height: 1.08;
            letter-spacing: -0.03em;
        }

        .empty-title--small {
            margin: 0;
            font-size: clamp(calc(22px * var(--public-font-scale)), calc(5vw * var(--public-font-scale)), calc(30px * var(--public-font-scale)));
        }

        .section-header {
            margin-bottom: 12px;
        }

        .section-title {
            margin-top: 6px;
            font-size: clamp(calc(26px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(36px * var(--public-font-scale)));
            line-height: 1.08;
        }

        .access-bar {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            padding: 8px;
        }

        .access-bar button {
            min-height: 36px;
            border-radius: 10px;
            border: 1px solid rgba(227, 190, 132, 0.22);
            background: rgba(227, 190, 132, 0.08);
            color: var(--accent);
            font: inherit;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            touch-action: manipulation;
        }

        .history-form {
            display: grid;
            gap: 10px;
            padding: 12px;
        }

        .history-form input {
            width: 100%;
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
            padding: 0 16px;
            font: inherit;
        }

        .history-form label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: var(--muted);
        }

        .history-toggle {
            padding: 0;
            overflow: hidden;
        }

        .history-toggle summary {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 54px;
            padding: 0 18px;
            cursor: pointer;
            color: var(--accent);
            font-weight: 900;
            list-style: none;
            background: rgba(13, 8, 8, 0.78);
        }

        .history-toggle summary:hover,
        .history-toggle summary:focus-visible {
            background: rgba(227, 190, 132, 0.08);
            outline: none;
        }

        .history-toggle summary::-webkit-details-marker {
            display: none;
        }

        .history-toggle[open] summary {
            border-bottom: 1px solid var(--line);
        }

        .history-content {
            display: grid;
            gap: 12px;
            padding: 14px;
        }

        .history-content--subtle {
            background: rgba(13, 8, 8, 0.22);
        }

        .history-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .history-date,
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            border-radius: 999px;
            padding: 0 12px;
            background: rgba(227, 190, 132, 0.10);
            border: 1px solid rgba(227, 190, 132, 0.12);
            color: var(--accent);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .history-list--compact {
            gap: 8px;
        }

        .history-link {
            display: block;
            color: inherit;
            text-decoration: none;
            border-radius: 18px;
            border: 1px solid rgba(227, 190, 132, 0.10);
            background: rgba(255, 255, 255, 0.035);
            padding: 12px;
            transition: border-color 0.16s ease, background 0.16s ease, transform 0.16s ease;
        }

        .history-link[data-selected="true"] {
            border-color: rgba(255, 217, 157, 0.38);
            background: rgba(227, 190, 132, 0.10);
        }

        .history-link:hover,
        .history-link:focus-visible {
            border-color: rgba(227, 190, 132, 0.34);
            background: rgba(227, 190, 132, 0.08);
            outline: none;
            transform: translateY(-1px);
        }

        .history-link .card-title {
            margin-top: 6px;
            font-size: clamp(calc(18px * var(--public-font-scale)), calc(4.4vw * var(--public-font-scale)), calc(22px * var(--public-font-scale)));
            line-height: 1.15;
        }

        .history-link .history-meta {
            margin-top: 6px;
            font-size: clamp(calc(14px * var(--public-font-scale)), calc(3.5vw * var(--public-font-scale)), calc(16px * var(--public-font-scale)));
            line-height: 1.45;
        }

        .history-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            justify-content: space-between;
        }

        .history-badge-muted {
            background: rgba(255, 255, 255, 0.04);
            border-color: rgba(255, 255, 255, 0.08);
            color: var(--muted);
        }

        .history-empty {
            border-radius: 18px;
            border: 1px dashed rgba(227, 190, 132, 0.18);
            background: rgba(255, 255, 255, 0.035);
            color: var(--muted);
            padding: 12px;
            font-size: 15px;
            font-weight: 800;
            text-align: center;
        }

        .history-live-results {
            display: grid;
            gap: 8px;
        }

        .history-live-results[hidden],
        .history-empty[hidden] {
            display: none;
        }

        .history-search-hint {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .celebration-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .celebration-title {
            font-size: clamp(calc(28px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(40px * var(--public-font-scale)));
            line-height: 1.06;
        }

        .celebration-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .lyrics {
            margin-top: 14px;
            white-space: pre-wrap;
            color: var(--text);
            font-size: clamp(calc(17px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(21px * var(--public-font-scale)));
            line-height: 1.7;
        }

        .lyrics p {
            margin: 0 0 0.55rem;
        }

        .lyrics-space {
            height: 0.55rem;
        }

        .lyrics-section-label {
            display: inline-flex;
            align-items: center;
            margin: 0.85rem 0 0.5rem;
            padding: 0.38rem 0.78rem;
            border-radius: 999px;
            background: rgba(227, 190, 132, 0.12);
            border: 1px solid rgba(227, 190, 132, 0.18);
            color: var(--accent);
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .lyrics-section-label--refrao {
            background: rgba(255, 217, 157, 0.18);
            border-color: rgba(255, 217, 157, 0.34);
            color: #fff3d7;
            font-weight: 950;
        }

        body[data-public-mode='musicos'] .lyrics {
            font-family: "Courier New", Courier, monospace;
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(20px * var(--public-font-scale)));
            line-height: 1.68;
        }

        body[data-public-mode='musicos'] .celebration-list {
            grid-template-columns: 1fr;
        }

        body[data-public-mode='musicos'] .celebration-item {
            background: rgba(13, 8, 8, 0.58);
            border-color: rgba(255, 217, 157, 0.18);
        }

        .chord-mark {
            display: inline-block;
            margin-right: 0.16em;
            padding: 0.08em 0.40em;
            border-radius: 999px;
            background: rgba(227, 190, 132, 0.12);
            color: #ffd99d;
            font-weight: 800;
        }

        body[data-public-mode='musicos'] .lyrics {
            --escala-fonte: 1;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-linha {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.16rem;
            margin-bottom: 0.36rem;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-segmento {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-end;
            min-height: 2.35rem;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-acordes {
            min-height: 1.1rem;
            margin-bottom: 0.02rem;
            color: #ffd99d;
            font-weight: 900;
            font-size: calc(0.95rem * var(--escala-fonte));
            line-height: calc(1rem * var(--escala-fonte));
            white-space: pre;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-acorde {
            display: inline-block;
            cursor: pointer;
            padding: 0.02rem 0.14rem;
            border-radius: 0.35rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-acorde:hover,
        body[data-public-mode='musicos'] .lyrics .cifra-acorde.ativa {
            background: rgba(255, 217, 157, 0.18);
            color: #fff7ee;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-letra {
            color: var(--text);
            font-size: calc(1rem * var(--escala-fonte));
            line-height: calc(1.58rem * var(--escala-fonte));
            white-space: pre-wrap;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-marcacao {
            display: inline-flex;
            align-items: center;
            margin: 0.85rem 0 0.5rem;
            padding: 0.42rem 0.82rem;
            border-radius: 999px;
            background: rgba(227, 190, 132, 0.12);
            color: var(--accent);
            font-size: 0.76rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-marcacao--refrao {
            background: rgba(255, 217, 157, 0.18);
            border: 1px solid rgba(255, 217, 157, 0.34);
            color: #fff3d7;
            font-weight: 950;
        }

        .public-chord-tooltip {
            position: fixed;
            z-index: 90;
            width: 230px;
            pointer-events: none;
            border-radius: 16px;
            border: 1px solid rgba(255, 217, 157, 0.34);
            background: rgba(15, 9, 9, 0.98);
            box-shadow: var(--shadow);
            padding: 12px;
        }

        .public-chord-tooltip[hidden] {
            display: none;
        }

        .public-chord-tooltip__name {
            margin: 0 0 6px;
            color: var(--accent);
            font-weight: 900;
        }

        .public-chord-tooltip svg {
            width: 100%;
            height: auto;
            display: block;
        }

        .scroll-controls {
            display: grid;
            gap: 8px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--line);
        }

        .scroll-controls__top {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .scroll-controls button {
            min-height: 42px;
            border-radius: 14px;
            border: 1px solid rgba(227, 190, 132, 0.22);
            background: rgba(227, 190, 132, 0.12);
            color: var(--accent);
            font-weight: 900;
        }

        .scroll-controls input {
            width: 100%;
            accent-color: #ffd99d;
        }

        .access-floating {
            position: fixed;
            right: 14px;
            bottom: 14px;
            z-index: 60;
        }

        .access-floating__button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            border-radius: 999px;
            border: 1px solid rgba(227, 190, 132, 0.22);
            background: rgba(13, 8, 8, 0.96);
            color: var(--accent);
            box-shadow: var(--shadow);
            cursor: pointer;
            font-size: 22px;
        }

        .access-floating__panel {
            position: absolute;
            right: 0;
            bottom: 64px;
            display: none;
            width: min(280px, calc(100vw - 28px));
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(22, 12, 13, 0.98);
            box-shadow: var(--shadow);
            padding: 10px;
        }

        .access-floating[data-open="true"] .access-floating__panel {
            display: block;
        }

        .access-floating .access-bar {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        @media (max-width: 719px) {
            .card {
                flex-direction: column;
                align-items: stretch;
            }

            .card-action,
            .empty-action,
            .history-form button,
            .history-form a {
                width: 100%;
            }

            .access-bar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .access-floating .access-bar {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .home-floating__link {
                width: 54px;
                padding: 0;
            }

            .home-floating__text {
                display: none;
            }
        }

        @media (max-width: 380px) {
            .brand img {
                width: 116px;
                height: 116px;
            }

            .hero-church {
                font-size: clamp(calc(23px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(32px * var(--public-font-scale)));
            }
        }

        @media (min-width: 720px) {
            .page {
                padding: 20px 18px 38px;
            }

            .section {
                padding: 18px;
            }

            .hero {
                padding: 24px;
            }

            .brand {
                align-items: center;
                gap: 28px;
            }

            .brand img {
                width: 180px;
                height: 180px;
                border-radius: 34px;
            }

            .cards,
            .history-list,
            .celebration-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .schedule-carousel {
                grid-auto-columns: minmax(250px, 31%);
            }

            .schedule-nav {
                position: absolute;
                top: 50%;
                z-index: 5;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                border-radius: 999px;
                border: 1px solid rgba(227, 190, 132, 0.22);
                background: rgba(13, 8, 8, 0.92);
                color: var(--accent);
                box-shadow: var(--shadow);
                cursor: pointer;
                transform: translateY(-50%);
            }

            .schedule-nav--prev {
                left: -12px;
            }

            .schedule-nav--next {
                right: -12px;
            }

            .history-form {
                grid-template-columns: minmax(0, 1fr) auto auto;
                align-items: end;
            }

            body[data-public-mode='musicos'] .celebration-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body data-contrast="high" data-public-mode="{{ $modoPublico ?? 'fieis' }}">
    <main class="page">
        <div
            hidden
            data-public-status-sync
            data-state="{{ $estadoCelebracao }}"
            data-status-url="{{ ($modoPublico ?? 'fieis') === 'musicos'
                ? route('igrejas.public.musicos.status', ['slug' => $igreja->slug, 'celebracao' => ($celebracaoSelecionadaId ?? 0) > 0 ? $celebracaoSelecionadaId : null])
                : route('igrejas.public.status', ['slug' => $igreja->slug, 'celebracao' => ($celebracaoSelecionadaId ?? 0) > 0 ? $celebracaoSelecionadaId : null]) }}"
            @if($countdownIso) data-target="{{ $countdownIso }}" @endif
        ></div>

        <div class="shell">
            <section class="section hero">
                <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="brand">
                    <img src="{{ $igreja->imagemUrl() }}" alt="Logo {{ $igreja->nome }}" class="{{ $igreja->temImagemPersonalizada() ? '' : 'brand-image--fallback' }}">
                    <div>
                        <p class="brand-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Músicos' : 'Igreja' }}</p>
                        <h1 class="hero-church">{{ $igreja->nome }}</h1>
                        @php
                            $cidadeEstadoLinha = trim(($igreja->cidade ?? '') . ' - ' . ($igreja->estado ?? ''), ' -');
                        @endphp
                        @if ($cidadeEstadoLinha !== '')
                            <p class="hero-city">{{ $cidadeEstadoLinha }}</p>
                        @endif
                    </div>
                </a>
            </section>

            @if (($modoPublico ?? 'fieis') === 'fieis')
                <section class="section">
                    <div class="section-header">
                        <p class="section-kicker">Agenda</p>
                        <h2 class="section-title">Programação</h2>
                    </div>

                    @if ($missasHoje->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver missas anteriores">‹</button>
                            <div class="cards schedule-carousel" data-schedule-carousel>
                            @foreach ($missasHoje as $missaHoje)
                                @php($missaHojeSelecionada = (int) $missaHoje['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHoje['id']]) }}#celebracao-publica"
                                    class="card card-link"
                                    data-selected="{{ $missaHojeSelecionada ? 'true' : 'false' }}"
                                >
                                    <div class="card-main">
                                        <span class="card-hour">{{ $missaHoje['horario'] }}</span>
                                        <h3 class="card-title">{{ $missaHoje['titulo'] }}</h3>
                                    </div>
                                </a>
                            @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximas missas">›</button>
                        </div>
                    @else
                        <div class="empty-state empty-state--compact">
                            <h3 class="empty-title empty-title--small">Ainda não há missas para hoje.</h3>
                            <p class="empty-copy">{{ $proximasMissas->isNotEmpty() ? 'A proxima missa publicada ja pode ser aberta abaixo.' : 'Volte mais tarde ou consulte celebrações anteriores.' }}</p>
                        </div>
                    @endif
                </section>

                @if ($proximasMissas->isNotEmpty())
                    <section class="section">
                        <div class="section-header">
                            <p class="section-kicker">Agenda</p>
                            <h2 class="section-title">Celebrações publicadas</h2>
                        </div>

                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver celebrações anteriores">‹</button>
                            <div class="cards schedule-carousel" data-schedule-carousel>
                            @foreach ($proximasMissas as $proximaMissaItem)
                                @php($proximaSelecionada = (int) $proximaMissaItem['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $proximaMissaItem['id']]) }}#celebracao-publica"
                                    class="card card-link"
                                    data-selected="{{ $proximaSelecionada ? 'true' : 'false' }}"
                                >
                                    <div class="card-main">
                                        <span class="card-hour">{{ $proximaMissaItem['horario'] }}</span>
                                        <h3 class="card-title">{{ $proximaMissaItem['titulo'] }}</h3>
                                        <p class="card-meta">{{ $proximaMissaItem['dia_semana'] }} • {{ $proximaMissaItem['data'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximas celebrações">›</button>
                        </div>
                    </section>
                @endif
            @else
                <section class="section">
                    <div class="section-header">
                        <p class="section-kicker">Repertório</p>
                        <h2 class="section-title">Celebrações publicadas</h2>
                    </div>

                    @if ($missasMusicos->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver repertórios anteriores">‹</button>
                            <div class="cards schedule-carousel" data-schedule-carousel>
                            @foreach ($missasMusicos as $missaMusico)
                                @php($missaMusicoSelecionada = (int) $missaMusico['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaMusico['id']]) }}#celebracao-publica"
                                    class="card card-link"
                                    data-selected="{{ $missaMusicoSelecionada ? 'true' : 'false' }}"
                                >
                                    <div class="card-main">
                                        <span class="card-hour">{{ $missaMusico['horario'] }}</span>
                                        <h3 class="card-title">{{ $missaMusico['titulo'] }}</h3>
                                        <p class="card-meta">{{ $missaMusico['dia_semana'] }} • {{ $missaMusico['data'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximos repertórios">›</button>
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Ainda não há repertórios publicados.</h3>
                            <p class="empty-copy">Volte mais tarde para consultar as cifras disponíveis.</p>
                        </div>
                    @endif
                </section>
            @endif

            @if ($missaPublica)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section celebration-section" id="celebracao-publica" data-celebration-section>
                    <div class="celebration-header">
                        <div>
                            <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório' : 'Celebração' }}</p>
                            <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Cifras disponíveis' : $missaPublica->titulo }}</h2>
                            <p class="celebration-meta-text">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                            </p>
                        </div>
                        <span class="badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório' : 'Abrir celebração' }}</span>
                    </div>

                    @if ($itensPublicos->isNotEmpty())
                        <div class="celebration-list">
                            @foreach ($itensPublicos as $item)
                                <article class="celebration-item">
                                    <div class="celebration-meta">
                                        <span class="badge">Ordem {{ $item['ordem'] }}</span>
                                        @if (!empty($item['momento']))
                                            <span class="badge">{{ $item['momento'] }}</span>
                                        @endif
                                        @if (($modoPublico ?? 'fieis') === 'musicos' && !empty($item['tom']))
                                            <span class="badge">Tom {{ $item['tom'] }}</span>
                                        @endif
                                    </div>
                                    <h3 class="card-title">{{ $item['titulo'] }}</h3>
                                    @if (($modoPublico ?? 'fieis') === 'musicos')
                                        <div class="lyrics" data-public-musician-lyrics data-lyrics="{{ e($item['letra_publica'] ?? '') }}">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
                                    @else
                                        <div class="lyrics">{!! $item['letra_publica'] !== '' ? ($item['letra_publica_html'] ?? nl2br(e($item['letra_publica']), false)) : 'A letra deste canto ainda não foi preparada para exibição pública.' !!}</div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório ainda não disponível.' : 'Celebração ainda sem repertório público.' }}</h3>
                        </div>
                    @endif
                </section>
            @endif

            <details class="section history-toggle" id="historico-publico" @if($historicoBusca !== '') open @endif>
                <summary>Consultar histórico</summary>

                <div class="history-content history-content--subtle">
                    @php($historicoBaseUrl = ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) : route('igrejas.public.show', ['slug' => $igreja->slug]))
                    <form method="GET" action="{{ $historicoBaseUrl }}" class="history-form" data-history-form data-history-base-url="{{ $historicoBaseUrl }}" data-history-selected="{{ (int) ($celebracaoSelecionadaId ?? 0) }}">
                        <div>
                            <label for="historico">Buscar no historico</label>
                            <input
                                id="historico"
                                name="historico"
                                type="text"
                                value="{{ $historicoBusca }}"
                                placeholder="Buscar por data, dia ou nome da missa"
                                autocomplete="off"
                                data-history-input
                            >
                            <p class="history-search-hint">Digite 3 letras ou uma data, como 02/05.</p>
                        </div>
                        <button type="submit">Buscar</button>
                        <a href="{{ $historicoBaseUrl }}" data-history-clear>Limpar</a>
                    </form>

                    <script type="application/json" data-history-items>@json($historicoSugestoes ?? [], JSON_UNESCAPED_UNICODE)</script>
                    <div class="history-live-results" data-history-live-results hidden></div>
                    <div class="history-empty" data-history-live-empty hidden>Nenhum resultado encontrado.</div>

                    @if ($historicoMissas->isNotEmpty())
                        <div class="history-list history-list--compact" data-history-server-results>
                            @foreach ($historicoMissas as $missaHistorica)
                                @php($historicoSelecionado = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) : route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}"
                                    class="history-link"
                                    data-history-card
                                    data-history-id="{{ $missaHistorica['id'] }}"
                                    data-selected="{{ $historicoSelecionado ? 'true' : 'false' }}"
                                >
                                    <div class="history-badges">
                                        <span class="history-date">{{ $missaHistorica['data'] }}</span>
                                        <span class="badge history-badge-muted">Historico</span>
                                        <span class="badge history-badge-muted" data-history-action>{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Abrir repertorio' : 'Abrir celebracao' }}</span>
                                    </div>
                                    <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                    <p class="history-meta">{{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['horario'] }} @if (!empty($missaHistorica['tempo_liturgico'])) • {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                </a>
                            @endforeach
                        </div>
                    @elseif ($historicoBusca !== '')
                        <div class="history-empty">
                            Nenhum resultado encontrado.
                        </div>
                    @endif
                </div>
            </details>
        </div>

        <div class="home-floating">
            <a href="{{ route('root') }}" class="home-floating__link" aria-label="Voltar para a página principal">
                <span aria-hidden="true">←</span>
                <span class="home-floating__text">&nbsp;Página principal</span>
            </a>
        </div>

        <div class="access-floating" data-access-floating data-open="false">
            <div class="access-floating__panel" id="access-panel">
                <div class="access-bar">
                    <button type="button" data-public-font="-1">A-</button>
                    <button type="button" data-public-font-reset>A</button>
                    <button type="button" data-public-font="1">A+</button>
                </div>
                @if (($modoPublico ?? 'fieis') === 'musicos')
                    <div class="scroll-controls">
                        <div class="scroll-controls__top">
                            <button type="button" data-public-scroll-toggle>Iniciar rolagem</button>
                            <span data-public-scroll-speed-label>1.00</span>
                        </div>
                        <input
                            type="range"
                            min="0.25"
                            max="6"
                            step="0.25"
                            value="1"
                            aria-label="Velocidade da rolagem"
                            data-public-scroll-speed
                        >
                    </div>
                @endif
            </div>
            <button
                type="button"
                class="access-floating__button"
                data-access-toggle
                aria-expanded="false"
                aria-controls="access-panel"
                aria-label="Abrir acessibilidade"
            >&#9881;</button>
        </div>

        @if (($modoPublico ?? 'fieis') === 'musicos')
            <div class="public-chord-tooltip" data-public-chord-tooltip hidden>
                <p class="public-chord-tooltip__name" data-public-chord-tooltip-name></p>
                <div data-public-chord-tooltip-diagram></div>
            </div>
        @endif
    </main>

    @if (($modoPublico ?? 'fieis') === 'musicos')
        @include('partials.chord-transposer-script')
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const statusSync = document.querySelector('[data-public-status-sync]');
            const accessFloating = document.querySelector('[data-access-floating]');
            const accessToggle = document.querySelector('[data-access-toggle]');
            const fontKey = 'vozecifra-public-font-scale';
            let escalaFonte = Number(localStorage.getItem(fontKey) || '1.02');

            const aplicarEscalaFonte = () => {
                const escalaSegura = Math.max(0.92, Math.min(1.45, escalaFonte));
                escalaFonte = escalaSegura;
                root.style.setProperty('--public-font-scale', escalaSegura.toFixed(2));
                localStorage.setItem(fontKey, escalaSegura.toFixed(2));
            };

            aplicarEscalaFonte();

            document.querySelectorAll('[data-public-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    escalaFonte += Number(botao.dataset.publicFont || 0) * 0.08;
                    aplicarEscalaFonte();
                });
            });

            const resetButton = document.querySelector('[data-public-font-reset]');
            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    escalaFonte = 1.02;
                    aplicarEscalaFonte();
                });
            }

            if (accessFloating && accessToggle) {
                accessToggle.addEventListener('click', () => {
                    const aberto = accessFloating.dataset.open === 'true';
                    accessFloating.dataset.open = aberto ? 'false' : 'true';
                    accessToggle.setAttribute('aria-expanded', aberto ? 'false' : 'true');
                });
            }

            document.querySelectorAll('.schedule-shell').forEach((shell) => {
                const carousel = shell.querySelector('[data-schedule-carousel]');
                const previous = shell.querySelector('[data-schedule-prev]');
                const next = shell.querySelector('[data-schedule-next]');
                const move = (direction) => {
                    if (!carousel) {
                        return;
                    }

                    carousel.scrollBy({
                        left: direction * Math.max(240, carousel.clientWidth * 0.82),
                        behavior: 'smooth',
                    });
                };

                previous?.addEventListener('click', () => move(-1));
                next?.addEventListener('click', () => move(1));
            });

            const celebrationSection = document.querySelector('[data-celebration-section]');
            const celebrationCards = Array.from(document.querySelectorAll('[data-celebration-card]'));
            const labelAbrir = document.body.dataset.publicMode === 'musicos' ? 'Abrir repertório' : 'Abrir celebração';
            const labelFechar = document.body.dataset.publicMode === 'musicos' ? 'Fechar repertório' : 'Fechar celebração';

            const atualizarCardsCelebracao = (cardAberto = null) => {
                celebrationCards.forEach((card) => {
                    const aberto = card === cardAberto;
                    card.dataset.selected = aberto ? 'true' : 'false';
                    card.setAttribute('aria-expanded', aberto ? 'true' : 'false');

                    const action = card.querySelector('.card-action');
                    if (action) {
                        action.textContent = aberto ? labelFechar : labelAbrir;
                    }
                });
            };

            celebrationCards.forEach((card) => {
                card.addEventListener('click', (event) => {
                    if (card.dataset.selected !== 'true') {
                        return;
                    }

                    event.preventDefault();
                    const deveFechar = !celebrationSection?.hidden;

                    if (celebrationSection) {
                        celebrationSection.hidden = deveFechar;
                    }

                    atualizarCardsCelebracao(deveFechar ? null : card);
                });
            });

            const historyForm = document.querySelector('[data-history-form]');
            const historyInput = document.querySelector('[data-history-input]');
            const historyItemsScript = document.querySelector('[data-history-items]');
            const historyLiveResults = document.querySelector('[data-history-live-results]');
            const historyLiveEmpty = document.querySelector('[data-history-live-empty]');
            const historyServerResults = document.querySelector('[data-history-server-results]');
            const historyCards = Array.from(document.querySelectorAll('[data-history-card]'));
            const historyBaseUrl = historyForm?.dataset.historyBaseUrl || window.location.pathname;
            const selectedHistoryId = Number(historyForm?.dataset.historySelected || 0);
            const historyOpenLabel = document.body.dataset.publicMode === 'musicos' ? 'Abrir repertorio' : 'Abrir celebracao';

            const normalizeSearch = (value) => value
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

            const montarHistoryUrl = (item) => {
                const url = new URL(historyBaseUrl, window.location.origin);
                url.searchParams.set('celebracao', item.id);
                url.hash = 'celebracao-publica';
                return url.toString();
            };

            const fecharCelebracaoHistorica = () => {
                if (celebrationSection) {
                    celebrationSection.hidden = true;
                }

                historyCards.forEach((card) => {
                    card.dataset.selected = 'false';
                    const action = card.querySelector('[data-history-action]');
                    if (action) {
                        action.textContent = historyOpenLabel;
                    }
                });

                const url = new URL(historyBaseUrl, window.location.origin);
                url.hash = 'historico-publico';
                window.history.replaceState(null, '', url.toString());
            };

            historyCards.forEach((card) => {
                card.addEventListener('click', (event) => {
                    if (true || card.dataset.selected !== 'true') {
                        return;
                    }

                    event.preventDefault();
                    fecharCelebracaoHistorica();
                });
            });

            let historyItems = [];
            try {
                historyItems = JSON.parse(historyItemsScript?.textContent || '[]');
            } catch (error) {
                historyItems = [];
            }

            const criarHistoryLink = (item) => {
                const link = document.createElement('a');
                const badges = document.createElement('div');
                const data = document.createElement('span');
                const tipo = document.createElement('span');
                const action = document.createElement('span');
                const titulo = document.createElement('h3');
                const meta = document.createElement('p');
                const selecionado = false;

                link.href = montarHistoryUrl(item);
                link.className = 'history-link';

                if (selecionado) {
                    link.addEventListener('click', (event) => {
                        event.preventDefault();
                        fecharCelebracaoHistorica();
                    });
                }

                badges.className = 'history-badges';
                data.className = 'history-date';
                data.textContent = item.data || '';
                tipo.className = 'badge history-badge-muted';
                tipo.textContent = 'Historico';
                action.className = 'badge history-badge-muted';
                action.textContent = historyOpenLabel;

                titulo.className = 'card-title';
                titulo.textContent = item.titulo || 'Missa sem titulo';

                meta.className = 'history-meta';
                meta.textContent = [item.dia_semana, item.horario, item.tempo_liturgico]
                    .filter(Boolean)
                    .join(' • ');

                badges.append(data, tipo, action);
                link.append(badges, titulo, meta);

                return link;
            };

            const renderizarHistoricoAoDigitar = () => {
                if (!historyInput || !historyLiveResults || !historyLiveEmpty) {
                    return;
                }

                const termoOriginal = historyInput.value.trim();
                const termo = normalizeSearch(termoOriginal);
                const digitos = termoOriginal.replace(/\D/g, '');
                const deveBuscar = termo.length >= 3 || digitos.length >= 2;

                historyLiveResults.hidden = true;
                historyLiveResults.replaceChildren();
                historyLiveEmpty.hidden = true;

                if (historyServerResults) {
                    historyServerResults.hidden = deveBuscar;
                }

                if (!deveBuscar) {
                    return;
                }

                const encontrados = historyItems
                    .filter((item) => {
                        const conteudo = normalizeSearch([
                            item.titulo || '',
                            item.data || '',
                            item.dia_semana || '',
                            item.horario || '',
                            item.tempo_liturgico || '',
                        ].join(' '));
                        const dataNumerica = (item.data || '').toString().replace(/\D/g, '');

                        return conteudo.includes(termo) || (digitos.length >= 2 && dataNumerica.includes(digitos));
                    })
                    .slice(0, 8);

                if (encontrados.length === 0) {
                    historyLiveEmpty.hidden = false;
                    return;
                }

                encontrados.forEach((item) => historyLiveResults.appendChild(criarHistoryLink(item)));
                historyLiveResults.hidden = false;
            };

            historyInput?.addEventListener('input', renderizarHistoricoAoDigitar);

            if (document.body.dataset.publicMode === 'musicos') {
                const helper = window.VozECifraChord;
                const bibliotecaAcordes = @json($bibliotecaAcordes ?? [], JSON_UNESCAPED_UNICODE);
                const gruposAcorde = helper ? helper.buildChordGroups(bibliotecaAcordes) : null;
                const tooltipAcorde = document.querySelector('[data-public-chord-tooltip]');
                const tooltipNome = document.querySelector('[data-public-chord-tooltip-name]');
                const tooltipDiagrama = document.querySelector('[data-public-chord-tooltip-diagram]');
                const botaoRolagem = document.querySelector('[data-public-scroll-toggle]');
                const controleVelocidade = document.querySelector('[data-public-scroll-speed]');
                const rotuloVelocidade = document.querySelector('[data-public-scroll-speed-label]');
                let intervaloRolagem = null;

                const renderizarDiagrama = (shape) => {
                    if (!shape) {
                        return '<div>Sem desenho cadastrado.</div>';
                    }

                    const config = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
                    const stringGap = config.width / (config.numStrings - 1);
                    const fretGap = config.height / config.numFrets;
                    const baseFret = shape.baseFret || 1;
                    const positions = shape.positions || [];
                    const barres = shape.barres || [];
                    const topMarkers = shape.topMarkers || [null, null, null, null, null, null];
                    let grid = '';
                    let marks = '';

                    if (baseFret === 1) {
                        grid += `<rect x="${config.startX}" y="${config.startY - 6}" width="${config.width}" height="6" rx="2" fill="#e5e7eb" />`;
                    } else {
                        grid += `<text x="${config.startX - 10}" y="${config.startY + 25}" text-anchor="end" fill="#f5ead9" font-weight="bold" font-size="18">${baseFret}a</text>`;
                        grid += `<line x1="${config.startX}" y1="${config.startY}" x2="${config.startX + config.width}" y2="${config.startY}" stroke="#cbd5e1" stroke-width="2" />`;
                    }

                    for (let i = 1; i <= config.numFrets; i++) {
                        const y = config.startY + (i * fretGap);
                        grid += `<line x1="${config.startX}" y1="${y}" x2="${config.startX + config.width}" y2="${y}" stroke="#cbd5e1" stroke-width="2" />`;
                    }

                    for (let i = 0; i < config.numStrings; i++) {
                        const x = config.startX + (i * stringGap);
                        const thickness = 0.8 + ((5 - i) * 0.5);
                        grid += `<line x1="${x}" y1="${config.startY}" x2="${x}" y2="${config.startY + config.height}" stroke="#e2e8f0" stroke-width="${thickness}" />`;
                    }

                    topMarkers.forEach((marker, i) => {
                        const x = config.startX + (i * stringGap);
                        const y = config.startY - 15;
                        if (marker === 'muted') {
                            marks += `<text x="${x}" y="${y + 5}" fill="#ef4444" font-size="18" font-weight="900" text-anchor="middle">X</text>`;
                        } else if (marker === 'open') {
                            marks += `<circle cx="${x}" cy="${y}" r="5" stroke="#93c5fd" stroke-width="2.5" fill="none" />`;
                        }
                    });

                    barres.forEach((barre) => {
                        const y = config.startY + (barre.fret * fretGap) - (fretGap / 2);
                        const x1 = config.startX + ((6 - barre.fromString) * stringGap);
                        const x2 = config.startX + ((6 - barre.toString) * stringGap);
                        marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" stroke="#ffd99d" stroke-width="14" stroke-linecap="round" opacity="0.95" />`;
                    });

                    positions.forEach((position) => {
                        const y = config.startY + (position.fret * fretGap) - (fretGap / 2);
                        const x = config.startX + ((6 - position.string) * stringGap);
                        marks += `<circle cx="${x}" cy="${y}" r="12" fill="#ffd99d" />`;
                        if (position.finger) {
                            marks += `<text x="${x}" y="${y + 1}" fill="#160c0d" font-size="14" font-weight="900" text-anchor="middle" dominant-baseline="central">${position.finger}</text>`;
                        }
                    });

                    return `<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#3b2418" stroke="#1f130d" stroke-width="2"></rect>${grid}${marks}</svg>`;
                };

                const ativarAcorde = (nome) => {
                    if (!helper || !gruposAcorde) {
                        return null;
                    }

                    const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                    const assinaturaAtual = helper.getChordSignature(nome);

                    document.querySelectorAll('[data-acorde-hover]').forEach((elemento) => {
                        const assinaturaElemento = helper.getChordSignature(elemento.dataset.acordeHover);
                        const ativo = elemento.dataset.acordeHover === nome || (assinaturaElemento && assinaturaAtual && assinaturaElemento === assinaturaAtual);
                        elemento.classList.toggle('ativa', ativo);
                    });

                    return acorde;
                };

                const mostrarTooltipAcorde = (nome, x, y) => {
                    const acorde = ativarAcorde(nome);
                    if (!tooltipAcorde || !tooltipNome || !tooltipDiagrama || !acorde) {
                        return;
                    }

                    tooltipNome.textContent = nome;
                    tooltipDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                    tooltipAcorde.hidden = false;
                    tooltipAcorde.style.left = `${Math.max(12, Math.min(x + 14, window.innerWidth - 244))}px`;
                    tooltipAcorde.style.top = `${Math.max(y - 220, 12)}px`;
                };

                if (helper) {
                    document.querySelectorAll('[data-public-musician-lyrics]').forEach((lyrics) => {
                        lyrics.innerHTML = helper.renderChordSheetHtml(lyrics.dataset.lyrics || '', { chordAttribute: 'data-acorde-hover' });
                    });
                }

                document.addEventListener('mouseover', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (acorde) {
                        mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
                    }
                });

                document.addEventListener('mousemove', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (acorde) {
                        mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
                    }
                });

                document.addEventListener('mouseout', (event) => {
                    if (event.target.closest('[data-acorde-hover]') && tooltipAcorde) {
                        tooltipAcorde.hidden = true;
                    }
                });

                document.addEventListener('click', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (!acorde) {
                        return;
                    }

                    const rect = acorde.getBoundingClientRect();
                    mostrarTooltipAcorde(acorde.dataset.acordeHover, rect.left, rect.top);
                });

                const pararRolagem = () => {
                    if (intervaloRolagem) {
                        window.clearInterval(intervaloRolagem);
                        intervaloRolagem = null;
                    }

                    if (botaoRolagem) {
                        botaoRolagem.textContent = 'Iniciar rolagem';
                    }
                };

                const iniciarRolagem = () => {
                    const velocidade = Number(controleVelocidade?.value || 1);
                    if (rotuloVelocidade) {
                        rotuloVelocidade.textContent = velocidade.toFixed(2);
                    }

                    intervaloRolagem = window.setInterval(() => {
                        window.scrollBy({ top: velocidade * 0.22, left: 0, behavior: 'auto' });

                        if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
                            pararRolagem();
                        }
                    }, 60);
                };

                botaoRolagem?.addEventListener('click', () => {
                    if (intervaloRolagem) {
                        pararRolagem();
                        return;
                    }

                    botaoRolagem.textContent = 'Parar rolagem';
                    iniciarRolagem();
                });

                controleVelocidade?.addEventListener('input', () => {
                    const velocidade = Number(controleVelocidade.value || 1);
                    if (rotuloVelocidade) {
                        rotuloVelocidade.textContent = velocidade.toFixed(2);
                    }

                    if (intervaloRolagem) {
                        window.clearInterval(intervaloRolagem);
                        iniciarRolagem();
                    }
                });
            }

            if (!statusSync || !statusSync.dataset.statusUrl) {
                return;
            }

            let ultimaChaveEstado = [
                statusSync.dataset.state || '',
                statusSync.dataset.target || '',
                window.location.pathname,
                window.location.search,
            ].join('|');

            window.setInterval(async () => {
                try {
                    const resposta = await fetch(statusSync.dataset.statusUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (!resposta.ok) {
                        return;
                    }

                    const payload = await resposta.json();
                    const novaChaveEstado = [
                        payload.estado || '',
                        payload.countdown_iso || '',
                        String(payload.missa_ref || ''),
                    ].join('|');

                    if (novaChaveEstado !== ultimaChaveEstado) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.debug('Falha ao sincronizar a página pública.', error);
                }
            }, 30000);
        });
    </script>
</body>
</html>
