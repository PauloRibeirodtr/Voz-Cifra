<x-publico.layouts.app
    title="Voz & Cifra | Desenvolvedores"
    description="Conheca a equipe de alunos do IFMS responsavel pelo desenvolvimento do projeto Voz & Cifra."
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
        <section class="section" style="padding-top: 1.4rem;">
            <div class="container">
                <article class="institution-card" style="margin-bottom: 1.25rem;">
                    <div class="institution-card__logo">
                        <img src="{{ $logoInstituicao }}" alt="Logo do IFMS">
                    </div>
                    <div style="display:grid;gap:.9rem;">
                        <div>
                            <span class="eyebrow">Instituicao parceira</span>
                            <h1 class="institution-card__title">Projeto de extensao desenvolvido por alunos de ADS do IFMS</h1>
                        </div>
                        <p class="institution-card__text">
                            Esta iniciativa une formacao academica, desenvolvimento de software e servico a comunidade. A proposta e incentivar novos programadores, fortalecer a vivencia pratica e entregar uma ferramenta util para igrejas e ministerios musicais.
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
                        Esta pagina apresenta os alunos envolvidos no desenvolvimento, documentacao, modelagem e evolucao do projeto Voz &amp; Cifra.
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
                    Projeto de extensao voltado a organizacao liturgica, experiencia publica de missas e apoio musical.
                </p>
            </div>

            <div>
                <h3 class="site-footer__title">Navegacao</h3>
                <div class="site-footer__links">
                    <a href="{{ route('root') }}">Inicio</a>
                    <a href="{{ route('root') }}#missas">Missas</a>
                    <a href="{{ route('root') }}#igrejas">Igrejas</a>
                    <a href="{{ route('root') }}#historico">Historico</a>
                </div>
            </div>

            <div>
                <h3 class="site-footer__title">Instituicao</h3>
                <p class="site-footer__text">
                    Iniciativa academica do IFMS com foco em tecnologia, extensao e servico a comunidade.
                </p>
            </div>
        </div>
        <div class="site-footer__bottom">
            <p>&copy; {{ date('Y') }} Voz &amp; Cifra. Projeto de extensao do IFMS dedicado a tecnologia e servico a comunidade.</p>
        </div>
    </footer>
</x-publico.layouts.app>
