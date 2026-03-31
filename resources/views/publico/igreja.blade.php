<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz & Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <style>
        :root {
            color-scheme: light;
            --bg-1: #0b3a2b;
            --bg-2: #145a3b;
            --bg-3: #2f8a57;
            --text: #f6fff8;
            --muted: rgba(240, 253, 244, 0.82);
            --soft: rgba(255, 255, 255, 0.07);
            --soft-2: rgba(255, 255, 255, 0.1);
            --accent: #d7ffe3;
            --accent-2: #f7c873;
            --shadow: 0 18px 48px rgba(0, 0, 0, 0.12);
            --public-font-scale: 1;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.10), transparent 24%),
                radial-gradient(circle at left bottom, rgba(255, 255, 255, 0.08), transparent 28%),
                linear-gradient(140deg, var(--bg-1), var(--bg-2) 48%, var(--bg-3));
            color: var(--text);
        }

        body {
            line-height: 1.6;
        }

        .page {
            width: 100%;
            min-height: 100vh;
            padding: 12px 10px 28px;
        }

        .shell,
        .celebration-section {
            width: min(100%, 1180px);
            margin: 0 auto;
            border-radius: 24px;
            background: var(--soft);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .celebration-section {
            margin-top: 16px;
            padding: 20px 16px 14px;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .hero-main {
            padding: 18px 16px 14px;
        }

        .hero-side {
            padding: 4px 16px 18px;
        }

        .brand {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }

        .brand img {
            width: 52px;
            height: auto;
            flex-shrink: 0;
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.14));
        }

        .brand-kicker {
            margin: 0 0 4px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .brand-name {
            margin: 0;
            font-size: clamp(24px, 5vw, 34px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.03em;
            text-wrap: balance;
        }

        .location {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: var(--soft-2);
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 700;
            color: var(--accent);
        }

        .title {
            margin: 18px 0 0;
            max-width: 14ch;
            font-size: clamp(34px, 9vw, 68px);
            line-height: 0.96;
            font-weight: 900;
            letter-spacing: -0.04em;
            text-wrap: balance;
        }

        .lead {
            margin: 18px 0 0;
            max-width: 56rem;
            font-size: clamp(calc(16px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(19px * var(--public-font-scale)));
            line-height: 1.85;
            color: var(--muted);
        }

        .celebration-focus {
            margin-top: 18px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(247, 200, 115, 0.16), rgba(255, 255, 255, 0.08));
            padding: 16px 18px;
            border: 1px solid rgba(255, 242, 202, 0.12);
        }

        .celebration-focus-label {
            display: block;
            margin: 0;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: #fff2ca;
        }

        .celebration-focus-title {
            margin: 8px 0 0;
            font-size: clamp(calc(24px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(38px * var(--public-font-scale)));
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.03em;
            color: #ffffff;
            text-wrap: balance;
        }

        .celebration-focus-meta {
            margin: 10px 0 0;
            font-size: clamp(calc(14px * var(--public-font-scale)), calc(3.6vw * var(--public-font-scale)), calc(17px * var(--public-font-scale)));
            line-height: 1.8;
            color: rgba(255, 255, 255, 0.84);
        }

        .access-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 18px;
        }

        .access-tools button {
            flex: 1 1 0;
            min-width: 0;
            border: 0;
            border-radius: 999px;
            background: var(--soft-2);
            color: var(--text);
            padding: 11px 16px;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            min-height: 48px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .access-tools button:hover {
            background: rgba(255, 255, 255, 0.18);
        }

        .info-row {
            margin-top: 22px;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 12px;
        }

        .info-card,
        .panel,
        .celebration-item {
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.06);
        }

        .info-card {
            padding: 16px 18px;
        }

        .label,
        .panel-title {
            display: block;
            margin: 0;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .value {
            display: block;
            margin-top: 8px;
            font-size: clamp(18px, 4vw, 22px);
            line-height: 1.55;
            font-weight: 600;
            word-break: break-word;
        }

        .panel {
            padding: 18px;
        }

        .panel + .panel {
            margin-top: 14px;
        }

        .countdown {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 16px;
        }

        .count-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.12);
            padding: 13px 14px;
            text-align: left;
        }

        .count-number {
            display: block;
            font-size: clamp(28px, 7vw, 34px);
            font-weight: 900;
            line-height: 1;
            color: #fff7db;
        }

        .count-label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .next-missa {
            margin-top: 16px;
            border-radius: 18px;
            background: rgba(247, 200, 115, 0.12);
            padding: 16px;
        }

        .next-missa strong {
            display: block;
            margin-bottom: 6px;
            font-size: 19px;
            line-height: 1.4;
        }

        .next-missa span {
            color: var(--muted);
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(17px * var(--public-font-scale)));
            line-height: 1.75;
        }

        .notice {
            position: relative;
            overflow: hidden;
        }

        .notice::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: linear-gradient(180deg, #f7c873, #fff0b8);
        }

        .notice-text {
            margin: 14px 0 0;
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(17px * var(--public-font-scale)));
            line-height: 1.85;
            color: var(--muted);
        }

        .loading {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
        }

        .dot {
            width: 10px;
            height: 10px;
            border-radius: 999px;
            background: #fff7db;
            animation: pulse 1.6s infinite ease-in-out;
        }

        .dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        .footer-note {
            margin-top: 18px;
            font-size: clamp(calc(13px * var(--public-font-scale)), calc(3.3vw * var(--public-font-scale)), calc(15px * var(--public-font-scale)));
            line-height: 1.8;
            color: rgba(240, 253, 244, 0.70);
        }

        .celebration-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .celebration-title {
            margin: 0;
            font-size: clamp(26px, 7vw, 40px);
            line-height: 1.08;
            font-weight: 900;
            letter-spacing: -0.03em;
            text-wrap: balance;
        }

        .celebration-lead {
            margin: 10px 0 0;
            max-width: 720px;
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(18px * var(--public-font-scale)));
            line-height: 1.85;
            color: var(--muted);
        }

        .celebration-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: rgba(247, 200, 115, 0.14);
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #fff2ca;
        }

        .celebration-list {
            display: grid;
            gap: 14px;
            margin-top: 22px;
        }

        .celebration-item {
            padding: 18px 18px 20px;
        }

        .celebration-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .celebration-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            padding: 7px 11px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .celebration-song {
            margin: 0;
            font-size: clamp(calc(22px * var(--public-font-scale)), calc(5vw * var(--public-font-scale)), calc(30px * var(--public-font-scale)));
            line-height: 1.18;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .celebration-lyrics {
            margin-top: 14px;
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.10);
            padding: 16px 16px 18px;
            color: rgba(255, 255, 255, 0.95);
            font-size: clamp(calc(19px * var(--public-font-scale)), calc(4.9vw * var(--public-font-scale)), calc(24px * var(--public-font-scale)));
            line-height: 2.05;
            white-space: break-spaces;
            word-break: break-word;
            overflow-wrap: anywhere;
            text-wrap: pretty;
        }

        .celebration-empty {
            margin-top: 24px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.04);
            padding: 20px;
            color: var(--muted);
            font-size: clamp(calc(16px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(18px * var(--public-font-scale)));
            line-height: 1.9;
        }

        @keyframes pulse {
            0%, 80%, 100% {
                opacity: 0.35;
                transform: scale(0.9);
            }

            40% {
                opacity: 1;
                transform: scale(1);
            }
        }

        @media (min-width: 720px) {
            .page {
                padding: 18px 18px 36px;
            }

            .hero-main {
                padding: 24px 22px 18px;
            }

            .hero-side {
                padding: 8px 22px 22px;
            }

            .celebration-section {
                padding: 24px 22px 16px;
            }

            .access-tools button {
                flex: 0 0 auto;
            }

            .info-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 961px) {
            .page {
                padding: 28px 24px 52px;
            }

            .shell,
            .celebration-section {
                border-radius: 28px;
            }

            .hero {
                grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.9fr);
            }

            .hero-main {
                padding: 36px 36px 30px;
            }

            .hero-side {
                padding: 36px 30px 30px 16px;
            }

            .countdown {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .count-item {
                display: block;
                text-align: center;
                padding: 14px 12px;
            }

            .count-label {
                margin-top: 8px;
            }

            .celebration-section {
                padding: 30px 36px 18px;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="shell">
            <div class="hero">
                <div class="hero-main">
                    <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="brand">
                        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz &amp; Cifra">
                        <div>
                            <p class="brand-kicker">Voz &amp; Cifra</p>
                            <p class="brand-name">
                                @if ($estadoCelebracao === 'em_andamento')
                                    Celebracao em andamento
                                @else
                                    Aguardando a proxima missa
                                @endif
                            </p>
                        </div>
                    </a>

                    <span class="location">{{ $igreja->cidade }} - {{ $igreja->estado }}</span>

                    <h1 class="title">{{ $igreja->nome }}</h1>

                    <p class="lead">
                        @if ($estadoCelebracao === 'em_andamento' && $missaPublica)
                            A celebracao cadastrada para esta igreja ja entrou em andamento. Este link publico fixo continuara sendo o endereco oficial da missa ativa, com horario oficial de Cuiaba - MT.
                        @elseif ($proximaMissa)
                            A proxima celebracao cadastrada para esta igreja ja foi identificada. Este link publico fixo sera usado para exibir a missa ativa com leitura limpa e sem cifras no horario oficial de Cuiaba - MT.
                        @else
                            Em breve, aqui aparecera a missa organizada pela equipe da igreja, com leitura limpa.
                        @endif
                    </p>

                    @if ($missaPublica)
                        <div class="celebration-focus">
                            <span class="celebration-focus-label">
                                @if ($estadoCelebracao === 'em_andamento')
                                    Celebracao em exibicao
                                @else
                                    Celebracao identificada
                                @endif
                            </span>
                            <h2 class="celebration-focus-title">{{ $missaPublica->titulo }}</h2>
                            <p class="celebration-focus-meta">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} as {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                @if ($missaPublica->tempoLiturgico)
                                    • {{ $missaPublica->tempoLiturgico->nome }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="access-tools">
                        <button type="button" data-public-font="-1">A-</button>
                        <button type="button" data-public-font-reset>Fonte padrao</button>
                        <button type="button" data-public-font="1">A+</button>
                    </div>

                    <div class="info-row">
                        <div class="info-card">
                            <span class="label">Endereco</span>
                            <span class="value">{{ $igreja->endereco ?: 'Endereco da igreja sera exibido aqui em breve.' }}</span>
                        </div>

                        <div class="info-card">
                            <span class="label">Link fixo desta igreja</span>
                            <span class="value">{{ $igreja->slug }}</span>
                        </div>
                    </div>
                </div>

                <aside class="hero-side">
                    <div class="panel">
                        <div data-public-status-sync data-state="{{ $estadoCelebracao }}" data-status-url="{{ route('igrejas.public.status', ['slug' => $igreja->slug]) }}" @if($countdownIso) data-target="{{ $countdownIso }}" @endif></div>

                        @if ($estadoCelebracao === 'proxima' && $missaPublica)
                            <p class="panel-title">Contagem para o inicio da missa</p>
                            <div class="countdown" data-countdown-display>
                                <div class="count-item"><span class="count-number" data-days>00</span><span class="count-label">Dias</span></div>
                                <div class="count-item"><span class="count-number" data-hours>00</span><span class="count-label">Horas</span></div>
                                <div class="count-item"><span class="count-number" data-minutes>00</span><span class="count-label">Min</span></div>
                                <div class="count-item"><span class="count-number" data-seconds>00</span><span class="count-label">Seg</span></div>
                            </div>
                        @endif

                        <div class="next-missa">
                            @if ($missaPublica)
                                <strong>
                                    @if ($estadoCelebracao === 'em_andamento')
                                        Missa em andamento: {{ $missaPublica->titulo }}
                                    @else
                                        {{ $missaPublica->titulo }}
                                    @endif
                                </strong>
                                <span>
                                    {{ $missaPublica->data_missa->format('d/m/Y') }}
                                    @if ($estadoCelebracao === 'em_andamento')
                                        • de {{ substr((string) $missaPublica->hora_inicio, 0, 5) }} ate {{ substr((string) $missaPublica->hora_fim, 0, 5) }}
                                    @else
                                        • as {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                    @endif
                                    @if ($missaPublica->padre)
                                        • Padre {{ $missaPublica->padre->nome }}
                                    @endif
                                    <br>
                                    Horario oficial de Cuiaba - MT.
                                    @if ($missaPublica->tempoLiturgico)
                                        <br>{{ $missaPublica->tempoLiturgico->nome }}
                                    @endif
                                </span>
                            @else
                                <strong>Proxima missa</strong>
                                <span>
                                    Ainda nao existe uma missa futura cadastrada para esta igreja. Assim que a celebracao for organizada, esta pagina sera atualizada automaticamente com a liturgia e os cantos preparados.
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="panel notice">
                        <p class="panel-title">Aguarde a publicacao da celebracao</p>
                        <p class="notice-text">
                            @if ($estadoCelebracao === 'em_andamento')
                                A celebracao ativa desta igreja ja esta em andamento. Assim que a proxima etapa do modulo publico for concluida, esta mesma pagina podera exibir o conteudo completo da missa em tempo real.
                            @elseif ($proximaMissa)
                                O link publico desta igreja ja esta fixo e pronto para uso. Quando chegar o horario da missa cadastrada, esta mesma pagina podera exibir o conteudo publico final da celebracao.
                            @else
                                O link publico desta igreja ja esta fixo e pronto para uso. O conteudo final sera liberado aqui quando a missa ativa for organizada pela administracao local.
                            @endif
                        </p>

                        <div class="loading" aria-hidden="true">
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span class="dot"></span>
                            <span>
                                @if ($estadoCelebracao === 'em_andamento')
                                    Mantendo a pagina sincronizada com a celebracao atual
                                @elseif ($proximaMissa)
                                    Preparando a celebracao cadastrada para esta igreja
                                @else
                                    Preparando a experiencia da proxima missa
                                @endif
                            </span>
                        </div>

                        <p class="footer-note">
                            Esta tela e apenas uma etapa de preparacao. O QR Code da igreja continua apontando para este mesmo link fixo.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        @if ($missaPublica)
            <section class="celebration-section">
                <div class="celebration-header">
                    <div>
                        <p class="brand-kicker">Leitura publica</p>
                        <h2 class="celebration-title">
                            @if ($estadoCelebracao === 'em_andamento')
                                Missa publica sem cifras
                            @else
                                Previa publica da proxima missa
                            @endif
                        </h2>
                        <p class="celebration-lead">
                            Esta area foi preparada para o fiel acompanhar os cantos com leitura limpa, sem cifras e sem elementos tecnicos da equipe musical.
                        </p>
                    </div>
                    <span class="celebration-badge">
                        @if ($estadoCelebracao === 'em_andamento')
                            Celebracao em andamento
                        @elseif ($estadoCelebracao === 'proxima')
                            Proxima celebracao
                        @else
                            Preparacao
                        @endif
                    </span>
                </div>

                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                @if ($itensPublicos->isNotEmpty())
                    <div class="celebration-list">
                        @foreach ($itensPublicos as $item)
                            <article class="celebration-item">
                                <div class="celebration-meta">
                                    <span class="celebration-pill">Ordem {{ $item['ordem'] }}</span>
                                    @if (!empty($item['momento']))
                                        <span class="celebration-pill">{{ $item['momento'] }}</span>
                                    @endif
                                </div>
                                <h3 class="celebration-song">{{ $item['titulo'] }}</h3>
                                <div class="celebration-lyrics">{{ $item['letra_publica'] !== '' ? $item['letra_publica'] : 'A letra deste canto ainda nao foi preparada para exibicao publica.' }}</div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="celebration-empty">
                        O repertorio desta missa ainda nao foi organizado para exibicao publica. Quando a equipe concluir a preparacao, os cantos sem cifras aparecerao aqui automaticamente.
                    </div>
                @endif
            </section>
        @endif
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const statusSync = document.querySelector('[data-public-status-sync]');
            const countdown = document.querySelector('[data-countdown-display]');
            const dias = document.querySelector('[data-days]');
            const horas = document.querySelector('[data-hours]');
            const minutos = document.querySelector('[data-minutes]');
            const segundos = document.querySelector('[data-seconds]');
            let escalaFonte = 1;

            const aplicarEscalaFonte = () => {
                const escalaSegura = Math.max(0.9, Math.min(1.45, escalaFonte));
                root.style.setProperty('--public-font-scale', escalaSegura.toFixed(2));
            };

            aplicarEscalaFonte();

            const vincularAcaoBotao = (elemento, callback) => {
                if (!elemento) {
                    return;
                }

                elemento.addEventListener('click', callback);
                elemento.addEventListener('touchend', (event) => {
                    event.preventDefault();
                    callback();
                }, { passive: false });
            };

            document.querySelectorAll('[data-public-font]').forEach((botao) => {
                vincularAcaoBotao(botao, () => {
                    escalaFonte = Math.max(0.9, Math.min(1.45, escalaFonte + (Number(botao.dataset.publicFont || 0) * 0.08)));
                    aplicarEscalaFonte();
                });
            });

            vincularAcaoBotao(document.querySelector('[data-public-font-reset]'), () => {
                escalaFonte = 1;
                aplicarEscalaFonte();
            });

            if (!statusSync) {
                return;
            }

            const target = statusSync.dataset.target;
            const countdownState = statusSync.dataset.state;
            const statusUrl = statusSync.dataset.statusUrl;
            let recargaAgendada = false;
            let ultimaChaveEstado = [countdownState, target || '', window.location.pathname].join('|');

            if (!target) {
                return;
            }

            const destino = new Date(target);

            window.setInterval(() => {
                const agora = new Date().getTime();
                const distancia = destino.getTime() - agora;

                if (countdown && dias && horas && minutos && segundos) {
                    if (distancia <= 0) {
                        dias.textContent = '00';
                        horas.textContent = '00';
                        minutos.textContent = '00';
                        segundos.textContent = '00';
                    } else {
                        const totalSegundos = Math.floor(distancia / 1000);
                        const totalMinutos = Math.floor(totalSegundos / 60);
                        const totalHoras = Math.floor(totalMinutos / 60);
                        const totalDias = Math.floor(totalHoras / 24);

                        dias.textContent = String(totalDias).padStart(2, '0');
                        horas.textContent = String(totalHoras % 24).padStart(2, '0');
                        minutos.textContent = String(totalMinutos % 60).padStart(2, '0');
                        segundos.textContent = String(totalSegundos % 60).padStart(2, '0');
                    }
                }

                if (distancia <= 0 && !recargaAgendada && (countdownState === 'em_andamento' || countdownState === 'proxima')) {
                    recargaAgendada = true;
                    window.setTimeout(() => window.location.reload(), 1500);
                }
            }, 1000);

            if (statusUrl) {
                window.setInterval(async () => {
                    try {
                        const resposta = await fetch(statusUrl, {
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
                        console.debug('Falha ao sincronizar a pagina publica.', error);
                    }
                }, 30000);
            }
        });
    </script>
</body>
</html>
