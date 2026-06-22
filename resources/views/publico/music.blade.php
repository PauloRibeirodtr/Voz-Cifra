<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz &amp; Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    @vite(['resources/css/publico/music.css', 'resources/js/publico/music.js'])
</head>
<body data-contrast="high" data-public-mode="musicos">
    @php
        $celebracaoSelecionadaParam = (int) ($celebracaoSelecionadaIdParam ?? 0);
        $celebracaoFoiEscolhida = $celebracaoSelecionadaParam > 0 && $missaPublica;
        $exibirCelebracao = (bool) $missaPublica;
        $cidadeEstadoLinha = trim(($igreja->cidade ?? '') . ' - ' . ($igreja->estado ?? ''), ' -');
        $historicoBaseUrl = route('igrejas.public.musicos.show', ['slug' => $igreja->slug]);
        $programacaoMusico = collect($historicoUltimasMissas ?? [])
            ->take(5)
            ->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ]))
            ->merge(collect($missasHoje ?? [])->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ])))
            ->merge(collect($proximasMissas ?? [])->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ])))
            ->unique('id')
            ->values();
        $historicoParaBusca = $programacaoMusico
            ->merge(collect($historicoSugestoes ?? []))
            ->unique('id')
            ->values();
        $programacaoFocoId = (int) (
            collect($missasHoje ?? [])->first()['id']
            ?? collect($proximasMissas ?? [])->first()['id']
            ?? $programacaoMusico->first()['id']
            ?? 0
        );
    @endphp

    <main class="page">
        <div
            hidden
            data-public-status-sync
            data-state="{{ $estadoCelebracao }}"
            data-status-url="{{ route('igrejas.public.musicos.status', ['slug' => $igreja->slug, 'celebracao' => $celebracaoSelecionadaParam > 0 ? $celebracaoSelecionadaParam : null]) }}"
            @if($countdownIso) data-target="{{ $countdownIso }}" @endif
        ></div>

        <div class="shell">
            @unless ($celebracaoFoiEscolhida)
                <section class="section hero">
                    <a href="{{ route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) }}" class="brand">
                        <img
                            src="{{ $igreja->imagemUrl() }}"
                            alt="Logo {{ $igreja->nome }}"
                            class="{{ $igreja->temImagemPersonalizada() ? '' : 'brand-image--fallback' }}"
                            width="124"
                            height="124"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                        <div>
                            <p class="brand-kicker">Músicos</p>
                            <h1 class="hero-church">{{ $igreja->nome }}</h1>
                            @if ($cidadeEstadoLinha !== '')
                                <p class="hero-city">{{ $cidadeEstadoLinha }}</p>
                            @endif
                        </div>
                    </a>
                </section>

                <section class="section">
                    <div class="section-header">
                        <p class="section-kicker">Repertório</p>
                        <h2 class="section-title">Celebrações publicadas</h2>
                    </div>

                    <form method="GET" action="{{ $historicoBaseUrl }}" class="public-history-quick" data-history-form data-history-form-top data-history-base-url="{{ $historicoBaseUrl }}" aria-label="Buscar repertório">
                        <button type="submit" class="public-history-quick__button" aria-label="Buscar repertório">
                            <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                            </svg>
                        </button>
                        <input type="text" name="historico" value="{{ $historicoBusca }}" placeholder="Buscar celebração, data, mês ou dia" autocomplete="off" data-history-input data-history-input-top>
                    </form>
                    <div class="history-live-results public-history-quick-results" data-history-live-results data-history-live-results-top hidden></div>
                    <div class="history-empty public-history-quick-empty" data-history-live-empty data-history-live-empty-top hidden>Nenhum resultado encontrado.</div>

                    @if (($historicoBusca ?? '') !== '')
                        @if ($historicoMissas->isNotEmpty())
                            <div class="schedule-shell">
                                <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver repertórios anteriores">‹</button>
                                <div class="cards schedule-carousel" data-schedule-carousel>
                                    @foreach ($historicoMissas as $missaHistorica)
                                        <a href="{{ route('igrejas.public.musicos.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica" class="card card-link">
                                            <div class="card-main">
                                                <div class="schedule-date-row">
                                                    <span class="schedule-date">{{ $missaHistorica['data'] }}</span>
                                                    <span class="card-hour">{{ $missaHistorica['horario'] }}</span>
                                                </div>
                                                <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                                <p class="card-meta">{{ $missaHistorica['dia_semana'] }} @if (!empty($missaHistorica['tempo_liturgico'])) • {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                                <span class="card-action">Abrir repertório</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                                <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximos repertórios">›</button>
                            </div>
                        @else
                            <div class="empty-state empty-state--compact">
                                <h3 class="empty-title empty-title--small">Nenhum repertório encontrado.</h3>
                            </div>
                        @endif
                    @elseif ($programacaoMusico->isNotEmpty())
                        <div class="schedule-shell">
                            <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver repertórios anteriores">‹</button>
                            <div class="cards schedule-carousel" data-schedule-carousel>
                                @foreach ($programacaoMusico as $missaMusico)
                                    @php($missaMusicoSelecionada = (int) $missaMusico['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                    <a
                                        href="{{ $missaMusico['url'] }}"
                                        class="card card-link"
                                        data-selected="{{ $missaMusicoSelecionada ? 'true' : 'false' }}"
                                        @if ((int) $missaMusico['id'] === $programacaoFocoId) data-schedule-focus @endif
                                    >
                                        <div class="card-main">
                                            <div class="schedule-date-row">
                                                <span class="schedule-date">{{ $missaMusico['data'] }}</span>
                                                <span class="card-hour">{{ $missaMusico['horario'] }}</span>
                                                @if (!empty($missaMusico['em_andamento']))
                                                    <span class="badge">Agora</span>
                                                @endif
                                            </div>
                                            <h3 class="card-title">{{ $missaMusico['titulo'] }}</h3>
                                            <p class="card-meta">{{ $missaMusico['dia_semana'] }} @if (!empty($missaMusico['tempo_liturgico'])) • {{ $missaMusico['tempo_liturgico'] }} @endif</p>
                                            <span class="card-action">Abrir repertório</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximos repertórios">›</button>
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Ainda não há missas publicadas para ensaio.</h3>
                            <p class="empty-copy">Este link será atualizado quando a equipe publicar um repertório.</p>
                            <p class="empty-copy">Acesso público em modo somente leitura.</p>
                        </div>
                    @endif
                </section>
            @endunless

            @if ($exibirCelebracao)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section celebration-section" id="celebracao-publica" data-celebration-section>
                    <div class="celebration-header">
                        <div class="celebration-header__left">
                            <a href="{{ route('igrejas.public.musicos.show', ['slug' => $igreja->slug]) }}" class="public-tool-button public-tool-button--ghost celebration-back" aria-label="Voltar para repertórios">←</a>
                            <div>
                                <p class="section-kicker">Repertório</p>
                                <h2 class="celebration-title">{{ $missaPublica->titulo }}</h2>
                                <p class="celebration-meta-text">
                                    {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                </p>
                            </div>
                        </div>
                        <details class="reading-settings">
                            <summary aria-label="Ajustes da leitura">
                                <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82A1.65 1.65 0 0 0 3 12.6H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.98.16 1.82.84 2.09 1.79A2 2 0 0 1 21 12.6h0a1.65 1.65 0 0 0-.33 1.82z"></path>
                                </svg>
                            </summary>
                            <div class="reading-settings__panel">
                                <button type="button" class="public-tool-button" data-public-active-song-font="-1">A- Texto</button>
                                <button type="button" class="public-tool-button" data-public-active-song-font="1">A+ Texto</button>
                            </div>
                        </details>
                    </div>

                    @if ($itensPublicos->isNotEmpty())
                        <div class="celebration-shell">
                            @if ($itensPublicos->count() > 1)
                                <div class="public-swipe-hint" aria-hidden="true">Deslize para o lado para ver o próximo canto</div>
                                <div class="celebration-nav" data-celebration-nav>
                                    <button type="button" aria-label="Canto anterior" data-celebration-prev>‹</button>
                                    <button type="button" aria-label="Próximo canto" data-celebration-next>›</button>
                                </div>
                            @endif

                            <div class="celebration-list" data-celebration-carousel>
                                @foreach ($itensPublicos as $item)
                                    @php($itemIndicePublico = $loop->index)
                                    <article class="celebration-item" data-public-song data-public-song-id="{{ $itemIndicePublico }}">
                                        <div class="celebration-meta">
                                            <span class="badge">Ordem {{ $item['ordem'] }}</span>
                                            @if (!empty($item['momento']))
                                                <span class="badge">{{ $item['momento'] }}</span>
                                            @endif
                                            @if (!empty($item['tom']))
                                                <span class="badge">Tom {{ $item['tom'] }}</span>
                                            @endif
                                            @if (empty($item['tem_versao_vinculada']))
                                                <span class="badge badge--warning">Sem cifra</span>
                                            @endif
                                        </div>
                                        <h3 class="card-title">{{ $item['titulo'] }}</h3>
                                        @if (empty($item['tem_versao_vinculada']))
                                            <p class="public-version-warning">Esta música ainda não tem cifra vinculada. Exibindo somente a letra.</p>
                                        @endif
                                        <div class="lyrics" data-public-musician-lyrics data-public-song-lyrics data-base-tom="{{ $item['tom'] ?? '' }}" data-lyrics="{{ e($item['letra_publica'] ?? '') }}">{!! $item['letra_publica_html'] ?? nl2br(e($item['letra_publica'] ?? ''), false) !!}</div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Repertório ainda não disponível.</h3>
                        </div>
                    @endif
                </section>
            @endif

            <script type="application/json" data-history-items>@json($historicoParaBusca, JSON_UNESCAPED_UNICODE)</script>
        </div>

        @unless ($celebracaoFoiEscolhida)
            <div class="home-floating">
                <a href="{{ route('root') }}" class="home-floating__link" aria-label="Voltar para a página principal">
                    <span aria-hidden="true">←</span>
                    <span class="home-floating__text">Página principal</span>
                </a>
            </div>
        @endunless

        @if ($exibirCelebracao)
            <div class="public-chord-tooltip" data-public-chord-tooltip hidden>
                <p class="public-chord-tooltip__name" data-public-chord-tooltip-name></p>
                <div data-public-chord-tooltip-diagram></div>
            </div>

            <div class="public-scroll-dock" data-public-scroll-dock>
                <button type="button" class="public-tool-button public-tool-button--primary" data-public-auto-scroll>Rolagem</button>
                <label for="public_scroll_speed" class="public-scroll-dock__speed">Velocidade</label>
                <input id="public_scroll_speed" type="range" min="1" max="5" value="1" step="1" data-public-scroll-speed aria-label="Velocidade da auto rolagem">
            </div>
        @endif
    </main>

    @if ($exibirCelebracao)
        @include('partials.chord-transposer-script')
        <script type="application/json" data-public-chord-library>@json($bibliotecaAcordes ?? [], JSON_UNESCAPED_UNICODE)</script>
    @endif
</body>
</html>
