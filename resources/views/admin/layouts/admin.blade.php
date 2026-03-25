<!DOCTYPE html>
<html lang="pt-BR">
<head>
    @include('admin.partials.head')
</head>
<body class="bg-gray-50 font-sans text-gray-900">
    <div
        id="admin_sidebar_overlay"
        class="fixed inset-0 z-30 hidden bg-slate-950/45 backdrop-blur-[1px] md:hidden"
        aria-hidden="true"
    ></div>

    @include('admin.partials.sidebar')

    <div class="min-h-screen md:pl-64">
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur md:hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-700">Voz &amp; Cifra</p>
                    <h1 class="truncate text-base font-extrabold text-slate-900">@yield('mobile_title', 'Painel administrativo')</h1>
                </div>

                <button
                    type="button"
                    id="admin_sidebar_toggle"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-gray-200 bg-white text-slate-700 shadow-sm transition hover:border-green-200 hover:bg-green-50 hover:text-green-700"
                    aria-controls="admin_sidebar"
                    aria-expanded="false"
                    aria-label="Abrir menu"
                >
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
            </div>
        </header>

        <main class="relative overflow-x-hidden px-4 py-4 sm:px-6 sm:py-6 lg:px-8" id="mainContent">
            @yield('content')
            @include('admin.partials.footer')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const body = document.body;
            const sidebar = document.getElementById('admin_sidebar');
            const toggle = document.getElementById('admin_sidebar_toggle');
            const overlay = document.getElementById('admin_sidebar_overlay');
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

</body>
</html>
