@extends('local-admin.layouts.admin')

@section('title', 'Visualiza&ccedil;&atilde;o da missa | Voz & Cifra')
@section('mobile_title', 'Visualiza&ccedil;&atilde;o')

@push('styles')
    @include('partials.cifra-viewer-styles')
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">Visualiza&ccedil;&atilde;o da missa</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ $missa->titulo }} &bull; {{ optional($missa->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missa->hora_inicio, 0, 5) }}
            </p>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">Esta tela funciona como uma pr&eacute;via de leitura para fi&eacute;is e m&uacute;sicos, com foco em clareza, espa&ccedil;amento e acompanhamento da celebra&ccedil;&atilde;o.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap">
            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50">
                Voltar para a missa
            </a>
            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-medium text-amber-800 hover:bg-amber-100">
                Baixar PDF completo
            </a>
        </div>
    </div>

    @if ($itensApresentacao->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Nenhuma cifra pronta para visualiza&ccedil;&atilde;o</h2>
            <p class="mt-2 text-sm text-gray-500">Vincule vers&otilde;es musicais aos itens do repert&oacute;rio para usar este modo cont&iacute;nuo da missa.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Sequ&ecirc;ncia da missa</h2>
                <p class="mt-1 text-sm text-gray-500">Avance pela celebra&ccedil;&atilde;o sem perder a ordem dos cantos.</p>

                <div class="mt-4 space-y-2">
                    @foreach ($itensApresentacao as $indice => $item)
                        <button
                            type="button"
                            class="botao-item-apresentacao flex w-full flex-col rounded-2xl border border-gray-200 px-4 py-3 text-left transition hover:border-sky-200 hover:bg-sky-50"
                            data-item-indice="{{ $indice }}"
                        >
                            <span class="text-xs font-black uppercase tracking-wider text-gray-400">Ordem {{ $item['ordem'] }}</span>
                            <span class="mt-1 text-sm font-bold text-gray-900">{{ $item['titulo'] }}</span>
                            <span class="mt-1 text-xs text-gray-500">{{ $item['momento'] ?: 'Momento ainda n&atilde;o definido' }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="apresentacao_ordem" class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Ordem</span>
                                <span id="apresentacao_momento" class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Momento</span>
                            </div>
                            <h2 id="apresentacao_titulo" class="mt-3 text-2xl font-black text-gray-900"></h2>
                            <p id="apresentacao_subtitulo" class="mt-2 text-sm text-gray-500"></p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Tom</span>
                                <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">-</button>
                                <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Original</button>
                                <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">+</button>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Fonte</span>
                                <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100">A-</button>
                                <button type="button" data-font-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Padr&atilde;o</button>
                                <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100">A+</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="apresentacao_anterior" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">Anterior</button>
                            <button type="button" id="apresentacao_proxima" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white hover:bg-sky-700">Pr&oacute;xima</button>
                            <span id="apresentacao_tom_badge" class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tom</span>
                            <span id="apresentacao_bpm_badge" class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">BPM</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <button type="button" id="toggle_autorrolagem_apresentacao" class="rounded-lg bg-[#6c4a21] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5b3d1a]">Iniciar auto rolagem</button>
                            <label for="velocidade_apresentacao" class="text-sm font-medium text-gray-600">Velocidade</label>
                            <input id="velocidade_apresentacao" type="range" min="0.25" max="6" value="0.75" step="0.25" class="accent-[#8c6933]">
                            <span id="velocidade_apresentacao_valor" class="text-sm font-semibold text-gray-700">0.75</span>
                        </div>
                    </div>

                    <div id="apresentacao_container" class="apresentacao-cifra-box max-h-[68vh] overflow-y-auto p-5">
                        <div id="apresentacao_letra" class="space-y-2"></div>
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($itensApresentacao->isNotEmpty())
        @include('partials.chord-transposer-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const helper = window.VozECifraChord;
                const itens = @json($itensApresentacao, JSON_UNESCAPED_UNICODE);
                const titulo = document.getElementById('apresentacao_titulo');
                const subtitulo = document.getElementById('apresentacao_subtitulo');
                const ordem = document.getElementById('apresentacao_ordem');
                const momento = document.getElementById('apresentacao_momento');
                const letra = document.getElementById('apresentacao_letra');
                const tomBadge = document.getElementById('apresentacao_tom_badge');
                const bpmBadge = document.getElementById('apresentacao_bpm_badge');
                const container = document.getElementById('apresentacao_container');
                const botaoAnterior = document.getElementById('apresentacao_anterior');
                const botaoProxima = document.getElementById('apresentacao_proxima');
                const botaoRolagem = document.getElementById('toggle_autorrolagem_apresentacao');
                const controleVelocidade = document.getElementById('velocidade_apresentacao');
                const valorVelocidade = document.getElementById('velocidade_apresentacao_valor');
                const botoesItem = document.querySelectorAll('[data-item-indice]');

                let indiceAtual = 0;
                let transposicaoAtual = 0;
                let fonteAtual = 18;
                let rolagemAtiva = false;
                let intervaloRolagem = null;

                if (!helper || !letra || !container) {
                    return;
                }

                const pararRolagem = () => {
                    if (intervaloRolagem) {
                        window.clearInterval(intervaloRolagem);
                        intervaloRolagem = null;
                    }

                    rolagemAtiva = false;
                    if (botaoRolagem) {
                        botaoRolagem.textContent = 'Iniciar auto rolagem';
                    }
                };

                const iniciarRolagem = () => {
                    if (!controleVelocidade) {
                        return;
                    }

                    const velocidade = Number(controleVelocidade.value || 0.75);
                    if (valorVelocidade) {
                        valorVelocidade.textContent = velocidade.toFixed(2);
                    }

                    intervaloRolagem = window.setInterval(() => {
                        container.scrollTop += velocidade * 0.18;
                        if (container.scrollTop + container.clientHeight >= container.scrollHeight) {
                            pararRolagem();
                        }
                    }, 70);
                };

                const renderizar = () => {
                    const item = itens[indiceAtual];
                    if (!item) {
                        return;
                    }

                    titulo.textContent = item.titulo;
                    subtitulo.textContent = [item.artista || 'Artista não informado', item.versao].filter(Boolean).join(' - ');
                    ordem.textContent = 'Ordem ' + item.ordem;
                    momento.textContent = item.momento || 'Momento ainda não definido';
                    letra.innerHTML = helper.renderChordSheetHtml(
                        helper.transposeBracketedText(item.letra || '', transposicaoAtual),
                        { chordAttribute: 'data-acorde-hover' }
                    );
                    letra.style.setProperty('--escala-fonte', String(fonteAtual / 18));
                    tomBadge.textContent = 'Tom ' + (
                        item.tom_exibicao && helper.isChord(item.tom_exibicao)
                            ? helper.transposeChord(item.tom_exibicao, transposicaoAtual)
                            : 'Não informado'
                    );
                    bpmBadge.textContent = 'BPM ' + (item.bpm || '-');
                    container.scrollTop = 0;

                    botoesItem.forEach((botao) => {
                        const ativo = Number(botao.dataset.itemIndice) === indiceAtual;
                        botao.classList.toggle('border-sky-200', ativo);
                        botao.classList.toggle('bg-sky-50', ativo);
                    });

                    if (botaoAnterior) {
                        botaoAnterior.disabled = indiceAtual === 0;
                        botaoAnterior.classList.toggle('opacity-50', indiceAtual === 0);
                    }

                    if (botaoProxima) {
                        botaoProxima.disabled = indiceAtual === itens.length - 1;
                        botaoProxima.classList.toggle('opacity-50', indiceAtual === itens.length - 1);
                    }
                };

                botaoAnterior?.addEventListener('click', () => {
                    if (indiceAtual > 0) {
                        indiceAtual--;
                        transposicaoAtual = 0;
                        pararRolagem();
                        renderizar();
                    }
                });

                botaoProxima?.addEventListener('click', () => {
                    if (indiceAtual < itens.length - 1) {
                        indiceAtual++;
                        transposicaoAtual = 0;
                        pararRolagem();
                        renderizar();
                    }
                });

                botoesItem.forEach((botao) => {
                    botao.addEventListener('click', () => {
                        indiceAtual = Number(botao.dataset.itemIndice || 0);
                        transposicaoAtual = 0;
                        pararRolagem();
                        renderizar();
                    });
                });

                document.querySelectorAll('[data-transpose]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        transposicaoAtual += Number(botao.dataset.transpose || 0);
                        renderizar();
                    });
                });

                document.querySelector('[data-transpose-reset]')?.addEventListener('click', () => {
                    transposicaoAtual = 0;
                    renderizar();
                });

                document.querySelectorAll('[data-font]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        fonteAtual = Math.min(34, Math.max(14, fonteAtual + (Number(botao.dataset.font || 0) * 2)));
                        renderizar();
                    });
                });

                document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
                    fonteAtual = 18;
                    renderizar();
                });

                botaoRolagem?.addEventListener('click', () => {
                    if (rolagemAtiva) {
                        pararRolagem();
                        return;
                    }

                    rolagemAtiva = true;
                    botaoRolagem.textContent = 'Parar auto rolagem';
                    iniciarRolagem();
                });

                controleVelocidade?.addEventListener('input', () => {
                    if (valorVelocidade) {
                        valorVelocidade.textContent = Number(controleVelocidade.value).toFixed(2);
                    }

                    if (rolagemAtiva) {
                        window.clearInterval(intervaloRolagem);
                        iniciarRolagem();
                    }
                });

                if (valorVelocidade && controleVelocidade) {
                    valorVelocidade.textContent = Number(controleVelocidade.value).toFixed(2);
                }

                renderizar();
            });
        </script>
    @endif
@endpush
