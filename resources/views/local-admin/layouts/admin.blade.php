<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('admin.partials.head')
</head>
@php
    $themePreference = auth()->user()->theme_preference ?? 'system';
@endphp
<body class="bg-gray-50 font-sans text-gray-900" data-theme-preference="{{ $themePreference }}">
    <div
        id="local_sidebar_overlay"
        class="fixed inset-0 z-30 hidden bg-slate-950/45 backdrop-blur-[1px] md:hidden"
        aria-hidden="true"
    ></div>

    @include('local-admin.partials.sidebar')

    <div class="min-h-screen md:pl-64">
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur md:hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-700">Voz &amp; Cifra</p>
                    <h1 class="truncate text-base font-extrabold text-slate-900">@yield('mobile_title', 'Painel da igreja')</h1>
                </div>

                <div class="flex items-center gap-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                            aria-label="Sair do sistema"
                        >
                            Sair
                        </button>
                    </form>

                    <button
                        type="button"
                        id="local_sidebar_toggle"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700"
                        aria-controls="local_sidebar"
                        aria-expanded="false"
                        aria-label="Abrir menu"
                    >
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                </div>
            </div>
        </header>

        <main class="relative overflow-x-hidden px-4 py-4 sm:px-6 sm:py-6 lg:px-8" id="mainContent">
            <div class="mb-6 hidden items-center justify-between gap-4 md:flex">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-700">Voz &amp; Cifra</p>
                    <p class="truncate text-sm text-slate-500">@yield('desktop_subtitle', 'Area administrativa da igreja')</p>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-red-200 hover:bg-red-50 hover:text-red-700"
                    >
                        Sair
                    </button>
                </form>
            </div>

            @yield('content')
            @include('admin.partials.footer')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preference = document.body.dataset.themePreference || 'system';
            const mediaScheme = window.matchMedia('(prefers-color-scheme: dark)');
            const aplicarTema = () => {
                const resolved = preference === 'system' ? (mediaScheme.matches ? 'dark' : 'light') : preference;
                document.body.classList.toggle('theme-dark', resolved === 'dark');
                document.body.classList.toggle('theme-light', resolved !== 'dark');
                document.documentElement.classList.toggle('theme-dark', resolved === 'dark');
                document.documentElement.classList.toggle('theme-light', resolved !== 'dark');
            };

            aplicarTema();
            mediaScheme.addEventListener?.('change', aplicarTema);

            const body = document.body;
            const sidebar = document.getElementById('local_sidebar');
            const toggle = document.getElementById('local_sidebar_toggle');
            const overlay = document.getElementById('local_sidebar_overlay');
            const mediaQuery = window.matchMedia('(min-width: 768px)');

            if (!sidebar || !toggle || !overlay) {
                return;
            }

            const abrirMenu = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                body.classList.add('overflow-hidden');
                toggle.setAttribute('aria-expanded', 'true');
            };

            const fecharMenu = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                body.classList.remove('overflow-hidden');
                toggle.setAttribute('aria-expanded', 'false');
            };

            toggle.addEventListener('click', () => {
                if (sidebar.classList.contains('-translate-x-full')) {
                    abrirMenu();
                    return;
                }

                fecharMenu();
            });

            overlay.addEventListener('click', fecharMenu);

            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (!mediaQuery.matches) {
                        fecharMenu();
                    }
                });
            });

            const sincronizarLayout = () => {
                if (mediaQuery.matches) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    body.classList.remove('overflow-hidden');
                    toggle.setAttribute('aria-expanded', 'false');
                    return;
                }

                sidebar.classList.add('-translate-x-full');
            };

            sincronizarLayout();
            mediaQuery.addEventListener('change', sincronizarLayout);
        });
    </script>

    @stack('scripts')
</body>
</html>
