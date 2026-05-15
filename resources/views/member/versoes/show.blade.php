@extends('member.layouts.app')

@section('title', ($versaoMusical->titulo ?: 'Versao musical') . ' | Voz & Cifra')
@section('mobile_title', 'Estudo da cifra')
@section('desktop_subtitle', 'Leitura musical simples para estudo')

@section('header_actions')
@endsection

@push('scripts')
    @include('partials.chord-transposer-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const helper = window.VozECifraChord;
            const preview = document.getElementById('letra_com_cifras_preview');
            const previewContainer = document.getElementById('preview_musico_container');
            const tomBadge = document.getElementById('tom_atual_badge');
            const tomIndicador = document.getElementById('indicador_tom_atual');
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
            const indicadorFonte = document.getElementById('indicador_fonte_atual');
            const studyToast = document.getElementById('study_toast');
            const modalPlaylist = document.getElementById('playlist_modal');
            const modalPlaylistBackdrop = document.getElementById('playlist_modal_backdrop');
            const abrirModalPlaylist = document.getElementById('abrir_modal_playlist');
            const fecharModalPlaylist = document.getElementById('fechar_modal_playlist');
            const modalControles = document.getElementById('controles_modal');
            const modalControlesBackdrop = document.getElementById('controles_modal_backdrop');
            const abrirModalControles = document.getElementById('abrir_modal_controles');
            const fecharModalControles = document.getElementById('fechar_modal_controles');
            let transposicaoAtual = 0;
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
                if (!shape) return '<div class="text-sm text-slate-300">Sem desenho disponivel.</div>';
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
                    if (painelDiagrama) painelDiagrama.innerHTML = '<div class="text-sm text-slate-300">Sem desenho disponivel.</div>';
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
                const valorAtual = !tomBase || !helper.isChord(tomBase) ? 'nao informado' : helper.transposeChord(tomBase, transposicaoAtual);
                if (tomBadge) tomBadge.textContent = `Tom atual ${valorAtual}`;
                if (tomIndicador) tomIndicador.textContent = `Tom atual: ${valorAtual}`;
            };

            const renderizar = () => {
                const textoTransposto = helper.transposeBracketedText(textoOriginal, transposicaoAtual);
                preview.innerHTML = helper.renderChordSheetHtml(textoTransposto, { chordAttribute: 'data-acorde-hover' });
                const fonte = fonteConfig[fonteNivel] || fonteConfig[1];
                previewContainer.style.setProperty('--escala-fonte', String(fonte.escala));
                if (indicadorFonte) indicadorFonte.textContent = `Fonte: ${fonte.label}`;
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
                    botaoRolagem.textContent = 'Iniciar auto rolagem';
                    botaoRolagem.classList.remove('study-button-danger');
                    botaoRolagem.classList.add('study-button-primary');
                    botaoRolagem.setAttribute('aria-pressed', 'false');
                }
                if (mensagem) mostrarToast(mensagem);
            };

            const iniciarRolagem = () => {
                const velocidade = atualizarRotuloVelocidade();
                intervaloRolagem = window.setInterval(() => {
                    rolagemProgramatica = true;
                    window.scrollBy({ top: velocidade.passo, left: 0, behavior: 'auto' });
                    window.setTimeout(() => {
                        rolagemProgramatica = false;
                    }, 80);
                    const chegouAoFim = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;
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
            document.querySelector('[data-transpose-reset]')?.addEventListener('click', () => {
                transposicaoAtual = 0;
                renderizar();
                mostrarToast('Tom original restaurado');
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
            document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
                fonteNivel = 1;
                renderizar();
                mostrarToast('Fonte normal');
            });
            botaoRolagem?.addEventListener('click', () => {
                if (rolagemAtiva) {
                    pararRolagem('Auto rolagem pausada');
                    return;
                }
                rolagemAtiva = true;
                botaoRolagem.textContent = 'Parar auto rolagem';
                botaoRolagem.classList.remove('study-button-primary');
                botaoRolagem.classList.add('study-button-danger');
                botaoRolagem.setAttribute('aria-pressed', 'true');
                iniciarRolagem();
                mostrarToast('Auto rolagem iniciada');
                fecharModal(modalControles, modalControlesBackdrop);
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
            abrirModalControles?.addEventListener('click', () => abrirModal(modalControles, modalControlesBackdrop));
            fecharModalControles?.addEventListener('click', () => fecharModal(modalControles, modalControlesBackdrop));
            modalControlesBackdrop?.addEventListener('click', () => fecharModal(modalControles, modalControlesBackdrop));
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
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    fecharModal(modalPlaylist, modalPlaylistBackdrop);
                    fecharModal(modalControles, modalControlesBackdrop);
                }
            });

            atualizarBpm(bpmInicial);
            const pausarPorInteracaoManual = () => {
                if (rolagemAtiva && !rolagemProgramatica) {
                    pararRolagem('Auto rolagem pausada');
                }
            };

            window.addEventListener('wheel', pausarPorInteracaoManual, { passive: true });
            window.addEventListener('touchstart', pausarPorInteracaoManual, { passive: true });
            window.addEventListener('pointerdown', (event) => {
                if (!event.target.closest('#toggle_autorrolagem, #controles_modal, #abrir_modal_controles')) {
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
        .study-stage { margin: -0.75rem; min-height: calc(100vh - 2rem); border-radius: 2rem; background: radial-gradient(circle at top left, rgba(16,185,129,.16), transparent 32rem), linear-gradient(135deg,#030712 0%,#08111f 48%,#111827 100%); color:#f8fafc; padding:1rem; }
        .study-panel { border:1px solid rgba(148,163,184,.18); background:rgba(15,23,42,.88); border-radius:1.5rem; box-shadow:0 20px 50px rgba(0,0,0,.28); }
        .study-shell { display:grid; grid-template-columns:minmax(0,1fr); gap:1rem; }
        .study-side { display:grid; gap:1rem; }
        .study-button { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; min-height:2.75rem; border-radius:1rem; border:1px solid rgba(148,163,184,.24); background:rgba(30,41,59,.92); color:#e5e7eb; padding:.75rem 1rem; font-weight:800; transition:.2s ease; }
        .study-button:hover { border-color:rgba(16,185,129,.42); background:rgba(51,65,85,.96); color:#fff; }
        .study-button-primary { border-color:rgba(16,185,129,.35); background:#059669; color:#fff; }
        .study-button-primary:hover { background:#047857; }
        .study-button-danger { border-color:rgba(248,113,113,.38); background:#dc2626; color:#fff; }
        .study-button-danger:hover { background:#b91c1c; }
        .study-badge { display:inline-flex; align-items:center; border-radius:9999px; padding:.35rem .75rem; font-size:.75rem; font-weight:900; }
        .study-badge-yellow { background:rgba(251,191,36,.14); color:#fde68a; }
        .study-badge-blue { background:rgba(96,165,250,.14); color:#bfdbfe; }
        .study-cifra-card { border:1px solid rgba(148,163,184,.18); background:#101a2d; border-radius:1.5rem; padding:1rem; }
        .study-cifra-scroll { overflow:visible; min-height:55vh; }
        .study-video-frame { aspect-ratio:16/9; overflow:hidden; border-radius:1.25rem; background:#020617; }
        .study-video-frame iframe { width:100%; height:100%; display:block; }
        .study-empty-video { display:flex; min-height:11rem; align-items:center; justify-content:center; border:1px dashed rgba(148,163,184,.3); border-radius:1.25rem; background:rgba(15,23,42,.72); color:#94a3b8; text-align:center; }
        .study-modal-backdrop { position:fixed; inset:0; z-index:90; background:rgba(2,6,23,.76); backdrop-filter:blur(4px); }
        .study-modal { position:fixed; inset:0; z-index:91; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .study-modal.hidden, .study-modal-backdrop.hidden { display:none; }
        .study-modal-card { width:min(100%,44rem); max-height:min(88vh,900px); overflow:auto; border:1px solid rgba(148,163,184,.2); border-radius:1.5rem; background:#0f172a; color:#f8fafc; box-shadow:0 24px 70px rgba(0,0,0,.45); }
        .study-floating-controls { position:fixed; right:1rem; bottom:1rem; z-index:70; box-shadow:0 18px 40px rgba(0,0,0,.35); }
        .study-toast { position:fixed; left:50%; bottom:5.25rem; z-index:95; transform:translate(-50%, 16px); border:1px solid rgba(16,185,129,.28); border-radius:999px; background:rgba(6,78,59,.96); color:#ecfdf5; padding:.75rem 1rem; font-size:.9rem; font-weight:900; box-shadow:0 18px 40px rgba(0,0,0,.35); opacity:0; pointer-events:none; transition:.18s ease; }
        .study-toast.is-visible { opacity:1; transform:translate(-50%, 0); }
        .playlist-card { border-radius:1.15rem; border:1px solid rgba(148,163,184,.15); background:rgba(15,23,42,.82); }
        .playlist-existing-item { border-radius:1rem; border:1px solid rgba(148,163,184,.15); background:rgba(255,255,255,.04); }
        .tooltip-acorde { position:fixed; z-index:80; width:220px; pointer-events:none; border-radius:1rem; border:1px solid rgba(16,185,129,.35); background:rgba(15,23,42,.96); box-shadow:0 18px 50px rgba(2,6,23,.28); padding:.85rem; backdrop-filter:blur(8px); }
        .tooltip-acorde.hidden { display:none; }
        .diagrama-acorde svg, .tooltip-acorde svg { width:100%; height:auto; max-width:240px; }
        .study-stage .cifra-linha { margin-bottom:.25rem; gap:.12rem; }
        .study-stage .cifra-linha--refrao { border-left:3px solid #fbbf24; border-radius:1rem; background:rgba(251,191,36,.1); margin:.18rem 0 .45rem; padding:.55rem .75rem; }
        .study-stage .cifra-linha--refrao .cifra-letra { color:#fde68a; font-weight:850; }
        .study-stage .cifra-segmento { min-height:2.2rem; }
        .study-stage .cifra-acordes { color:#fb923c; font-size:calc(.9rem * var(--escala-fonte, 1)); line-height:1; }
        .study-stage .cifra-letra { color:#d1fae5; font-size:calc(1.02rem * var(--escala-fonte, 1)); line-height:1.35; }
        .study-stage .cifra-marcacao { margin:.7rem 0 .45rem; background:rgba(16,185,129,.16); color:#a7f3d0; }
        @media (min-width:1280px) {
            .study-shell { grid-template-columns:minmax(0,1fr) 23rem; gap:1.25rem; }
            .study-cifra-card { padding:1.35rem; }
            .study-side { align-self:start; position:sticky; top:1rem; }
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
            <div class="mb-4 rounded-2xl border border-emerald-400/30 bg-emerald-950 px-5 py-4 text-sm text-emerald-100">{{ session('success') }}</div>
        @endif
        @if (session('status'))
            <div class="mb-4 rounded-2xl border border-amber-400/30 bg-amber-950 px-5 py-4 text-sm text-amber-100">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-2xl border border-red-400/30 bg-red-950 px-5 py-4 text-sm text-red-100">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="study-panel p-5 lg:p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="min-w-0">
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Modo de estudo</p>
                    <h1 class="mt-2 text-3xl font-black text-white md:text-4xl">{{ $musica->titulo }}</h1>
                    <p class="mt-2 text-sm text-slate-300">
                        {{ $versaoMusical->titulo ?: $musica->artista ?: 'Versao principal' }}
                        @if ($missaAtiva)
                            <span class="text-slate-500">/</span> Missa ativa: {{ $missaAtiva->titulo }}
                        @endif
                    </p>
                </div>

                <details class="relative">
                    <summary class="study-button cursor-pointer">Acoes</summary>
                    <div class="mt-2 grid gap-2 rounded-2xl border border-white/10 bg-slate-950 p-2 shadow-xl md:absolute md:right-0 md:z-30 md:w-56">
                        <button type="button" id="abrir_modal_playlist" class="rounded-xl px-3 py-2 text-left text-sm font-semibold text-slate-100 hover:bg-white/10">Adicionar a playlist</button>
                        <a href="{{ route('member.versoes.print', [$musica, $versaoMusical]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">Imprimir</a>
                        <a href="{{ route('member.versoes.pdf', [$musica, $versaoMusical]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">PDF</a>
                        <a href="{{ route('member.colecoes.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">Playlists salvas</a>
                        <a href="{{ route('member.musicas.index') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">Biblioteca musical</a>
                        <a href="{{ route('member.repertorio') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">Meu repertorio</a>
                        <a href="{{ route('member.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-white/10">Painel</a>
                    </div>
                </details>
            </div>

            <div class="mt-5 flex flex-wrap gap-2">
                @if ($itemMissa && $itemMissa->tom_usado)
                    <span class="study-badge study-badge-yellow">Tom da missa {{ $itemMissa->tom_usado }}</span>
                @endif
                @if ($tomOriginal)
                    <span class="study-badge study-badge-yellow">Tom original {{ $tomOriginal }}</span>
                @endif
                @if ($versaoMusical->bpm)
                    <span class="study-badge study-badge-blue">BPM {{ $versaoMusical->bpm }}</span>
                @endif
                <span id="tom_atual_badge" class="study-badge bg-emerald-400/15 text-emerald-200">Tom atual {{ $tomExibicao ?: 'nao informado' }}</span>
            </div>
        </section>

        <div class="mt-4 study-shell">
            <main class="study-cifra-card">
                <div class="study-cifra-scroll" id="preview_musico_container">
                    <div id="letra_com_cifras_preview" class="space-y-1"></div>
                </div>
            </main>

            <aside class="study-side">
                <section class="study-panel desktop-video p-4">
                    <h2 class="text-base font-black text-white">Video</h2>
                    @if ($youtubeVideoId)
                        <div class="study-video-frame mt-3">
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeVideoId }}" title="Video de apoio" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="study-empty-video mt-3 p-4">
                            <div>
                                <p class="font-black text-slate-200">Video nao informado</p>
                                <p class="mt-1 text-sm">Nenhum ID ou link valido do YouTube foi vinculado.</p>
                            </div>
                        </div>
                    @endif
                </section>

                <details class="study-panel p-4 md:hidden">
                    <summary class="cursor-pointer text-base font-black text-white">Ver video</summary>
                    @if ($youtubeVideoId)
                        <div class="study-video-frame mt-3">
                            <iframe src="https://www.youtube.com/embed/{{ $youtubeVideoId }}" title="Video de apoio" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    @else
                        <div class="study-empty-video mt-3 p-4 text-sm">Video nao informado.</div>
                    @endif
                </details>

                <details class="study-panel p-4 xl:block">
                    <summary class="cursor-pointer text-base font-black text-white">Dicionario de acordes</summary>
                    <p class="mt-1 text-sm text-slate-300">Toque ou passe sobre um acorde para ver o shape.</p>
                    <div class="mt-4 rounded-[1.25rem] border border-white/10 bg-white/5 p-4">
                        <div class="diagrama-acorde flex justify-center" id="painel_diagrama_acorde"></div>
                        <div class="mt-4 text-center">
                            <div id="nome_acorde_ativo" class="text-lg font-black text-white">Nenhum acorde selecionado</div>
                            <p id="descricao_acorde_ativo" class="mt-1 text-sm text-slate-300">Selecione um acorde para visualizar o desenho.</p>
                        </div>
                        <div id="variacoes_acorde" class="mt-4 flex flex-wrap justify-center gap-2"></div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2" id="lista_acordes_transpostos">
                        @foreach ($acordesDaVersao as $acorde)
                            <button type="button" class="study-button px-3 py-2 text-sm" data-acorde-card="{{ $acorde }}">{{ $acorde }}</button>
                        @endforeach
                    </div>
                </details>
            </aside>
        </div>

        <button type="button" id="abrir_modal_controles" class="study-floating-controls study-button study-button-primary">
            <i class="fa-solid fa-sliders"></i>
            Controles
        </button>
        <div id="study_toast" class="study-toast" role="status" aria-live="polite"></div>

        <div id="tooltip_acorde" class="tooltip-acorde hidden"><div class="text-center"><div id="tooltip_acorde_nome" class="text-sm font-black text-white">Acorde</div><div id="tooltip_acorde_diagrama" class="mt-3 diagrama-acorde"></div></div></div>

        <div id="controles_modal_backdrop" class="study-modal-backdrop hidden"></div>
        <div id="controles_modal" class="study-modal hidden" aria-hidden="true">
            <div class="study-modal-card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Controles</p>
                        <h2 class="mt-2 text-2xl font-black text-white">Ajustes da leitura</h2>
                    </div>
                    <button type="button" id="fechar_modal_controles" class="study-button" aria-label="Fechar controles"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <div class="mt-6 grid gap-5">
                    <section class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <h3 class="font-black text-white">Auto rolagem</h3>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="button" id="toggle_autorrolagem" class="study-button study-button-primary text-sm" aria-pressed="false">Iniciar auto rolagem</button>
                            <label for="velocidade_rolagem" class="text-sm font-semibold text-slate-300">Velocidade</label>
                            <input id="velocidade_rolagem" type="range" min="1" max="3" value="2" step="1" class="accent-emerald-500" aria-describedby="valor_velocidade">
                            <span id="valor_velocidade" class="min-w-[4.5rem] rounded-full bg-emerald-400/10 px-3 py-1 text-center text-sm font-black text-emerald-100">Normal</span>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <h3 class="font-black text-white">Metronomo</h3>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <button type="button" id="toggle_metronomo" class="study-button text-sm">Iniciar metronomo</button>
                            <div class="inline-flex items-center overflow-hidden rounded-xl border border-white/10 bg-slate-950">
                                <button type="button" id="diminuir_bpm" class="h-11 w-11 text-lg font-bold text-slate-100 hover:bg-white/10">-</button>
                                <input id="controle_bpm" type="number" min="20" max="240" value="{{ $versaoMusical->bpm ?: 72 }}" class="w-20 border-0 bg-slate-900 text-center text-base font-bold text-white focus:ring-0">
                                <button type="button" id="aumentar_bpm" class="h-11 w-11 text-lg font-bold text-slate-100 hover:bg-white/10">+</button>
                            </div>
                            <span id="rotulo_bpm" class="study-badge study-badge-blue">{{ $versaoMusical->bpm ?: 72 }} BPM</span>
                            <label class="text-sm font-semibold text-slate-300" for="volume_metronomo">Volume</label>
                            <select id="volume_metronomo" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-3 text-sm font-bold text-white focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="baixo">Baixo</option>
                                <option value="medio" selected>Medio</option>
                                <option value="alto">Alto</option>
                            </select>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-white/10 bg-white/5 p-4">
                        <h3 class="font-black text-white">Tom e fonte</h3>
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            <span class="study-badge study-badge-yellow" id="indicador_tom_atual">Tom atual: {{ $tomExibicao ?: 'nao informado' }}</span>
                            <button type="button" data-transpose="-1" class="study-button py-2 text-sm">Tom -</button>
                            <button type="button" data-transpose-reset class="study-button py-2 text-sm">Tom original</button>
                            <button type="button" data-transpose="1" class="study-button py-2 text-sm">Tom +</button>
                            <span id="indicador_fonte_atual" class="study-badge study-badge-blue">Fonte: normal</span>
                            <button type="button" data-font="-1" class="study-button py-2 text-sm">A-</button>
                            <button type="button" data-font-reset class="study-button py-2 text-sm">Fonte padrao</button>
                            <button type="button" data-font="1" class="study-button py-2 text-sm">A+</button>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div id="playlist_modal_backdrop" class="study-modal-backdrop hidden"></div>
        <div id="playlist_modal" class="study-modal hidden" aria-hidden="true">
            <div class="study-modal-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Playlist</p>
                        <h2 class="mt-2 text-2xl font-black text-white">Adicionar "{{ $musica->titulo }}"</h2>
                        <p class="mt-2 text-sm text-slate-300">Escolha uma playlist existente ou crie uma nova sem sair da tela de estudo.</p>
                    </div>
                    <button type="button" id="fechar_modal_playlist" class="study-button" aria-label="Fechar modal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                    <section class="playlist-card p-4">
                        <h3 class="text-base font-bold text-white">Criar nova playlist</h3>
                        <p class="mt-1 text-sm text-slate-300">Separe por ensaio, missa ou estudo pessoal.</p>
                        <form action="{{ route('member.colecoes.store') }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                            <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                            <input type="text" name="nome" class="block w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="Ex.: Ensaio de quarta" required>
                            <button type="submit" class="study-button study-button-primary w-full text-sm">Criar e adicionar</button>
                        </form>
                    </section>

                    <section class="playlist-card p-4">
                        <h3 class="text-base font-bold text-white">Playlist existente</h3>
                        <div class="mt-4 space-y-3">
                            @forelse ($colecoes as $colecao)
                                <form action="{{ route('member.colecoes.itens.store', $colecao) }}" method="POST" class="playlist-existing-item flex items-center gap-3 px-3 py-3">
                                    @csrf
                                    <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                                    <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-white">{{ $colecao->nome }}</p>
                                        <p class="text-xs text-slate-400">{{ $colecao->itens_count }} itens</p>
                                    </div>
                                    @if ($colecaoIdsComMusica->contains($colecao->id))
                                        <span class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300">Ja adicionada</span>
                                    @else
                                        <button type="submit" class="study-button px-3 py-2 text-xs">Adicionar</button>
                                    @endif
                                </form>
                            @empty
                                <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-4 text-sm text-slate-300">Nenhuma playlist criada ainda.</div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
@endsection
