<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('admin.partials.head')
</head>
@php
    $themePreference = auth()->user()->theme_preference ?? 'system';
@endphp
<body class="font-sans text-gray-900" data-theme-preference="{{ $themePreference }}">
    <div
        id="admin_sidebar_overlay"
        class="fixed inset-0 z-30 hidden bg-slate-950/45 backdrop-blur-[1px] md:hidden"
        aria-hidden="true"
    ></div>

    @include('admin.partials.sidebar')

    <div class="min-h-screen md:pl-72">
        <header class="admin-mobile-header sticky top-0 z-20 border-b border-white/10 backdrop-blur md:hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-[#d6ad6c]">Voz &amp; Cifra</p>
                    <h1 class="truncate text-base font-extrabold text-[#fff8ed]">@yield('mobile_title', 'Painel administrativo')</h1>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                        class="inline-flex h-11 items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-[#fff1ea] shadow-sm transition hover:bg-white/10"
                        aria-label="Sair do sistema"
                    >
                        Sair
                        </button>
                    </form>

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

        <main class="relative overflow-x-hidden px-4 py-4 sm:px-6 sm:py-6 lg:px-8" id="mainContent">
            <div class="admin-topbar-card mb-6 hidden items-center justify-between gap-4 px-5 py-4 md:flex lg:px-6">
                <div class="min-w-0">
                    <p class="admin-page-kicker">Painel Admin Master</p>
                    <p class="mt-2 truncate text-sm text-gray-500">@yield('desktop_subtitle', 'Area administrativa do sistema')</p>
                </div>

                @auth
                    <div class="admin-user-chip">
                        <div class="admin-user-chip-avatar">
                            {{ strtoupper(substr(auth()->user()->nome, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-semibold text-gray-800">{{ auth()->user()->nome }}</div>
                            <div class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</div>
                        </div>
                    </div>
                @endauth
            </div>

            @yield('content')
            @include('admin.partials.footer')
        </main>
    </div>

    <script src="{{ asset('js/admin/layout.js') }}"></script>
    @stack('scripts')
</body>
</html>
