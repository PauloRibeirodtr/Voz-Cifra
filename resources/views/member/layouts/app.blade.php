<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('admin.partials.head')
</head>
@php
    $themePreference = auth()->user()->theme_preference ?? 'system';
    $isAdminMasterLayout = auth()->user()?->ehAdminMaster();
    $profileRoute = $isAdminMasterLayout
        ? 'admin.profile'
        : (request()->routeIs('coordenador.*') ? 'coordenador.profile' : 'member.profile');
@endphp
<body class="font-sans text-gray-900" data-theme-preference="{{ $themePreference }}">
    <div
        id="admin_sidebar_overlay"
        class="fixed inset-0 z-40 hidden bg-slate-950/70 backdrop-blur-[3px] lg:hidden"
        aria-hidden="true"
    ></div>

    <div class="admin-app">
        @if ($isAdminMasterLayout)
            @include('admin.partials.sidebar')
        @else
            @include('partials.operational-sidebar', ['sidebarId' => 'admin_sidebar'])
        @endif

        <div class="admin-main">
            <header class="admin-mobile-header sticky top-0 z-20 border-b border-white/10 backdrop-blur lg:hidden">
                <div class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#d6ad6c]">Voz &amp; Cifra</p>
                        <h1 class="truncate text-base font-extrabold text-[#fff8ed]">@yield('mobile_title', 'Area do musico')</h1>
                    </div>

                    <div class="flex items-center gap-2">
                        @include('partials.internal-notifications', ['tone' => 'dark'])

                        <button
                            type="button"
                            id="admin_sidebar_toggle"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-[#2a1b1b] text-[#f3dfbd] shadow-sm transition hover:border-[#c9a15f]/40 hover:bg-[#352121] hover:text-[#fff8ed]"
                            aria-controls="admin_sidebar"
                            aria-expanded="false"
                            aria-label="Abrir menu"
                        >
                            <i class="fa-solid fa-bars text-base"></i>
                        </button>
                    </div>
                </div>
            </header>

            <main class="admin-shell relative overflow-x-hidden" id="mainContent">
                <div class="admin-main-content">
                    <div class="admin-topbar-card hidden items-center justify-between gap-4 px-5 py-4 lg:flex lg:px-6">
                        <div class="min-w-0">
                            <p class="admin-page-kicker">@yield('desktop_kicker', $isAdminMasterLayout ? 'Painel Admin Master' : 'Painel operacional')</p>
                            <p class="mt-2 truncate text-sm text-gray-500">@yield('desktop_subtitle', 'Area do musico')</p>
                        </div>

                        <div class="flex items-center gap-3">
                            @yield('header_actions')

                            @auth
                                @include('partials.internal-notifications')

                                <a href="{{ route($profileRoute) }}" class="admin-user-chip admin-user-link" aria-label="Abrir meu perfil">
                                    <div class="admin-user-chip-avatar">
                                        @if (filled(auth()->user()->foto_perfil_path))
                                            <img
                                                src="{{ auth()->user()->fotoPerfilUrl() }}"
                                                alt="Foto de {{ auth()->user()->nome }}"
                                                class="admin-avatar-image"
                                                data-fallback-logo="{{ asset('logo/final.png') }}"
                                                onerror="this.onerror=null;this.src=this.dataset.fallbackLogo;"
                                            >
                                        @else
                                            {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-gray-800">{{ auth()->user()->nome }}</div>
                                        <div class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</div>
                                    </div>
                                </a>
                            @endauth
                        </div>
                    </div>

                    @yield('content')
                    @include('admin.partials.footer')
                </div>
            </main>
        </div>
    </div>

    @include('partials.help-tour')
    <script src="{{ asset('js/admin/layout.js') }}"></script>
    @stack('scripts')
</body>
</html>
