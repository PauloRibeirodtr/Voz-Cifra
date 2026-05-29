@extends('member.layouts.app')

@section('title', ($versaoMusical->titulo ?: 'Versao musical') . ' | Voz & Cifra')
@section('mobile_title', 'Estudo da cifra')
@section('desktop_subtitle', 'Leitura musical simples para estudo')

@section('header_actions')
@endsection

@php
    $tonsMusicais = config('musical.tons', []);
    $pedidoTomPendente = $itemMissa
        ? $itemMissa->solicitacoesMudancaTom
            ->where('usuario_id', auth()->id())
            ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE)
            ->first()
        : null;
@endphp

@push('scripts')
    @include('partials.chord-transposer-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const helper = window.VozECifraChord;
            const preview = document.getElementById('letra_com_cifras_preview');
            const previewContainer = document.getElementById('preview_musico_container');
            const tomBadge = document.getElementById('tom_atual_badge');
            const tomIndicadores = Array.from(document.querySelectorAll('[data-tom-indicator]'));
            const textoOriginal = @json($textoCifraExibicao, JSON_UNESCAPED_UNICODE);
            const tomBase = @json($tomExibicao);
            const bibliotecaAcordes = @json($bibliotecaAcordes);
            const bpmInicial = Number(@json($versaoMusical->bpm ?: 72));
            const gruposAcorde = helper ? helper.buildChordGroups(bibliotecaAcordes) : null;
            const painelDiagrama = document.getElementById('painel_diagrama_acorde');
            const nomeAcordeAtivo = document.getElementById('nome_acorde_ativo');
            const descricaoAcordeAtivo = document.getElementById('descricao_acorde_ativo');
            const variacoesAcorde = document.getElementById('variacoes_acorde');
            const tooltipAcorde = document.getElementById('tooltip_acorde');
            const tooltipAcordeNome = document.getElementById('tooltip_acorde_nome');
            const tooltipAcordeDiagrama = document.getElementById('tooltip_acorde_diagrama');
            const listaAcordes = document.getElementById('lista_acordes_transpostos');
            const botaoRolagem = document.getElementById('toggle_autorrolagem');
            const controleVelocidade = document.getElementById('velocidade_rolagem');
            const valorVelocidade = document.getElementById('valor_velocidade');
            const botaoMetronomo = document.getElementById('toggle_metronomo');
            const controleBpm = document.getElementById('controle_bpm');
            const botaoDiminuirBpm = document.getElementById('diminuir_bpm');
            const botaoAumentarBpm = document.getElementById('aumentar_bpm');
            const rotuloBpm = document.getElementById('rotulo_bpm');
            const controleVolumeMetronomo = document.getElementById('volume_metronomo');
            const indicadoresFonte = Array.from(document.querySelectorAll('[data-font-indicator]'));
            const controlesCapotraste = Array.from(document.querySelectorAll('[data-capo-control]'));
            const capoBadge = document.getElementById('capotraste_badge');
            const capoIndicadores = Array.from(document.querySelectorAll('[data-capo-indicator]'));
            const studyToast = document.getElementById('study_toast');
            const modalPlaylist = document.getElementById('playlist_modal');
            const modalPlaylistBackdrop = document.getElementById('playlist_modal_backdrop');
            const abrirModalPlaylist = document.getElementById('abrir_modal_playlist');
            const fecharModalPlaylist = document.getElementById('fechar_modal_playlist');
            const drawerAcordes = document.getElementById('acordes_drawer');
            const abrirDrawerAcordes = Array.from(document.querySelectorAll('[data-open-chords]'));
            const fecharDrawerAcordes = document.getElementById('fechar_acordes_drawer');
            const botoesAutoRolagemRapida = Array.from(document.querySelectorAll('[data-toggle-autoscroll-quick]'));
            const botoesCapoPopover = Array.from(document.querySelectorAll('[data-toggle-capo-popover]'));
            const capoPopover = document.getElementById('capo_popover');
            const fecharCapoPopover = document.getElementById('fechar_capo_popover');
            const botoesVideoApoio = Array.from(document.querySelectorAll('[data-scroll-video]'));
            let transposicaoAtual = 0;
            let capotrasteAtual = 0;
            let fonteNivel = 1;
            let rolagemAtiva = false;
            let intervaloRolagem = null;
            let intervaloMetronomo = null;
            let contextoAudio = null;
            let bpmAtual = bpmInicial;
            let rolagemProgramatica = false;
            let toastTimeout = null;

            const velocidadeConfig = {
                1: { label: 'Lenta', passo: 0.9 },
                2: { label: 'Normal', passo: 1.8 },
                3: { label: 'Rapida', passo: 3.2 },
            };
            const fonteConfig = {
                0: { label: 'pequena', escala: 0.92 },
                1: { label: 'normal', escala: 1 },
                2: { label: 'grande', escala: 1.18 },
                3: { label: 'muito grande', escala: 1.36 },
            };
            const volumeMetronomo = {
                baixo: 0.12,
                medio: 0.24,
                alto: 0.38,
            };

            if (!preview || !helper || !previewContainer) {
                return;
            }

            const abrirModal = (modal, backdrop) => {
                modal?.classList.remove('hidden');
                backdrop?.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            };

            const fecharModal = (modal, backdrop) => {
                modal?.classList.add('hidden');
                backdrop?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            };

            const abrirDrawer = (drawer) => {
                drawer?.classList.remove('hidden');
                drawer?.setAttribute('aria-hidden', 'false');
            };

            const fecharDrawer = (drawer) => {
                drawer?.classList.add('hidden');
                drawer?.setAttribute('aria-hidden', 'true');
            };

            const alternarPopoverCapo = (mostrar = null) => {
                if (!capoPopover) {
                    return;
                }

                const deveMostrar = mostrar === null ? capoPopover.classList.contains('hidden') : mostrar;
                capoPopover.classList.toggle('hidden', !deveMostrar);
            };

            const mostrarToast = (mensagem) => {
                if (!studyToast) {
                    return;
                }

                studyToast.textContent = mensagem;
                studyToast.classList.add('is-visible');

                if (toastTimeout) {
                    window.clearTimeout(toastTimeout);
                }

                toastTimeout = window.setTimeout(() => {
                    studyToast.classList.remove('is-visible');
                }, 2200);
            };

            const renderizarDiagrama = (shape) => {
                if (!shape) return '<div class="text-sm text-slate-500">Sem desenho disponivel.</div>';
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
                    grid += `<text x="${config.startX - 10}" y="${config.startY + 25}" text-anchor="end" fill="#94a3b8" font-weight="bold" font-size="18">${baseFret}a</text>`;
                    grid += `<line x1="${config.startX}" y1="${config.startY}" x2="${config.startX + config.width}" y2="${config.startY}" stroke="#94a3b8" stroke-width="2" />`;
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
                    if (marker === 'muted') marks += `<text x="${x}" y="${y + 5}" fill="#ef4444" font-size="18" font-weight="900" text-anchor="middle">X</text>`;
                    if (marker === 'open') marks += `<circle cx="${x}" cy="${y}" r="5" stroke="#2563eb" stroke-width="2.5" fill="none" />`;
                });

                barres.forEach((barre) => {
                    const y = config.startY + (barre.fret * fretGap) - (fretGap / 2);
                    const x1 = config.startX + ((6 - barre.fromString) * stringGap);
                    const x2 = config.startX + ((6 - barre.toString) * stringGap);
                    marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" stroke="#f97316" stroke-width="14" stroke-linecap="round" opacity="0.95" />`;
                });

                positions.forEach((position) => {
                    const y = config.startY + (position.fret * fretGap) - (fretGap / 2);
                    const x = config.startX + ((6 - position.string) * stringGap);
                    marks += `<circle cx="${x}" cy="${y}" r="12" fill="#f97316" />`;
                    if (position.finger) {
                        marks += `<text x="${x}" y="${y + 1}" fill="white" font-size="14" font-weight="800" text-anchor="middle" dominant-baseline="central">${position.finger}</text>`;
                    }
                });

                return `<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#3b2418" stroke="#1f130d" stroke-width="2"></rect>${grid}${marks}</svg>`;
            };

            const preencherVariacoes = (nome, indiceAtivo = 0) => {
                const variacoes = helper.getChordMatches(gruposAcorde, nome);
                if (!variacoesAcorde) return;
                if (variacoes.length <= 1) {
                    variacoesAcorde.innerHTML = '';
                    return;
                }

                variacoesAcorde.innerHTML = variacoes.map((variacao, indice) => `<button type="button" class="study-button px-3 py-2 text-xs ${indice === indiceAtivo ? 'study-button-primary' : ''}" data-variacao-acorde="${helper.escapeHtml(nome)}" data-variacao-indice="${indice}">${variacao.descricao ? helper.escapeHtml(variacao.descricao) : `Variacao ${indice + 1}`}</button>`).join('');
                variacoesAcorde.querySelectorAll('[data-variacao-acorde]').forEach((botao) => {
                    botao.addEventListener('click', () => ativarAcorde(nome, Number(botao.dataset.variacaoIndice)));
                });
            };

            const ativarAcorde = (nome, indice = 0) => {
                const acorde = helper.getChordMatches(gruposAcorde, nome)[indice] || null;
                const assinaturaAtual = helper.getChordSignature(nome);
                document.querySelectorAll('[data-acorde-hover], [data-acorde-card]').forEach((elemento) => {
                    const valorElemento = elemento.dataset.acordeHover || elemento.dataset.acordeCard;
                    const assinaturaElemento = helper.getChordSignature(valorElemento);
                    const ativo = valorElemento === nome || (assinaturaElemento && assinaturaAtual && assinaturaElemento === assinaturaAtual);
                    elemento.classList.toggle('ring-2', ativo);
                    elemento.classList.toggle('ring-emerald-400', ativo);
                });

                if (!acorde) {
                    if (painelDiagrama) painelDiagrama.innerHTML = '<div class="text-sm text-slate-500">Sem desenho disponivel.</div>';
                    if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = nome || 'Nenhum acorde selecionado';
                    if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = 'Esse acorde nao possui desenho cadastrado.';
                    return;
                }

                if (painelDiagrama) painelDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = nome;
                if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = acorde.descricao || 'Shape salvo na biblioteca de acordes.';
                preencherVariacoes(nome, indice);
            };

            const mostrarTooltipAcorde = (nome, x, y) => {
                const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                if (!tooltipAcorde || !tooltipAcordeNome || !tooltipAcordeDiagrama || !acorde) return;
                tooltipAcordeNome.textContent = nome;
                tooltipAcordeDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                tooltipAcorde.classList.remove('hidden');
                tooltipAcorde.style.left = `${Math.max(12, Math.min(x + 14, window.innerWidth - 240))}px`;
                tooltipAcorde.style.top = `${Math.max(y - 220, 12)}px`;
            };

            const renderizarListaAcordes = (textoTransposto) => {
                if (!listaAcordes) return;
                const acordesAtuais = helper.extractChordsFromBracketedText(textoTransposto);
                listaAcordes.innerHTML = acordesAtuais.map((acorde) => `<button type="button" class="study-button px-3 py-2 text-sm" data-acorde-card="${helper.escapeHtml(acorde)}">${helper.escapeHtml(acorde)}</button>`).join('');
            };

            const atualizarTomBadge = () => {
                const temTomBase = tomBase && helper.isChord(tomBase);
                const valorAtual = temTomBase ? helper.transposeChord(tomBase, transposicaoAtual) : 'nao informado';
                const formaAtual = temTomBase ? helper.transposeChord(tomBase, transposicaoAtual - capotrasteAtual) : null;
                const capoTexto = capotrasteAtual > 0 ? `Capo ${capotrasteAtual}` : 'Sem capo';
                const capoResumo = capotrasteAtual > 0
                    ? (formaAtual ? `${capoTexto} / tocar como ${formaAtual}` : `${capoTexto} / cifra ${capotrasteAtual} semitom(ns) abaixo`)
                    : 'Sem capo';
                if (tomBadge) tomBadge.textContent = `Tom ${valorAtual}`;
                tomIndicadores.forEach((indicador) => {
                    indicador.textContent = `Tom: ${valorAtual}`;
                });
                if (capoBadge) capoBadge.textContent = capoResumo;
                capoIndicadores.forEach((indicador) => {
                    indicador.textContent = capoResumo;
                });
            };

            const renderizar = () => {
                const textoTransposto = helper.transposeBracketedText(textoOriginal, transposicaoAtual - capotrasteAtual);
                preview.innerHTML = helper.renderChordSheetHtml(textoTransposto, { chordAttribute: 'data-acorde-hover' });
                const fonte = fonteConfig[fonteNivel] || fonteConfig[1];
                previewContainer.style.setProperty('--escala-fonte', String(fonte.escala));
                indicadoresFonte.forEach((indicador) => {
                    indicador.textContent = `Fonte: ${fonte.label}`;
                });
                renderizarListaAcordes(textoTransposto);
                atualizarTomBadge();
            };

            const atualizarRotuloVelocidade = () => {
                const config = velocidadeConfig[Number(controleVelocidade?.value || 2)] || velocidadeConfig[2];
                if (valorVelocidade) valorVelocidade.textContent = config.label;
                return config;
            };

            const pararRolagem = (mensagem = null) => {
                if (intervaloRolagem) {
                    window.clearInterval(intervaloRolagem);
                    intervaloRolagem = null;
                }
                rolagemAtiva = false;
                if (botaoRolagem) {
                    botaoRolagem.innerHTML = '<i class="fa-solid fa-angles-down"></i> Auto rolagem';
                    botaoRolagem.classList.remove('is-running');
                    botaoRolagem.setAttribute('aria-pressed', 'false');
                }
                if (mensagem) mostrarToast(mensagem);
            };

            const iniciarRolagem = () => {
                const velocidade = atualizarRotuloVelocidade();
                intervaloRolagem = window.setInterval(() => {
                    rolagemProgramatica = true;
                    previewContainer.scrollBy({ top: velocidade.passo, left: 0, behavior: 'auto' });
                    window.setTimeout(() => {
                        rolagemProgramatica = false;
                    }, 80);
                    const chegouAoFim = previewContainer.scrollTop + previewContainer.clientHeight >= previewContainer.scrollHeight - 2;
                    if (chegouAoFim) pararRolagem('Auto rolagem finalizada');
                }, 50);
            };

            const tocarPulso = () => {
                try {
                    contextoAudio = contextoAudio || new (window.AudioContext || window.webkitAudioContext)();
                    if (contextoAudio.state === 'suspended') {
                        contextoAudio.resume();
                    }
                    const oscilador = contextoAudio.createOscillator();
                    const ganho = contextoAudio.createGain();
                    const volume = volumeMetronomo[controleVolumeMetronomo?.value || 'medio'] || volumeMetronomo.medio;
                    oscilador.type = 'square';
                    oscilador.frequency.value = 880;
                    ganho.gain.setValueAtTime(0.0001, contextoAudio.currentTime);
                    ganho.gain.exponentialRampToValueAtTime(volume, contextoAudio.currentTime + 0.01);
                    ganho.gain.exponentialRampToValueAtTime(0.0001, contextoAudio.currentTime + 0.12);
                    oscilador.connect(ganho);
                    ganho.connect(contextoAudio.destination);
                    oscilador.start();
                    oscilador.stop(contextoAudio.currentTime + 0.13);
                } catch (error) {
                    console.error(error);
                }
            };

            const atualizarBpm = (novoBpm) => {
                bpmAtual = Math.max(20, Math.min(240, Number(novoBpm) || 72));
                if (controleBpm) controleBpm.value = String(bpmAtual);
                if (rotuloBpm) rotuloBpm.textContent = `${bpmAtual} BPM`;
                if (intervaloMetronomo) {
                    window.clearInterval(intervaloMetronomo);
                    intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpmAtual));
                }
            };

            document.querySelectorAll('[data-transpose]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    transposicaoAtual += Number(botao.dataset.transpose || 0);
                    renderizar();
                    mostrarToast('Tom alterado');
                });
            });
            document.querySelectorAll('[data-transpose-reset]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    transposicaoAtual = 0;
                    renderizar();
                    mostrarToast('Tom original restaurado');
                });
            });
            controlesCapotraste.forEach((controle) => {
                controle.addEventListener('change', () => {
                    capotrasteAtual = Math.max(0, Math.min(11, Number(controle.value || 0)));
                    controlesCapotraste.forEach((outroControle) => {
                        if (outroControle !== controle) {
                            if (outroControle.type === 'radio') {
                                outroControle.checked = Number(outroControle.value || 0) === capotrasteAtual;
                            } else {
                                outroControle.value = String(capotrasteAtual);
                            }
                        }
                    });
                    renderizar();
                    mostrarToast(capotrasteAtual > 0 ? `Capotraste na casa ${capotrasteAtual}` : 'Capotraste removido');
                });
            });
            document.querySelectorAll('[data-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const direcao = Number(botao.dataset.font || 0);
                    const fonteAnterior = fonteNivel;
                    fonteNivel = Math.min(3, Math.max(0, fonteNivel + direcao));
                    renderizar();
                    if (fonteNivel === fonteAnterior) {
                        mostrarToast(direcao > 0 ? 'Fonte ja esta no maximo' : 'Fonte ja esta no minimo');
                    } else {
                        mostrarToast(direcao > 0 ? 'Fonte aumentada' : 'Fonte reduzida');
                    }
                });
            });
            document.querySelectorAll('[data-font-reset]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    fonteNivel = 1;
                    renderizar();
                    mostrarToast('Fonte normal');
                });
            });
            botaoRolagem?.addEventListener('click', () => {
                if (rolagemAtiva) {
                    pararRolagem('Auto rolagem pausada');
                    return;
                }
                rolagemAtiva = true;
                botaoRolagem.innerHTML = '<i class="fa-solid fa-pause"></i> Parar rolagem';
                botaoRolagem.classList.add('is-running');
                botaoRolagem.setAttribute('aria-pressed', 'true');
                iniciarRolagem();
                mostrarToast('Auto rolagem iniciada');
            });
            botoesAutoRolagemRapida.forEach((botao) => {
                botao.addEventListener('click', () => botaoRolagem?.click());
            });
            controleVelocidade?.addEventListener('input', () => {
                const velocidade = atualizarRotuloVelocidade();
                mostrarToast(`Velocidade ${velocidade.label.toLowerCase()}`);
                if (rolagemAtiva) {
                    window.clearInterval(intervaloRolagem);
                    iniciarRolagem();
                }
            });
            botaoMetronomo?.addEventListener('click', () => {
                if (intervaloMetronomo) {
                    window.clearInterval(intervaloMetronomo);
                    intervaloMetronomo = null;
                    botaoMetronomo.textContent = 'Iniciar metronomo';
                    botaoMetronomo.classList.remove('study-button-danger');
                    mostrarToast('Metronomo parado');
                    return;
                }
                tocarPulso();
                intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpmAtual));
                botaoMetronomo.textContent = 'Parar metronomo';
                botaoMetronomo.classList.add('study-button-danger');
                mostrarToast('Metronomo iniciado');
            });
            botaoDiminuirBpm?.addEventListener('click', () => {
                atualizarBpm(bpmAtual - 1);
                mostrarToast(`${bpmAtual} BPM`);
            });
            botaoAumentarBpm?.addEventListener('click', () => {
                atualizarBpm(bpmAtual + 1);
                mostrarToast(`${bpmAtual} BPM`);
            });
            controleBpm?.addEventListener('input', () => {
                atualizarBpm(controleBpm.value);
                mostrarToast(`${bpmAtual} BPM`);
            });
            controleVolumeMetronomo?.addEventListener('change', () => {
                mostrarToast(`Volume ${controleVolumeMetronomo.value}`);
            });
            abrirModalPlaylist?.addEventListener('click', () => abrirModal(modalPlaylist, modalPlaylistBackdrop));
            fecharModalPlaylist?.addEventListener('click', () => fecharModal(modalPlaylist, modalPlaylistBackdrop));
            modalPlaylistBackdrop?.addEventListener('click', () => fecharModal(modalPlaylist, modalPlaylistBackdrop));
            abrirDrawerAcordes.forEach((botao) => {
                botao.addEventListener('click', () => abrirDrawer(drawerAcordes));
            });
            fecharDrawerAcordes?.addEventListener('click', () => fecharDrawer(drawerAcordes));
            botoesCapoPopover.forEach((botao) => {
                botao.addEventListener('click', () => alternarPopoverCapo());
            });
            fecharCapoPopover?.addEventListener('click', () => alternarPopoverCapo(false));
            botoesVideoApoio.forEach((botao) => {
                botao.addEventListener('click', () => {
                    document.getElementById('video_apoio')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });
            document.addEventListener('mouseover', (event) => {
                const acorde = event.target.closest('[data-acorde-hover]');
                if (!acorde) return;
                ativarAcorde(acorde.dataset.acordeHover);
                mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
            });
            document.addEventListener('mousemove', (event) => {
                const acorde = event.target.closest('[data-acorde-hover]');
                if (!acorde) return;
                mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
            });
            document.addEventListener('mouseout', (event) => {
                if (event.target.closest('[data-acorde-hover]')) tooltipAcorde?.classList.add('hidden');
            });
            document.addEventListener('click', (event) => {
                const acorde = event.target.closest('[data-acorde-hover], [data-acorde-card]');
                if (acorde) ativarAcorde(acorde.dataset.acordeHover || acorde.dataset.acordeCard);

                if (capoPopover && !capoPopover.classList.contains('hidden')) {
                    const clicouNoPopover = event.target.closest('#capo_popover, [data-toggle-capo-popover]');
                    if (!clicouNoPopover) {
                        alternarPopoverCapo(false);
                    }
                }
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    fecharModal(modalPlaylist, modalPlaylistBackdrop);
                    fecharDrawer(drawerAcordes);
                    alternarPopoverCapo(false);
                }
            });

            atualizarBpm(bpmInicial);
            const pausarPorInteracaoManual = () => {
                if (rolagemAtiva && !rolagemProgramatica) {
                    pararRolagem('Auto rolagem pausada');
                }
            };

            previewContainer.addEventListener('wheel', pausarPorInteracaoManual, { passive: true });
            previewContainer.addEventListener('touchstart', pausarPorInteracaoManual, { passive: true });
            window.addEventListener('pointerdown', (event) => {
                if (!event.target.closest('#toggle_autorrolagem, #velocidade_rolagem, [data-open-chords], #acordes_drawer, #capo_popover, [data-toggle-capo-popover]')) {
                    pausarPorInteracaoManual();
                }
            });
            window.addEventListener('keydown', (event) => {
                if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' '].includes(event.key)) {
                    pausarPorInteracaoManual();
                }
            });

            atualizarRotuloVelocidade();
            renderizar();
        });
    </script>
@endpush

@push('styles')
    @include('partials.cifra-viewer-styles')
    <style>
        .study-stage { margin: -0.75rem; min-height: calc(100vh - 2rem); border-radius: 2rem; background:#f7efe3; color:#172033; padding:1rem; }
        .study-panel { border:1px solid rgba(140,105,51,.16); background:#fffdf9; border-radius:1.5rem; box-shadow:0 18px 42px rgba(34,20,12,.08); }
        .study-shell { display:grid; grid-template-columns:minmax(0,1fr); gap:1rem; }
        .study-side { display:grid; gap:1rem; }
        .study-reader-frame { display:grid; grid-template-columns:minmax(0,1fr); gap:1rem; align-items:start; }
        .study-toolrail { display:flex; gap:.55rem; overflow-x:auto; padding:.25rem .1rem .65rem; scrollbar-width:none; }
        .study-toolrail::-webkit-scrollbar { display:none; }
        .study-tool-button { flex:0 0 auto; display:inline-flex; align-items:center; justify-content:center; gap:.45rem; min-height:2.75rem; border:1px solid rgba(148,163,184,.22); border-radius:1rem; background:rgba(248,250,252,.96); color:#334155; padding:.65rem .85rem; font-size:.82rem; font-weight:900; box-shadow:0 10px 26px rgba(2,6,23,.14); transition:.16s ease; }
        .study-tool-button:hover { border-color:rgba(16,185,129,.45); color:#064e3b; transform:translateY(-1px); }
        .study-tool-button.is-primary { background:#f97316; border-color:#fb923c; color:#fff; }
        .study-tool-button i { width:1rem; text-align:center; color:currentColor; }
        .study-tool-panel { flex:0 0 15rem; border:1px solid rgba(148,163,184,.22); border-radius:1rem; background:rgba(248,250,252,.96); color:#334155; padding:.75rem; box-shadow:0 10px 26px rgba(2,6,23,.14); }
        .study-tool-panel-title { display:flex; width:100%; align-items:center; gap:.45rem; border:0; background:transparent; color:#0f172a; font-size:.82rem; font-weight:950; cursor:pointer; }
        .study-tool-panel-title.is-running { color:#b91c1c; }
        .study-tool-panel-control { margin-top:.65rem; display:grid; grid-template-columns:1fr auto; align-items:center; gap:.5rem; }
        .study-tool-panel input[type="range"] { width:100%; accent-color:#059669; }
        .study-tool-panel-value { min-width:4.25rem; border-radius:999px; background:#ecfdf5; color:#047857; padding:.3rem .55rem; text-align:center; font-size:.72rem; font-weight:950; }
        .study-popover { position:absolute; z-index:75; width:min(18rem, calc(100vw - 2rem)); border:1px solid rgba(148,163,184,.22); border-radius:1.15rem; background:#fffaf2; color:#1f2937; padding:1rem; box-shadow:0 20px 48px rgba(2,6,23,.32); }
        .study-popover.hidden { display:none; }
        .study-capo-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.45rem; }
        .study-capo-option { position:relative; display:block; }
        .study-capo-option input { position:absolute; opacity:0; pointer-events:none; }
        .study-capo-option span { display:flex; min-height:2.55rem; align-items:center; justify-content:center; border:1px solid rgba(148,163,184,.35); border-radius:.85rem; background:#fff; color:#334155; font-size:.8rem; font-weight:900; }
        .study-capo-option input:checked + span { border-color:#059669; background:#ecfdf5; color:#065f46; box-shadow:0 0 0 3px rgba(16,185,129,.12); }
        .study-button { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; min-height:2.75rem; border-radius:1rem; border:1px solid rgba(140,105,51,.2); background:#fff; color:#172033; padding:.75rem 1rem; font-weight:800; transition:.2s ease; }
        .study-button:hover { border-color:rgba(16,185,129,.38); background:#ecfdf5; color:#065f46; }
        .study-button-primary { border-color:rgba(16,185,129,.35); background:#059669; color:#fff; }
        .study-button-primary:hover { background:#047857; }
        .study-button-danger { border-color:rgba(248,113,113,.38); background:#dc2626; color:#fff; }
        .study-button-danger:hover { background:#b91c1c; }
        .study-badge { display:inline-flex; align-items:center; border-radius:9999px; padding:.35rem .75rem; font-size:.75rem; font-weight:900; }
        .study-badge-yellow { background:#fff7ed; color:#9a3412; }
        .study-badge-blue { background:#eff6ff; color:#1d4ed8; }
        .study-cifra-card { border:1px solid rgba(226,232,240,.95); background:#fff; border-radius:1.5rem; padding:1rem; }
        .study-cifra-scroll { overflow:auto; min-height:55vh; max-height:calc(100vh - 9rem); padding-right:.6rem; scroll-behavior:auto; }
        .study-video-frame { aspect-ratio:16/9; overflow:hidden; border-radius:1.25rem; background:#020617; }
        .study-video-frame iframe { width:100%; height:100%; display:block; }
        .study-empty-video { display:flex; min-height:11rem; align-items:center; justify-content:center; border:1px dashed rgba(148,163,184,.4); border-radius:1.25rem; background:#f8fafc; color:#64748b; text-align:center; }
        .study-modal-backdrop { position:fixed; inset:0; z-index:90; background:rgba(2,6,23,.76); backdrop-filter:blur(4px); }
        .study-modal { position:fixed; inset:0; z-index:91; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .study-modal.hidden, .study-modal-backdrop.hidden { display:none; }
        .study-modal-card { width:min(100%,44rem); max-height:min(88vh,900px); overflow:auto; border:1px solid rgba(140,105,51,.18); border-radius:1.5rem; background:#fffdf9; color:#172033; box-shadow:0 24px 70px rgba(34,20,12,.22); }
        .study-drawer { position:fixed; inset:0 0 0 auto; z-index:91; width:min(28rem, calc(100vw - 1.25rem)); overflow:auto; border-left:1px solid rgba(148,163,184,.22); background:#f8fafc; color:#0f172a; box-shadow:-24px 0 70px rgba(2,6,23,.42); padding:1rem; }
        .study-drawer.hidden { display:none; }
        .study-drawer .study-button { background:#fff; color:#334155; border-color:rgba(148,163,184,.28); }
        .study-drawer .study-button-primary { background:#059669; color:#fff; }
        .study-drawer .diagrama-acorde svg { max-width:210px; }
        .study-floating-controls { position:fixed; right:1rem; bottom:1rem; z-index:70; box-shadow:0 18px 40px rgba(0,0,0,.35); }
        .study-toast { position:fixed; left:50%; bottom:5.25rem; z-index:95; transform:translate(-50%, 16px); border:1px solid rgba(16,185,129,.28); border-radius:999px; background:rgba(6,78,59,.96); color:#ecfdf5; padding:.75rem 1rem; font-size:.9rem; font-weight:900; box-shadow:0 18px 40px rgba(0,0,0,.35); opacity:0; pointer-events:none; transition:.18s ease; }
        .study-toast.is-visible { opacity:1; transform:translate(-50%, 0); }
        .playlist-card { border-radius:1.15rem; border:1px solid rgba(226,232,240,.9); background:#fff; }
        .playlist-existing-item { border-radius:1rem; border:1px solid rgba(226,232,240,.9); background:#fff; }
        .tooltip-acorde { position:fixed; z-index:80; width:220px; pointer-events:none; border-radius:1rem; border:1px solid rgba(16,185,129,.35); background:#fff; box-shadow:0 18px 50px rgba(2,6,23,.18); padding:.85rem; }
        .tooltip-acorde.hidden { display:none; }
        .diagrama-acorde svg, .tooltip-acorde svg { width:100%; height:auto; max-width:240px; }
        .study-stage .cifra-linha { margin-bottom:.25rem; gap:.12rem; }
        .study-stage .cifra-linha--acordes { display:block; padding-left:var(--cifra-indent, 0); margin:.12rem 0 .42rem; }
        .study-stage .cifra-linha--acordes .cifra-acordes { display:inline-flex; flex-wrap:wrap; gap:.75rem; min-height:auto; line-height:1.35; }
        .study-stage .cifra-linha--refrao { border-left:4px solid #f59e0b; border-radius:.2rem; background:linear-gradient(90deg, #fff7ed, #fff); margin:.18rem 0 .68rem; padding:.42rem 0 .42rem .75rem; }
        .study-stage .cifra-linha--refrao .cifra-letra { color:#172033; font-weight:850; }
        .study-stage .cifra-segmento { min-height:2.2rem; }
        .study-stage .cifra-acordes { color:#ea580c; font-size:calc(.9rem * var(--escala-fonte, 1)); line-height:1; }
        .study-stage .cifra-letra { color:#172033; font-size:calc(1.02rem * var(--escala-fonte, 1)); line-height:1.35; }
        .study-stage .cifra-marcacao { margin:.7rem 0 .45rem; background:#ecfdf5; color:#047857; }
        @media (min-width:1280px) {
            .study-shell { grid-template-columns:minmax(0,1fr) 23rem; gap:1.25rem; }
            .study-reader-frame { grid-template-columns:10.5rem minmax(0,1fr); }
            .study-toolrail { position:sticky; top:1rem; display:grid; overflow:visible; padding:0; }
            .study-tool-button { justify-content:flex-start; width:100%; min-height:3rem; }
            .study-tool-panel { width:100%; }
            .study-cifra-card { padding:1.35rem; }
            .study-side { align-self:start; position:sticky; top:1rem; }
        }
        @media (min-width:1024px) {
            .study-floating-controls { display:none; }
        }
        @media (max-width:767px) {
            .study-stage { margin:-1rem; border-radius:0; padding:.75rem; }
            .study-side .desktop-video { display:none; }
            .study-cifra-card { padding:.85rem; }
            .study-stage .cifra-linha { margin-bottom:.18rem; }
            .study-stage .cifra-segmento { min-height:2rem; max-width:100%; }
            .study-stage .cifra-acordes { white-space:normal; }
            .study-stage .cifra-letra { overflow-wrap:anywhere; }
        }
    </style>
@endpush

@section('content')
    @php
        $youtubeValor = trim((string) $versaoMusical->youtube_video_id);
        $youtubeVideoId = null;

        if ($youtubeValor !== '') {
            if (preg_match('/^[A-Za-z0-9_-]{11}$/', $youtubeValor) === 1) {
                $youtubeVideoId = $youtubeValor;
            } elseif (preg_match('/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/)([A-Za-z0-9_-]{11})/', $youtubeValor, $youtubeMatches) === 1) {
                $youtubeVideoId = $youtubeMatches[1];
            }
        }
    @endphp

    <div class="study-stage">
        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-800">{{ session('status') }}</div>
        @endif
        @if (session('info'))
            <div class="mb-4 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-bold text-sky-800">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('member.partials.church-switcher')

        <section class="study-panel p-5 lg:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-700">Modo de estudo</p>
                    <h1 class="mt-2 text-3xl font-black text-gray-950 md:text-4xl">{{ $musica->titulo }}</h1>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ $versaoMusical->titulo ?: $musica->artista ?: 'Versao principal' }}
                        @if ($missaAtiva)
                            <span class="text-gray-400">/</span> Missa ativa: {{ $missaAtiva->titulo }}
                        @endif
                    </p>
                </div>

                <details class="relative">
                    <summary class="study-button cursor-pointer">Acoes</summary>
                    <div class="mt-2 grid gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-xl md:absolute md:right-0 md:z-30 md:w-56">
                        <button type="button" id="abrir_modal_playlist" class="rounded-xl px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-emerald-50">Adicionar a playlist</button>
                        <a href="{{ route('member.versoes.print', [$musica, $versaoMusical]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">Imprimir</a>
                        <a href="{{ route('member.versoes.pdf', [$musica, $versaoMusical]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">PDF</a>
                        <a href="{{ route('member.colecoes.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">Playlists salvas</a>
                        <a href="{{ route('member.musicas.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">Biblioteca musical</a>
                        <a href="{{ route('member.repertorio') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">Meu repertorio</a>
                        <a href="{{ route('member.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50">Painel</a>
                    </div>
                </details>
            </div>

            <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold text-gray-500">
                <span id="tom_atual_badge">Tom {{ $tomExibicao ?: 'nao informado' }}</span>
                <span aria-hidden="true">/</span>
                <span id="capotraste_badge">Sem capo</span>
            </div>

            @if ($itemMissa)
                <details class="mt-5 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black text-gray-900 [&::-webkit-details-marker]:hidden">
                        <span>{{ $pedidoTomPendente ? 'Pedido de tom em analise' : 'Sugerir mudanca de tom para esta missa' }}</span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs text-emerald-700">{{ $pedidoTomPendente ? $pedidoTomPendente->tom_sugerido : 'Abrir' }}</span>
                    </summary>

                    @if ($pedidoTomPendente)
                        <p class="mt-3 text-sm text-gray-600">Pedido enviado para tocar em {{ $pedidoTomPendente->tom_sugerido }}. Quando a equipe aprovar ou recusar, voce recebe aviso no sininho.</p>
                    @else
                        <form action="{{ route('member.repertorio.tom.solicitar', $itemMissa) }}" method="POST" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-[12rem_1fr_auto] md:items-end">
                            @csrf
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500">Novo tom</label>
                                <select name="tom_sugerido" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                    <option value="">Escolha</option>
                                    @foreach ($tonsMusicais as $tomMusical)
                                        <option value="{{ $tomMusical }}">{{ $tomMusical }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-gray-500">Motivo opcional</label>
                                <input name="observacao" maxlength="500" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-slate-900" placeholder="Ex.: fica melhor para as vozes">
                            </div>
                            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white hover:bg-emerald-700">
                                Enviar pedido
                            </button>
                        </form>
                    @endif
                </details>
            @endif
        </section>

        <div class="mt-4 study-shell">
            <div class="study-reader-frame">
                <nav class="study-toolrail" aria-label="Ferramentas da cifra">
                    <button type="button" class="study-tool-button is-primary" data-scroll-video>
                        <i class="fa-solid fa-play"></i>
                        Video
                    </button>
                    <div class="study-tool-panel" aria-label="Auto rolagem">
                        <button type="button" id="toggle_autorrolagem" class="study-tool-panel-title" aria-pressed="false">
                            <i class="fa-solid fa-angles-down"></i>
                            Auto rolagem
                        </button>
                        <div class="study-tool-panel-control">
                            <input id="velocidade_rolagem" type="range" min="1" max="3" value="2" step="1" aria-label="Velocidade da auto rolagem" aria-describedby="valor_velocidade">
                            <span id="valor_velocidade" class="study-tool-panel-value">Normal</span>
                        </div>
                    </div>
                    <button type="button" class="study-tool-button" data-font="-1" aria-label="Diminuir fonte">
                        <i class="fa-solid fa-minus"></i>
                        Texto
                    </button>
                    <button type="button" class="study-tool-button" data-font="1" aria-label="Aumentar fonte">
                        <i class="fa-solid fa-plus"></i>
                        Texto
                    </button>
                    <button type="button" class="study-tool-button" data-transpose="-1">
                        <i class="fa-solid fa-minus"></i>
                        Tom
                    </button>
                    <button type="button" class="study-tool-button" data-transpose="1">
                        <i class="fa-solid fa-plus"></i>
                        Tom
                    </button>
                    <button type="button" class="study-tool-button" data-open-chords>
                        <i class="fa-solid fa-guitar"></i>
                        Acordes
                    </button>
                    <div class="relative">
                        <button type="button" class="study-tool-button" data-toggle-capo-popover>
                            <i class="fa-solid fa-grip-lines-vertical"></i>
                            Capotraste
                        </button>
                        <div id="capo_popover" class="study-popover hidden xl:left-full xl:top-0 xl:ml-3 max-xl:left-0 max-xl:top-full max-xl:mt-2">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">Capotraste</p>
                                    <p class="mt-1 text-sm font-bold text-slate-600" data-capo-indicator>Sem capo</p>
                                </div>
                                <button type="button" id="fechar_capo_popover" class="rounded-full border border-slate-200 px-2 py-1 text-sm font-black text-slate-500">x</button>
                            </div>
                            <div class="study-capo-grid" role="radiogroup" aria-label="Casa do capotraste">
                                <label class="study-capo-option">
                                    <input type="radio" name="capo_visual" value="0" data-capo-control checked>
                                    <span>Sem</span>
                                </label>
                                @for ($casaCapotraste = 1; $casaCapotraste <= 11; $casaCapotraste++)
                                    <label class="study-capo-option">
                                        <input type="radio" name="capo_visual" value="{{ $casaCapotraste }}" data-capo-control>
                                        <span>{{ $casaCapotraste }} casa</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>
                </nav>

                <main class="study-cifra-card">
                <div class="study-cifra-scroll" id="preview_musico_container">
                    <div id="letra_com_cifras_preview" class="space-y-1"></div>
                </div>
                </main>
            </div>

            <aside class="study-side">
                <details id="video_apoio" class="study-panel desktop-video p-4">
                    <summary class="cursor-pointer text-base font-black text-gray-950">Video de apoio</summary>
                    @if ($youtubeVideoId)
                        <div class="study-video-frame mt-3">
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeVideoId }}" title="Video de apoio" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="study-empty-video mt-3 p-4">
                            <div>
                                <p class="font-black text-gray-900">Video nao informado</p>
                                <p class="mt-1 text-sm">Nenhum ID ou link valido do YouTube foi vinculado.</p>
                            </div>
                        </div>
                    @endif
                </details>
            </aside>
        </div>

        <div id="study_toast" class="study-toast" role="status" aria-live="polite"></div>

        <div id="tooltip_acorde" class="tooltip-acorde hidden"><div class="text-center"><div id="tooltip_acorde_nome" class="text-sm font-black text-gray-950">Acorde</div><div id="tooltip_acorde_diagrama" class="mt-3 diagrama-acorde"></div></div></div>

        <aside id="acordes_drawer" class="study-drawer hidden" aria-hidden="true">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-700">Acordes desta cifra</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">Dicionario rapido</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-500">Toque em um acorde na cifra ou escolha abaixo para ver o desenho.</p>
                </div>
                <button type="button" id="fechar_acordes_drawer" class="study-button" aria-label="Fechar acordes">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-5 rounded-[1.25rem] border border-slate-200 bg-white p-4 shadow-sm">
                <div class="diagrama-acorde flex justify-center" id="painel_diagrama_acorde"></div>
                <div class="mt-4 text-center">
                    <div id="nome_acorde_ativo" class="text-lg font-black text-slate-950">Nenhum acorde selecionado</div>
                    <p id="descricao_acorde_ativo" class="mt-1 text-sm font-semibold text-slate-500">Selecione um acorde para visualizar o desenho.</p>
                </div>
                <div id="variacoes_acorde" class="mt-4 flex flex-wrap justify-center gap-2"></div>
            </div>

            <div class="mt-5">
                <p class="mb-3 text-xs font-black uppercase tracking-[0.18em] text-slate-400">Usados na musica</p>
                <div class="flex flex-wrap gap-2" id="lista_acordes_transpostos">
                    @foreach ($acordesDaVersao as $acorde)
                        <button type="button" class="study-button px-3 py-2 text-sm" data-acorde-card="{{ $acorde }}">{{ $acorde }}</button>
                    @endforeach
                </div>
            </div>
        </aside>

        <div id="playlist_modal_backdrop" class="study-modal-backdrop hidden"></div>
        <div id="playlist_modal" class="study-modal hidden" aria-hidden="true">
            <div class="study-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-700">Playlist</p>
                        <h2 class="mt-2 text-2xl font-black text-gray-950">Adicionar "{{ $musica->titulo }}"</h2>
                        <p class="mt-2 text-sm text-gray-600">Escolha uma playlist existente ou crie uma nova sem sair da tela de estudo.</p>
                    </div>
                    <button type="button" id="fechar_modal_playlist" class="study-button" aria-label="Fechar modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <section class="playlist-card p-4">
                        <h3 class="text-base font-bold text-gray-950">Criar nova playlist</h3>
                        <p class="mt-1 text-sm text-gray-600">Separe por ensaio, missa ou estudo pessoal.</p>
                        <form action="{{ route('member.colecoes.store') }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                            <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                            <input type="text" name="nome" class="block w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 placeholder:text-gray-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="Ex.: Ensaio de quarta" required>
                            <button type="submit" class="study-button study-button-primary w-full text-sm">Criar e adicionar</button>
                        </form>
                    </section>

                    <section class="playlist-card p-4">
                        <h3 class="text-base font-bold text-gray-950">Playlist existente</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($colecoes as $colecao)
                                <form action="{{ route('member.colecoes.itens.store', $colecao) }}" method="POST" class="playlist-existing-item flex items-center gap-3 px-3 py-3">
                                    @csrf
                                    <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                                    <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-gray-900">{{ $colecao->nome }}</p>
                                        <p class="text-xs text-slate-400">{{ $colecao->itens_count }} itens</p>
                                    </div>
                                    @if ($colecaoIdsComMusica->contains($colecao->id))
                                        <span class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700">Ja adicionada</span>
                                    @else
                                        <button type="submit" class="study-button px-3 py-2 text-xs">Adicionar</button>
                                    @endif
                                </form>
                            @empty
                                <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-4 text-sm text-gray-500">Nenhuma playlist criada ainda.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
