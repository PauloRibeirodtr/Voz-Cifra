@extends('admin.layouts.admin')

@section('title', 'Configuracoes | Voz & Cifra')
@section('mobile_title', 'Configuracoes')

@section('content')
    @php
        $themeAtual = old('theme_preference', $usuario->theme_preference ?? 'system');
        $recebeEmail = (bool) old('receber_notificacoes_email', $usuario->receber_notificacoes_email ?? true);
        $temaLabel = ['system' => 'Automatico', 'light' => 'Claro', 'dark' => 'Escuro'][$themeAtual] ?? 'Automatico';
        $emailLabel = $recebeEmail ? 'Avisos gerais por e-mail ligados' : 'Somente alertas criticos por e-mail';

        $atalhos = [
            ['titulo' => 'Editar perfil', 'texto' => 'Foto, e-mail, telefone e senha.', 'icone' => 'fa-user-pen', 'url' => route('admin.profile'), 'grupo' => 'conta perfil senha foto'],
            ['titulo' => 'Usuarios e papeis', 'texto' => 'Cadastrar, promover e vincular usuarios.', 'icone' => 'fa-users-gear', 'url' => route('admin.usuarios.index'), 'grupo' => 'usuarios papeis acesso seguranca'],
            ['titulo' => 'Enviar aviso', 'texto' => 'Comunicar pessoas, papeis ou igrejas.', 'icone' => 'fa-bullhorn', 'url' => route('admin.avisos.create'), 'grupo' => 'notificacoes avisos email'],
            ['titulo' => 'Auditoria', 'texto' => 'Conferir acoes sensiveis do sistema.', 'icone' => 'fa-shield-halved', 'url' => route('admin.auditoria.index'), 'grupo' => 'seguranca auditoria logs'],
            ['titulo' => 'Igrejas', 'texto' => 'Dados, links publicos e liderancas.', 'icone' => 'fa-church', 'url' => route('admin.igrejas.index'), 'grupo' => 'igrejas vinculos contexto'],
            ['titulo' => 'Biblioteca musical', 'texto' => 'Musicas, versoes, cifras e acordes.', 'icone' => 'fa-music', 'url' => route('admin.musicas.index'), 'grupo' => 'musicas cifras acordes repertorio'],
        ];

        $metricas = [
            ['label' => 'Usuarios', 'valor' => $metricasSistema['total_usuarios']],
            ['label' => 'Igrejas', 'valor' => $metricasSistema['total_igrejas']],
            ['label' => 'Musicas', 'valor' => $metricasSistema['total_musicas']],
            ['label' => 'Missas', 'valor' => $metricasSistema['total_missas']],
        ];
    @endphp

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Configuracoes e privacidade</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Central do admin master</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Encontre ajustes da conta, notificacoes, seguranca e atalhos administrativos sem precisar garimpar o menu lateral.
                </p>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[20rem_minmax(0,1fr)]">
            <aside class="space-y-4 xl:sticky xl:top-6 xl:self-start">
                <div class="admin-panel p-4">
                    <label for="settings-search" class="admin-label">Pesquisar configuracoes</label>
                    <div class="mt-2 flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        <input
                            id="settings-search"
                            type="search"
                            class="w-full border-0 bg-transparent text-sm font-semibold text-gray-800 outline-none placeholder:text-gray-400 focus:ring-0"
                            placeholder="Tema, notificacoes, usuarios..."
                            data-settings-search
                        >
                    </div>
                </div>

                <nav class="admin-panel overflow-hidden p-2" aria-label="Categorias de configuracao">
                    <a href="#mais-acessadas" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-star text-[#8a5a26]"></i>
                        Mais acessadas
                    </a>
                    <a href="#conta" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-user-shield text-[#8a5a26]"></i>
                        Conta
                    </a>
                    <a href="#preferencias" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-sliders text-[#8a5a26]"></i>
                        Preferencias
                    </a>
                    <a href="#notificacoes" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-bell text-[#8a5a26]"></i>
                        Notificacoes
                    </a>
                    <a href="#seguranca" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-lock text-[#8a5a26]"></i>
                        Seguranca
                    </a>
                    <a href="#sistema" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black text-gray-800 transition hover:bg-[#f5eee6]">
                        <i class="fa-solid fa-chart-simple text-[#8a5a26]"></i>
                        Sistema
                    </a>
                </nav>

                <div class="admin-inline-note px-4 py-4 text-sm">
                    <strong class="block text-gray-900">Regra de seguranca</strong>
                    Notificacoes internas importantes continuam ativas mesmo quando o e-mail geral estiver desligado.
                </div>
            </aside>

            <div class="space-y-6">
                <section id="mais-acessadas" class="admin-panel p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Comece por aqui</p>
                        <h2 class="text-xl font-black text-gray-900">Configuracoes mais acessadas</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <a href="{{ route('admin.profile') }}" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg" data-settings-card data-settings-keywords="perfil foto senha email telefone conta">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                                <i class="fa-solid fa-user-pen"></i>
                            </span>
                            <h3 class="mt-5 text-base font-black text-gray-950">Perfil</h3>
                            <p class="mt-2 text-sm text-gray-600">Atualize foto, contato e senha.</p>
                        </a>

                        <a href="#preferencias" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg" data-settings-card data-settings-keywords="modo escuro claro tema aparencia">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                                <i class="fa-solid fa-moon"></i>
                            </span>
                            <h3 class="mt-5 text-base font-black text-gray-950">Modo escuro</h3>
                            <p class="mt-2 text-sm text-gray-600">Atual: {{ $temaLabel }}.</p>
                        </a>

                        <a href="#notificacoes" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg" data-settings-card data-settings-keywords="notificacoes sininho email avisos">
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                                <i class="fa-solid fa-bell"></i>
                            </span>
                            <h3 class="mt-5 text-base font-black text-gray-950">Notificacoes</h3>
                            <p class="mt-2 text-sm text-gray-600">{{ $notificacoesNaoLidas }} aviso(s) nao lido(s).</p>
                        </a>
                    </div>
                </section>

                <section id="preferencias" class="admin-panel p-5 sm:p-6">
                    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="admin-page-kicker">Preferencias</p>
                            <h2 class="text-xl font-black text-gray-900">Aparencia e comunicacao</h2>
                            <p class="mt-2 text-sm text-gray-600">Ajustes rapidos que mudam a experiencia sem tocar nos dados sensiveis do perfil.</p>
                        </div>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">{{ $emailLabel }}</span>
                    </div>

                    <form method="POST" action="{{ route('admin.settings.preferences.update') }}" class="space-y-5" data-preferences-form>
                        @csrf
                        @method('PUT')

                        <div class="rounded-3xl border border-gray-200 bg-white p-4">
                            <span class="admin-label mb-3 block">Tema da interface</span>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                @foreach (['system' => ['Automatico', 'Segue o dispositivo', 'fa-display'], 'light' => ['Claro', 'Mais limpo de dia', 'fa-sun'], 'dark' => ['Escuro', 'Mais confortavel a noite', 'fa-moon']] as $valor => $tema)
                                    <label class="cursor-pointer rounded-2xl border px-4 py-4 transition {{ $themeAtual === $valor ? 'border-emerald-300 bg-emerald-50 text-emerald-950' : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-[#c9a15f]' }}">
                                        <input type="radio" name="theme_preference" value="{{ $valor }}" class="sr-only" @checked($themeAtual === $valor) data-preference-radio>
                                        <span class="flex items-center gap-3">
                                            <i class="fa-solid {{ $tema[2] }}"></i>
                                            <span>
                                                <strong class="block">{{ $tema[0] }}</strong>
                                                <small class="text-xs">{{ $tema[1] }}</small>
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="rounded-3xl border border-gray-200 bg-white p-4">
                            <label class="flex cursor-pointer items-start justify-between gap-4 rounded-2xl bg-gray-50 p-4">
                                <span>
                                    <strong class="block text-gray-950">Receber avisos gerais por e-mail</strong>
                                    <span class="mt-1 block text-sm text-gray-600">Chamados, avisos administrativos e comunicados comuns podem chegar por e-mail.</span>
                                    <span class="mt-2 block text-xs font-bold text-amber-700">Alertas criticos de seguranca continuam ativos.</span>
                                </span>
                                <span class="relative mt-1 inline-flex h-7 w-12 flex-none items-center rounded-full {{ $recebeEmail ? 'bg-emerald-600' : 'bg-gray-300' }}" data-toggle-shell>
                                    <input type="hidden" name="receber_notificacoes_email" value="0">
                                    <input type="checkbox" name="receber_notificacoes_email" value="1" class="peer sr-only" @checked($recebeEmail) data-email-toggle>
                                    <span class="absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                                </span>
                            </label>
                        </div>

                        <button type="submit" class="admin-btn admin-btn-primary w-full sm:w-auto">Salvar preferencias</button>
                    </form>
                </section>

                <section id="conta" class="admin-panel p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Conta</p>
                        <h2 class="text-xl font-black text-gray-900">Perfil e acesso</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <a href="{{ route('admin.profile') }}" class="settings-card rounded-3xl border border-gray-200 bg-white p-5 transition hover:border-[#c9a15f]" data-settings-card data-settings-keywords="perfil conta foto email telefone">
                            <h3 class="font-black text-gray-950">Editar meus dados</h3>
                            <p class="mt-2 text-sm text-gray-600">Foto, telefone, e-mail e senha ficam em uma tela propria.</p>
                        </a>
                        <a href="{{ route('admin.profile') }}#password" class="settings-card rounded-3xl border border-gray-200 bg-white p-5 transition hover:border-[#c9a15f]" data-settings-card data-settings-keywords="senha seguranca primeiro acesso">
                            <h3 class="font-black text-gray-950">Trocar senha</h3>
                            <p class="mt-2 text-sm text-gray-600">Mantem o acesso global separado dos papeis por igreja.</p>
                        </a>
                    </div>
                </section>

                <section id="notificacoes" class="admin-panel p-5 sm:p-6">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="admin-page-kicker">Notificacoes</p>
                            <h2 class="text-xl font-black text-gray-900">Central de avisos internos</h2>
                            <p class="mt-2 text-sm text-gray-600">O sininho mostra pedidos de tom, mudancas de acesso, avisos e acoes direcionadas.</p>
                        </div>
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">
                            {{ $notificacoesNaoLidas }} nao lida(s)
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($notificacoesRecentes as $notificacao)
                            <form method="POST" action="{{ route('notificacoes.ler', $notificacao) }}" class="rounded-2xl border {{ $notificacao->lida_em ? 'border-gray-100 bg-gray-50' : 'border-emerald-100 bg-emerald-50' }} px-4 py-4">
                                @csrf
                                <button type="submit" class="block w-full text-left">
                                    <span class="block text-sm font-black text-gray-900">{{ $notificacao->titulo }}</span>
                                    @if ($notificacao->mensagem)
                                        <span class="mt-1 block text-sm text-gray-600">{{ $notificacao->mensagem }}</span>
                                    @endif
                                    <span class="mt-2 block text-xs font-bold text-gray-400">{{ $notificacao->created_at?->diffForHumans() }}</span>
                                </button>
                            </form>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500">
                                Tudo limpo por aqui. Quando houver pedido de tom, aviso ou alteracao de acesso, aparece aqui e no sininho.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.avisos.create') }}" class="admin-btn admin-btn-secondary inline-flex">Enviar novo aviso</a>
                    </div>
                </section>

                <section id="seguranca" class="admin-panel p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Seguranca</p>
                        <h2 class="text-xl font-black text-gray-900">Acoes sensiveis e rastreio</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <a href="{{ route('admin.auditoria.index') }}" class="settings-card rounded-3xl border border-gray-200 bg-white p-5 transition hover:border-[#c9a15f]" data-settings-card data-settings-keywords="auditoria seguranca logs">
                            <h3 class="font-black text-gray-950">Auditoria</h3>
                            <p class="mt-2 text-sm text-gray-600">Veja movimentacoes importantes e quem executou cada acao.</p>
                        </a>
                        <a href="{{ route('admin.usuarios.index') }}" class="settings-card rounded-3xl border border-gray-200 bg-white p-5 transition hover:border-[#c9a15f]" data-settings-card data-settings-keywords="usuarios papeis acesso perfil">
                            <h3 class="font-black text-gray-950">Acessos</h3>
                            <p class="mt-2 text-sm text-gray-600">Promova, remova ou ajuste papeis por igreja com controle.</p>
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="settings-card rounded-3xl border border-red-100 bg-red-50 p-5" data-settings-card data-settings-keywords="sair logout sessao">
                            @csrf
                            <h3 class="font-black text-red-950">Sessao atual</h3>
                            <p class="mt-2 text-sm text-red-800">Encerre o acesso quando terminar de administrar.</p>
                            <button type="submit" class="mt-4 rounded-xl bg-red-600 px-4 py-2 text-sm font-black text-white transition hover:bg-red-700">Sair da conta</button>
                        </form>
                    </div>
                </section>

                <section id="sistema" class="admin-highlight-surface p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Sistema</p>
                        <h2 class="text-xl font-black text-gray-900">Resumo operacional</h2>
                        <p class="mt-2 text-sm text-gray-600">Numeros rapidos e atalhos para manutencao central.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                        @foreach ($metricas as $metrica)
                            <div class="rounded-2xl border border-white/70 bg-white/70 p-4">
                                <span class="block text-xs font-black uppercase tracking-[0.16em] text-gray-400">{{ $metrica['label'] }}</span>
                                <span class="mt-2 block text-3xl font-black text-gray-950">{{ $metrica['valor'] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($atalhos as $atalho)
                            <a href="{{ $atalho['url'] }}" class="settings-card rounded-3xl border border-white/70 bg-white/75 p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg" data-settings-card data-settings-keywords="{{ $atalho['grupo'] }}">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#f8f1e8] text-[#8a5a26]">
                                    <i class="fa-solid {{ $atalho['icone'] }}"></i>
                                </span>
                                <h3 class="mt-4 font-black text-gray-950">{{ $atalho['titulo'] }}</h3>
                                <p class="mt-2 text-sm text-gray-600">{{ $atalho['texto'] }}</p>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm text-emerald-950">
                        <strong>Igreja ativa:</strong> {{ $igrejaAtiva?->nome ?? 'Nenhuma igreja ativa neste momento.' }}
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const search = document.querySelector('[data-settings-search]');
            const cards = Array.from(document.querySelectorAll('[data-settings-card]'));
            const normalize = (value) => (value || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

            search?.addEventListener('input', () => {
                const term = normalize(search.value);

                cards.forEach((card) => {
                    const haystack = normalize(`${card.textContent} ${card.dataset.settingsKeywords || ''}`);
                    card.classList.toggle('hidden', term !== '' && !haystack.includes(term));
                });
            });

            document.querySelectorAll('[data-preference-radio]').forEach((radio) => {
                radio.addEventListener('change', () => radio.closest('form')?.requestSubmit());
            });

            const emailToggle = document.querySelector('[data-email-toggle]');
            const toggleShell = document.querySelector('[data-toggle-shell]');
            emailToggle?.addEventListener('change', () => {
                toggleShell?.classList.toggle('bg-emerald-600', emailToggle.checked);
                toggleShell?.classList.toggle('bg-gray-300', !emailToggle.checked);
            });
        });
    </script>
@endpush
