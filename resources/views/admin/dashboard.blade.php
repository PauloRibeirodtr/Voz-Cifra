@extends('admin.layouts.admin')

@section('title', 'Painel do Admin Master | Voz & Cifra')
@section('mobile_title', 'Painel')

@section('content')
    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Visão central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Painel do administrador principal</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Uma leitura mais limpa da operação central. O foco aqui é acompanhar a base, entrar rápido nos módulos principais
                    e manter os cadastros sob controle sem poluição visual.
                </p>
            </div>
            <div class="admin-page-actions">
                <a href="{{ route('admin.usuarios.create') }}" class="admin-btn admin-btn-primary">Cadastrar usuário</a>
                <a href="{{ route('admin.igrejas.index') }}" class="admin-btn admin-btn-secondary">Ver igrejas</a>
            </div>
        </section>

        <section class="admin-inline-note px-5 py-4 text-sm leading-7 sm:px-6">
            A igreja pode ser cadastrada sem admin local. O papel de <strong>admin local</strong> só passa a ser obrigatório quando alguém for operar a igreja e cadastrar missas.
        </section>

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('admin.usuarios.index') }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Usuários</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_usuarios'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Contas centrais, perfis operacionais e padres reaproveitados.</p>
            </a>

            <a href="{{ route('admin.igrejas.index') }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Igrejas</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_igrejas'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Base de igrejas com links públicos, coordenação e operação local.</p>
            </a>

            <a href="{{ route('admin.musicas.index') }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Músicas</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_musicas'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Biblioteca musical central com versões, cifras e apoio ao estudo.</p>
            </a>

            <a href="{{ route('admin.igrejas.index') }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Missas</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_missas'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Missas cadastradas na base e prontas para operação por igreja.</p>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'admin_local']) }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Admins locais</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['admins_locais'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Pessoas com operação local já vinculadas a alguma igreja.</p>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'musico']) }}" class="admin-stat-card block p-5 sm:p-6">
                <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Músicos</div>
                <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['musicos'] ?? 0 }}</div>
                <p class="mt-3 text-sm text-gray-500">Base musical operacional pronta para ensaio e acompanhamento.</p>
            </a>
        </section>

        <section class="admin-highlight-surface rounded-[1.75rem] p-6 sm:p-7">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="admin-page-kicker">Fluxo recomendado</p>
                    <h2 class="mt-2 text-xl font-black text-gray-800 sm:text-2xl">Comece pelo cadastro central de usuários</h2>
                    <p class="mt-3 text-sm leading-7 text-gray-600 sm:text-base">
                        O novo fluxo do master permite criar a conta primeiro e só depois decidir a igreja e os papéis. Isso reduz duplicidade,
                        facilita a promoção de padre para usuário operacional e deixa a base mais previsível.
                    </p>
                </div>

                <div class="admin-actions">
                    <a href="{{ route('admin.usuarios.create') }}" class="admin-btn admin-btn-primary">Cadastrar usuário</a>
                    <a href="{{ route('admin.igrejas.index') }}" class="admin-btn admin-btn-secondary">Ver igrejas</a>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <p class="admin-page-kicker">Acessos rapidos</p>
                    <h2 class="mt-2 text-lg font-bold text-gray-800">Atalhos principais do painel</h2>
                    <p class="mt-2 text-sm text-gray-500">Links importantes para manter a rotina do admin master objetiva e organizada.</p>
                </div>
            </div>

            <div class="admin-panel-body">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('admin.igrejas.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Igrejas</span>
                        <span class="mt-2 block text-base font-bold">Gerenciar igrejas</span>
                        <span class="mt-2 block text-sm text-gray-500">Editar dados, links públicos e lideranças.</span>
                    </a>

                    <a href="{{ route('admin.usuarios.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Usuários</span>
                        <span class="mt-2 block text-base font-bold">Cadastros e papéis</span>
                        <span class="mt-2 block text-sm text-gray-500">Cadastrar conta, promover perfil e vincular igreja.</span>
                    </a>

                    <a href="{{ route('admin.musicas.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Músicas</span>
                        <span class="mt-2 block text-base font-bold">Biblioteca musical</span>
                        <span class="mt-2 block text-sm text-gray-500">Organizar repertório, versões e revisões.</span>
                    </a>

                    <a href="{{ route('admin.acordes.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Acordes</span>
                        <span class="mt-2 block text-base font-bold">Dicionário de acordes</span>
                        <span class="mt-2 block text-sm text-gray-500">Base visual para apoio das cifras.</span>
                    </a>

                    <a href="{{ route('admin.auditoria.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Auditoria</span>
                        <span class="mt-2 block text-base font-bold">Ações sensíveis</span>
                        <span class="mt-2 block text-sm text-gray-500">Conferir movimentações e eventos de segurança.</span>
                    </a>

                    <a href="{{ route('admin.settings') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Configurações</span>
                        <span class="mt-2 block text-base font-bold">Conta e sistema</span>
                        <span class="mt-2 block text-sm text-gray-500">Ajustes do master e visão geral da instalação.</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="admin-muted-surface rounded-3xl p-6 sm:p-7">
            <p class="admin-page-kicker">Próxima frente</p>
            <h2 class="mt-2 text-lg font-bold text-gray-800">Painel pronto para seguir refinando as demais areas</h2>
            <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600 sm:text-base">
                O menu e o painel principal agora estão mais legíveis e menos pesados. A partir daqui dá para continuar refinando
                as telas de cadastro, listagem e configuração sem quebrar a identidade do sistema.
            </p>
        </section>
    </div>
@endsection
