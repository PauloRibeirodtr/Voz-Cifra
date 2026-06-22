        document.addEventListener('DOMContentLoaded', () => {
            const statusSync = document.querySelector('[data-public-status-sync]');
            const guardarPosicao = () => {
                try {
                    sessionStorage.setItem(`public-scroll-y:${window.location.pathname}${window.location.search}`, String(window.scrollY));
                } catch (error) {
                    // Sem sessionStorage, a página continua funcionando normalmente.
                }
            };

            if (document.body.dataset.publicMode !== 'musicos') {
                try {
                    const chaveScroll = `public-scroll-y:${window.location.pathname}${window.location.search}`;
                    const scrollSalvo = Number(sessionStorage.getItem(chaveScroll) || 0);
                    if (scrollSalvo > 0 && !window.location.hash) {
                        requestAnimationFrame(() => window.scrollTo({ top: scrollSalvo, behavior: 'auto' }));
                    }
                } catch (error) {
                    // Navegadores restritos podem bloquear sessionStorage.
                }
            }

            window.addEventListener('pagehide', guardarPosicao);
            document.querySelectorAll('.celebration-list').forEach((lista, indice) => {
                const chaveLista = `public-celebration-list:${window.location.pathname}${window.location.search}:${indice}`;

                try {
                    const posicaoSalva = Number(sessionStorage.getItem(chaveLista) || 0);
                    if (posicaoSalva > 0) {
                        requestAnimationFrame(() => {
                            lista.scrollLeft = posicaoSalva;
                        });
                    }
                } catch (error) {
                    // Se não puder salvar, apenas mantém o carrossel padrão.
                }

                lista.addEventListener('scroll', () => {
                    try {
                        sessionStorage.setItem(chaveLista, String(lista.scrollLeft));
                    } catch (error) {
                        // Sem persistencia local.
                    }
                }, { passive: true });
            });

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

                    if (previous) {
                        previous.disabled = !podeRolar || noInicio;
                    }

                    if (next) {
                        next.disabled = !podeRolar || noFim;
                    }
                };

                const move = (direction) => {
                    const card = carousel.querySelector('.card');
                    const estilos = window.getComputedStyle(carousel);
                    const gap = Number.parseFloat(estilos.columnGap || estilos.gap || '12') || 12;
                    const larguraCard = card ? card.getBoundingClientRect().width + gap : Math.max(240, carousel.clientWidth * 0.82);

                    carousel.scrollBy({
                        left: direction * larguraCard,
                        behavior: 'smooth',
                    });
                };

                previous?.addEventListener('click', () => move(-1));
                next?.addEventListener('click', () => move(1));
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

            document.querySelectorAll('[data-celebration-section]').forEach((section) => {
                const carousel = section.querySelector('[data-celebration-carousel]');
                const nav = section.querySelector('[data-celebration-nav]');
                const previous = section.querySelector('[data-celebration-prev]');
                const next = section.querySelector('[data-celebration-next]');

                if (!carousel || !nav) {
                    return;
                }

                let hideTimer = null;

                const mostrarNav = () => {
                    nav.classList.add('is-visible');
                    window.clearTimeout(hideTimer);
                    hideTimer = window.setTimeout(() => nav.classList.remove('is-visible'), 2200);
                };

                const atualizarNav = () => {
                    const podeRolar = carousel.scrollWidth > carousel.clientWidth + 2;
                    const noInicio = carousel.scrollLeft <= 2;
                    const noFim = carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 2;

                    if (previous) {
                        previous.disabled = !podeRolar || noInicio;
                    }

                    if (next) {
                        next.disabled = !podeRolar || noFim;
                    }
                };

                const moverMusica = (direction) => {
                    const item = carousel.querySelector('[data-public-song]');
                    const estilos = window.getComputedStyle(carousel);
                    const gap = Number.parseFloat(estilos.columnGap || estilos.gap || '16') || 16;
                    const larguraItem = item ? item.getBoundingClientRect().width + gap : carousel.clientWidth;

                    carousel.scrollBy({
                        left: direction * larguraItem,
                        behavior: 'smooth',
                    });
                    mostrarNav();
                };

                previous?.addEventListener('click', () => moverMusica(-1));
                next?.addEventListener('click', () => moverMusica(1));
                carousel.addEventListener('scroll', () => {
                    atualizarNav();
                    mostrarNav();
                }, { passive: true });
                carousel.addEventListener('pointerdown', mostrarNav, { passive: true });
                section.addEventListener('pointerdown', mostrarNav, { passive: true });
                window.addEventListener('resize', atualizarNav);
                atualizarNav();
            });

            const historyForm = document.querySelector('[data-history-form]');
            const historyInput = document.querySelector('[data-history-input]');
            const historyItemsScript = document.querySelector('[data-history-items]');
            const historyLiveResults = document.querySelector('[data-history-live-results]');
            const historyLiveEmpty = document.querySelector('[data-history-live-empty]');
            const historyLiveResultsTop = document.querySelector('[data-history-live-results-top]');
            const historyLiveEmptyTop = document.querySelector('[data-history-live-empty-top]');
            const historyServerResults = document.querySelector('[data-history-server-results]');
            const historyBaseUrl = historyForm?.dataset.historyBaseUrl || window.location.pathname;
            const historyOpenLabel = document.body.dataset.publicMode === 'musicos' ? 'Abrir repertório' : 'Abrir celebração';
            const historyTopIsSameElement = historyLiveResultsTop && historyLiveResultsTop === historyLiveResults;
            const historyTopEmptyIsSameElement = historyLiveEmptyTop && historyLiveEmptyTop === historyLiveEmpty;

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
                const tipo = document.createElement('span');
                const action = document.createElement('span');
                const titulo = document.createElement('h3');
                const meta = document.createElement('p');
                link.href = montarHistoryUrl(item);
                link.className = 'history-link';

                badges.className = 'history-badges';
                data.className = 'history-date';
                data.textContent = item.data || '';
                tipo.className = 'badge history-badge-muted';
                tipo.textContent = 'Histórico';
                action.className = 'badge history-badge-muted';
                action.textContent = historyOpenLabel;

                titulo.className = 'card-title';
                titulo.textContent = item.titulo || 'Missa sem título';

                meta.className = 'history-meta';
                meta.textContent = [item.dia_semana, item.horario, item.tempo_liturgico]
                    .filter(Boolean)
                    .join(' • ');

                badges.append(data, tipo, action);
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
                if (historyLiveResultsTop && !historyTopIsSameElement) {
                    historyLiveResultsTop.replaceChildren();
                    historyLiveResultsTop.hidden = true;
                }
                if (historyLiveEmptyTop && !historyTopEmptyIsSameElement) {
                    historyLiveEmptyTop.hidden = true;
                }

                if (historyServerResults) {
                    historyServerResults.hidden = deveBuscar;
                }

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
                    if (historyLiveEmptyTop && !historyTopEmptyIsSameElement) {
                        historyLiveEmptyTop.hidden = false;
                    }
                    return;
                }

                encontrados.forEach((item) => historyLiveResults.appendChild(criarHistoryLink(item)));
                historyLiveResults.hidden = false;
                if (historyLiveResultsTop && !historyTopIsSameElement) {
                    encontrados.forEach((item) => historyLiveResultsTop.appendChild(criarHistoryLink(item)));
                    historyLiveResultsTop.hidden = false;
                }
            };

            historyInput?.addEventListener('input', renderizarHistoricoAoDigitar);
            const historyInputTop = document.querySelector('[data-history-input-top]');
            const historyFormTop = document.querySelector('[data-history-form-top]');
            historyInputTop?.addEventListener('input', () => {
                if (!historyInput) {
                    return;
                }

                historyInput.value = historyInputTop.value;
                renderizarHistoricoAoDigitar();
            });
            historyFormTop?.addEventListener('submit', (event) => {
                if (!historyInputTop || historyInputTop.value.trim() !== '') {
                    return;
                }

                event.preventDefault();
                historyInputTop.focus();
            });

            let publicPlainFontLevel = 1;
            document.querySelectorAll('[data-public-plain-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    publicPlainFontLevel = Math.max(0, Math.min(3, publicPlainFontLevel + Number(botao.dataset.publicPlainFont || 0)));
                    const escala = [0.94, 1.02, 1.16, 1.3][publicPlainFontLevel] || 1.02;
                    document.documentElement.style.setProperty('--public-font-scale', String(escala));
                });
            });

            if (document.body.dataset.publicMode === 'musicos') {
                const helper = window.VozECifraChord;
                const bibliotecaAcordesScript = document.querySelector('[data-public-chord-library]');
                let bibliotecaAcordes = [];
                try {
                    bibliotecaAcordes = JSON.parse(bibliotecaAcordesScript?.textContent || '[]');
                } catch (error) {
                    bibliotecaAcordes = [];
                }
                const gruposAcorde = helper ? helper.buildChordGroups(bibliotecaAcordes) : null;
                const tooltipAcorde = document.querySelector('[data-public-chord-tooltip]');
                const tooltipNome = document.querySelector('[data-public-chord-tooltip-name]');
                const tooltipDiagrama = document.querySelector('[data-public-chord-tooltip-diagram]');
                const botaoAutoRolagem = document.querySelector('[data-public-auto-scroll]');
                const controleVelocidadeRolagem = document.querySelector('[data-public-scroll-speed]');
                const dockRolagem = document.querySelector('[data-public-scroll-dock]');
                const botaoAcordesFechar = document.querySelector('[data-public-chords-close]');
                const drawerAcordes = document.querySelector('[data-public-chords-drawer]');
                const drawerAcordesBackdrop = document.querySelector('[data-public-chords-backdrop]');
                const gradeAcordes = document.querySelector('[data-public-chords-grid]');
                const carouselMusicas = document.querySelector('[data-celebration-carousel]');
                const musicasPublicas = Array.from(document.querySelectorAll('[data-public-song]'));
                const estadoMusicas = new Map();
                let musicaSelecionada = musicasPublicas[0] || null;
                let publicAutoScrollActive = false;
                let publicAutoScrollFrame = null;
                let ultimoTempoRolagem = null;
                let restoRolagem = 0;
                const obterAlvoRolagemPublica = () => document.scrollingElement || document.documentElement || document.body;
                const pararRolagemPublica = () => {
                    if (publicAutoScrollFrame) {
                        window.cancelAnimationFrame(publicAutoScrollFrame);
                        publicAutoScrollFrame = null;
                    }

                    ultimoTempoRolagem = null;
                    restoRolagem = 0;
                    publicAutoScrollActive = false;
                    if (botaoAutoRolagem) botaoAutoRolagem.textContent = 'Rolagem';
                    dockRolagem?.classList.remove('is-running');
                };
                const executarPassoRolagem = (timestamp) => {
                    if (!publicAutoScrollActive) return;

                    const alvoRolagem = obterAlvoRolagemPublica();
                    const topoAtual = alvoRolagem.scrollTop || window.scrollY || 0;
                    const fim = topoAtual + window.innerHeight >= alvoRolagem.scrollHeight - 8;
                    if (fim) {
                        pararRolagemPublica();
                        return;
                    }

                    const velocidade = Math.max(1, Math.min(5, Number(controleVelocidadeRolagem?.value || 1)));
                    const pixelsPorSegundo = [18, 32, 52, 78, 112];
                    const delta = ultimoTempoRolagem ? Math.min(80, timestamp - ultimoTempoRolagem) : 16;
                    ultimoTempoRolagem = timestamp;
                    const deslocamento = ((pixelsPorSegundo[velocidade - 1] || pixelsPorSegundo[0]) * delta) / 1000;
                    restoRolagem += deslocamento;
                    const passo = Math.floor(restoRolagem);

                    if (passo >= 1) {
                        restoRolagem -= passo;
                        alvoRolagem.scrollTop = topoAtual + passo;
                    }

                    publicAutoScrollFrame = window.requestAnimationFrame(executarPassoRolagem);
                };
                const renderizarDiagrama = (shape) => {
                    if (!shape) {
                        return '<div>Sem desenho cadastrado.</div>';
                    }

                    const config = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
                    const stringGap = config.width / (config.numStrings - 1);
                    const fretGap = config.height / config.numFrets;
                    const baseFret = shape.baseFret || 1;
                    const positions = shape.positions || [];
                    const barres = shape.barres || [];
                    const topMarkers = shape.topMarkers || [null, null, null, null, null, null];
                    let grid = '';
                    let marks = '';

                    if (baseFret === 1) {
                        grid += `<rect x="${config.startX}" y="${config.startY - 6}" width="${config.width}" height="6" rx="2" fill="#e5e7eb" />`;
                    } else {
                        grid += `<text x="${config.startX - 10}" y="${config.startY + 25}" text-anchor="end" fill="#f5ead9" font-weight="bold" font-size="18">${baseFret}a</text>`;
                        grid += `<line x1="${config.startX}" y1="${config.startY}" x2="${config.startX + config.width}" y2="${config.startY}" stroke="#cbd5e1" stroke-width="2" />`;
                    }

                    for (let i = 1; i <= config.numFrets; i++) {
                        const y = config.startY + (i * fretGap);
                        grid += `<line x1="${config.startX}" y1="${y}" x2="${config.startX + config.width}" y2="${y}" stroke="#cbd5e1" stroke-width="2" />`;
                    }

                    for (let i = 0; i < config.numStrings; i++) {
                        const x = config.startX + (i * stringGap);
                        const thickness = 0.8 + ((5 - i) * 0.5);
                        grid += `<line x1="${x}" y1="${config.startY}" x2="${x}" y2="${config.startY + config.height}" stroke="#e2e8f0" stroke-width="${thickness}" />`;
                    }

                    topMarkers.forEach((marker, i) => {
                        const x = config.startX + (i * stringGap);
                        const y = config.startY - 15;
                        if (marker === 'muted') {
                            marks += `<text x="${x}" y="${y + 5}" fill="#ef4444" font-size="18" font-weight="900" text-anchor="middle">X</text>`;
                        } else if (marker === 'open') {
                            marks += `<circle cx="${x}" cy="${y}" r="5" stroke="#93c5fd" stroke-width="2.5" fill="none" />`;
                        }
                    });

                    barres.forEach((barre) => {
                        const y = config.startY + (barre.fret * fretGap) - (fretGap / 2);
                        const x1 = config.startX + ((6 - barre.fromString) * stringGap);
                        const x2 = config.startX + ((6 - barre.toString) * stringGap);
                        marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" stroke="#ffd99d" stroke-width="14" stroke-linecap="round" opacity="0.95" />`;
                    });

                    positions.forEach((position) => {
                        const y = config.startY + (position.fret * fretGap) - (fretGap / 2);
                        const x = config.startX + ((6 - position.string) * stringGap);
                        marks += `<circle cx="${x}" cy="${y}" r="12" fill="#ffd99d" />`;
                        if (position.finger) {
                            marks += `<text x="${x}" y="${y + 1}" fill="#160c0d" font-size="14" font-weight="900" text-anchor="middle" dominant-baseline="central">${position.finger}</text>`;
                        }
                    });

                    return `<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#3b2418" stroke="#1f130d" stroke-width="2"></rect>${grid}${marks}</svg>`;
                };

                const ativarAcorde = (nome) => {
                    if (!helper || !gruposAcorde) {
                        return null;
                    }

                    const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                    const assinaturaAtual = helper.getChordSignature(nome);

                    document.querySelectorAll('[data-acorde-hover]').forEach((elemento) => {
                        const assinaturaElemento = helper.getChordSignature(elemento.dataset.acordeHover);
                        const ativo = elemento.dataset.acordeHover === nome || (assinaturaElemento && assinaturaAtual && assinaturaElemento === assinaturaAtual);
                        elemento.classList.toggle('ativa', ativo);
                    });

                    return acorde;
                };

                const mostrarTooltipAcorde = (nome, x, y) => {
                    const acorde = ativarAcorde(nome);
                    if (!tooltipAcorde || !tooltipNome || !tooltipDiagrama || !acorde) {
                        return;
                    }

                    tooltipNome.textContent = nome;
                    tooltipDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                    tooltipAcorde.hidden = false;
                    tooltipAcorde.style.left = `${Math.max(12, Math.min(x + 14, window.innerWidth - 244))}px`;
                    tooltipAcorde.style.top = `${Math.max(y - 220, 12)}px`;
                };

                const obterEstadoMusica = (musica) => {
                    if (!musica) {
                        return { capo: 0, fontLevel: 1 };
                    }

                    const id = musica.dataset.publicSongId || '0';
                    if (!estadoMusicas.has(id)) {
                        estadoMusicas.set(id, { capo: 0, fontLevel: 1 });
                    }

                    return estadoMusicas.get(id);
                };

                const resumoCapotraste = (capo) => capo > 0 ? `Capo ${capo}` : 'Sem capotraste';
                const escalaFonte = (nivel) => [0.92, 1, 1.14, 1.28][nivel] || 1;
                const resumoFontePorNivel = (nivel) => `Fonte ${['menor', 'normal', 'maior', 'grande'][nivel] || 'normal'}`;

                const renderizarMusicaPublica = (musica) => {
                    if (!helper) {
                        return;
                    }

                    const lyrics = musica?.querySelector('[data-public-song-lyrics]');
                    if (!musica || !lyrics) {
                        return;
                    }

                    const estado = obterEstadoMusica(musica);
                    const textoComCapo = helper.transposeBracketedText(lyrics.dataset.lyrics || '', -estado.capo);
                    lyrics.innerHTML = helper.renderChordSheetHtml(textoComCapo, { chordAttribute: 'data-acorde-hover' });
                    lyrics.style.setProperty('--celebration-font-scale', String(escalaFonte(estado.fontLevel)));

                    musica.querySelectorAll('[data-public-capo-item]').forEach((badge) => {
                        const tomBase = badge.dataset.baseTom || '';
                        if (estado.capo <= 0 || !helper.isChord(tomBase)) {
                            badge.hidden = true;
                            badge.textContent = '';
                            return;
                        }

                        badge.hidden = false;
                        badge.textContent = `Capo ${estado.capo} / tocar como ${helper.transposeChord(tomBase, -estado.capo)}`;
                    });

                    musica.querySelectorAll('[data-public-song-capo-summary]').forEach((resumo) => {
                        resumo.textContent = resumoCapotraste(estado.capo);
                    });
                    musica.querySelectorAll('[data-public-song-font-summary]').forEach((resumo) => {
                        resumo.textContent = resumoFontePorNivel(estado.fontLevel);
                    });
                    musica.querySelectorAll('[data-public-song-capo]').forEach((controle) => {
                        controle.checked = Number(controle.value || 0) === estado.capo;
                    });
                };

                const renderizarCifrasPublicas = () => {
                    musicasPublicas.forEach((musica) => renderizarMusicaPublica(musica));
                };

                const renderizarGradeAcordes = () => {
                    if (!gradeAcordes || !helper || !gruposAcorde) {
                        return;
                    }

                    const acordes = new Set();
                    const musica = musicaSelecionada || musicasPublicas[0] || null;
                    const lyrics = musica?.querySelector('[data-public-song-lyrics]');
                    const estado = obterEstadoMusica(musica);

                    if (lyrics) {
                        helper.extractChordsFromBracketedText(helper.transposeBracketedText(lyrics.dataset.lyrics || '', -estado.capo))
                            .forEach((acorde) => acordes.add(acorde));

                        if (acordes.size === 0) {
                            lyrics.querySelectorAll('[data-acorde-hover], .cifra-acorde, .chord').forEach((elemento) => {
                                const nome = (elemento.dataset.acordeHover || elemento.textContent || '').trim();
                                if (nome && helper.isChord(nome)) {
                                    acordes.add(nome);
                                }
                            });
                        }
                    }

                    if (acordes.size === 0) {
                        gradeAcordes.innerHTML = '<p class="empty-copy" style="color:#5b4634;">Nenhum acorde identificado nesta música.</p>';
                        return;
                    }

                    gradeAcordes.innerHTML = Array.from(acordes).map((nome) => {
                        const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                        return `<button type="button" class="public-chord-card" data-public-chord-card="${helper.escapeHtml(nome)}"><strong>${helper.escapeHtml(nome)}</strong>${acorde ? renderizarDiagrama(acorde.shape) : '<p>Sem desenho.</p>'}</button>`;
                    }).join('');
                };

                const selecionarMusica = (musica) => {
                    if (!musica) {
                        return;
                    }

                    musicaSelecionada = musica;
                    musicasPublicas.forEach((item) => item.classList.toggle('is-selected', item === musica));
                    renderizarGradeAcordes();
                };

                const selecionarMusicaVisivel = () => {
                    if (!carouselMusicas || musicasPublicas.length === 0) {
                        selecionarMusica(musicaSelecionada || musicasPublicas[0]);
                        return;
                    }

                    const centro = carouselMusicas.scrollLeft + (carouselMusicas.clientWidth / 2);
                    const maisProxima = musicasPublicas.reduce((melhor, musica) => {
                        const centroMusica = musica.offsetLeft + (musica.offsetWidth / 2);
                        const distancia = Math.abs(centroMusica - centro);

                        if (!melhor || distancia < melhor.distancia) {
                            return { musica, distancia };
                        }

                        return melhor;
                    }, null);

                    selecionarMusica(maisProxima?.musica || musicasPublicas[0]);
                };

                musicasPublicas.forEach((musica) => {
                    musica.addEventListener('focusin', () => {
                        selecionarMusica(musica);
                    });

                    musica.querySelectorAll('[data-public-song-font]').forEach((botao) => {
                        botao.addEventListener('click', () => {
                            selecionarMusica(musica);
                            const estado = obterEstadoMusica(musica);
                            estado.fontLevel = Math.max(0, Math.min(3, estado.fontLevel + Number(botao.dataset.publicSongFont || 0)));
                            renderizarMusicaPublica(musica);
                        });
                    });

                    musica.querySelector('[data-public-song-capo-toggle]')?.addEventListener('click', () => {
                        selecionarMusica(musica);
                        const painel = musica.querySelector('[data-public-song-capo-panel]');
                        if (painel) {
                            painel.hidden = !painel.hidden;
                        }
                    });

                    musica.querySelectorAll('[data-public-song-capo]').forEach((controle) => {
                        controle.addEventListener('change', () => {
                            selecionarMusica(musica);
                            const estado = obterEstadoMusica(musica);
                            estado.capo = Math.max(0, Math.min(11, Number(controle.value || 0)));
                            renderizarMusicaPublica(musica);
                            renderizarGradeAcordes();
                        });
                    });

                    musica.querySelector('[data-public-chords-open]')?.addEventListener('click', () => {
                        selecionarMusica(musica);
                        renderizarGradeAcordes();
                        if (drawerAcordes) drawerAcordes.hidden = false;
                        if (drawerAcordesBackdrop) drawerAcordesBackdrop.hidden = false;
                    });

                    musica.addEventListener('click', (event) => {
                        if (event.target.closest('[data-acorde-hover]')) {
                            selecionarMusica(musica);
                        }
                    });
                });

                document.querySelectorAll('[data-public-active-song-font]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        const musica = musicaSelecionada || musicasPublicas[0] || null;

                        if (!musica) {
                            return;
                        }

                        const estado = obterEstadoMusica(musica);
                        estado.fontLevel = Math.max(0, Math.min(3, estado.fontLevel + Number(botao.dataset.publicActiveSongFont || 0)));
                        renderizarMusicaPublica(musica);
                    });
                });

                botaoAutoRolagem?.addEventListener('click', () => {
                    if (publicAutoScrollActive) {
                        pararRolagemPublica();
                        return;
                    }

                    publicAutoScrollActive = true;
                    botaoAutoRolagem.textContent = 'Pausar';
                    dockRolagem?.classList.add('is-running');
                    publicAutoScrollFrame = window.requestAnimationFrame(executarPassoRolagem);
                });

                const fecharDrawerAcordes = () => {
                    if (drawerAcordes) drawerAcordes.hidden = true;
                    if (drawerAcordesBackdrop) drawerAcordesBackdrop.hidden = true;
                };
                botaoAcordesFechar?.addEventListener('click', fecharDrawerAcordes);
                drawerAcordesBackdrop?.addEventListener('click', fecharDrawerAcordes);
                gradeAcordes?.addEventListener('click', (event) => {
                    const card = event.target.closest('[data-public-chord-card]');
                    if (card) {
                        ativarAcorde(card.dataset.publicChordCard);
                    }
                });

                renderizarCifrasPublicas();
                renderizarGradeAcordes();
                selecionarMusicaVisivel();
                carouselMusicas?.addEventListener('scroll', selecionarMusicaVisivel, { passive: true });
                window.addEventListener('resize', selecionarMusicaVisivel);

                document.addEventListener('mouseover', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (acorde) {
                        mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
                    }
                });

                document.addEventListener('mousemove', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (acorde) {
                        mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
                    }
                });

                document.addEventListener('mouseout', (event) => {
                    if (event.target.closest('[data-acorde-hover]') && tooltipAcorde) {
                        tooltipAcorde.hidden = true;
                    }
                });

                document.addEventListener('click', (event) => {
                    const acorde = event.target.closest('[data-acorde-hover]');
                    if (!acorde) {
                        if (tooltipAcorde) {
                            tooltipAcorde.hidden = true;
                        }
                        return;
                    }

                    const rect = acorde.getBoundingClientRect();
                    mostrarTooltipAcorde(acorde.dataset.acordeHover, rect.left, rect.top);
                });

            }

            const leitorMusicoAberto = document.querySelector('[data-celebration-section]');
            if (!statusSync || !statusSync.dataset.statusUrl || leitorMusicoAberto) {
                return;
            }

            let ultimaChaveEstado = null;

            window.setInterval(async () => {
                try {
                    const resposta = await fetch(statusSync.dataset.statusUrl, {
                        headers: {
                            'Accept': 'application/json',
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

                    if (ultimaChaveEstado === null) {
                        ultimaChaveEstado = novaChaveEstado;
                        return;
                    }

                    if (novaChaveEstado !== ultimaChaveEstado) {
                        guardarPosicao();
                        window.location.reload();
                    }
                } catch (error) {
                    console.debug('Falha ao sincronizar a página pública.', error);
                }
            }, 30000);
        });


