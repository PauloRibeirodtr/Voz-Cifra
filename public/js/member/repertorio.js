document.addEventListener('DOMContentLoaded', () => {
    const helper = window.VozECifraChord;

    if (helper) {
        const estados = new WeakMap();
        const fonteConfig = {
            0: 0.92,
            1: 1,
            2: 1.16,
            3: 1.32,
        };
        const lerJson = (id, fallback = '') => {
            const fonte = document.getElementById(id);

            if (!fonte) {
                return fallback;
            }

            try {
                return JSON.parse(fonte.textContent || 'null') ?? fallback;
            } catch (error) {
                console.error(error);
                return fallback;
            }
        };

        const renderizarItem = (container) => {
            const texto = lerJson(container.dataset.textoCifraId, '');
            const tomBase = container.dataset.tomBase || '';
            const palco = container.closest('.cifra-palco');
            const listaAcordes = palco?.querySelector('[data-repertorio-acordes]');
            const tomLabel = palco?.querySelector('[data-item-tom-label]');
            const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
            const textoTransposto = helper.transposeBracketedText(texto, estado.transposicao - estado.capo);
            const tomAtual = tomBase && helper.isChord(tomBase)
                ? helper.transposeChord(tomBase, estado.transposicao)
                : (tomBase || 'nao informado');

            container.innerHTML = helper.renderChordSheetHtml(textoTransposto, {
                chordAttribute: 'data-acorde-hover',
            });
            container.style.setProperty('--escala-fonte', String(fonteConfig[estado.fonte] || 1));

            if (tomLabel) {
                tomLabel.textContent = estado.capo > 0 ? `Tom ${tomAtual} / capo ${estado.capo}` : `Tom ${tomAtual}`;
            }

            const acordes = helper.extractChordsFromBracketedText(textoTransposto);

            if (listaAcordes && acordes.length > 0) {
                listaAcordes.innerHTML = acordes
                    .map((acorde) => `<span class="acorde-chip">${helper.escapeHtml(acorde)}</span>`)
                    .join('');
            } else if (listaAcordes) {
                listaAcordes.innerHTML = '<span class="text-xs text-slate-400">Nenhum acorde identificado nesta cifra.</span>';
            }
        };

        document.querySelectorAll('[data-repertorio-cifra]').forEach((container) => {
            estados.set(container, { transposicao: 0, capo: 0, fonte: 1 });
            renderizarItem(container);

            const palco = container.closest('.cifra-palco');
            palco?.querySelectorAll('[data-item-transpose]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
                    estado.transposicao += Number(botao.dataset.itemTranspose || 0);
                    estados.set(container, estado);
                    renderizarItem(container);
                });
            });

            palco?.querySelector('[data-item-transpose-reset]')?.addEventListener('click', () => {
                const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
                estado.transposicao = 0;
                estados.set(container, estado);
                renderizarItem(container);
            });

            palco?.querySelector('[data-item-capo]')?.addEventListener('change', (event) => {
                const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
                estado.capo = Math.max(0, Math.min(11, Number(event.target.value || 0)));
                estados.set(container, estado);
                renderizarItem(container);
            });

            palco?.querySelectorAll('[data-item-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
                    estado.fonte = Math.max(0, Math.min(3, estado.fonte + Number(botao.dataset.itemFont || 0)));
                    estados.set(container, estado);
                    renderizarItem(container);
                });
            });

            palco?.querySelector('[data-item-font-reset]')?.addEventListener('click', () => {
                const estado = estados.get(container) || { transposicao: 0, capo: 0, fonte: 1 };
                estado.fonte = 1;
                estados.set(container, estado);
                renderizarItem(container);
            });
        });
    }

    document.querySelectorAll('[data-musica-item]').forEach((details) => {
        details.addEventListener('toggle', () => {
            const itemId = details.id.replace('repertorio-item-', '');
            document.querySelectorAll('[data-repertorio-flow-link]').forEach((link) => {
                link.classList.toggle('is-active', details.open && link.dataset.repertorioFlowLink === itemId);
            });

            if (!details.open) {
                return;
            }

            document.querySelectorAll('[data-musica-item][open]').forEach((outro) => {
                if (outro !== details) {
                    outro.open = false;
                }
            });
        });
    });

    const toggle = document.querySelector('[data-scroll-toggle]');
    const velocidade = document.querySelector('[data-scroll-speed]');
    const velocidadeLabel = document.querySelector('[data-scroll-speed-label]');
    const topo = document.querySelector('[data-scroll-top]');
    let autoScrollFrame = null;
    let autoScrollActive = false;
    let autoScrollSpeed = Number.parseFloat(velocidade?.value || '1');
    let autoScrollLastTime = null;
    let autoScrollRemainder = 0;

    const scrollTarget = () => document.scrollingElement || document.documentElement;

    const parar = () => {
        if (autoScrollFrame) {
            window.cancelAnimationFrame(autoScrollFrame);
            autoScrollFrame = null;
        }

        autoScrollActive = false;
        autoScrollLastTime = null;
        autoScrollRemainder = 0;

        if (toggle) {
            toggle.textContent = 'Rolagem';
            toggle.classList.remove('bg-amber-600', 'hover:bg-amber-700');
            toggle.classList.add('bg-emerald-700', 'hover:bg-emerald-800');
        }
    };

    const tick = (timestamp) => {
        const alvo = scrollTarget();
        if (!alvo) {
            parar();
            return;
        }

        const maxScroll = document.body.scrollHeight - window.innerHeight;
        if (alvo.scrollTop >= maxScroll - 4) {
            parar();
            return;
        }

        const delta = autoScrollLastTime ? Math.min(80, timestamp - autoScrollLastTime) : 16;
        autoScrollLastTime = timestamp;
        autoScrollRemainder += (Math.max(0.2, autoScrollSpeed) * 18 * delta) / 1000;
        const distance = Math.floor(autoScrollRemainder);

        if (distance > 0) {
            autoScrollRemainder -= distance;
            alvo.scrollTop += distance;
        }
        autoScrollFrame = window.requestAnimationFrame(tick);
    };

    const iniciar = () => {
        parar();

        if (toggle) {
            toggle.textContent = 'Pausar';
            toggle.classList.remove('bg-emerald-700', 'hover:bg-emerald-800');
            toggle.classList.add('bg-amber-600', 'hover:bg-amber-700');
        }

        autoScrollActive = true;
        autoScrollFrame = window.requestAnimationFrame(tick);
    };

    toggle?.addEventListener('click', () => {
        autoScrollActive ? parar() : iniciar();
    });

    velocidade?.addEventListener('input', () => {
        autoScrollSpeed = Number.parseFloat(velocidade.value) || 1;
        velocidadeLabel.textContent = `${Number.parseFloat(velocidade.value).toFixed(1)}x`;
    });

    topo?.addEventListener('click', () => {
        parar();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    window.addEventListener('wheel', () => {
        if (autoScrollActive) parar();
    }, { passive: true });
    window.addEventListener('touchstart', () => {
        if (autoScrollActive) parar();
    }, { passive: true });
    window.addEventListener('keydown', (event) => {
        if (autoScrollActive && ['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' '].includes(event.key)) {
            parar();
        }
    });
});
