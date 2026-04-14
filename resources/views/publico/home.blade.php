<x-publico.layouts.app
    title="Voz & Cifra | Missas e celebracoes"
    description="Acompanhe e organize as celebracoes da sua comunidade com uma pagina publica elegante, responsiva e centrada na experiencia da missa."
>
    <header class="site-header">
        <div class="container site-header__inner">
            <a href="{{ route('root') }}#inicio" class="brand">
                <img src="{{ asset('logo/final.png') }}" alt="Logo Voz & Cifra">
                <div>
                    <p class="brand__eyebrow">Voz &amp; Cifra</p>
                    <p class="brand__name">Missas e celebracoes da comunidade</p>
                </div>
            </a>

            <div class="site-header__actions">
                <button type="button" class="toolbar-button" data-contrast-toggle aria-pressed="false">
                    <span>Aa</span>
                    <span data-contrast-label>Contraste alto</span>
                </button>

                <button type="button" class="nav-toggle" aria-expanded="false" data-nav-toggle aria-label="Abrir menu">
                    <span></span>
                </button>

                <nav class="site-nav" data-site-nav data-open="false">
                    <a href="{{ route('root') }}#inicio">Inicio</a>
                    <a href="{{ route('root') }}#missas">Missas</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('root') }}#historico">Historico</a>
                    <a href="{{ route('developers') }}">Desenvolvedores</a>
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
                            <span class="eyebrow">Calendario liturgico e celebracoes publicas</span>
                            <h1 class="hero__title">Acompanhe e organize as celebracoes da sua comunidade</h1>
                            <p class="hero__lead">
                                Organize missas, consulte repertorios e acompanhe o calendario liturgico de forma simples e reverente.
                            </p>

                            <div class="hero__actions">
                                <x-public.button href="#missas">Ver Missas</x-public.button>
                                <x-public.button :href="route('login')" variant="secondary">Entrar</x-public.button>
                            </div>
                        </div>

                        <div class="hero__foot">
                            <div class="hero-stat">
                                <span class="hero-stat__label">Projeto em servico</span>
                                <span class="hero-stat__value">Comunidades, missas e ministerio musical em um so lugar</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Resumo da comunidade</span>
                    <h2 class="section__title">Uma pagina publica mais simples</h2>
                    <p class="section__lead">
                        A proposta desta home e acolher, orientar e facilitar o acesso as celebracoes publicas, sem excesso de numeros ou blocos tecnicos na tela.
                    </p>
                </div>

                <article class="summary-card">
                    <h3 class="summary-card__title">Pensada para a comunidade</h3>
                    <p class="summary-card__description">
                        Aqui o foco esta em encontrar a missa certa, abrir o link publico da igreja e acompanhar a celebracao com tranquilidade, especialmente no celular.
                    </p>
                </article>
            </div>
        </section>

        <section id="missas" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Consulta publica</span>
                    <h2 class="section__title">Encontre a celebracao certa para acompanhar</h2>
                    <p class="section__lead">
                        A pagina agora destaca primeiro a missa mais relevante do momento e deixa as demais como apoio, para a busca ficar mais natural e menos carregada.
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
                            <label for="tempo_liturgico">Tempo liturgico</label>
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
                            <label for="tipo">Tipo de celebracao</label>
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

        <section class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Missa em destaque</span>
                    <h2 class="section__title">A celebracao mais importante para abrir agora</h2>
                    <p class="section__lead">
                        Em vez de mostrar tudo com o mesmo peso, a missa mais relevante do momento aparece em destaque logo acima. Isso ajuda quem quer abrir rapido sem precisar interpretar muitos cards.
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
                            <div><strong style="color:#f5efe6;">Data e horario:</strong> {{ $missaEmDestaque['data_formatada'] }} • {{ $missaEmDestaque['horario'] }}</div>
                            <div><strong style="color:#f5efe6;">Tempo liturgico:</strong> {{ $missaEmDestaque['tempo_liturgico'] }}</div>
                        </div>

                        <div style="display:flex;flex-wrap:wrap;gap:1rem;align-items:center;justify-content:space-between;">
                            <span style="color:#d2aa66;font-size:1rem;font-weight:700;">Abrir missa publica agora</span>
                            <x-public.button :href="$missaEmDestaque['url']">Ver Missa</x-public.button>
                        </div>
                    </article>
                @else
                    <article class="summary-card">
                        <h3 class="summary-card__title">Nenhuma missa encontrada</h3>
                        <p class="summary-card__description">
                            Ainda nao existem celebracoes com estes filtros. Quando os dados reais forem conectados, esta area destacara automaticamente a missa mais relevante.
                        </p>
                    </article>
                @endif
            </div>
        </section>

        <section class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Outras missas</span>
                    <h2 class="section__title">Mais celebracoes publicas disponiveis</h2>
                    <p class="section__lead">
                        Aqui ficam as demais missas para consulta, com informacoes curtas e botao direto para abertura do link publico.
                    </p>
                </div>

                <div class="cards-grid">
                    @forelse ($missasRecentes as $missa)
                        <x-public.missa-card :missa="$missa" />
                    @empty
                        <article class="summary-card" style="grid-column: 1 / -1;">
                            <h3 class="summary-card__title">Nenhuma outra missa disponivel</h3>
                            <p class="summary-card__description">
                                A missa em destaque ja representa a melhor opcao do momento. Outras celebracoes aparecerao aqui conforme forem publicadas.
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
                    <h2 class="section__title">Comunidades com link publico pronto para compartilhar</h2>
                    <p class="section__lead">
                        Uma vitrine simples para facilitar o acesso dos musicos, ministerios e fieis sem exigir conta para consultar a missa publicada.
                    </p>
                </div>

                <div class="church-grid">
                    @foreach ($igrejasDestaque as $igreja)
                        <article class="church-card">
                            <h3 class="church-card__title">{{ $igreja['nome'] }}</h3>
                            <p class="church-card__meta">{{ $igreja['localidade'] !== '' ? $igreja['localidade'] : 'Localidade em configuracao' }}</p>
                            <p class="church-card__meta"><strong style="color:#f5efe6;">Proxima celebracao:</strong> {{ $igreja['proxima_missa'] }}</p>
                            <p class="church-card__meta">{{ $igreja['proxima_data'] }}</p>
                            <div style="margin-top:1rem;">
                                <x-public.button :href="$igreja['slug'] ? route('igrejas.public.show', ['slug' => $igreja['slug']]) : route('login')" variant="secondary" style="width:100%;">
                                    Ver Missa
                                </x-public.button>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="historico" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Historico</span>
                    <h2 class="section__title">Celebracoes passadas organizadas para consulta futura</h2>
                    <p class="section__lead">
                        O historico ajuda a manter memoria das celebracoes e prepara o sistema para consultas futuras com dados reais, sem poluir a home com detalhes tecnicos.
                    </p>
                </div>

                <article class="summary-card">
                    <h3 class="summary-card__title">Memoria da comunidade</h3>
                    <p class="summary-card__description">
                        O historico permanece como base para consulta futura das celebracoes, preservando a memoria liturgica e a organizacao musical das comunidades.
                    </p>
                </article>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="container site-footer__grid">
            <div>
                <h3 class="site-footer__title">Voz &amp; Cifra</h3>
                <p class="site-footer__text">
                    Plataforma publica pensada para acompanhar missas com leitura clara, identidade reverente e acesso simples.
                </p>
            </div>

            <div>
                <h3 class="site-footer__title">Navegacao</h3>
                <div class="site-footer__links">
                    <a href="#inicio">Inicio</a>
                    <a href="#missas">Missas</a>
                    <a href="#igrejas">Igrejas</a>
                    <a href="#historico">Historico</a>
                </div>
            </div>

            <div>
                <h3 class="site-footer__title">Contato</h3>
                <p class="site-footer__text">
                    Suporte e acesso interno pela pagina de login.<br>
                    <a href="{{ route('login') }}" style="color:#d2aa66;">Entrar no sistema</a>
                </p>
            </div>
        </div>
        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Voz &amp; Cifra. Projeto de extensao do IFMS voltado a organizacao liturgica e ao apoio musical das comunidades.</p>
        </div>
    </footer>
</x-publico.layouts.app>
