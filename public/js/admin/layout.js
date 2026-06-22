document.addEventListener('DOMContentLoaded', () => {
    const storageKey = 'voz-cifra-theme';
    const savedPreference = (() => {
        try { return window.localStorage.getItem(storageKey); } catch (error) { return null; }
    })();
    const allowedThemes = ['system', 'light', 'dark'];
    const candidate = savedPreference || document.body.dataset.themePreference || 'system';
    let preference = allowedThemes.includes(candidate) ? candidate : 'system';
    const mediaScheme = window.matchMedia('(prefers-color-scheme: dark)');

    const closeThemeMenus = (except = null) => {
        document.querySelectorAll('[data-theme-menu]').forEach((menu) => {
            if (menu === except) return;
            menu.hidden = true;
            menu.closest('.admin-theme-control')?.querySelector('[data-theme-menu-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const aplicarTema = () => {
        const resolved = preference === 'system' ? (mediaScheme.matches ? 'dark' : 'light') : preference;
        document.body.classList.toggle('theme-dark', resolved === 'dark');
        document.body.classList.toggle('theme-light', resolved !== 'dark');
        document.documentElement.classList.toggle('theme-dark', resolved === 'dark');
        document.documentElement.classList.toggle('theme-light', resolved !== 'dark');

        document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
            const labels = { system: 'Tema automático', light: 'Tema claro', dark: 'Tema escuro' };
            const icons = { system: 'fa-desktop', light: 'fa-sun', dark: 'fa-moon' };
            const icon = button.querySelector('[data-theme-toggle-icon]');
            button.title = `${labels[preference]} — abrir opções`;
            button.setAttribute('aria-label', `${labels[preference]}. Abrir opções de tema`);
            if (icon) {
                icon.classList.remove('fa-desktop', 'fa-sun', 'fa-moon');
                icon.classList.add(icons[preference]);
            }
        });

        document.querySelectorAll('[data-theme-option]').forEach((option) => {
            const selected = option.dataset.themeOption === preference;
            option.setAttribute('aria-checked', selected ? 'true' : 'false');
            option.classList.toggle('is-active', selected);
        });
    };

    aplicarTema();
    mediaScheme.addEventListener?.('change', aplicarTema);

    document.querySelectorAll('[data-theme-menu-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const menu = button.closest('.admin-theme-control')?.querySelector('[data-theme-menu]');
            if (!menu) return;
            const willOpen = menu.hidden;
            closeThemeMenus(menu);
            menu.hidden = !willOpen;
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    document.querySelectorAll('[data-theme-option]').forEach((option) => {
        option.addEventListener('click', () => {
            preference = allowedThemes.includes(option.dataset.themeOption) ? option.dataset.themeOption : 'system';
            try {
                if (preference === 'system') window.localStorage.removeItem(storageKey);
                else window.localStorage.setItem(storageKey, preference);
            } catch (error) { /* armazenamento indisponivel */ }
            aplicarTema();
            closeThemeMenus();
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.admin-theme-control')) closeThemeMenus();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeThemeMenus();
    });

    document.querySelectorAll('form').forEach((form) => {
        const method = String(form.getAttribute('method') || 'GET').toUpperCase();
        if (method === 'GET' || form.hasAttribute('data-allow-repeat-submit')) return;

        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            window.queueMicrotask(() => {
                if (event.defaultPrevented) return;
                form.dataset.submitting = 'true';
                const buttons = Array.from(form.querySelectorAll('button[type="submit"], input[type="submit"]'));
                buttons.forEach((button) => {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                    button.classList.add('opacity-70', 'cursor-wait');
                    if (button.tagName === 'BUTTON') {
                        button.dataset.originalText = button.textContent;
                        button.textContent = 'Processando...';
                    }
                });

                window.setTimeout(() => {
                    if (!document.body.contains(form)) return;
                    form.dataset.submitting = 'false';
                    buttons.forEach((button) => {
                        button.disabled = false;
                        button.removeAttribute('aria-disabled');
                        button.classList.remove('opacity-70', 'cursor-wait');
                        if (button.dataset.originalText) button.textContent = button.dataset.originalText;
                    });
                }, 15000);
            });
        });
    });

    document.querySelectorAll('form[data-draft-form]').forEach((form) => {
        const storageKeyDraft = `voz-cifra-draft:${form.dataset.draftForm}:${window.location.pathname}`;
        let dirty = false;
        let saveTimer = null;

        const ignored = (field) => !field.name || field.name.startsWith('_') || field.disabled || field.type === 'file';
        const serialize = () => {
            const fields = {};
            Array.from(form.elements).forEach((field) => {
                if (ignored(field)) return;
                if ((field.type === 'checkbox' || field.type === 'radio') && !field.checked) return;
                const value = String(field.value ?? '');
                fields[field.name] = fields[field.name] || [];
                fields[field.name].push(value);
            });
            return fields;
        };
        let baseline = JSON.stringify(serialize());

        const saveDraft = () => {
            const fields = serialize();
            dirty = JSON.stringify(fields) !== baseline;
            if (!dirty) return;
            try {
                window.localStorage.setItem(storageKeyDraft, JSON.stringify({ savedAt: Date.now(), fields }));
            } catch (error) { /* armazenamento indisponivel */ }
        };

        const applyDraft = (fields) => {
            Array.from(form.elements).forEach((field) => {
                if (ignored(field) || !Object.hasOwn(fields, field.name)) return;
                const values = Array.isArray(fields[field.name]) ? fields[field.name] : [fields[field.name]];
                if (field.type === 'checkbox' || field.type === 'radio') {
                    field.checked = values.includes(String(field.value));
                } else if (field.multiple && field.options) {
                    Array.from(field.options).forEach((option) => { option.selected = values.includes(String(option.value)); });
                } else {
                    field.value = values[0] ?? '';
                }
                field.dispatchEvent(new Event('change', { bubbles: true }));
            });
            dirty = true;
        };

        let draft = null;
        try { draft = JSON.parse(window.localStorage.getItem(storageKeyDraft) || 'null'); } catch (error) { draft = null; }
        const draftIsRecent = draft?.savedAt && (Date.now() - Number(draft.savedAt)) < 7 * 24 * 60 * 60 * 1000;

        if (draftIsRecent && draft?.fields) {
            const banner = document.createElement('div');
            banner.className = 'draft-recovery';
            banner.innerHTML = '<span>Há um rascunho não enviado salvo neste aparelho.</span><span class="draft-recovery__actions"><button type="button" class="draft-recovery__restore">Restaurar</button><button type="button" class="draft-recovery__discard">Descartar</button></span>';
            form.prepend(banner);
            banner.querySelector('.draft-recovery__restore')?.addEventListener('click', () => {
                applyDraft(draft.fields);
                banner.remove();
            });
            banner.querySelector('.draft-recovery__discard')?.addEventListener('click', () => {
                try { window.localStorage.removeItem(storageKeyDraft); } catch (error) { /* armazenamento indisponivel */ }
                banner.remove();
            });
        }

        form.addEventListener('input', () => {
            dirty = JSON.stringify(serialize()) !== baseline;
            window.clearTimeout(saveTimer);
            saveTimer = window.setTimeout(saveDraft, 450);
        });
        form.addEventListener('change', saveDraft);
        form.addEventListener('submit', (event) => {
            window.queueMicrotask(() => {
                if (event.defaultPrevented) return;
                dirty = false;
                try { window.localStorage.removeItem(storageKeyDraft); } catch (error) { /* armazenamento indisponivel */ }
            });
        });
        window.addEventListener('beforeunload', (event) => {
            if (!dirty || form.dataset.submitting === 'true') return;
            event.preventDefault();
            event.returnValue = '';
        });
    });

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
