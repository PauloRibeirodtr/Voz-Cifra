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
            margin-top: 9px;
            border-radius: 22px;
            border: 1px solid var(--line);
            background: var(--panel);
            box-shadow: var(--shadow);
            padding: 12px;
            backdrop-filter: blur(12px);
        }

        .hero {
            padding: 14px;
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
            width: 124px;
            height: 124px;
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
            font-size: clamp(calc(24px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(38px * var(--public-font-scale)));
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
            gap: 9px;
        }

        .schedule-shell {
            position: relative;
            overflow: visible;
        }

        .schedule-carousel {
            display: grid;
            grid-auto-columns: minmax(min(82vw, 320px), 88%);
            grid-auto-flow: column;
            gap: 12px;
            overflow-x: auto;
            overscroll-behavior-x: contain;
            padding: 2px 4px 10px;
            scroll-snap-type: x mandatory;
            scrollbar-width: none;
        }

        .schedule-carousel::-webkit-scrollbar {
            display: none;
        }

        .schedule-carousel .card {
            min-height: 116px;
            width: 100%;
            min-width: 0;
            scroll-snap-align: start;
        }

        .schedule-nav {
            display: none;
        }

        .card,
        .history-item,
        .celebration-item,
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
            padding: 14px;
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
            min-width: 72px;
            padding: 7px 11px;
            border-radius: 16px;
            background: rgba(227, 190, 132, 0.12);
            color: var(--accent);
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.03em;
        }

        .card-title {
            margin-top: 8px;
            font-size: clamp(calc(18px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(24px * var(--public-font-scale)));
            line-height: 1.1;
        }

        .card-meta,
        .section-copy,
        .history-meta,
        .empty-copy,
        .celebration-meta-text {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: clamp(calc(14px * var(--public-font-scale)), calc(3.4vw * var(--public-font-scale)), calc(16px * var(--public-font-scale)));
            line-height: 1.5;
        }

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

        .celebration-section[hidden] {
            display: none;
        }

        .celebration-section {
            --celebration-font-scale: 1;
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
            padding: 14px;
        }

        .empty-title {
            margin-top: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(calc(22px * var(--public-font-scale)), calc(5.2vw * var(--public-font-scale)), calc(30px * var(--public-font-scale)));
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
            font-size: clamp(calc(22px * var(--public-font-scale)), calc(5vw * var(--public-font-scale)), calc(32px * var(--public-font-scale)));
            line-height: 1.08;
        }

        .history-form {
            display: grid;
            gap: 10px;
            padding: 12px;
        }

        .history-search-field {
            position: relative;
        }

        .history-search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            width: 20px;
            height: 20px;
            transform: translateY(-50%);
            color: var(--accent);
            pointer-events: none;
            opacity: 0.9;
        }

        .history-form input {
            width: 100%;
            min-height: 52px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
            padding: 0 16px 0 46px;
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

        .history-form-actions {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
        }

        .history-section-title {
            margin: 2px 0 0;
            color: var(--accent);
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.14em;
            text-transform: uppercase;
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

        .capo-control {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 14px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
        }

        .capo-control label {
            color: var(--accent);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .capo-control select {
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: rgba(13, 8, 8, 0.94);
            color: var(--text);
            font: inherit;
            font-weight: 800;
            padding: 0 12px;
        }

        .capo-control__status {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .public-reader-tools {
            position: sticky;
            top: 8px;
            z-index: 40;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            margin: 14px 0 0;
            padding: 4px 2px 10px;
            scrollbar-width: none;
        }

        .public-reader-tools::-webkit-scrollbar {
            display: none;
        }

        .public-song-tools {
            position: sticky;
            top: 6px;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 14px 0;
            padding: 10px;
            border: 1px solid rgba(227, 190, 132, 0.12);
            border-radius: 18px;
            background: rgba(13, 8, 8, 0.88);
            backdrop-filter: blur(10px);
        }

        .public-song-tools__status {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            width: 100%;
        }

        .public-tool-button {
            flex: 0 0 auto;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            border: 1px solid rgba(227, 190, 132, 0.20);
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.94);
            color: #3b2a20;
            padding: 0 13px;
            font: inherit;
            font-size: 13px;
            font-weight: 900;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.22);
            cursor: pointer;
        }

        .public-tool-button--primary {
            background: #f97316;
            border-color: #fb923c;
            color: white;
        }

        .public-tool-button--ghost {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text);
            border-color: rgba(227, 190, 132, 0.16);
            box-shadow: none;
        }

        .public-tool-popover {
            position: fixed;
            left: 50%;
            bottom: 16px;
            z-index: 95;
            width: min(20rem, calc(100vw - 2rem));
            transform: translateX(-50%);
            border: 1px solid rgba(227, 190, 132, 0.28);
            border-radius: 20px;
            background: #fffaf2;
            color: #271b15;
            box-shadow: var(--shadow);
            padding: 14px;
        }

        .public-tool-popover[hidden] {
            display: none;
        }

        .public-capo-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 7px;
            margin-top: 12px;
        }

        .public-capo-choice {
            position: relative;
            display: block;
        }

        .public-capo-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .public-capo-choice span {
            display: flex;
            min-height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(140, 105, 51, 0.24);
            border-radius: 13px;
            background: white;
            color: #4b382a;
            font-size: 12px;
            font-weight: 900;
        }

        .public-capo-choice input:checked + span {
            border-color: #047857;
            background: #ecfdf5;
            color: #065f46;
        }

        .public-capo-panel {
            width: 100%;
            border-top: 1px solid rgba(227, 190, 132, 0.12);
            padding-top: 10px;
        }

        .public-capo-panel[hidden] {
            display: none;
        }

        .public-chord-drawer {
            position: fixed;
            inset: 0 0 0 auto;
            z-index: 94;
            width: min(27rem, calc(100vw - 1rem));
            overflow: auto;
            border-left: 1px solid rgba(227, 190, 132, 0.22);
            background: #fffaf2;
            color: #211713;
            box-shadow: -24px 0 70px rgba(0, 0, 0, 0.34);
            padding: 16px;
        }

        .public-chord-drawer[hidden],
        .public-drawer-backdrop[hidden] {
            display: none;
        }

        .public-drawer-backdrop {
            position: fixed;
            inset: 0;
            z-index: 93;
            background: rgba(13, 8, 8, 0.54);
            backdrop-filter: blur(3px);
        }

        .public-chord-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 14px;
        }

        .public-chord-card {
            border: 1px solid rgba(140, 105, 51, 0.18);
            border-radius: 16px;
            background: white;
            padding: 10px;
            text-align: center;
        }

        .public-chord-card svg {
            width: 100%;
            height: auto;
            max-width: 150px;
        }

        .public-history-quick {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: center;
            gap: 8px;
            max-width: 420px;
            margin-top: 12px;
            padding: 8px;
            border: 1px solid rgba(227, 190, 132, 0.16);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.06);
        }

        .public-history-quick__button {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(227, 190, 132, 0.18);
            border-radius: 999px;
            background: rgba(227, 190, 132, 0.12);
            color: var(--accent);
            cursor: pointer;
        }

        .public-history-quick input {
            width: 100%;
            min-height: 42px;
            border: 0;
            background: transparent;
            color: var(--text);
            font: inherit;
            outline: none;
        }

        .public-scroll-dock {
            position: fixed;
            left: 50%;
            bottom: 14px;
            z-index: 82;
            display: grid;
            grid-template-columns: auto auto minmax(7rem, 12rem) auto;
            align-items: center;
            gap: 10px;
            width: min(42rem, calc(100vw - 1.5rem));
            transform: translateX(-50%);
            border: 1px solid rgba(227, 190, 132, 0.24);
            border-radius: 999px;
            background: rgba(255, 250, 242, 0.96);
            color: #3b2a20;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.28);
            padding: 8px;
        }

        .public-scroll-dock[hidden] {
            display: none;
        }

        .public-scroll-dock input[type="range"] {
            width: 100%;
            accent-color: #047857;
        }

        .public-scroll-dock__speed {
            font-size: 13px;
            font-weight: 900;
            white-space: nowrap;
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
            font-size: clamp(calc(28px * var(--celebration-font-scale, 1)), calc(6vw * var(--celebration-font-scale, 1)), calc(40px * var(--celebration-font-scale, 1)));
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
            font-size: clamp(calc(17px * var(--celebration-font-scale, 1)), calc(4vw * var(--celebration-font-scale, 1)), calc(21px * var(--celebration-font-scale, 1)));
            line-height: 1.7;
        }

        .lyrics p {
            margin: 0;
        }

        .lyrics-stanza {
            margin-bottom: 0.62rem;
            border-left: 3px solid rgba(227, 190, 132, 0.22);
            background: rgba(255, 255, 255, 0.035);
            padding: 8px 0 8px 14px;
        }

        .lyrics-stanza--refrao {
            border-color: rgba(255, 217, 157, 0.72);
            background: linear-gradient(90deg, rgba(255, 217, 157, 0.12), transparent);
        }

        .lyrics-stanza--refrao p {
            color: #fff3d7;
            font-weight: 850;
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
            font-size: clamp(calc(15px * var(--celebration-font-scale, 1)), calc(3.8vw * var(--celebration-font-scale, 1)), calc(20px * var(--celebration-font-scale, 1)));
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
            --escala-fonte: var(--celebration-font-scale, 1);
        }

        body[data-public-mode='musicos'] .lyrics .cifra-linha {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.16rem;
            margin-bottom: 0.36rem;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-linha--refrao {
            border-left: 4px solid #ffd99d;
            background: linear-gradient(90deg, rgba(255, 217, 157, 0.11), transparent);
            margin: 0.14rem 0 0.58rem;
            padding: 0.42rem 0 0.42rem 0.65rem;
        }

        body[data-public-mode='musicos'] .lyrics .cifra-linha--refrao .cifra-letra {
            color: #fff3d7;
            font-weight: 850;
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

        @media (max-width: 719px) {
            .card {
                flex-direction: column;
                align-items: stretch;
            }

            .empty-action,
            .history-form button,
            .history-form a {
                width: 100%;
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
                grid-auto-columns: minmax(260px, 34%);
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

            .schedule-nav:disabled {
                cursor: not-allowed;
                opacity: 0.35;
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

        @media (max-width: 719px) {
            .celebration-list {
                display: grid;
                grid-auto-columns: minmax(88vw, 92vw);
                grid-auto-flow: column;
                overflow-x: auto;
                overscroll-behavior-x: contain;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
                padding: 2px 2px 12px;
            }

            .celebration-list::-webkit-scrollbar {
                display: none;
            }

            .celebration-item {
                scroll-snap-align: start;
                min-width: 0;
            }

            .public-scroll-dock {
                grid-template-columns: 1fr auto;
                border-radius: 22px;
            }

            .public-scroll-dock label,
            .public-scroll-dock input[type="range"] {
                grid-column: 1 / -1;
            }

            .public-song-tools {
                position: static;
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
                            <h3 class="empty-title">Ainda não há missas publicadas para ensaio.</h3>
                            <p class="empty-copy">Este link é somente leitura e será atualizado quando a equipe publicar um repertório.</p>
                        </div>
                    @endif
                </section>
            @endif

            @php($celebracaoFoiEscolhida = (bool) $missaPublica)

            @if ($missaPublica && $celebracaoFoiEscolhida)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section celebration-section" id="celebracao-publica" data-celebration-section>
                    <div class="celebration-header">
                        <div>
                            <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório' : 'Celebração' }}</p>
                            <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Cifras disponíveis' : $missaPublica->titulo }}</h2>
                            <p class="celebration-meta-text">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                            </p>
                            @if (($modoPublico ?? 'fieis') === 'musicos')
                                @php($historicoBaseUrlTopo = route('igrejas.public.musicos.show', ['slug' => $igreja->slug]))
                                <form method="GET" action="{{ $historicoBaseUrlTopo }}" class="public-history-quick" data-history-form-top data-history-base-url="{{ $historicoBaseUrlTopo }}" aria-label="Buscar repertorio no historico">
                                    <button type="submit" class="public-history-quick__button" aria-label="Buscar no historico">
                                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                            <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                        </svg>
                                    </button>
                                    <input type="text" name="historico" value="{{ $historicoBusca }}" placeholder="Buscar missa anterior" autocomplete="off" data-history-input-top>
                                </form>
                                <div class="celebration-meta" style="margin-top: 12px;">
                                    <span class="badge">Controles por musica</span>
                                    <span class="badge">Auto rolagem no rodape</span>
                                </div>
                            @endif
                        </div>
                        <span class="badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Abrir repertório' : 'Abrir celebração' }}</span>
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
                                            <span class="badge" data-public-capo-item data-base-tom="{{ $item['tom'] }}" hidden></span>
                                        @endif
                                    </div>
                                    <h3 class="card-title">{{ $item['titulo'] }}</h3>
                                    @if (($modoPublico ?? 'fieis') === 'musicos')
                                        <div class="lyrics" data-public-musician-lyrics data-base-tom="{{ $item['tom'] ?? '' }}" data-lyrics="{{ e($item['letra_publica'] ?? '') }}">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
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

            @php($historicoUltimasMissas = collect($historicoUltimasMissas ?? []))
            <details class="section history-toggle" id="historico-publico" @if($historicoBusca !== '' || $historicoUltimasMissas->isNotEmpty()) open @endif>
                <summary>Consultar histórico</summary>

                <div class="history-content history-content--subtle">
                    @php($historicoBaseUrl = ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) : route('igrejas.public.show', ['slug' => $igreja->slug]))
                    <form method="GET" action="{{ $historicoBaseUrl }}" class="history-form" data-history-form data-history-base-url="{{ $historicoBaseUrl }}" data-history-selected="{{ (int) ($celebracaoSelecionadaId ?? 0) }}">
                        <div>
                            <label for="historico">Buscar no historico</label>
                            <div class="history-search-field">
                                <svg class="history-search-icon" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                    <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                </svg>
                                <input
                                    id="historico"
                                    name="historico"
                                    type="text"
                                    value="{{ $historicoBusca }}"
                                    placeholder="Data, dia ou nome da missa"
                                    autocomplete="off"
                                    data-history-input
                                >
                            </div>
                            <p class="history-search-hint">Digite 3 letras ou uma data, como 02/05.</p>
                        </div>
                        <div class="history-form-actions">
                            <button type="submit">Buscar</button>
                            <a href="{{ $historicoBaseUrl }}" data-history-clear>Limpar</a>
                        </div>
                    </form>

                    <script type="application/json" data-history-items>@json($historicoSugestoes ?? [], JSON_UNESCAPED_UNICODE)</script>
                    <div class="history-live-results" data-history-live-results hidden></div>
                    <div class="history-empty" data-history-live-empty hidden>Nenhum resultado encontrado.</div>

                    @if ($historicoMissas->isNotEmpty())
                        <p class="history-section-title">Resultado da busca</p>
                        <div class="history-list history-list--compact" data-history-server-results>
                            @foreach ($historicoMissas as $missaHistorica)
                                @php($historicoSelecionado = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) : route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica"
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

                    @if ($historicoBusca === '' && $historicoUltimasMissas->isNotEmpty())
                        <p class="history-section-title">Ultimas 5 missas anteriores</p>
                        <div class="history-list history-list--compact" data-history-server-results>
                            @foreach ($historicoUltimasMissas as $missaHistorica)
                                @php($historicoSelecionado = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                <a
                                    href="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) : route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica"
                                    class="history-link"
                                    data-history-card
                                    data-history-id="{{ $missaHistorica['id'] }}"
                                    data-selected="{{ $historicoSelecionado ? 'true' : 'false' }}"
                                >
                                    <div class="history-badges">
                                        <span class="history-date">{{ $missaHistorica['data'] }}</span>
                                        <span class="badge history-badge-muted">Anterior</span>
                                        <span class="badge history-badge-muted" data-history-action>{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Abrir repertorio' : 'Abrir celebracao' }}</span>
                                    </div>
                                    <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                    <p class="history-meta">{{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['horario'] }} @if (!empty($missaHistorica['tempo_liturgico'])) • {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                </a>
                            @endforeach
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

        @if (($modoPublico ?? 'fieis') === 'musicos')
            <div class="public-tool-popover" data-public-capo-popover hidden>
                <div class="history-top">
                    <div>
                        <p class="history-section-title">Capotraste</p>
                        <p class="history-search-hint" data-public-capo-summary-popover>Sem capotraste</p>
                    </div>
                    <button type="button" class="public-tool-button" data-public-capo-close aria-label="Fechar capotraste">x</button>
                </div>
                <div class="public-capo-grid" role="radiogroup" aria-label="Casa do capotraste">
                    <label class="public-capo-choice">
                        <input type="radio" name="public_capo_visual" value="0" data-public-capo checked>
                        <span>Sem</span>
                    </label>
                    @for ($casaCapotraste = 1; $casaCapotraste <= 11; $casaCapotraste++)
                        <label class="public-capo-choice">
                            <input type="radio" name="public_capo_visual" value="{{ $casaCapotraste }}" data-public-capo>
                            <span>{{ $casaCapotraste }} casa</span>
                        </label>
                    @endfor
                </div>
            </div>

            <div class="public-drawer-backdrop" data-public-chords-backdrop hidden></div>
            <aside class="public-chord-drawer" data-public-chords-drawer hidden aria-label="Acordes do repertorio">
                <div class="history-top">
                    <div>
                        <p class="history-section-title">Acordes</p>
                        <h2 class="card-title" style="color:#211713;font-size:28px;">Dicionario rapido</h2>
                    </div>
                    <button type="button" class="public-tool-button" data-public-chords-close aria-label="Fechar acordes">x</button>
                </div>
                <div class="public-chord-grid" data-public-chords-grid></div>
            </aside>

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
            const statusSync = document.querySelector('[data-public-status-sync]');

            document.querySelectorAll('.schedule-shell').forEach((shell) => {
                const carousel = shell.querySelector('[data-schedule-carousel]');
                const previous = shell.querySelector('[data-schedule-prev]');
                const next = shell.querySelector('[data-schedule-next]');

                if (!carousel) {
                    return;
                }

                const atualizarBotoes = () => {
                    const podeRolar = carousel.scrollWidth > carousel.clientWidth + 2;
                    const noInicio = carousel.scrollLeft <= 2;
                    const noFim = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;

                    if (previous) {
                        previous.disabled = !podeRolar || noInicio;
                    }

                    if (next) {
                        next.disabled = !podeRolar || noFim;
                    }
                };

                const move = (direction) => {
                    const card = carousel.querySelector('.card');
                    const estilos = window.getComputedStyle(carousel);
                    const gap = Number.parseFloat(estilos.columnGap || estilos.gap || '12') || 12;
                    const larguraCard = card ? card.getBoundingClientRect().width + gap : Math.max(240, carousel.clientWidth * 0.82);

                    carousel.scrollBy({
                        left: direction * larguraCard,
                        behavior: 'smooth',
                    });
                };

                previous?.addEventListener('click', () => move(-1));
                next?.addEventListener('click', () => move(1));
                carousel.addEventListener('scroll', atualizarBotoes, { passive: true });
                window.addEventListener('resize', atualizarBotoes);
                atualizarBotoes();
            });

            const historyForm = document.querySelector('[data-history-form]');
            const historyInput = document.querySelector('[data-history-input]');
            const historyItemsScript = document.querySelector('[data-history-items]');
            const historyLiveResults = document.querySelector('[data-history-live-results]');
            const historyLiveEmpty = document.querySelector('[data-history-live-empty]');
            const historyServerResults = document.querySelector('[data-history-server-results]');
            const historyBaseUrl = historyForm?.dataset.historyBaseUrl || window.location.pathname;
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
                link.href = montarHistoryUrl(item);
                link.className = 'history-link';

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
                const controlesCapotraste = Array.from(document.querySelectorAll('[data-public-capo]'));
                const resumosCapotraste = Array.from(document.querySelectorAll('[data-public-capo-summary], [data-public-capo-summary-popover]'));
                const resumoFonte = document.querySelector('[data-public-font-summary]');
                const botaoAutoRolagem = document.querySelector('[data-public-auto-scroll]');
                const botoesFonte = Array.from(document.querySelectorAll('[data-public-font]'));
                const botaoCapoAbrir = document.querySelector('[data-public-capo-open]');
                const botaoCapoFechar = document.querySelector('[data-public-capo-close]');
                const popoverCapo = document.querySelector('[data-public-capo-popover]');
                const botaoAcordesAbrir = document.querySelector('[data-public-chords-open]');
                const botaoAcordesFechar = document.querySelector('[data-public-chords-close]');
                const drawerAcordes = document.querySelector('[data-public-chords-drawer]');
                const drawerAcordesBackdrop = document.querySelector('[data-public-chords-backdrop]');
                const gradeAcordes = document.querySelector('[data-public-chords-grid]');
                let capotrasteAtual = 0;
                let publicFontLevel = 1;
                let publicAutoScrollActive = false;
                let publicAutoScrollInterval = null;
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

                const renderizarCifrasPublicas = () => {
                    if (!helper) {
                        return;
                    }

                    document.querySelectorAll('[data-public-musician-lyrics]').forEach((lyrics) => {
                        const textoComCapo = helper.transposeBracketedText(lyrics.dataset.lyrics || '', -capotrasteAtual);
                        lyrics.innerHTML = helper.renderChordSheetHtml(textoComCapo, { chordAttribute: 'data-acorde-hover' });
                    });

                    document.querySelectorAll('[data-public-capo-item]').forEach((badge) => {
                        const tomBase = badge.dataset.baseTom || '';
                        if (capotrasteAtual <= 0 || !helper.isChord(tomBase)) {
                            badge.hidden = true;
                            badge.textContent = '';
                            return;
                        }

                        badge.hidden = false;
                        badge.textContent = `Capo ${capotrasteAtual} / tocar como ${helper.transposeChord(tomBase, -capotrasteAtual)}`;
                    });

                    const textoCapo = capotrasteAtual > 0
                        ? `Capotraste ${capotrasteAtual} casa`
                        : 'Sem capotraste';
                    resumosCapotraste.forEach((resumo) => {
                        resumo.textContent = textoCapo;
                    });
                    controlesCapotraste.forEach((controle) => {
                        controle.checked = Number(controle.value || 0) === capotrasteAtual;
                    });
                };

                const renderizarGradeAcordes = () => {
                    if (!gradeAcordes || !helper || !gruposAcorde) {
                        return;
                    }

                    const acordes = new Set();
                    document.querySelectorAll('[data-public-musician-lyrics]').forEach((lyrics) => {
                        helper.extractChordsFromBracketedText(helper.transposeBracketedText(lyrics.dataset.lyrics || '', -capotrasteAtual))
                            .forEach((acorde) => acordes.add(acorde));
                    });

                    gradeAcordes.innerHTML = Array.from(acordes).map((nome) => {
                        const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                        return `<button type="button" class="public-chord-card" data-public-chord-card="${helper.escapeHtml(nome)}"><strong>${helper.escapeHtml(nome)}</strong>${acorde ? renderizarDiagrama(acorde.shape) : '<p>Sem desenho.</p>'}</button>`;
                    }).join('');
                };

                controlesCapotraste.forEach((controle) => {
                    controle.addEventListener('change', () => {
                        capotrasteAtual = Math.max(0, Math.min(11, Number(controle.value || 0)));
                        renderizarCifrasPublicas();
                        renderizarGradeAcordes();
                    });
                });

                botoesFonte.forEach((botao) => {
                    botao.addEventListener('click', () => {
                        publicFontLevel = Math.max(0, Math.min(3, publicFontLevel + Number(botao.dataset.publicFont || 0)));
                        const escala = [0.92, 1, 1.14, 1.28][publicFontLevel] || 1;
                        document.documentElement.style.setProperty('--celebration-font-scale', String(escala));
                        if (resumoFonte) {
                            resumoFonte.textContent = `Fonte ${['menor', 'normal', 'maior', 'grande'][publicFontLevel]}`;
                        }
                    });
                });

                botaoAutoRolagem?.addEventListener('click', () => {
                    if (publicAutoScrollActive) {
                        window.clearInterval(publicAutoScrollInterval);
                        publicAutoScrollInterval = null;
                        publicAutoScrollActive = false;
                        botaoAutoRolagem.textContent = 'Auto rolagem';
                        return;
                    }

                    publicAutoScrollActive = true;
                    botaoAutoRolagem.textContent = 'Pausar rolagem';
                    publicAutoScrollInterval = window.setInterval(() => {
                        const fim = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 8;
                        if (fim) {
                            botaoAutoRolagem.click();
                            return;
                        }
                        window.scrollBy({ top: 1.7, left: 0, behavior: 'auto' });
                    }, 34);
                });

                botaoCapoAbrir?.addEventListener('click', () => {
                    if (popoverCapo) popoverCapo.hidden = false;
                });
                botaoCapoFechar?.addEventListener('click', () => {
                    if (popoverCapo) popoverCapo.hidden = true;
                });
                botaoAcordesAbrir?.addEventListener('click', () => {
                    renderizarGradeAcordes();
                    if (drawerAcordes) drawerAcordes.hidden = false;
                    if (drawerAcordesBackdrop) drawerAcordesBackdrop.hidden = false;
                });
                const fecharDrawerAcordes = () => {
                    if (drawerAcordes) drawerAcordes.hidden = true;
                    if (drawerAcordesBackdrop) drawerAcordesBackdrop.hidden = true;
                };
                botaoAcordesFechar?.addEventListener('click', fecharDrawerAcordes);
                drawerAcordesBackdrop?.addEventListener('click', fecharDrawerAcordes);
                gradeAcordes?.addEventListener('click', (event) => {
                    const card = event.target.closest('[data-public-chord-card]');
                    if (card) {
                        ativarAcorde(card.dataset.publicChordCard);
                    }
                });

                renderizarCifrasPublicas();
                renderizarGradeAcordes();

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
