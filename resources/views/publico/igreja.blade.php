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
            margin-top: 10px;
            font-size: clamp(calc(22px * var(--public-font-scale)), calc(4.8vw * var(--public-font-scale)), calc(28px * var(--public-font-scale)));
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
            line-height: 1.8;
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
            line-height: 1.95;
        }

        body[data-public-mode='musicos'] .lyrics {
            font-family: "Courier New", Courier, monospace;
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(20px * var(--public-font-scale)));
            line-height: 2;
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

            .history-form {
                grid-template-columns: minmax(0, 1fr) auto auto;
                align-items: end;
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
                    <img src="{{ $igreja->imagemUrl() }}" alt="Logo {{ $igreja->nome }}">
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
                        <p class="section-kicker">Hoje</p>
                        <h2 class="section-title">Missas de hoje</h2>
                    </div>

                    @if ($missasHoje->isNotEmpty())
                        <div class="cards">
                            @foreach ($missasHoje as $missaHoje)
                                <article class="card">
                                    <div class="card-main">
                                        <span class="card-hour">{{ $missaHoje['horario'] }}</span>
                                        <h3 class="card-title">{{ $missaHoje['titulo'] }}</h3>
                                    </div>
                                    <a
                                        href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHoje['id']]) }}#celebracao-publica"
                                        class="card-action"
                                    >
                                        Abrir celebração
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state empty-state--compact">
                            <h3 class="empty-title empty-title--small">Nenhuma missa publicada hoje.</h3>
                            <p class="empty-copy">Volte mais tarde ou consulte celebrações anteriores.</p>
                        </div>
                    @endif
                </section>
            @else
                <section class="section">
                    <div class="section-header">
                        <p class="section-kicker">Ensaio</p>
                        <h2 class="section-title">Celebrações publicadas</h2>
                    </div>

                    @if ($missasMusicos->isNotEmpty())
                        <div class="cards">
                            @foreach ($missasMusicos as $missaMusico)
                                <article class="card">
                                    <div class="card-main">
                                        <span class="card-hour">{{ $missaMusico['horario'] }}</span>
                                        <h3 class="card-title">{{ $missaMusico['titulo'] }}</h3>
                                        <p class="card-meta">{{ $missaMusico['dia_semana'] }} • {{ $missaMusico['data'] }}</p>
                                    </div>
                                    <a
                                        href="{{ route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaMusico['id']]) }}#celebracao-publica"
                                        class="card-action"
                                    >
                                        Abrir repertório
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Sem repertório publicado.</h3>
                        </div>
                    @endif
                </section>
            @endif

            @if ($missaPublica)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section" id="celebracao-publica">
                    <div class="celebration-header">
                        <div>
                            <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Ensaio' : 'Celebração' }}</p>
                            <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório publicado' : $missaPublica->titulo }}</h2>
                            <p class="celebration-meta-text">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                            </p>
                        </div>
                        <span class="badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Modo músico' : 'Somente leitura' }}</span>
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
                                        <div class="lyrics">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
                                    @else
                                        <div class="lyrics">{{ $item['letra_publica'] !== '' ? $item['letra_publica'] : 'A letra deste canto ainda não foi preparada para exibição pública.' }}</div>
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

                <div class="history-content">
                    <form method="GET" action="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) : route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="history-form">
                        <div>
                            <label for="historico">Buscar missas passadas</label>
                            <input
                                id="historico"
                                name="historico"
                                type="text"
                                value="{{ $historicoBusca }}"
                                placeholder="Ex.: domingo, 24/03"
                            >
                        </div>
                        @if (($celebracaoSelecionadaId ?? 0) > 0)
                            <input type="hidden" name="celebracao" value="{{ $celebracaoSelecionadaId }}">
                        @endif
                        <button type="submit">Buscar</button>
                        <a href="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) : route('igrejas.public.show', ['slug' => $igreja->slug]) }}">Limpar</a>
                    </form>

                    @if ($historicoMissas->isNotEmpty())
                        <div class="history-list">
                            @foreach ($historicoMissas as $missaHistorica)
                                <article class="history-item">
                                    <div class="history-top">
                                        <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                        <span class="history-date">{{ $missaHistorica['data'] }}</span>
                                    </div>
                                    <p class="history-meta">{{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['horario'] }}</p>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Nenhuma missa encontrada.</h3>
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
    </main>

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
