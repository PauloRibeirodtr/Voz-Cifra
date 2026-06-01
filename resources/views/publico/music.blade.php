<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz &amp; Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    @vite(['resources/css/publico/music.css', 'resources/js/publico/music.js'])
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
                        <p class="brand-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'MÃºsicos' : 'Igreja' }}</p>
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
                        <h2 class="section-title">ProgramaÃ§Ã£o</h2>
                    </div>

                    @if ($missasHoje->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver missas anteriores">â€¹</button>
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
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver prÃ³ximas missas">â€º</button>
                        </div>
                    @else
                        <div class="empty-state empty-state--compact">
                            <h3 class="empty-title empty-title--small">Ainda nÃ£o hÃ¡ missas para hoje.</h3>
                            <p class="empty-copy">{{ $proximasMissas->isNotEmpty() ? 'A proxima missa publicada ja pode ser aberta abaixo.' : 'Volte mais tarde ou consulte celebraÃ§Ãµes anteriores.' }}</p>
                        </div>
                    @endif
                </section>

                @if ($proximasMissas->isNotEmpty())
                    <section class="section">
                        <div class="section-header">
                            <p class="section-kicker">Agenda</p>
                            <h2 class="section-title">CelebraÃ§Ãµes publicadas</h2>
                        </div>

                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver celebraÃ§Ãµes anteriores">â€¹</button>
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
                                        <p class="card-meta">{{ $proximaMissaItem['dia_semana'] }} â€¢ {{ $proximaMissaItem['data'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver prÃ³ximas celebraÃ§Ãµes">â€º</button>
                        </div>
                    </section>
                @endif
            @else
                <section class="section">
                    <div class="section-header">
                        <p class="section-kicker">RepertÃ³rio</p>
                        <h2 class="section-title">CelebraÃ§Ãµes publicadas</h2>
                    </div>

                    @if ($missasMusicos->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver repertÃ³rios anteriores">â€¹</button>
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
                                        <p class="card-meta">{{ $missaMusico['dia_semana'] }} â€¢ {{ $missaMusico['data'] }}</p>
                                    </div>
                                </a>
                            @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver prÃ³ximos repertÃ³rios">â€º</button>
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Ainda nÃ£o hÃ¡ missas publicadas para ensaio.</h3>
                            <p class="empty-copy">Este link Ã© somente leitura e serÃ¡ atualizado quando a equipe publicar um repertÃ³rio.</p>
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
                            <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'RepertÃ³rio' : 'CelebraÃ§Ã£o' }}</p>
                            <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertorio' : $missaPublica->titulo }}</h2>
                            <p class="celebration-meta-text">
                                {{ $missaPublica->data_missa->format('d/m/Y') }} â€¢ {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
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
                                <div class="history-live-results public-history-quick-results" data-history-live-results-top hidden></div>
                                <div class="history-empty public-history-quick-empty" data-history-live-empty-top hidden>Nenhum resultado encontrado.</div>
                            @else
                                <div class="public-reader-tools" aria-label="Ajuste de leitura" style="position:static;">
                                    <button type="button" class="public-tool-button" data-public-plain-font="-1">A- Letra</button>
                                    <button type="button" class="public-tool-button" data-public-plain-font="1">A+ Letra</button>
                                </div>
                            @endif
                        </div>
                        <span class="badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Abrir repertÃ³rio' : 'Abrir celebraÃ§Ã£o' }}</span>
                    </div>

                    @if ($itensPublicos->isNotEmpty())
                        @if ($itensPublicos->count() > 1)
                            <div class="public-swipe-hint" aria-hidden="true">Deslize para o lado para ver o proximo canto</div>
                        @endif
                        <div class="celebration-list">
                            @foreach ($itensPublicos as $item)
                                @php($itemIndicePublico = $loop->index)
                                <article class="celebration-item" @if (($modoPublico ?? 'fieis') === 'musicos') data-public-song data-public-song-id="{{ $itemIndicePublico }}" @endif>
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
                                        <div class="public-song-tools" aria-label="Ferramentas desta musica">
                                            <button type="button" class="public-tool-button" data-public-song-font="-1">A- Texto</button>
                                            <button type="button" class="public-tool-button" data-public-song-font="1">A+ Texto</button>
                                            <button type="button" class="public-tool-button" data-public-chords-open>Acordes</button>
                                        </div>
                                        <div class="lyrics" data-public-musician-lyrics data-public-song-lyrics data-base-tom="{{ $item['tom'] ?? '' }}" data-lyrics="{{ e($item['letra_publica'] ?? '') }}">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
                                    @else
                                        <div class="lyrics">{!! $item['letra_publica'] !== '' ? ($item['letra_publica_html'] ?? nl2br(e($item['letra_publica']), false)) : 'A letra deste canto ainda nÃ£o foi preparada para exibiÃ§Ã£o pÃºblica.' !!}</div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'RepertÃ³rio ainda nÃ£o disponÃ­vel.' : 'CelebraÃ§Ã£o ainda sem repertÃ³rio pÃºblico.' }}</h3>
                        </div>
                    @endif
                </section>
            @endif

            @php($historicoUltimasMissas = collect($historicoUltimasMissas ?? []))
            <details class="section history-toggle" id="historico-publico" @if(($modoPublico ?? 'fieis') === 'musicos') hidden @endif @if($historicoBusca !== '' || $historicoUltimasMissas->isNotEmpty()) open @endif>
                <summary>Consultar histÃ³rico</summary>

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
                                    <p class="history-meta">{{ $missaHistorica['dia_semana'] }} â€¢ {{ $missaHistorica['horario'] }} @if (!empty($missaHistorica['tempo_liturgico'])) â€¢ {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
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
                                    <p class="history-meta">{{ $missaHistorica['dia_semana'] }} â€¢ {{ $missaHistorica['horario'] }} @if (!empty($missaHistorica['tempo_liturgico'])) â€¢ {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
        </div>

        <div class="home-floating">
            <a href="{{ route('root') }}" class="home-floating__link" aria-label="Voltar para a pÃ¡gina principal">
                <span aria-hidden="true">â†</span>
                <span class="home-floating__text">&nbsp;PÃ¡gina principal</span>
            </a>
        </div>

        @if (($modoPublico ?? 'fieis') === 'musicos')
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

            <div class="public-scroll-dock" data-public-scroll-dock>
                <button type="button" class="public-tool-button public-tool-button--primary" data-public-auto-scroll>Iniciar rolagem</button>
                <label for="public_scroll_speed" class="public-scroll-dock__speed">Velocidade</label>
                <input id="public_scroll_speed" type="range" min="1" max="5" value="1" step="1" data-public-scroll-speed aria-label="Velocidade da auto rolagem">
                <button type="button" class="public-tool-button" data-public-scroll-top>Topo</button>
            </div>
        @endif
    </main>

    @if (($modoPublico ?? 'fieis') === 'musicos')
        @include('partials.chord-transposer-script')
        <script type="application/json" data-public-chord-library>@json($bibliotecaAcordes ?? [], JSON_UNESCAPED_UNICODE)</script>
    @endif
</body>
</html>

