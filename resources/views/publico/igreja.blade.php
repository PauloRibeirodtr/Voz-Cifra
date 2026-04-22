<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz & Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <style>
        :root {
            color-scheme: dark;
            --bg-1: #1b0e0f;
            --bg-2: #2a1415;
            --bg-3: #5f4229;
            --panel: rgba(28, 15, 15, 0.94);
            --panel-soft: rgba(44, 24, 22, 0.92);
            --text: #fff8ef;
            --muted: #eadcc7;
            --accent: #f0d7a5;
            --line: rgba(240, 215, 165, 0.18);
            --shadow: 0 22px 60px rgba(0, 0, 0, 0.34);
            --public-font-scale: 1.08;
        }

        body[data-contrast='high'] {
            --panel: rgba(14, 8, 8, 0.98);
            --panel-soft: rgba(20, 12, 12, 0.98);
            --text: #fffdf8;
            --muted: #f4e8d1;
            --accent: #ffe0a8;
            --line: rgba(255, 224, 168, 0.34);
            --shadow: 0 0 0 1px rgba(255, 224, 168, 0.16), 0 22px 60px rgba(0, 0, 0, 0.5);
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
            overflow-x: hidden;
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(240, 215, 165, 0.12), transparent 24%),
                radial-gradient(circle at left bottom, rgba(107, 74, 45, 0.14), transparent 28%),
                linear-gradient(140deg, var(--bg-1), var(--bg-2) 44%, var(--bg-3));
            color: var(--text);
        }

        body {
            line-height: 1.8;
            -webkit-text-size-adjust: 100%;
        }

        .page {
            width: 100%;
            min-height: 100vh;
            padding: 12px 10px 28px;
        }

        .shell,
        .content-section {
            width: min(100%, 1120px);
            margin: 0 auto;
            border-radius: 28px;
            background: var(--panel);
            box-shadow: var(--shadow);
            border: 1px solid var(--line);
            backdrop-filter: blur(12px);
        }

        .content-section {
            margin-top: 16px;
            padding: 22px 16px 18px;
        }

        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
        }

        .hero-main {
            padding: 20px 16px 18px;
        }

        .hero-side {
            padding: 0 16px 20px;
        }

        .brand {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 20px;
            text-decoration: none;
            color: inherit;
        }

        .brand img {
            width: 56px;
            height: auto;
            flex-shrink: 0;
            filter: drop-shadow(0 6px 16px rgba(0, 0, 0, 0.18));
        }

        .brand-kicker,
        .section-kicker,
        .panel-title,
        .label {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .brand-name {
            margin: 6px 0 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(26px, 4.8vw, 36px);
            line-height: 1.1;
            font-weight: 900;
            letter-spacing: -0.02em;
        }

        .location {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--line);
            padding: 12px 16px;
            font-size: 16px;
            font-weight: 700;
            color: var(--accent);
        }

        .title {
            margin: 18px 0 0;
            max-width: 14ch;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(40px, 9vw, 74px);
            line-height: 0.98;
            font-weight: 900;
            letter-spacing: -0.04em;
            text-wrap: balance;
        }

        .lead,
        .panel-text,
        .section-lead,
        .history-empty {
            font-size: clamp(calc(17px * var(--public-font-scale)), calc(3.8vw * var(--public-font-scale)), calc(20px * var(--public-font-scale)));
            line-height: 1.9;
            color: var(--muted);
        }

        .lead {
            margin: 16px 0 0;
            max-width: 56rem;
        }

        .status-strip {
            margin-top: 18px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border-radius: 999px;
            padding: 12px 16px;
            background: rgba(202, 161, 96, 0.12);
            border: 1px solid rgba(240, 215, 165, 0.16);
            color: #fff3cf;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .access-tools {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-top: 22px;
        }

        .access-tools button,
        .history-form button,
        .history-form input {
            min-height: 58px;
            font: inherit;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .access-tools button {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.06);
            color: var(--text);
            padding: 16px 14px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
        }

        .access-tools button:last-child {
            grid-column: 1 / -1;
        }

        .summary-block,
        .panel,
        .agenda-item,
        .celebration-item,
        .history-item,
        .history-form {
            border-radius: 22px;
            background: var(--panel-soft);
            border: 1px solid rgba(255,255,255,0.05);
        }

        .summary-block {
            margin-top: 24px;
            background: linear-gradient(135deg, rgba(202, 161, 96, 0.16), rgba(255, 255, 255, 0.04));
            padding: 18px 18px 16px;
            border: 1px solid rgba(240, 215, 165, 0.14);
        }

        .summary-title,
        .section-title,
        .celebration-title {
            margin: 8px 0 0;
            font-family: Georgia, "Times New Roman", serif;
            font-weight: 900;
            color: #fffdf8;
            letter-spacing: -0.03em;
            text-wrap: balance;
        }

        .summary-title {
            font-size: clamp(calc(28px * var(--public-font-scale)), calc(6vw * var(--public-font-scale)), calc(40px * var(--public-font-scale)));
            line-height: 1.14;
        }

        .summary-meta,
        .agenda-meta,
        .history-meta {
            color: var(--muted);
            font-size: clamp(calc(16px * var(--public-font-scale)), calc(3.5vw * var(--public-font-scale)), calc(19px * var(--public-font-scale)));
            line-height: 1.8;
        }

        .summary-meta {
            margin: 10px 0 0;
            color: rgba(255, 255, 255, 0.9);
        }

        .panel {
            padding: 20px;
        }

        .panel + .panel {
            margin-top: 14px;
        }

        .panel-text,
        .section-lead,
        .history-empty {
            margin: 12px 0 0;
        }

        .agenda-list,
        .celebration-list,
        .history-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .agenda-item,
        .celebration-item,
        .history-item {
            padding: 18px;
        }

        .agenda-top,
        .history-top {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .agenda-title,
        .history-title {
            margin: 0;
            font-size: clamp(calc(20px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(24px * var(--public-font-scale)));
            line-height: 1.35;
            font-weight: 800;
            color: #fff8ef;
        }

        .agenda-hour,
        .history-date {
            font-size: clamp(calc(20px * var(--public-font-scale)), calc(4.2vw * var(--public-font-scale)), calc(26px * var(--public-font-scale)));
            font-weight: 900;
            color: var(--accent);
        }

        .section-header,
        .celebration-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .section-title,
        .celebration-title {
            font-size: clamp(30px, 7vw, 42px);
            line-height: 1.08;
        }

        .history-form {
            margin-top: 18px;
            padding: 16px;
            display: grid;
            gap: 12px;
        }

        .history-form label {
            display: block;
            margin-bottom: 8px;
            color: var(--accent);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .history-form input {
            width: 100%;
            border: 1px solid rgba(240, 215, 165, 0.16);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            padding: 0 16px;
            font-size: 17px;
        }

        .history-form input::placeholder {
            color: rgba(234, 220, 199, 0.72);
        }

        .history-form button {
            border: 1px solid rgba(240, 215, 165, 0.2);
            border-radius: 16px;
            background: rgba(202, 161, 96, 0.15);
            color: #fff8ef;
            padding: 0 18px;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
        }

        .history-form a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 58px;
            border-radius: 16px;
            border: 1px solid rgba(240, 215, 165, 0.14);
            color: var(--muted);
            font-weight: 700;
            padding: 0 18px;
            background: rgba(255, 255, 255, 0.04);
        }

        .history-note {
            margin-top: 10px;
            color: rgba(255, 248, 239, 0.72);
            font-size: clamp(calc(15px * var(--public-font-scale)), calc(3.2vw * var(--public-font-scale)), calc(17px * var(--public-font-scale)));
            line-height: 1.8;
        }

        .celebration-badge,
        .celebration-pill,
        .history-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--accent);
            background: rgba(255, 255, 255, 0.08);
        }

        .celebration-badge {
            gap: 8px;
            padding: 12px 15px;
            color: #fff2ca;
            border: 1px solid rgba(240, 215, 165, 0.16);
            background: rgba(202, 161, 96, 0.16);
        }

        .celebration-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }

        .celebration-song {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(calc(28px * var(--public-font-scale)), calc(5vw * var(--public-font-scale)), calc(34px * var(--public-font-scale)));
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .celebration-lyrics {
            margin-top: 14px;
            border-radius: 18px;
            background: rgba(0, 0, 0, 0.14);
            padding: 18px 18px 20px;
            color: rgba(255, 255, 255, 0.98);
            font-size: clamp(calc(23px * var(--public-font-scale)), calc(4.9vw * var(--public-font-scale)), calc(28px * var(--public-font-scale)));
            line-height: 2.15;
            white-space: break-spaces;
            word-break: break-word;
            overflow-wrap: anywhere;
            text-wrap: pretty;
        }

        .empty-box {
            margin-top: 20px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.04);
            padding: 20px;
            color: var(--muted);
            font-size: clamp(calc(18px * var(--public-font-scale)), calc(4vw * var(--public-font-scale)), calc(21px * var(--public-font-scale)));
            line-height: 1.9;
        }

        @media (min-width: 720px) {
            .page {
                padding: 18px 18px 36px;
            }

            .hero-main {
                padding: 24px 22px 22px;
            }

            .hero-side {
                padding: 0 22px 22px;
            }

            .content-section {
                padding: 24px 22px 20px;
            }

            .access-tools {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .access-tools button:last-child {
                grid-column: auto;
            }

            .history-form {
                grid-template-columns: minmax(0, 1fr) auto auto;
                align-items: end;
            }
        }

        @media (min-width: 980px) {
            .page {
                padding: 28px 24px 52px;
            }

            .hero {
                grid-template-columns: minmax(0, 1.35fr) minmax(320px, 0.9fr);
            }

            .hero-main {
                padding: 34px 34px 30px;
            }

            .hero-side {
                padding: 34px 30px 30px 10px;
            }

            .content-section {
                padding: 28px 34px 24px;
            }

            .history-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>
</head>
<body data-contrast="normal">
    <main class="page">
        <div hidden data-public-status-sync data-state="{{ $estadoCelebracao }}" data-status-url="{{ route('igrejas.public.status', ['slug' => $igreja->slug]) }}" @if($countdownIso) data-target="{{ $countdownIso }}" @endif></div>

        <section class="shell">
            <div class="hero">
                <div class="hero-main">
                    <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="brand">
                        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz &amp; Cifra">
                        <div>
                            <p class="brand-kicker">Voz &amp; Cifra</p>
                            <p class="brand-name">
                                @if ($estadoCelebracao === 'em_andamento')
                                    Missa em andamento
                                @elseif ($proximaMissa)
                                    Horarios da comunidade
                                @else
                                    Aguarde a proxima missa
                                @endif
                            </p>
                        </div>
                    </a>

                    <span class="location">{{ $igreja->cidade }} - {{ $igreja->estado }}</span>

                    <h1 class="title">{{ $igreja->nome }}</h1>

                    <p class="lead">
                        @if (($modoPublico ?? 'fieis') === 'musicos')
                            @if ($missaPublica)
                                Este link foi preparado para leitura musical. Quando a missa estiver publicada para musicos, as musicas aparecem em sequencia com cifras e mais foco no estudo da celebracao.
                            @elseif ($proximasMissas->isNotEmpty())
                                Aqui os musicos encontram os proximos horarios publicados para estudo. Quando a celebracao for liberada para musicos, o repertorio aparecera nesta mesma pagina.
                            @else
                                Este link ficara pronto para estudo musical assim que uma nova missa for publicada para musicos nesta igreja.
                            @endif
                        @elseif ($estadoCelebracao === 'em_andamento' && $missaPublica)
                            Esta pagina esta aberta para acompanhar a missa atual com leitura limpa, letras grandes e menos distracao na tela.
                        @elseif ($proximasMissas->isNotEmpty())
                            Aqui voce encontra os proximos horarios de missa desta comunidade. Quando chegar o momento da celebracao, o repertorio publico aparecera nesta mesma pagina.
                        @else
                            Este link publico ficara pronto para a comunidade acompanhar a missa assim que um novo horario for organizado pela equipe local.
                        @endif
                    </p>

                    <div class="status-strip">
                        @if (($modoPublico ?? 'fieis') === 'musicos' && $missaPublica)
                            Repertorio musical disponivel
                        @elseif ($estadoCelebracao === 'em_andamento')
                            Celebracao aberta agora
                        @elseif ($proximasMissas->isNotEmpty())
                            Proximos horarios disponiveis
                        @else
                            Aguardando novo horario
                        @endif
                    </div>

                    @if ($missaPublica)
                        <div class="summary-block">
                            <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Leitura do musico' : 'Missa aberta' }}</p>
                            <h2 class="summary-title">{{ $missaPublica->titulo }}</h2>
                            <p class="summary-meta">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} as {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                @if ($missaPublica->tempoLiturgico)
                                    • {{ $missaPublica->tempoLiturgico->nome }}
                                @endif
                            </p>
                        </div>
                    @endif

                    <div class="access-tools">
                        <button type="button" data-public-font="-1">A-</button>
                        <button type="button" data-public-font-reset>A</button>
                        <button type="button" data-public-font="1">A+</button>
                        <button type="button" data-public-contrast-toggle aria-pressed="false">Contraste</button>
                    </div>
                </div>

                <aside class="hero-side">
                    <div class="panel">
                        <p class="panel-title">Proximas missas</p>

                        @if ($proximasMissas->isNotEmpty())
                            <div class="agenda-list">
                                @foreach ($proximasMissas as $agenda)
                                    <article class="agenda-item">
                                        <div class="agenda-top">
                                            <h3 class="agenda-title">{{ $agenda['titulo'] }}</h3>
                                            <span class="agenda-hour">{{ $agenda['horario'] }}</span>
                                        </div>
                                        <p class="agenda-meta">
                                            {{ $agenda['dia_semana'] }} • {{ $agenda['data'] }}
                                            @if (!empty($agenda['tempo_liturgico']))
                                                <br>{{ $agenda['tempo_liturgico'] }}
                                            @endif
                                        </p>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <p class="panel-text">
                                Ainda nao ha novos horarios cadastrados para esta igreja. Quando a equipe publicar uma nova missa, ela aparecera aqui de forma simples.
                            </p>
                        @endif
                    </div>

                    <div class="panel">
                        <p class="panel-title">Como funciona este link</p>
                        <p class="panel-text">
                            @if (($modoPublico ?? 'fieis') === 'musicos')
                                Este e o link publico oficial dos musicos desta igreja. Ele mostra apenas missas publicadas para musicos e reaproveita a mesma base de repertorio da celebracao.
                            @else
                                Este e o link publico oficial desta igreja. Ele mostra os horarios da comunidade e abre o repertorio somente quando a missa estiver disponivel para acompanhamento.
                            @endif
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="content-section">
            <div class="section-header">
                <div>
                    <p class="section-kicker">Historico</p>
                    <h2 class="section-title">Buscar missas passadas da comunidade</h2>
                    <p class="section-lead">
                        Voce pode pesquisar pelo nome da missa, dia da semana, data ou tempo liturgico para encontrar celebracoes anteriores desta igreja.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slugPublicoMusicos()]) : route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="history-form">
                <div>
                    <label for="historico">Buscar no historico</label>
                    <input
                        id="historico"
                        name="historico"
                        type="text"
                        value="{{ $historicoBusca }}"
                        placeholder="Ex.: domingo, 24/03, quinta, pascoa"
                    >
                </div>
                <button type="submit">Buscar</button>
                <a href="{{ ($modoPublico ?? 'fieis') === 'musicos' ? route('igrejas.public.musicos.show', ['slug' => $igreja->slugPublicoMusicos()]) : route('igrejas.public.show', ['slug' => $igreja->slug]) }}">Limpar</a>
            </form>

            <p class="history-note">
                Mostrando ate 12 missas passadas mais recentes{{ $historicoBusca !== '' ? ' para a busca informada.' : '.' }}
            </p>

            @if ($historicoMissas->isNotEmpty())
                <div class="history-list">
                    @foreach ($historicoMissas as $missaHistorica)
                        <article class="history-item">
                            <div class="history-top">
                                <h3 class="history-title">{{ $missaHistorica['titulo'] }}</h3>
                                <span class="history-date">{{ $missaHistorica['data'] }}</span>
                            </div>
                            <p class="history-meta">
                                {{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['horario'] }}
                            </p>
                            @if (!empty($missaHistorica['tempo_liturgico']))
                                <div style="margin-top: 10px;">
                                    <span class="history-pill">{{ $missaHistorica['tempo_liturgico'] }}</span>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="empty-box">
                    Nenhuma missa passada foi encontrada com esse termo. Tente buscar por data, nome da celebracao, dia da semana ou tempo liturgico.
                </div>
            @endif
        </section>

        @if ($missaPublica)
            <section class="content-section">
                <div class="celebration-header">
                    <div>
                        <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Leitura musical' : 'Leitura publica' }}</p>
                        <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertorio publicado para musicos' : 'Acompanhe a missa atual' }}</h2>
                        <p class="section-lead">
                            @if (($modoPublico ?? 'fieis') === 'musicos')
                                Esta area foi preparada para estudo e execucao do repertorio com a sequencia publicada para os musicos da igreja.
                            @else
                                Esta area foi preparada para leitura tranquila durante a celebracao, com menos elementos na tela e foco no canto que esta sendo acompanhado.
                            @endif
                        </p>
                    </div>
                    <span class="celebration-badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Modo musico' : 'Celebracao em andamento' }}</span>
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
                                <div class="celebration-lyrics">{{ $item['letra_publica'] !== '' ? $item['letra_publica'] : (($modoPublico ?? 'fieis') === 'musicos' ? 'A cifra e a letra desta musica ainda nao foram preparadas para o link publico do musico.' : 'A letra deste canto ainda nao foi preparada para exibicao publica.') }}</div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="empty-box">
                        {{ ($modoPublico ?? 'fieis') === 'musicos'
                            ? 'O repertorio desta missa ainda nao foi preparado para o link publico do musico. Assim que a equipe concluir a organizacao, as musicas aparecerao aqui.'
                            : 'O repertorio desta missa ainda nao foi preparado para exibicao publica. Assim que a equipe concluir a organizacao, os cantos aparecerao aqui.' }}
                    </div>
                @endif
            </section>
        @endif
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.documentElement;
            const statusSync = document.querySelector('[data-public-status-sync]');
            const contrastToggle = document.querySelector('[data-public-contrast-toggle]');
            const body = document.body;
            const fontKey = 'vozecifra-public-font-scale';
            const contrastKey = 'vozecifra-public-contrast';
            let escalaFonte = Number(localStorage.getItem(fontKey) || '1.08');

            const aplicarEscalaFonte = () => {
                const escalaSegura = Math.max(1, Math.min(1.55, escalaFonte));
                escalaFonte = escalaSegura;
                root.style.setProperty('--public-font-scale', escalaSegura.toFixed(2));
                localStorage.setItem(fontKey, escalaSegura.toFixed(2));
            };

            const aplicarContraste = (modo) => {
                const contrasteAtivo = modo === 'high';
                body.dataset.contrast = contrasteAtivo ? 'high' : 'normal';

                if (contrastToggle) {
                    contrastToggle.setAttribute('aria-pressed', contrasteAtivo ? 'true' : 'false');
                    contrastToggle.textContent = contrasteAtivo ? 'Contraste normal' : 'Contraste';
                }
            };

            aplicarEscalaFonte();
            aplicarContraste(localStorage.getItem(contrastKey) || 'normal');

            document.querySelectorAll('[data-public-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    escalaFonte += Number(botao.dataset.publicFont || 0) * 0.08;
                    aplicarEscalaFonte();
                });
            });

            const resetButton = document.querySelector('[data-public-font-reset]');
            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    escalaFonte = 1.08;
                    aplicarEscalaFonte();
                });
            }

            if (contrastToggle) {
                contrastToggle.addEventListener('click', () => {
                    const novoModo = body.dataset.contrast === 'high' ? 'normal' : 'high';
                    localStorage.setItem(contrastKey, novoModo);
                    aplicarContraste(novoModo);
                });
            }

            if (!statusSync) {
                return;
            }

            const statusUrl = statusSync.dataset.statusUrl;
            let ultimaChaveEstado = [
                statusSync.dataset.state || '',
                statusSync.dataset.target || '',
                window.location.pathname,
            ].join('|');

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
