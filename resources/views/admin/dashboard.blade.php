@extends('admin.layouts.admin')

@section('title', 'Painel do Admin Master | Voz & Cifra')
@section('mobile_title', 'Painel')

@section('content')
    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Visao central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Painel do administrador principal</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Acompanhe a operacao central, acesse os modulos principais e mantenha os cadastros sob controle.
                </p>
            </div>
        </section>

        <section class="admin-inline-note px-5 py-4 text-sm leading-7 sm:px-6">
            A igreja pode ser cadastrada sem admin local. O papel de <strong>admin local</strong> so passa a ser obrigatorio quando alguem for operar a igreja e cadastrar missas.
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <p class="admin-page-kicker">Comece por aqui</p>
                    <h2 class="mt-2 text-lg font-bold text-gray-800">Acoes principais do painel</h2>
                    <p class="mt-2 text-sm text-gray-500">Escolha uma tarefa comum e siga sem precisar procurar no menu lateral.</p>
                </div>
            </div>

            <div class="admin-panel-body">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('admin.usuarios.create') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Cadastro</span>
                        <span class="mt-2 block text-base font-bold">Cadastrar usuario</span>
                        <span class="mt-2 block text-sm text-gray-500">Criar conta e depois definir igreja e papeis.</span>
                    </a>

                    <a href="{{ route('admin.igrejas.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Igrejas</span>
                        <span class="mt-2 block text-base font-bold">Gerenciar igrejas</span>
                        <span class="mt-2 block text-sm text-gray-500">Editar dados, links publicos e liderancas.</span>
                    </a>

                    <a href="{{ route('admin.usuarios.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Usuarios</span>
                        <span class="mt-2 block text-base font-bold">Cadastros e papeis</span>
                        <span class="mt-2 block text-sm text-gray-500">Promover perfil, reenviar convite e vincular igreja.</span>
                    </a>

                    <a href="{{ route('admin.auditoria.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Auditoria</span>
                        <span class="mt-2 block text-base font-bold">Acoes sensiveis</span>
                        <span class="mt-2 block text-sm text-gray-500">Conferir movimentacoes e eventos de seguranca.</span>
                    </a>

                    <a href="{{ route('admin.musicas.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Musicas</span>
                        <span class="mt-2 block text-base font-bold">Biblioteca musical</span>
                        <span class="mt-2 block text-sm text-gray-500">Organizar repertorio, versoes e revisoes.</span>
                    </a>

                    <a href="{{ route('admin.momentos-liturgicos.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Liturgia</span>
                        <span class="mt-2 block text-base font-bold">Momentos da missa</span>
                        <span class="mt-2 block text-sm text-gray-500">Cadastrar entrada, salmo, comunhao e demais momentos.</span>
                    </a>

                    <a href="{{ route('admin.tempos-liturgicos.index') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Calendario</span>
                        <span class="mt-2 block text-base font-bold">Tempos liturgicos</span>
                        <span class="mt-2 block text-sm text-gray-500">Manter Advento, Natal, Quaresma, Pascoa e Tempo Comum.</span>
                    </a>

                    <a href="{{ route('admin.settings') }}" class="admin-quick-link px-4 py-4">
                        <span class="admin-quick-link-label block text-xs font-black uppercase tracking-[0.18em]">Configuracoes</span>
                        <span class="mt-2 block text-base font-bold">Conta e sistema</span>
                        <span class="mt-2 block text-sm text-gray-500">Ajustes do master e visao geral da instalacao.</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <p class="admin-page-kicker">Metricas da operacao</p>
                    <h2 class="mt-2 text-lg font-bold text-gray-800">Resumo atual do sistema</h2>
                    <p class="mt-2 text-sm text-gray-500">Numeros principais para acompanhar volume, operacao e acessos recentes.</p>
                </div>
            </div>

            <div class="admin-panel-body">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <a href="{{ route('admin.usuarios.index', ['presenca' => 'online']) }}" class="admin-stat-card block p-5 sm:p-6">
                        <div class="admin-stat-label text-sm font-bold uppercase tracking-[0.18em]">Online agora</div>
                        <div class="admin-stat-value mt-4 text-3xl font-black sm:text-4xl">{{ $metrics['usuarios_online'] ?? 0 }}</div>
                        <p class="mt-3 text-sm text-gray-500">Ver usuarios com atividade registrada nos ultimos 5 minutos.</p>
                    </a>

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
                </div>
            </div>
        </section>
    </div>
@endsection
