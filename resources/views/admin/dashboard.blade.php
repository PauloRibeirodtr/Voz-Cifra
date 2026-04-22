@extends('admin.layouts.admin')

@section('title', 'Painel do Admin Master | Voz & Cifra')
@section('mobile_title', 'Painel')

@section('content')
    <section class="admin-page-intro">
        <p class="admin-page-kicker">Visao central</p>
        <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Painel do administrador principal</h1>
        <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
            Uma leitura mais limpa da operacao central. O foco aqui e acompanhar a base, entrar rapido nos modulos principais
            e manter os cadastros sob controle sem poluicao visual.
        </p>
    </section>

    <section class="admin-inline-note mb-6 px-5 py-4 text-sm leading-7 sm:px-6">
        A igreja pode ser cadastrada sem admin local. O papel de <strong>admin local</strong> so passa a ser obrigatorio quando alguem for operar a igreja e criar missas.
    </section>

    <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('admin.usuarios.index') }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Usuarios</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_usuarios'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Contas centrais, perfis operacionais e padres reaproveitados.</p>
        </a>

        <a href="{{ route('admin.igrejas.index') }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Igrejas</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_igrejas'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Base de igrejas com links publicos, coordenacao e operacao local.</p>
        </a>

        <a href="{{ route('admin.musicas.index') }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Musicas</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_musicas'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Biblioteca musical central com versoes, cifras e apoio ao estudo.</p>
        </a>

        <a href="{{ route('admin.igrejas.index') }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Missas</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['total_missas'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Missas cadastradas na base e prontas para operacao por igreja.</p>
        </a>

        <a href="{{ route('admin.usuarios.index', ['tipo' => 'admin_local']) }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Admins locais</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['admins_locais'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Pessoas com operacao local ja vinculadas a alguma igreja.</p>
        </a>

        <a href="{{ route('admin.usuarios.index', ['tipo' => 'musico']) }}" class="admin-stat-card block p-5 sm:p-6">
            <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Musicos</div>
            <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['musicos'] ?? 0 }}</div>
            <p class="mt-3 text-sm text-gray-500">Base musical operacional pronta para ensaio e acompanhamento.</p>
        </a>
    </section>

    <section class="admin-highlight-surface mb-8 rounded-[1.75rem] p-6 sm:p-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <p class="admin-page-kicker">Fluxo recomendado</p>
                <h2 class="mt-2 text-xl font-black text-gray-800 sm:text-2xl">Comece pelo cadastro central de usuarios</h2>
                <p class="mt-3 text-sm leading-7 text-gray-600 sm:text-base">
                    O novo fluxo do master permite criar a conta primeiro e so depois decidir a igreja e os papeis. Isso reduz duplicidade,
                    facilita promocao de padre para usuario operacional e deixa a base mais previsivel.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center rounded-xl bg-[#6f4726] px-5 py-3 text-sm font-semibold text-white hover:bg-[#5d3b1f]">
                    Novo usuario
                </a>
                <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-white/70">
                    Ver igrejas
                </a>
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5">
            <p class="admin-page-kicker">Acessos rapidos</p>
            <h2 class="mt-2 text-lg font-bold text-gray-800">Atalhos principais do painel</h2>
            <p class="mt-2 text-sm text-gray-500">Links importantes para manter a rotina do admin master objetiva e organizada.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('admin.igrejas.index') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Igrejas</span>
                <span class="mt-2 block text-base font-bold">Gerenciar igrejas</span>
                <span class="mt-2 block text-sm text-gray-500">Editar dados, links publicos e liderancas.</span>
            </a>

            <a href="{{ route('admin.usuarios.index') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Usuarios</span>
                <span class="mt-2 block text-base font-bold">Cadastros e papeis</span>
                <span class="mt-2 block text-sm text-gray-500">Criar conta, promover perfil e vincular igreja.</span>
            </a>

            <a href="{{ route('admin.musicas.index') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Musicas</span>
                <span class="mt-2 block text-base font-bold">Biblioteca musical</span>
                <span class="mt-2 block text-sm text-gray-500">Organizar repertorio, versoes e revisoes.</span>
            </a>

            <a href="{{ route('admin.acordes.index') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Acordes</span>
                <span class="mt-2 block text-base font-bold">Dicionario de acordes</span>
                <span class="mt-2 block text-sm text-gray-500">Base visual para apoio das cifras.</span>
            </a>

            <a href="{{ route('admin.auditoria.index') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Auditoria</span>
                <span class="mt-2 block text-base font-bold">Acoes sensiveis</span>
                <span class="mt-2 block text-sm text-gray-500">Conferir movimentacoes e eventos de seguranca.</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="admin-quick-link px-4 py-4">
                <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Configuracoes</span>
                <span class="mt-2 block text-base font-bold">Conta e sistema</span>
                <span class="mt-2 block text-sm text-gray-500">Ajustes do master e visao geral da instalacao.</span>
            </a>
        </div>
    </section>

    <section class="admin-muted-surface rounded-3xl p-6 sm:p-7">
        <p class="admin-page-kicker">Proxima frente</p>
        <h2 class="mt-2 text-lg font-bold text-gray-800">Painel pronto para seguir refinando as demais areas</h2>
        <p class="mt-3 max-w-3xl text-sm leading-7 text-gray-600 sm:text-base">
            O menu e o painel principal agora estao mais legiveis e menos pesados. A partir daqui da para continuar refinando
            as telas de cadastro, listagem e configuracao sem quebrar a identidade do sistema.
        </p>
    </section>
@endsection
