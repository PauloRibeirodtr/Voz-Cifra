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
    const sidebar = document.getElementById('admin_sidebar');
    const toggle = document.getElementById('admin_sidebar_toggle');
    const closeButton = document.getElementById('admin_sidebar_close');
    const overlay = document.getElementById('admin_sidebar_overlay');
    const mediaQuery = window.matchMedia('(min-width: 1024px)');

    if (!sidebar || !toggle || !overlay) {
        return;
    }

    const abrirMenu = () => {
        sidebar.classList.add('is-open');
        overlay.classList.remove('hidden');
        body.classList.add('overflow-hidden', 'admin-sidebar-open');
        toggle.setAttribute('aria-expanded', 'true');
        sidebar.setAttribute('aria-hidden', 'false');
    };

    const fecharMenu = () => {
        sidebar.classList.remove('is-open');
        overlay.classList.add('hidden');
        body.classList.remove('overflow-hidden', 'admin-sidebar-open');
        toggle.setAttribute('aria-expanded', 'false');
        sidebar.setAttribute('aria-hidden', mediaQuery.matches ? 'false' : 'true');
    };

    toggle.addEventListener('click', () => {
        if (!sidebar.classList.contains('is-open')) {
            abrirMenu();
            return;
        }

        fecharMenu();
    });

    overlay.addEventListener('click', fecharMenu);
    closeButton?.addEventListener('click', fecharMenu);

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (!mediaQuery.matches) {
                fecharMenu();
            }
        });
    });

    const sincronizarLayout = () => {
        if (mediaQuery.matches) {
            sidebar.classList.remove('is-open');
            overlay.classList.add('hidden');
            body.classList.remove('overflow-hidden', 'admin-sidebar-open');
            toggle.setAttribute('aria-expanded', 'false');
            sidebar.setAttribute('aria-hidden', 'false');
            return;
        }

        sidebar.classList.remove('is-open');
        sidebar.setAttribute('aria-hidden', 'true');
        body.classList.remove('admin-sidebar-open');
    };

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !mediaQuery.matches && sidebar.classList.contains('is-open')) {
            fecharMenu();
        }
    });

    sincronizarLayout();
    mediaQuery.addEventListener('change', sincronizarLayout);
});
