<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz & Cifra</title>
    <style>
        :root {
            color-scheme: light;
            --bg-1: #0b3a2b;
            --bg-2: #145a3b;
            --bg-3: #2f8a57;
            --text: #f6fff8;
            --muted: rgba(240, 253, 244, 0.84);
            --soft: rgba(255, 255, 255, 0.10);
            --soft-border: rgba(255, 255, 255, 0.14);
            --accent: #d7ffe3;
            --accent-2: #f7c873;
            --shadow: 0 28px 80px rgba(0, 0, 0, 0.22);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.10), transparent 24%),
                radial-gradient(circle at left bottom, rgba(255, 255, 255, 0.08), transparent 28%),
                linear-gradient(140deg, var(--bg-1), var(--bg-2) 48%, var(--bg-3));
            color: var(--text);
        }

        body { min-height: 100vh; }

        .page {
            width: min(1160px, calc(100% - 28px));
            margin: 0 auto;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 28px 0;
        }

        .shell {
            width: 100%;
            border-radius: 34px;
            border: 1px solid var(--soft-border);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
            overflow: hidden;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(320px, 0.95fr);
        }

        .hero-main { padding: 34px; }

        .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        .brand img {
            width: 64px;
            height: auto;
            filter: drop-shadow(0 10px 24px rgba(0, 0, 0, 0.18));
        }

        .brand-kicker {
            margin: 0 0 4px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.26em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .brand-name {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .location {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 999px;
            border: 1px solid var(--soft-border);
            background: rgba(255, 255, 255, 0.08);
            padding: 10px 16px;
            font-size: 14px;
            color: var(--accent);
        }

        .title {
            margin: 22px 0 0;
            font-size: clamp(36px, 5vw, 66px);
            line-height: 0.98;
            font-weight: 900;
            letter-spacing: -0.04em;
            max-width: 780px;
        }

        .lead {
            margin: 22px 0 0;
            max-width: 760px;
            font-size: 18px;
            line-height: 1.8;
            color: var(--muted);
        }

        .info-row {
            margin-top: 26px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .info-card {
            border-radius: 24px;
            border: 1px solid var(--soft-border);
            background: rgba(0, 0, 0, 0.10);
            padding: 18px 20px;
        }

        .label {
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .value {
            display: block;
            margin-top: 10px;
            font-size: 21px;
            line-height: 1.45;
            font-weight: 700;
            word-break: break-word;
        }

        .hero-side {
            border-left: 1px solid rgba(255, 255, 255, 0.10);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.06), rgba(0, 0, 0, 0.08));
            padding: 34px 28px;
        }

        .panel {
            border-radius: 28px;
            border: 1px solid var(--soft-border);
            background: rgba(255, 255, 255, 0.08);
            padding: 22px;
        }

        .panel + .panel { margin-top: 18px; }

        .panel-title {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .countdown {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-top: 18px;
        }

        .count-item {
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.12);
            padding: 14px 10px;
            text-align: center;
        }

        .count-number {
            display: block;
            font-size: 28px;
            font-weight: 900;
            line-height: 1;
            color: #fff7db;
        }

        .count-label {
            display: block;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .next-missa {
            margin-top: 18px;
            border-radius: 20px;
            background: rgba(247, 200, 115, 0.12);
            border: 1px solid rgba(247, 200, 115, 0.22);
            padding: 16px 18px;
        }

        .access-tools {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 18px;
        }

        .access-tools button {
            border: 1px solid var(--soft-border);
            background: rgba(255, 255, 255, 0.08);
            color: var(--text);
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .access-tools button:hover {
            background: rgba(255, 255, 255, 0.16);
        }

        .next-missa strong {
            display: block;
            margin-bottom: 6px;
            font-size: 18px;
        }

        .next-missa span {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
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
            font-size: 15px;
            line-height: 1.8;
            color: var(--muted);
        }

        .loading {
            display: flex;
            align-items: center;
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

        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

        .footer-note {
            margin-top: 18px;
            font-size: 13px;
            line-height: 1.7;
            color: rgba(240, 253, 244, 0.70);
        }

        @keyframes pulse {
            0%, 80%, 100% { opacity: 0.35; transform: scale(0.9); }
            40% { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 960px) {
            .hero { grid-template-columns: 1fr; }
            .hero-side {
                border-left: 0;
                border-top: 1px solid rgba(255, 255, 255, 0.10);
            }
        }

        @media (max-width: 680px) {
            .page {
                width: min(100% - 18px, 1160px);
                padding: 18px 0;
            }

            .hero-main, .hero-side { padding: 22px; }
            .brand img { width: 52px; }
            .brand-name { font-size: 24px; }
            .lead { font-size: 16px; }
            .info-row, .countdown { grid-template-columns: 1fr; }

            .count-item {
                display: flex;
                align-items: center;
                justify-content: space-between;
                text-align: left;
                padding: 14px 16px;
            }

            .count-label { margin-top: 0; }
        }
    </style>
</head>
<body>
    <main class="page">
        <section class="shell">
            <div class="hero">
                <div class="hero-main">
                    <div class="brand">
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
                    </div>

                    <span class="location">{{ $igreja->cidade }} - {{ $igreja->estado }}</span>

                    <h1 class="title">{{ $igreja->nome }}</h1>

                    <p class="lead">
                        @if ($estadoCelebracao === 'em_andamento' && $missaPublica)
                            A celebracao cadastrada para esta igreja ja entrou em andamento. Este link publico fixo continuara sendo o endereco oficial da missa ativa, com horario de Corumba - MS.
                        @elseif ($proximaMissa)
                            A proxima celebracao cadastrada para esta igreja ja foi identificada. Este link publico fixo sera usado para exibir a missa ativa com leitura limpa e sem cifras no horario oficial de Corumba - MS.
                        @else
                            Em breve, aqui aparecera a missa organizada pela equipe da igreja, com leitura limpa .
                        @endif
                    </p>

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
                        <p class="panel-title">
                            @if ($estadoCelebracao === 'em_andamento')
                                Contagem para o fim da missa
                            @elseif ($proximaMissa)
                                Contagem para a proxima missa
                            @else
                                Aguardando agendamento
                            @endif
                        </p>
                        <div class="countdown" data-countdown data-state="{{ $estadoCelebracao }}" data-status-url="{{ route('igrejas.public.status', ['slug' => $igreja->slug]) }}" @if($countdownIso) data-target="{{ $countdownIso }}" @endif>
                            <div class="count-item"><span class="count-number" data-days>00</span><span class="count-label">Dias</span></div>
                            <div class="count-item"><span class="count-number" data-hours>00</span><span class="count-label">Horas</span></div>
                            <div class="count-item"><span class="count-number" data-minutes>00</span><span class="count-label">Min</span></div>
                            <div class="count-item"><span class="count-number" data-seconds>00</span><span class="count-label">Seg</span></div>
                        </div>

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
                                        as {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                    @endif
                                    @if ($missaPublica->padre)
                                        • Padre {{ $missaPublica->padre->nome }}
                                    @endif
                                    <br>
                                    Horario oficial de Corumba - MS.
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
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const countdown = document.querySelector('[data-countdown]');
            const dias = document.querySelector('[data-days]');
            const horas = document.querySelector('[data-hours]');
            const minutos = document.querySelector('[data-minutes]');
            const segundos = document.querySelector('[data-seconds]');
            let escalaFonte = 1;

            const aplicarEscalaFonte = () => {
                document.documentElement.style.fontSize = `${Math.max(0.9, Math.min(1.4, escalaFonte)) * 16}px`;
            };

            if (!countdown || !dias || !horas || !minutos || !segundos) {
                return;
            }

            const target = countdown.dataset.target;
            const countdownState = countdown.dataset.state;
            const statusUrl = countdown.dataset.statusUrl;
            let recargaAgendada = false;
            let ultimaChaveEstado = [countdownState, target || '', window.location.pathname].join('|');

            if (!target) {
                dias.textContent = '--';
                horas.textContent = '--';
                minutos.textContent = '--';
                segundos.textContent = '--';
                return;
            }

            const destino = new Date(target);

            const atualizar = () => {
                const agora = new Date().getTime();
                const distancia = destino.getTime() - agora;

                if (distancia <= 0) {
                    dias.textContent = '00';
                    horas.textContent = '00';
                    minutos.textContent = '00';
                    segundos.textContent = '00';

                    if (!recargaAgendada && (countdownState === 'em_andamento' || countdownState === 'proxima')) {
                        recargaAgendada = true;
                        window.setTimeout(() => window.location.reload(), 1500);
                    }

                    return;
                }

                const totalSegundos = Math.floor(distancia / 1000);
                const totalMinutos = Math.floor(totalSegundos / 60);
                const totalHoras = Math.floor(totalMinutos / 60);
                const totalDias = Math.floor(totalHoras / 24);

                dias.textContent = String(totalDias).padStart(2, '0');
                horas.textContent = String(totalHoras % 24).padStart(2, '0');
                minutos.textContent = String(totalMinutos % 60).padStart(2, '0');
                segundos.textContent = String(totalSegundos % 60).padStart(2, '0');
            };

            atualizar();
            setInterval(atualizar, 1000);

            document.querySelectorAll('[data-public-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    escalaFonte = Math.max(0.9, Math.min(1.4, escalaFonte + (Number(botao.dataset.publicFont || 0) * 0.08)));
                    aplicarEscalaFonte();
                });
            });

            document.querySelector('[data-public-font-reset]')?.addEventListener('click', () => {
                escalaFonte = 1;
                aplicarEscalaFonte();
            });

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
                            String(payload.missa_id || ''),
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
