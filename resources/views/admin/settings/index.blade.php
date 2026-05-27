@extends('admin.layouts.admin')

@section('title', 'Configuracoes | Voz & Cifra')
@section('mobile_title', 'Configuracoes')

@section('content')
    @php
        $themeAtual = old('theme_preference', $usuario->theme_preference ?? 'system');
        $recebeEmail = (bool) old('receber_notificacoes_email', $usuario->receber_notificacoes_email ?? true);
        $temaLabel = ['system' => 'Automatico', 'light' => 'Claro', 'dark' => 'Escuro'][$themeAtual] ?? 'Automatico';
        $emailLabel = $recebeEmail ? 'E-mail ligado' : 'Somente criticas';
        $atalhos = [
            ['titulo' => 'Usuarios', 'texto' => 'Papeis e acessos.', 'icone' => 'fa-users-gear', 'url' => route('admin.usuarios.index')],
            ['titulo' => 'Igrejas', 'texto' => 'Dados e links.', 'icone' => 'fa-church', 'url' => route('admin.igrejas.index')],
            ['titulo' => 'Avisos', 'texto' => 'Enviar comunicado.', 'icone' => 'fa-bullhorn', 'url' => route('admin.avisos.create')],
            ['titulo' => 'Auditoria', 'texto' => 'Acoes sensiveis.', 'icone' => 'fa-shield-halved', 'url' => route('admin.auditoria.index')],
        ];
    @endphp

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Configuracoes</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Ajustes da conta</h1>
                <p class="admin-page-copy mt-3 max-w-2xl text-sm sm:text-base">
                    Preferencias pessoais e atalhos principais do admin master.
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

        <section class="admin-panel p-5 sm:p-6">
            <div class="mb-5">
                <p class="admin-page-kicker">Mais usado</p>
                <h2 class="text-xl font-black text-gray-900">Acesso rapido</h2>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <a href="{{ route('admin.profile') }}" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                        <i class="fa-solid fa-user-pen"></i>
                    </span>
                    <h3 class="mt-5 text-base font-black text-gray-950">Perfil</h3>
                    <p class="mt-2 text-sm text-gray-600">Foto, contato e senha.</p>
                </a>

                <a href="#aparencia" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                        <i class="fa-solid fa-moon"></i>
                    </span>
                    <h3 class="mt-5 text-base font-black text-gray-950">Tema</h3>
                    <p class="mt-2 text-sm text-gray-600">Atual: {{ $temaLabel }}.</p>
                </a>

                <a href="#notificacoes-email" class="settings-card rounded-3xl border border-gray-200 bg-[#f8f1e8] p-5 transition hover:-translate-y-0.5 hover:border-[#c9a15f] hover:shadow-lg">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-[#8a5a26] shadow-sm">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <h3 class="mt-5 text-base font-black text-gray-950">E-mail</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $emailLabel }}.</p>
                </a>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(22rem,0.85fr)]">
            <section id="aparencia" class="admin-panel p-5 sm:p-6">
                <div class="mb-5">
                    <p class="admin-page-kicker">Preferencias</p>
                    <h2 class="text-xl font-black text-gray-900">Aparencia e notificacoes</h2>
                    <p class="mt-2 text-sm text-gray-600">Mude o tema e escolha se quer receber avisos gerais por e-mail.</p>
                </div>

                <form method="POST" action="{{ route('admin.settings.preferences.update') }}" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="rounded-3xl border border-gray-200 bg-white p-4">
                        <span class="admin-label mb-3 block">Tema da interface</span>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            @foreach (['system' => ['Automatico', 'fa-display'], 'light' => ['Claro', 'fa-sun'], 'dark' => ['Escuro', 'fa-moon']] as $valor => $tema)
                                <label class="settings-option cursor-pointer rounded-2xl border px-4 py-4 transition {{ $themeAtual === $valor ? 'is-selected border-emerald-300 bg-emerald-50 text-emerald-950' : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-[#c9a15f]' }}">
                                    <input type="radio" name="theme_preference" value="{{ $valor }}" class="sr-only" @checked($themeAtual === $valor)>
                                    <span class="flex items-center gap-3">
                                        <i class="fa-solid {{ $tema[1] }}"></i>
                                        <strong>{{ $tema[0] }}</strong>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div id="notificacoes-email" class="rounded-3xl border border-gray-200 bg-white p-4">
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl bg-gray-50 p-4">
                            <span>
                                <strong class="block text-gray-950">Avisos gerais por e-mail</strong>
                                <span class="mt-1 block text-sm text-gray-600">Alertas criticos de seguranca continuam ativos.</span>
                            </span>
                            <span class="relative inline-flex h-7 w-12 flex-none items-center rounded-full {{ $recebeEmail ? 'bg-emerald-600' : 'bg-gray-300' }}" data-toggle-shell>
                                <input type="hidden" name="receber_notificacoes_email" value="0">
                                <input type="checkbox" name="receber_notificacoes_email" value="1" class="peer sr-only" @checked($recebeEmail) data-email-toggle>
                                <span class="absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-primary w-full sm:w-auto">Salvar ajustes</button>
                </form>
            </section>

            <section class="admin-panel p-5 sm:p-6">
                <div class="mb-5">
                    <p class="admin-page-kicker">Admin master</p>
                    <h2 class="text-xl font-black text-gray-900">Atalhos</h2>
                    <p class="mt-2 text-sm text-gray-600">Entradas principais para manutencao do sistema.</p>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @foreach ($atalhos as $atalho)
                        <a href="{{ $atalho['url'] }}" class="settings-card flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-4 transition hover:border-[#c9a15f] hover:shadow-md">
                            <span class="flex h-11 w-11 flex-none items-center justify-center rounded-2xl bg-[#f8f1e8] text-[#8a5a26]">
                                <i class="fa-solid {{ $atalho['icone'] }}"></i>
                            </span>
                            <span class="min-w-0">
                                <strong class="block text-gray-950">{{ $atalho['titulo'] }}</strong>
                                <span class="mt-1 block text-sm text-gray-600">{{ $atalho['texto'] }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const emailToggle = document.querySelector('[data-email-toggle]');
            const toggleShell = document.querySelector('[data-toggle-shell]');

            emailToggle?.addEventListener('change', () => {
                toggleShell?.classList.toggle('bg-emerald-600', emailToggle.checked);
                toggleShell?.classList.toggle('bg-gray-300', !emailToggle.checked);
            });
        });
    </script>
@endpush
