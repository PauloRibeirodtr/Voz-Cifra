<x-publico.layouts.app
    title="Voz & Cifra | Desenvolvedores"
    description="Conheça a equipe de alunos do IFMS responsável pelo desenvolvimento do projeto Voz & Cifra."
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
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('login') }}" class="site-nav__login">Entrar</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="section" style="padding-top: 1.4rem;">
            <div class="container">
                <article class="institution-card" style="margin-bottom: 1.25rem;">
                    <div class="institution-card__logo">
                        <img src="{{ $logoInstituicao }}" alt="Logo do IFMS">
                    </div>
                    <div style="display:grid;gap:.9rem;">
                        <div>
                            <span class="eyebrow">Instituição parceira</span>
                            <h1 class="institution-card__title">Projeto de extensão desenvolvido por alunos de ADS do IFMS</h1>
                        </div>
                        <p class="institution-card__text">
                            Esta iniciativa une formação acadêmica, desenvolvimento de software e serviço à comunidade. A proposta é incentivar novos programadores, fortalecer a vivência prática e entregar uma ferramenta útil para igrejas e ministérios musicais.
                        </p>
                        <div class="institution-card__actions">
                            <a class="institution-card__action" href="https://www.ifms.edu.br/" target="_blank" rel="noreferrer">Conhecer o IFMS</a>
                            <a class="institution-card__action" href="https://github.com/roberth-silva-lab" target="_blank" rel="noreferrer">GitHub do projeto</a>
                        </div>
                    </div>
                </article>

                <div class="section__header">
                    <span class="eyebrow">Desenvolvedores</span>
                    <h2 class="section__title">Equipe que construiu esta iniciativa</h2>
                    <p class="section__lead">
                        Esta página apresenta os alunos envolvidos no desenvolvimento, documentação, modelagem e evolução do projeto Voz &amp; Cifra.
                    </p>
                </div>

                <div class="team-grid">
                    @foreach ($equipeProjeto as $integrante)
                        <article class="team-card">
                            <div class="team-card__photo">
                                <img src="{{ $integrante['foto'] }}" alt="Foto de {{ $integrante['nome'] }}">
                            </div>
                            <div class="team-card__content">
                                <span class="team-card__role">{{ $integrante['papel'] }}</span>
                                <h3 class="team-card__name">{{ $integrante['nome'] }}</h3>
                                <p class="team-card__description">{{ $integrante['descricao'] }}</p>
                                <div class="team-card__meta">
                                    <a class="team-card__link" href="{{ $integrante['github'] }}" target="_blank" rel="noreferrer">
                                        Ver GitHub
                                    </a>
                                </div>
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
                    <a href="{{ route('root') }}#inicio">Início</a>
                    <a href="{{ route('root') }}#destaque">Próxima missa</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
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
