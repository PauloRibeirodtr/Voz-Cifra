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
                                <x-public.button href="#igrejas">Encontrar minha igreja</x-public.button>
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

        <section id="igrejas" class="section">
            <div class="container">
                <div class="section__header">
                    <span class="eyebrow">Encontre sua igreja</span>
                    <h2 class="section__title">Abra a página da sua comunidade</h2>
                    <p class="section__lead">
                        Busque pelo nome da igreja ou pela cidade para ver as missas publicadas.
                    </p>
                </div>

                <div class="church-finder">
                    <div class="church-finder__bar">
                        <label class="field church-finder__field" for="busca_igreja">
                            <span>Cidade ou igreja</span>
                            <input id="busca_igreja" type="search" placeholder="Ex.: Corumbá, Catedral, Centro" data-church-search>
                        </label>

                        <div class="city-badges" aria-label="Cidades com igrejas cadastradas">
                            <button type="button" class="city-badge is-active" data-city-filter="">Todas</button>
                            @foreach ($igrejasDestaque->pluck('cidade')->filter()->unique()->values() as $cidade)
                                <button type="button" class="city-badge" data-city-filter="{{ $cidade }}">{{ $cidade }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="church-grid church-grid--visual" data-church-list>
                        @foreach ($igrejasDestaque as $igreja)
                            @php($temMissaPublicada = $igreja['proxima_missa'] !== 'Sem missa publicada no momento')
                            <a
                                href="{{ $igreja['proxima_url'] }}"
                                class="church-tile"
                                data-church-card
                                data-search="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [$igreja['nome'], $igreja['cidade'], $igreja['estado'], $igreja['bairro'], $igreja['endereco']]))) }}"
                                data-city="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $igreja['cidade'])) }}"
                            >
                                <span class="church-tile__image {{ $igreja['tem_imagem_personalizada'] ? 'church-tile__image--photo' : 'church-tile__image--fallback' }}">
                                    <img src="{{ $igreja['imagem_url'] }}" alt="Imagem da igreja {{ $igreja['nome'] }}" loading="lazy">
                                    <span class="church-tile__image-overlay"></span>
                                </span>
                                <span class="church-tile__content">
                                    <span class="church-tile__city">{{ $igreja['localidade'] !== '' ? $igreja['localidade'] : 'Localidade em configuração' }}</span>
                                    <span class="church-tile__name">{{ $igreja['nome'] }}</span>
                                    <span class="church-tile__address">{{ $igreja['endereco'] !== '' ? $igreja['endereco'] : 'Endereço não informado' }}</span>
                                    <span class="church-tile__status {{ $temMissaPublicada ? 'church-tile__status--active' : 'church-tile__status--empty' }}">
                                        <span aria-hidden="true">{{ $temMissaPublicada ? '●' : '●' }}</span>
                                        {{ $temMissaPublicada ? 'Missa publicada' : 'Sem missa hoje' }}
                                    </span>
                                    @if ($temMissaPublicada)
                                        <span class="church-tile__next">
                                            {{ $igreja['proxima_data'] }}
                                            @if ($igreja['proxima_horario'])
                                                · {{ $igreja['proxima_horario'] }}
                                            @endif
                                        </span>
                                    @endif
                                    <span class="church-tile__button">{{ $temMissaPublicada ? 'Abrir próxima missa' : 'Ver missas' }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <p class="church-finder__empty" data-church-empty hidden>Nenhuma igreja encontrada para essa busca.</p>
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

    <style>
            .church-finder {
                display: grid;
                gap: 1rem;
            }

            .church-finder__bar {
                display: grid;
                gap: 1rem;
                align-items: end;
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
                background: var(--panel);
                box-shadow: var(--shadow);
                padding: 1rem;
            }

            .church-finder__field span {
                display: block;
                margin-bottom: 0.5rem;
                color: var(--gold-soft);
                font-size: 0.95rem;
                font-weight: 700;
            }

            .city-badges {
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
            }

            .city-badge {
                min-height: 2.75rem;
                border: 1px solid rgba(210, 170, 102, 0.22);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.04);
                color: var(--muted);
                cursor: pointer;
                font: inherit;
                font-size: 0.92rem;
                font-weight: 800;
                padding: 0.55rem 0.9rem;
            }

            .city-badge.is-active,
            .city-badge:hover,
            .city-badge:focus-visible {
                background: rgba(201, 161, 95, 0.2);
                color: var(--gold-soft);
                outline: none;
            }

            .church-grid--visual {
                align-items: stretch;
                gap: 0.9rem;
            }

            .church-tile {
                display: grid;
                overflow: hidden;
                border: 1px solid var(--line);
                border-radius: var(--radius-lg);
                background: var(--panel);
                box-shadow: var(--shadow);
                grid-template-columns: 6.5rem minmax(0, 1fr);
                min-height: 10.25rem;
                transition: transform 0.18s ease, border-color 0.18s ease, box-shadow 0.18s ease;
            }

            .church-tile:hover,
            .church-tile:focus-visible {
                transform: translateY(-2px);
                border-color: rgba(244, 221, 180, 0.5);
                box-shadow: 0 28px 72px rgba(0, 0, 0, 0.46);
                outline: none;
            }

            .church-tile:hover .church-tile__button,
            .church-tile:focus-visible .church-tile__button {
                background: rgba(244, 221, 180, 0.24);
                border-color: rgba(244, 221, 180, 0.44);
                color: var(--text);
            }

            .church-tile[hidden] {
                display: none;
            }

            .church-tile__image {
                position: relative;
                display: block;
                height: 100%;
                min-height: 10.25rem;
                background: rgba(255, 255, 255, 0.05);
                overflow: hidden;
            }

            .church-tile__image img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.26s ease;
            }

            .church-tile__image--fallback {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background:
                    radial-gradient(circle at center, rgba(245, 174, 20, 0.18), transparent 58%),
                    rgba(255, 255, 255, 0.04);
            }

            .church-tile__image--fallback img {
                width: 74%;
                height: 74%;
                object-fit: contain;
            }

            .church-tile:hover .church-tile__image img,
            .church-tile:focus-visible .church-tile__image img {
                transform: scale(1.035);
            }

            .church-tile__image-overlay {
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(180deg, rgba(8, 6, 6, 0.08), rgba(8, 6, 6, 0.58)),
                    linear-gradient(90deg, rgba(8, 6, 6, 0.28), rgba(8, 6, 6, 0.02));
                pointer-events: none;
            }

            .church-tile__content {
                display: flex;
                flex-direction: column;
                gap: 0.52rem;
                padding: 0.9rem;
                min-width: 0;
            }

            .church-tile__city {
                color: var(--gold-soft);
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .church-tile__name {
                color: var(--text);
                font-family: var(--font-display);
                font-size: clamp(1.08rem, 1.55vw, 1.32rem);
                font-weight: 800;
                line-height: 1.12;
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                overflow: hidden;
            }

            .church-tile__status {
                display: inline-flex;
                width: fit-content;
                align-items: center;
                gap: 0.45rem;
                border-radius: 999px;
                padding: 0.42rem 0.65rem;
                font-size: 0.78rem;
                font-weight: 900;
                line-height: 1;
            }

            .church-tile__address {
                color: var(--muted);
                display: -webkit-box;
                font-size: 0.8rem;
                font-weight: 700;
                line-height: 1.3;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 2;
                overflow: hidden;
            }

            .church-tile__next {
                color: var(--muted);
                font-size: 0.82rem;
                font-weight: 700;
                line-height: 1.25;
            }

            .church-tile__status--active {
                background: rgba(95, 127, 87, 0.2);
                border: 1px solid rgba(143, 190, 129, 0.28);
                color: #dff4d8;
            }

            .church-tile__status--empty {
                background: rgba(166, 123, 67, 0.2);
                border: 1px solid rgba(232, 188, 121, 0.28);
                color: #ffe2ad;
            }

            .church-tile__button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 2.55rem;
                width: 100%;
                border-radius: 999px;
                border: 1px solid rgba(210, 170, 102, 0.28);
                background: rgba(201, 161, 95, 0.14);
                color: var(--gold-soft);
                font-size: 0.9rem;
                font-weight: 900;
                margin-top: auto;
                text-align: center;
                transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
            }

            .church-finder__empty {
                margin: 0;
                border: 1px dashed var(--line);
                border-radius: var(--radius-md);
                color: var(--muted);
                padding: 1rem;
            }

            @media (min-width: 768px) {
                .church-finder__bar {
                    grid-template-columns: minmax(18rem, 0.8fr) minmax(0, 1.2fr);
                    padding: 1.2rem;
                }

                .church-tile__image {
                    min-height: 10.25rem;
                }
            }

            @media (min-width: 1024px) {
                .church-tile {
                    grid-template-columns: 1fr;
                    grid-template-rows: auto 1fr;
                    min-height: 21rem;
                }

                .church-tile__image {
                    height: 8.25rem;
                    min-height: 0;
                }

                .church-tile__content {
                    min-height: 12.75rem;
                }
            }
    </style>

    <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.querySelector('[data-church-search]');
                const cards = Array.from(document.querySelectorAll('[data-church-card]'));
                const empty = document.querySelector('[data-church-empty]');
                const cityButtons = Array.from(document.querySelectorAll('[data-city-filter]'));
                let activeCity = '';

                const normalize = (value) => value
                    .toString()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();

                const applyFilter = () => {
                    const term = normalize(input ? input.value : '');
                    let visibleCount = 0;

                    cards.forEach((card) => {
                        const matchesText = term === '' || card.dataset.search.includes(term);
                        const matchesCity = activeCity === '' || card.dataset.city === activeCity;
                        const visible = matchesText && matchesCity;

                        card.hidden = !visible;
                        if (visible) {
                            visibleCount++;
                        }
                    });

                    if (empty) {
                        empty.hidden = visibleCount > 0;
                    }
                };

                if (input) {
                    input.addEventListener('input', applyFilter);
                }

                cityButtons.forEach((button) => {
                    button.addEventListener('click', () => {
                        activeCity = normalize(button.dataset.cityFilter || '');
                        cityButtons.forEach((item) => item.classList.toggle('is-active', item === button));
                        applyFilter();
                    });
                });
            });
    </script>
</x-publico.layouts.app>
