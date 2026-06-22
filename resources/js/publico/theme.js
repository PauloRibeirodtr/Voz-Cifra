const publicThemeKey = 'voz-cifra-public-theme';

const readTheme = () => {
    try { return window.localStorage.getItem(publicThemeKey); } catch (error) { return null; }
};

const resolveTheme = () => readTheme()
    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');

const applyTheme = (theme = resolveTheme()) => {
    const dark = theme === 'dark';
    document.documentElement.classList.toggle('public-theme-dark', dark);
    document.body?.classList.toggle('public-theme-dark', dark);

    document.querySelectorAll('[data-public-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', dark ? 'true' : 'false');
        button.setAttribute('aria-label', dark ? 'Usar modo claro' : 'Usar modo escuro');
        button.textContent = dark ? '☀' : '☾';
    });
};

applyTheme();

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();

    if (!document.querySelector('[data-public-theme-toggle]')) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'public-theme-toggle';
        button.dataset.publicThemeToggle = '';
        document.body.appendChild(button);
    }

    applyTheme();
    document.querySelectorAll('[data-public-theme-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = document.body.classList.contains('public-theme-dark') ? 'light' : 'dark';
            try { window.localStorage.setItem(publicThemeKey, next); } catch (error) { /* armazenamento indisponivel */ }
            applyTheme(next);
        });
    });
});
