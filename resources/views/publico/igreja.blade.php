@php
    $cidadeEstadoMeta = trim(($igreja->cidade ?? '') . ' - ' . ($igreja->estado ?? ''), ' -');
    $celebracaoMetaId = (int) ($celebracaoSelecionadaIdParam ?? 0);
    $canonicalPublico = $celebracaoMetaId > 0
        ? route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $celebracaoMetaId])
        : route('igrejas.public.show', ['slug' => $igreja->slug]);
    $tituloPublico = $missaPublica
        ? $missaPublica->titulo . ' - ' . $igreja->nome . ' | Voz & Cifra'
        : $igreja->nome . ' | Missas e repertórios | Voz & Cifra';
    $descricaoPublica = trim('Acompanhe missas, celebrações e repertórios publicados por ' . $igreja->nome . ($cidadeEstadoMeta !== '' ? ' em ' . $cidadeEstadoMeta : '') . '.');
    $imagemPublica = $igreja->imagemUrl();
    $dadosEstruturados = [
        '@context' => 'https://schema.org',
        '@type' => 'Place',
        'name' => $igreja->nome,
        'url' => route('igrejas.public.show', ['slug' => $igreja->slug]),
        'image' => $imagemPublica,
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => trim(($igreja->endereco ?? '') . ($igreja->numero ? ', ' . $igreja->numero : ''), ' ,'),
            'addressLocality' => $igreja->cidade,
            'addressRegion' => $igreja->estado,
            'addressCountry' => 'BR',
        ]),
        'telephone' => $igreja->telefone_secretaria,
    ];

    if ($missaPublica) {
        $dadosEstruturados = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $missaPublica->titulo,
            'url' => $canonicalPublico,
            'startDate' => $missaPublica->dataHoraInicio('America/Cuiaba')->toIso8601String(),
            'endDate' => $missaPublica->dataHoraFim('America/Cuiaba')->toIso8601String(),
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'eventStatus' => 'https://schema.org/EventScheduled',
            'location' => [
                '@type' => 'Place',
                'name' => $igreja->nome,
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => trim(($igreja->endereco ?? '') . ($igreja->numero ? ', ' . $igreja->numero : ''), ' ,'),
                    'addressLocality' => $igreja->cidade,
                    'addressRegion' => $igreja->estado,
                    'addressCountry' => 'BR',
                ]),
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => $igreja->nome,
                'url' => route('igrejas.public.show', ['slug' => $igreja->slug]),
            ],
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tituloPublico }}</title>
    <meta name="description" content="{{ $descricaoPublica }}">
    <link rel="canonical" href="{{ $canonicalPublico }}">
    <meta property="og:type" content="{{ $missaPublica ? 'event' : 'website' }}">
    <meta property="og:title" content="{{ $tituloPublico }}">
    <meta property="og:description" content="{{ $descricaoPublica }}">
    <meta property="og:url" content="{{ $canonicalPublico }}">
    <meta property="og:image" content="{{ $imagemPublica }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $tituloPublico }}">
    <meta name="twitter:description" content="{{ $descricaoPublica }}">
    <meta name="twitter:image" content="{{ $imagemPublica }}">
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#4a2b22">
    <script type="application/ld+json">
        {!! json_encode($dadosEstruturados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
    @vite(['resources/css/publico/igreja.css', 'resources/js/publico/igreja.js'])
</head>
<body data-contrast="high" data-public-mode="fieis">
    @php
        $celebracaoSelecionadaParam = (int) ($celebracaoSelecionadaIdParam ?? 0);
        $celebracaoFoiEscolhida = $celebracaoSelecionadaParam > 0 && $missaPublica;
        $exibirCelebracao = (bool) $missaPublica;
        $cidadeEstadoLinha = trim(($igreja->cidade ?? '') . ' - ' . ($igreja->estado ?? ''), ' -');

        $programacaoPublica = collect($historicoUltimasMissas ?? [])
            ->take(5)
            ->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ]))
            ->merge(collect($missasHoje ?? [])->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ])))
            ->merge(collect($proximasMissas ?? [])->map(fn ($missa) => array_merge($missa, [
                'url' => route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missa['id']]) . '#celebracao-publica',
            ])))
            ->unique('id')
            ->values();

        $historicoParaBusca = $programacaoPublica
            ->merge(collect($historicoSugestoes ?? []))
            ->unique('id')
            ->values();

        $programacaoFocoId = (int) (
            collect($missasHoje ?? [])->first()['id']
            ?? collect($proximasMissas ?? [])->first()['id']
            ?? $programacaoPublica->first()['id']
            ?? 0
        );
        $urlCompartilhamento = $missaPublica
            ? route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaPublica->id]) . '#celebracao-publica'
            : route('igrejas.public.show', ['slug' => $igreja->slug]);
    @endphp

    <main class="page">
        <div
            hidden
            data-public-status-sync
            data-state="{{ $estadoCelebracao }}"
            data-status-url="{{ route('igrejas.public.status', ['slug' => $igreja->slug, 'celebracao' => $celebracaoSelecionadaParam > 0 ? $celebracaoSelecionadaParam : null]) }}"
            @if($countdownIso) data-target="{{ $countdownIso }}" @endif
        ></div>

        <div class="shell">
            @unless ($celebracaoFoiEscolhida)
                <section class="section hero">
                    <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}" class="brand">
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
                            <p class="brand-kicker">Igreja</p>
                            <h1 class="hero-church">{{ $igreja->nome }}</h1>
                            @if ($cidadeEstadoLinha !== '')
                                <p class="hero-city">{{ $cidadeEstadoLinha }}</p>
                            @endif
                        </div>
                    </a>
                </section>

                <details class="section program-section" id="programacao-publica" open>
                    <summary>
                        <span class="section-title">Programação</span>
                    </summary>

                    <div class="program-body">
                        <h2 class="section-title">Celebrações publicadas</h2>
                        @php($historicoBaseUrlTopo = route('igrejas.public.show', ['slug' => $igreja->slug]))
                        <form method="GET" action="{{ $historicoBaseUrlTopo }}" class="public-history-quick" data-history-form-top data-history-base-url="{{ $historicoBaseUrlTopo }}" aria-label="Buscar celebração">
                            <button type="submit" class="public-history-quick__button" aria-label="Buscar celebração">
                                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                                    <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                                </svg>
                            </button>
                            <input type="text" name="historico" value="{{ $historicoBusca ?? '' }}" placeholder="Buscar missa, data ou mês" autocomplete="off" data-history-input-top>
                        </form>

                        <div class="history-live-results public-history-quick-results" data-history-live-results-top hidden></div>
                        <div class="history-empty public-history-quick-empty" data-history-live-empty-top hidden>Nenhum resultado encontrado.</div>

                        @if (collect($missasHoje ?? [])->isEmpty())
                            <div class="empty-state empty-state--compact">
                                <h3 class="empty-title empty-title--small">Ainda não há missas para hoje.</h3>
                                <a href="#programacao-publica" class="public-tool-button public-tool-button--ghost">Consultar histórico</a>
                            </div>
                        @endif

                        @if (($historicoBusca ?? '') !== '')
                            @if ($historicoMissas->isNotEmpty())
                                <div class="schedule-shell">
                                    <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver celebrações anteriores">‹</button>
                                    <div class="cards schedule-carousel" data-schedule-carousel>
                                        @foreach ($historicoMissas as $missaHistorica)
                                            @php($selecionada = (int) $missaHistorica['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                            <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug, 'celebracao' => $missaHistorica['id']]) }}#celebracao-publica" class="card card-link" data-selected="{{ $selecionada ? 'true' : 'false' }}">
                                                <div class="schedule-date-row">
                                                    <span class="schedule-date">{{ $missaHistorica['data'] }}</span>
                                                    <span class="card-hour">{{ $missaHistorica['horario'] }}</span>
                                                </div>
                                                <h3 class="card-title">{{ $missaHistorica['titulo'] }}</h3>
                                                <p class="card-meta">{{ $missaHistorica['dia_semana'] }} @if (!empty($missaHistorica['tempo_liturgico'])) • {{ $missaHistorica['tempo_liturgico'] }} @endif</p>
                                                <span class="card-action">Abrir celebração</span>
                                            </a>
                                        @endforeach
                                    </div>
                                    <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximas celebrações">›</button>
                                </div>
                            @else
                                <div class="empty-state empty-state--compact">
                                    <h3 class="empty-title empty-title--small">Nenhuma celebração encontrada.</h3>
                                    <p class="empty-copy">Tente outra data, título ou mês.</p>
                                </div>
                            @endif
                        @elseif ($programacaoPublica->isNotEmpty())
                            <div class="schedule-shell">
                                <button type="button" class="schedule-nav schedule-nav--prev" data-schedule-prev aria-label="Ver celebrações anteriores">‹</button>
                                <div class="cards schedule-carousel" data-schedule-carousel>
                                    @foreach ($programacaoPublica as $missaProgramada)
                                        @php($selecionada = (int) $missaProgramada['id'] === (int) ($celebracaoSelecionadaId ?? 0))
                                        <a href="{{ $missaProgramada['url'] }}" class="card card-link" data-selected="{{ $selecionada ? 'true' : 'false' }}" @if ((int) $missaProgramada['id'] === $programacaoFocoId) data-schedule-focus @endif>
                                            <div class="schedule-date-row">
                                                <span class="schedule-date">{{ $missaProgramada['data'] }}</span>
                                                <span class="card-hour">{{ $missaProgramada['horario'] }}</span>
                                            </div>
                                            <h3 class="card-title">{{ $missaProgramada['titulo'] }}</h3>
                                            <p class="card-meta">{{ $missaProgramada['dia_semana'] }} @if (!empty($missaProgramada['tempo_liturgico'])) • {{ $missaProgramada['tempo_liturgico'] }} @endif</p>
                                            <span class="card-action">Abrir celebração</span>
                                        </a>
                                    @endforeach
                                </div>
                                <button type="button" class="schedule-nav schedule-nav--next" data-schedule-next aria-label="Ver próximas celebrações">›</button>
                            </div>
                        @else
                            <div class="empty-state empty-state--compact">
                                <h3 class="empty-title empty-title--small">Ainda não há missas publicadas.</h3>
                                <p class="empty-copy">Quando a igreja publicar uma celebração, ela aparecerá aqui.</p>
                            </div>
                        @endif
                    </div>
                </details>
            @endunless

            @if ($exibirCelebracao)
                @php($itensPublicos = collect($missaPublica->itens_publicos ?? []))

                <section class="section celebration-section" id="celebracao-publica" data-celebration-section>
                    <div class="celebration-header">
                        <div class="celebration-header__left">
                            <a href="{{ route('igrejas.public.show', ['slug' => $igreja->slug]) }}#programacao-publica" class="public-tool-button public-tool-button--ghost celebration-back" aria-label="Voltar para a programação">←</a>
                            <div>
                                <p class="section-kicker">Celebração</p>
                                <h2 class="celebration-title">{{ $missaPublica->titulo }}</h2>
                                <p class="celebration-meta-text">
                                    {{ $missaPublica->data_missa->format('d/m/Y') }} • {{ substr((string) $missaPublica->hora_inicio, 0, 5) }}
                                </p>
                                @include('publico.partials.share-tools', [
                                    'url' => $urlCompartilhamento,
                                    'title' => $missaPublica->titulo . ' | ' . $igreja->nome,
                                    'text' => 'Acompanhe a celebração ' . $missaPublica->titulo . ' da ' . $igreja->nome . ' no Voz & Cifra:',
                                ])
                            </div>
                        </div>
                        <div class="celebration-header__right">
                            <details class="reading-settings">
                                <summary aria-label="Ajustes de leitura">
                                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"></path>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82A1.65 1.65 0 0 0 3 12.6H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.09a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9c.98.16 1.82.84 2.09 1.79A2 2 0 0 1 21 12.6h0a1.65 1.65 0 0 0-.33 1.82z"></path>
                                    </svg>
                                </summary>
                                <div class="reading-settings__panel">
                                    <button type="button" class="public-tool-button" data-public-plain-font="-1">A- Letra</button>
                                    <button type="button" class="public-tool-button" data-public-plain-font="1">A+ Letra</button>
                                </div>
                            </details>
                        </div>
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
                                    <article class="celebration-item">
                                        <div class="celebration-meta">
                                            <span class="badge">Ordem {{ $item['ordem'] }}</span>
                                            @if (!empty($item['momento']))
                                                <span class="badge">{{ $item['momento'] }}</span>
                                            @endif
                                        </div>
                                        <h3 class="card-title">{{ $item['titulo'] }}</h3>
                                        <div class="lyrics">{!! $item['letra_publica'] !== '' ? ($item['letra_publica_html'] ?? nl2br(e($item['letra_publica']), false)) : 'A letra deste canto ainda não foi preparada para exibição pública.' !!}</div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <h3 class="empty-title">Celebração ainda sem repertório público.</h3>
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
    </main>
</body>
</html>
