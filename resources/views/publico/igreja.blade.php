<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz &amp; Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    @vite(['resources/css/publico/igreja.css', 'resources/js/publico/igreja.js'])
</head>
<body data-contrast="high" data-public-mode="{{ $modoPublico ?? 'fieis' }}">
        @php
            $celebracaoFoiEscolhida = (bool) ($missaPublica ?? false);
        @endphp
    <main class="page">
        <div
            hidden
            data-public-status-sync
            data-state="{{ $estadoCelebracao }}"
            data-status-url="{{ ($modoPublico ?? 'fieis') === 'musicos'
                ? route('igrejas.public.musicos.status', ['slug' => $igreja->slug, 'celebracao' => ($celebracaoSelecionadaIdParam ?? 0) > 0 ? $celebracaoSelecionadaIdParam : null])
                : route('igrejas.public.status', ['slug' => $igreja->slug, 'celebracao' => ($celebracaoSelecionadaIdParam ?? 0) > 0 ? $celebracaoSelecionadaIdParam : null]) }}"
            @if($countdownIso) data-target="{{ $countdownIso }}" @endif
        ></div>

        <div class="shell">
            @unless ((int) ($celebracaoSelecionadaIdParam ?? 0) > 0 && $missaPublica)
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
                        <h2 class="section-title">Programação</h2>
                    </div>

                    <div class="public-history-search">
                        @php($historicoBaseUrlTopo = route('igrejas.public.show', ['slug' => $igreja->slug]))
                        <form method="GET" action="{{ $historicoBaseUrlTopo }}" class="public-history-quick" data-history-form-top data-history-base-url="{{ $historicoBaseUrlTopo }}" aria-label="Buscar missa anterior">
                            <button type="submit" class="public-history-quick__button" aria-label="Buscar no histórico">
                                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                    <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                </svg>
                            </button>
                            <input type="text" name="historico" value="{{ $historicoBusca ?? '' }}" placeholder="Buscar celebração anterior" autocomplete="off" data-history-input-top>
                        </form>
                        <div class="history-live-results public-history-quick-results" data-history-live-results-top hidden></div>
                        <div class="history-empty public-history-quick-empty" data-history-live-empty-top hidden>Nenhum resultado encontrado.</div>
                    </div>

                    @if ($historicoBusca !== '')
                        @if ($historicoMissas->isNotEmpty())
                            <div class="section-header">
                                <p class="section-kicker">Resultado</p>
                                <h2 class="section-title">Celebrações encontradas</h2>
                            </div>
                            <div class="history-list history-list--compact" data-history-server-results>
                                @foreach ($historicoMissas as $missaHistorica)
                                    @php($historicoSelecionado = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                    <a
                                        href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica"
                                        class="history-link"
                                        data-history-card
                                        data-history-id="{{ $missaHistorica['id'] }}"
                                        data-selected="{{ $historicoSelecionado ? 'true' : 'false' }}"
                                    >
                                        <div class="history-badges">
                                            <span class="history-date">{{ $missaHistorica['data'] }}</span>
                                            <span class="badge history-badge-muted">Encontrada</span>
                                            <span class="badge history-badge-muted" data-history-action>Ver celebração</span>
                                        </div>
                                        <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                        <p class="history-meta">{{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['horario'] }} @if (!empty($missaHistorica['tempo_liturgico'])) • {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state empty-state--compact">
                                <h3 class="empty-title empty-title--small">Nenhuma celebração encontrada.</h3>
                                <p class="empty-copy">Tente outra data, título ou dia da semana.</p>
                            </div>
                        @endif
                    @elseif ($missasHoje->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver missas anteriores">Voltar</button>
                            <div class="cards schedule-carousel" data-schedule-carousel>
                                @foreach ($missasHoje as $missaHoje)
                                    @php($missaHojeSelecionada = (int) $missaHoje['id'] === (int) ($celebracaoSelecionadaIdParam ?? 0))
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
                        @php($historicoUltimasMissas = collect($historicoUltimasMissas ?? []))
                        @if ($historicoUltimasMissas->isNotEmpty())
                            <div class="section-header">
                                <p class="section-kicker">Anterior</p>
                            </div>
                            <div class="schedule-shell">
                                <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver missas anteriores">‹</button>
                                <div class="cards schedule-carousel" data-schedule-carousel>
                                    @foreach ($historicoUltimasMissas as $missaHistorica)
                                        @php($historicoSelecionado = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                        <a
                                            href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica"
                                            class="card card-link"
                                            data-selected="{{ $historicoSelecionado ? 'true' : 'false' }}"
                                        >
                                            <div class="card-main">
                                                <span class="card-hour">{{ $missaHistorica['horario'] }}</span>
                                                <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                                <p class="card-meta">{{ $missaHistorica['dia_semana'] }} • {{ $missaHistorica['data'] }}</p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximas missas">›</button>
                            </div>
                        @else
                            <div class="empty-state empty-state--compact">
                                <h3 class="empty-title empty-title--small">Ainda não há missas para hoje.</h3>
                                <p class="empty-copy">{{ $proximasMissas->isNotEmpty() ? 'A próxima missa publicada já pode ser aberta abaixo.' : 'Volte mais tarde ou consulte celebrações anteriores.' }}</p>
                            </div>
                        @endif
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
                                @php($proximaSelecionada = (int) $proximaMissaItem['id'] === (int) ($celebracaoSelecionadaIdParam ?? 0))
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
                                @php($missaMusicoSelecionada = (int) $missaMusico['id'] === (int) ($celebracaoSelecionadaIdParam ?? 0))
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

            @endunless

            @php($celebracaoFoiEscolhida = (bool) ($celebracaoSelecionadaIdParam ?? 0) > 0)

            @if ($missaPublica && $celebracaoFoiEscolhida)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section celebration-section" id="celebracao-publica" data-celebration-section>
                    <div class="celebration-header">
                        <div class="celebration-header__left">
                            <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="public-tool-button public-tool-button--ghost" aria-label="Voltar para a programação">←</a>
                            <div>
                                <p class="section-kicker">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório' : 'Celebração' }}</p>
                                <h2 class="celebration-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Cifras disponíveis' : $missaPublica->titulo }}</h2>
                                <p class="celebration-meta-text">
                                    {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                </p>
                            </div>
                        </div>
                        <div class="celebration-header__right">
                            <span class="badge">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Abrir repertório' : 'Abrir celebração' }}</span>
                            <div class="celebration-header__actions">
                                <details class="reading-settings">
                                    <summary aria-label="Ajustes de leitura">
                                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82A1.65 1.65 0 0 0 3 12.6H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.98.16 1.82.84 2.09 1.79A2 2 0 0 1 21 12.6h0a1.65 1.65 0 0 0-.33 1.82z"></path>
                                        </svg>
                                    </summary>
                                    <div class="reading-settings__panel">
                                        <button type="button" class="public-tool-button" data-public-plain-font="-1">A-</button>
                                        <button type="button" class="public-tool-button" data-public-plain-font="1">A+</button>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>

                    @if ($itensPublicos->isNotEmpty())
                        <div class="celebration-shell">
                            @if ($itensPublicos->count() > 1)
                                <div class="public-swipe-hint" aria-hidden="true">Deslize para o lado para ver o próximo canto</div>
                            @endif
                            <div class="celebration-nav" data-celebration-nav>
                                <button type="button" aria-label="Próxima música" data-celebration-next>›</button>
                            </div>
                            <div class="celebration-list" data-celebration-carousel>
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
                                            <span class="badge" data-public-capo-item data-base-tom="{{ $item['tom'] }}" hidden></span>
                                        @endif
                                    </div>
                                    <h3 class="card-title">{{ $item['titulo'] }}</h3>
                                    @if (($modoPublico ?? 'fieis') === 'musicos')
                                        <div class="public-song-tools" aria-label="Ferramentas desta musica">
                                            <div class="public-song-tools__status">
                                                <span class="badge" data-public-song-capo-summary>Sem capotraste</span>
                                                <span class="badge" data-public-song-font-summary>Fonte normal</span>
                                            </div>
                                            <button type="button" class="public-tool-button" data-public-song-font="-1">A- Texto</button>
                                            <button type="button" class="public-tool-button" data-public-song-font="1">A+ Texto</button>
                                            <button type="button" class="public-tool-button" data-public-song-capo-toggle>Capotraste</button>
                                            <button type="button" class="public-tool-button" data-public-chords-open>Acordes</button>
                                            <div class="public-capo-panel" data-public-song-capo-panel hidden>
                                                <div class="public-capo-grid" role="radiogroup" aria-label="Casa do capotraste desta musica">
                                                    <label class="public-capo-choice">
                                                        <input type="radio" name="public_capo_visual_{{ $itemIndicePublico }}" value="0" data-public-song-capo checked>
                                                        <span>Sem</span>
                                                    </label>
                                                    @for ($casaCapotraste = 1; $casaCapotraste <= 11; $casaCapotraste++)
                                                        <label class="public-capo-choice">
                                                            <input type="radio" name="public_capo_visual_{{ $itemIndicePublico }}" value="{{ $casaCapotraste }}" data-public-song-capo>
                                                            <span>{{ $casaCapotraste }} casa</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <div class="lyrics" data-public-musician-lyrics data-public-song-lyrics data-base-tom="{{ $item['tom'] ?? '' }}" data-lyrics="{{ e($item['letra_publica'] ?? '') }}">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
                                    @else
                                        <div class="lyrics">{!! $item['letra_publica'] !== '' ? ($item['letra_publica_html'] ?? nl2br(e($item['letra_publica']), false)) : 'A letra deste canto ainda não foi preparada para exibição pública.' !!}</div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">{{ ($modoPublico ?? 'fieis') === 'musicos' ? 'Repertório ainda não disponível.' : 'Celebração ainda sem repertório público.' }}</h3>
                        </div>
                    @endif
                </section>
            @endif

            <script type="application/json" data-history-items>@json($historicoSugestoes ?? [], JSON_UNESCAPED_UNICODE)</script>
        </div>

        <div class="home-floating">
            <a href="{{ route('root') }}" class="home-floating__link" aria-label="Voltar para a página principal">
                <span aria-hidden="true">←</span>
                <span class="home-floating__text">Página principal</span>
            </a>
        </div>

        @if (($modoPublico ?? 'fieis') === 'musicos')
            <div class="public-drawer-backdrop" data-public-chords-backdrop hidden></div>
            <aside class="public-chord-drawer" data-public-chords-drawer hidden aria-label="Acordes do repertorio">
                <div class="history-top">
                    <div>
                        <p class="history-section-title">Acordes</p>
                        <h2 class="card-title" style="color:#211713;font-size:28px;">Dicionário rápido</h2>
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
