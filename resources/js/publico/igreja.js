document.addEventListener('DOMContentLoaded', () => {
    const statusSync = document.querySelector('[data-public-status-sync]');

    const guardarPosicao = () => {
        try {
            sessionStorage.setItem(
                `public-scroll-y:${window.location.pathname}${window.location.search}`,
                String(window.scrollY)
            );
        } catch (error) {
            // A página segue normalmente se o navegador bloquear sessionStorage.
        }
    };

    try {
        const chaveScroll = `public-scroll-y:${window.location.pathname}${window.location.search}`;
        const scrollSalvo = Number(sessionStorage.getItem(chaveScroll) || 0);

        if (scrollSalvo > 0 && !window.location.hash) {
            requestAnimationFrame(() => window.scrollTo({ top: scrollSalvo, behavior: 'auto' }));
        }
    } catch (error) {
        // Sem persistência local.
    }

    window.addEventListener('pagehide', guardarPosicao);

    document.querySelectorAll('.schedule-shell').forEach((shell) => {
        const carousel = shell.querySelector('[data-schedule-carousel]');
        const previous = shell.querySelector('[data-schedule-prev]');
        const next = shell.querySelector('[data-schedule-next]');

        if (!carousel) {
            return;
        }

        const atualizarBotoes = () => {
            const podeRolar = carousel.scrollWidth > carousel.clientWidth + 2;
            const noInicio = carousel.scrollLeft <= 2;
            const noFim = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;

            if (previous) previous.disabled = !podeRolar || noInicio;
            if (next) next.disabled = !podeRolar || noFim;
        };

        const mover = (direction) => {
            const card = carousel.querySelector('.card');
            const styles = window.getComputedStyle(carousel);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '12') || 12;
            const cardWidth = card ? card.getBoundingClientRect().width + gap : Math.max(240, carousel.clientWidth * 0.82);

            carousel.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
        };

        previous?.addEventListener('click', () => mover(-1));
        next?.addEventListener('click', () => mover(1));
        carousel.addEventListener('scroll', atualizarBotoes, { passive: true });
        window.addEventListener('resize', atualizarBotoes);

        requestAnimationFrame(() => {
            const itemFoco = carousel.querySelector('[data-selected="true"], [data-schedule-focus]');

            if (itemFoco) {
                itemFoco.scrollIntoView({ behavior: 'auto', block: 'nearest', inline: 'start' });
            }

            atualizarBotoes();
        });
    });

    const historyInput = document.querySelector('[data-history-input-top]');
    const historyForm = document.querySelector('[data-history-form-top]');
    const historyItemsScript = document.querySelector('[data-history-items]');
    const historyLiveResults = document.querySelector('[data-history-live-results-top]');
    const historyLiveEmpty = document.querySelector('[data-history-live-empty-top]');
    const historyBaseUrl = historyForm?.dataset.historyBaseUrl || window.location.pathname;

    const normalizeSearch = (value) => value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const montarHistoryUrl = (item) => {
        const url = new URL(historyBaseUrl, window.location.origin);
        url.searchParams.set('celebracao', item.id);
        url.hash = 'celebracao-publica';
        return url.toString();
    };

    let historyItems = [];

    try {
        historyItems = JSON.parse(historyItemsScript?.textContent || '[]');
    } catch (error) {
        historyItems = [];
    }

    const criarHistoryLink = (item) => {
        const link = document.createElement('a');
        const badges = document.createElement('div');
        const data = document.createElement('span');
        const hora = document.createElement('span');
        const titulo = document.createElement('h3');
        const meta = document.createElement('p');

        link.href = montarHistoryUrl(item);
        link.className = 'history-link';

        badges.className = 'history-badges';
        data.className = 'schedule-date';
        data.textContent = item.data || '';
        hora.className = 'card-hour';
        hora.textContent = item.horario || '';

        titulo.className = 'card-title';
        titulo.textContent = item.titulo || 'Missa sem título';

        meta.className = 'card-meta';
        meta.textContent = [item.dia_semana, item.tempo_liturgico].filter(Boolean).join(' - ');

        badges.append(data, hora);
        link.append(badges, titulo, meta);

        return link;
    };

    const renderizarHistoricoAoDigitar = () => {
        if (!historyInput || !historyLiveResults || !historyLiveEmpty) {
            return;
        }

        const termoOriginal = historyInput.value.trim();
        const termo = normalizeSearch(termoOriginal);
        const digitos = termoOriginal.replace(/\D/g, '');
        const deveBuscar = termo.length >= 3 || digitos.length >= 2;

        historyLiveResults.hidden = true;
        historyLiveResults.replaceChildren();
        historyLiveEmpty.hidden = true;

        if (!deveBuscar) {
            return;
        }

        const encontrados = historyItems
            .filter((item) => {
                const conteudo = normalizeSearch([
                    item.titulo || '',
                    item.data || '',
                    item.dia_semana || '',
                    item.mes || '',
                    item.horario || '',
                    item.tempo_liturgico || '',
                ].join(' '));
                const dataNumerica = (item.data || '').toString().replace(/\D/g, '');

                return conteudo.includes(termo) || (digitos.length >= 2 && dataNumerica.includes(digitos));
            })
            .slice(0, 8);

        if (encontrados.length === 0) {
            historyLiveEmpty.hidden = false;
            return;
        }

        encontrados.forEach((item) => historyLiveResults.appendChild(criarHistoryLink(item)));
        historyLiveResults.hidden = false;
    };

    historyInput?.addEventListener('input', renderizarHistoricoAoDigitar);
    historyForm?.addEventListener('submit', (event) => {
        if (!historyInput || historyInput.value.trim() !== '') {
            return;
        }

        event.preventDefault();
        historyInput.focus();
    });

    let publicPlainFontLevel = 1;
    document.querySelectorAll('[data-public-plain-font]').forEach((button) => {
        button.addEventListener('click', () => {
            publicPlainFontLevel = Math.max(0, Math.min(3, publicPlainFontLevel + Number(button.dataset.publicPlainFont || 0)));
            const scale = [0.94, 1, 1.14, 1.28][publicPlainFontLevel] || 1;
            document.documentElement.style.setProperty('--public-font-scale', String(scale));
        });
    });

    document.querySelectorAll('[data-celebration-section]').forEach((section) => {
        const carousel = section.querySelector('[data-celebration-carousel]');
        const nav = section.querySelector('[data-celebration-nav]');
        const previous = section.querySelector('[data-celebration-prev]');
        const next = section.querySelector('[data-celebration-next]');
        let navTimer = null;

        if (!carousel || !nav) {
            return;
        }

        const atualizarBotoes = () => {
            const podeRolar = carousel.scrollWidth > carousel.clientWidth + 2;
            const noInicio = carousel.scrollLeft <= 2;
            const noFim = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;

            if (previous) previous.disabled = !podeRolar || noInicio;
            if (next) next.disabled = !podeRolar || noFim;
        };

        const mostrarNavegacao = () => {
            nav.classList.add('is-visible');
            window.clearTimeout(navTimer);
            navTimer = window.setTimeout(() => nav.classList.remove('is-visible'), 2200);
        };

        const mover = (direction) => {
            const card = carousel.querySelector('.celebration-item');
            const styles = window.getComputedStyle(carousel);
            const gap = Number.parseFloat(styles.columnGap || styles.gap || '16') || 16;
            const cardWidth = card ? card.getBoundingClientRect().width + gap : carousel.clientWidth;

            carousel.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
            mostrarNavegacao();
        };

        previous?.addEventListener('click', () => mover(-1));
        next?.addEventListener('click', () => mover(1));
        section.addEventListener('pointerdown', mostrarNavegacao);
        carousel.addEventListener('scroll', atualizarBotoes, { passive: true });
        window.addEventListener('resize', atualizarBotoes);
        atualizarBotoes();
    });

    const leitorFielAberto = document.querySelector('[data-celebration-section]');

    if (!statusSync || !statusSync.dataset.statusUrl || leitorFielAberto) {
        return;
    }

    let ultimaChaveEstado = [
        statusSync.dataset.state || '',
        statusSync.dataset.target || '',
        window.location.pathname,
        window.location.search,
    ].join('|');

    window.setInterval(async () => {
        try {
            const resposta = await fetch(statusSync.dataset.statusUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!resposta.ok) {
                return;
            }

            const payload = await resposta.json();
            const novaChaveEstado = [
                payload.estado || '',
                payload.countdown_iso || '',
                String(payload.missa_ref || ''),
            ].join('|');

            if (novaChaveEstado !== ultimaChaveEstado) {
                guardarPosicao();
                window.location.reload();
            }
        } catch (error) {
            console.debug('Falha ao sincronizar a página pública.', error);
        }
    }, 30000);
});
