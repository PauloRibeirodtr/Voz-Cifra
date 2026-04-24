<x-publico.layouts.app
    title="Voz & Cifra | Missas e celebrações"
    description="Acompanhe e organize as celebrações da sua comunidade com uma página pública elegante, responsiva e centrada na experiência da missa."
    forced-contrast="high"
>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('root') }}#inicio" class="brand">
                <img src="{{ asset('logo/final.png') }}" alt="Logo Voz & Cifra">
                <div>
                    <p class="brand__eyebrow">Voz &amp; Cifra</p>
                    <p class="brand__name">Missas e celebrações da comunidade</p>
                </div>
            </a>

            <div class="site-header__actions">
                <button type="button" class="nav-toggle" aria-expanded="false" data-nav-toggle aria-label="Abrir menu">
                    <span></span>
                </button>

                <nav class="site-nav" data-site-nav data-open="false">
                    <a href="{{ route('root') }}#inicio">Início</a>
                    <a href="{{ route('root') }}#destaque">Próxima missa</a>
                    <a href="{{ route('root') }}#missas">Missas</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('login') }}" class="site-nav__login">Entrar</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section id="inicio" class="hero">
            <div class="container">
                <div class="hero__panel">
                    <div class="hero__image" style="background-image: url('{{ $heroImage }}');"></div>
                    <div class="hero__overlay"></div>

                    <div class="hero__content">
                        <div>
                            <span class="eyebrow">Missas da comunidade</span>
                            <h1 class="hero__title">Encontre a próxima missa da sua comunidade</h1>
                            <p class="hero__lead">
                                Veja os horários, abra a celebração e acompanhe tudo com clareza, especialmente no celular.
                            </p>

                            <div class="hero__actions">
                                <x-public.button href="#destaque">Ver próxima missa</x-public.button>
                                <x-public.button :href="route('login')" variant="secondary">Entrar</x-public.button>
                            </div>
                        </div>

                        <div class="hero__foot">
                            <div class="hero-stat">
                                <span class="hero-stat__label">Acesso rápido</span>
                                <span class="hero-stat__value">Missas, horários e páginas públicas em um só lugar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="missas" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Buscar missas</span>
                    <h2 class="section__title">Escolha a missa que você quer acompanhar</h2>
                    <p class="section__lead">
                        Filtre por data, igreja, tempo litúrgico ou tipo de celebração.
                    </p>
                </div>

                <form method="GET" action="{{ route('root') }}" class="filter-card">
                    <div class="filters-grid">
                        <div class="field">
                            <label for="data">Data</label>
                            <input id="data" name="data" type="date" value="{{ $filtros['data'] }}">
                        </div>

                        <div class="field">
                            <label for="igreja">Igreja</label>
                            <select id="igreja" name="igreja">
                                <option value="">Todas as igrejas</option>
                                @foreach ($igrejas as $igreja)
                                    <option value="{{ $igreja->id }}" @selected($filtros['igreja'] === (string) $igreja->id)>
                                        {{ $igreja->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="tempo_liturgico">Tempo litúrgico</label>
                            <select id="tempo_liturgico" name="tempo_liturgico">
                                <option value="">Todos os tempos</option>
                                @foreach ($temposLiturgicos as $tempo)
                                    <option value="{{ $tempo->id }}" @selected($filtros['tempo_liturgico'] === (string) $tempo->id)>
                                        {{ $tempo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="tipo">Tipo de celebração</label>
                            <select id="tipo" name="tipo">
                                <option value="">Todos os tipos</option>
                                @foreach ($tiposCelebracao as $tipo)
                                    <option value="{{ $tipo }}" @selected($filtros['tipo'] === $tipo)>
                                        {{ $tipo }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <x-public.button type="submit">Aplicar filtros</x-public.button>
                        <x-public.button :href="route('root')" variant="ghost">Limpar consulta</x-public.button>
                    </div>
                </form>
            </div>
        </section>

        <section id="destaque" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Próxima missa</span>
                    <h2 class="section__title">A celebração principal para abrir agora</h2>
                    <p class="section__lead">
                        Aqui aparece primeiro a opção mais importante do momento.
                    </p>
                </div>

                @if ($missaEmDestaque)
                    <article class="summary-card" style="padding:1.5rem;display:grid;gap:1rem;">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
                            <div>
                                <p style="margin:0;color:#d2aa66;text-transform:uppercase;letter-spacing:.12em;font-size:.78rem;font-weight:800;">{{ $missaEmDestaque['igreja'] }}</p>
                                @if (!empty($missaEmDestaque['igreja_localidade']))
                                    <p style="margin:.35rem 0 0;color:#d8c7b4;font-size:1rem;line-height:1.7;">{{ $missaEmDestaque['igreja_localidade'] }}</p>
                                @endif
                                <h3 style="margin:.65rem 0 0;font-family:Georgia,'Times New Roman',serif;font-size:clamp(2rem,5vw,3rem);line-height:1.08;">{{ $missaEmDestaque['titulo'] }}</h3>
                            </div>

                            <x-public.status-badge :status="$missaEmDestaque['status']" />
                        </div>

                        <p style="margin:0;color:#f0e4d4;font-size:1.12rem;line-height:1.9;max-width:56rem;">{{ $missaEmDestaque['resumo'] }}</p>

                        <div style="display:grid;gap:.8rem;color:#ccbba7;line-height:1.8;">
                            <div><strong style="color:#f5efe6;">Data e horário:</strong> {{ $missaEmDestaque['data_formatada'] }} • {{ $missaEmDestaque['horario'] }}</div>
                            <div><strong style="color:#f5efe6;">Tempo litúrgico:</strong> {{ $missaEmDestaque['tempo_liturgico'] }}</div>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
                            <span style="color:#d2aa66;font-size:1rem;font-weight:700;">Abrir missa pública agora</span>
                            <x-public.button :href="$missaEmDestaque['url']">Ver Missa</x-public.button>
                        </div>
                    </article>
                @else
                    <article class="summary-card">
                        <h3 class="summary-card__title">Nenhuma missa encontrada</h3>
                        <p class="summary-card__description">
                            Não encontramos uma missa para estes filtros no momento.
                        </p>
                    </article>
                @endif
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Outras missas</span>
                    <h2 class="section__title">Mais opções para você consultar</h2>
                    <p class="section__lead">
                        Se a missa em destaque não for a que você procura, veja as outras celebrações abaixo.
                    </p>
                </div>

                <div class="cards-grid">
                    @forelse ($missasRecentes as $missa)
                        <x-public.missa-card :missa="$missa" />
                    @empty
                        <article class="summary-card" style="grid-column: 1 / -1;">
                            <h3 class="summary-card__title">Nenhuma outra missa disponivel</h3>
                            <p class="summary-card__description">
                                Quando novas celebrações forem publicadas, elas aparecerão aqui.
                            </p>
                        </article>
                    @endforelse
                </div>
            </div>
        </section>

        <section id="igrejas" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Igrejas</span>
                    <h2 class="section__title">Páginas públicas das comunidades</h2>
                    <p class="section__lead">
                        Abra a página da sua igreja para ver as celebrações publicadas.
                    </p>
                </div>

                <div class="church-grid">
                    @foreach ($igrejasDestaque as $igreja)
                        <article class="church-card">
                            <h3 class="church-card__title">{{ $igreja['nome'] }}</h3>
                            <p class="church-card__meta">{{ $igreja['localidade'] !== '' ? $igreja['localidade'] : 'Localidade em configuração' }}</p>
                            <p class="church-card__meta"><strong style="color:#f5efe6;">Próxima celebração:</strong> {{ $igreja['proxima_missa'] }}</p>
                            <p class="church-card__meta">{{ $igreja['proxima_data'] }}</p>
                            <div style="margin-top:1rem;">
                                <x-public.button :href="$igreja['slug'] ? route('igrejas.public.show', ['slug' => $igreja['slug']]) : route('login')" variant="secondary" style="width:100%;">
                                    Ver igreja
                                </x-public.button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div>
                <h3 class="site-footer__title">Voz &amp; Cifra</h3>
                <p class="site-footer__text">
                    Plataforma pública pensada para acompanhar missas com leitura clara, identidade reverente e acesso simples.
                </p>
            </div>

            <div>
                <h3 class="site-footer__title">Navegação</h3>
                <div class="site-footer__links">
                    <a href="#inicio">Início</a>
                    <a href="#destaque">Próxima missa</a>
                    <a href="#missas">Missas</a>
                    <a href="#igrejas">Igrejas</a>
                </div>
            </div>

            <div>
                <h3 class="site-footer__title">Acesso</h3>
                <p class="site-footer__text">
                    Acesso interno e informações do projeto.<br>
                    <a href="{{ route('login') }}" style="color:#d2aa66;">Entrar no sistema</a>
                    <br>
                    <a href="{{ route('developers') }}" style="color:#d2aa66;">Desenvolvedores</a>
                </p>
            </div>
        </div>
        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Voz &amp; Cifra. Um acesso simples para consultar missas e páginas públicas das comunidades.</p>
        </div>
    </footer>
</x-publico.layouts.app>
