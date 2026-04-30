@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $letraInicial = old('letra_com_cifras', $versaoMusical->letra_com_cifras ?? $musica->letra ?? '');
@endphp

@if (session('info'))
    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 text-sm rounded">
        {{ session('info') }}
    </div>
@endif

@if (session('warning'))
    <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 text-sm rounded">
        {{ session('warning') }}
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Musica base</label>
                    <input type="text" value="{{ $musica->titulo }}" disabled class="{{ $classeInput }} bg-gray-50 text-gray-500 cursor-not-allowed" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Titulo da versao</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $versaoMusical->titulo ?? '') }}" placeholder="Ex.: Tom original, Versao para assembleia" class="{{ $classeInput }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tom musical</label>
                        <select name="tom_musical" class="{{ $classeInput }}">
                            <option value="">Selecione um tom</option>
                            @foreach (($tonsMusicais ?? []) as $tomMusical)
                                <option value="{{ $tomMusical }}" @selected(old('tom_musical', $versaoMusical->tom_musical ?? '') === $tomMusical)>
                                    {{ $tomMusical }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Use um tom padronizado para manter o repertorio consistente entre igrejas e versoes.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">BPM</label>
                        <input type="number" name="bpm" min="1" max="999" value="{{ old('bpm', $versaoMusical->bpm ?? '') }}" placeholder="Ex.: 72" class="{{ $classeInput }}" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">YouTube video ID</label>
                    <input type="text" name="youtube_video_id" value="{{ old('youtube_video_id', $versaoMusical->youtube_video_id ?? '') }}" placeholder="Ex.: dQw4w9WgXcQ ou cole a URL" class="{{ $classeInput }}" />
                    <p class="text-xs text-gray-500 mt-1">Voce pode informar apenas o ID do video ou colar o link inteiro do YouTube.</p>
                </div>

                <details class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-blue-900 [&::-webkit-details-marker]:hidden">
                        <span class="inline-flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white">
                                <i class="fa-solid fa-circle-info"></i>
                            </span>
                            <span>
                                <span class="block text-base font-bold">Como preencher</span>
                                <span class="block text-sm text-blue-800">Exemplos de cifra e conversao automatica.</span>
                            </span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-sm"></i>
                    </summary>

                    <div class="mt-5 flex items-start gap-3">
                        <div class="mt-1 hidden h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white sm:flex">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <div class="flex-1 space-y-4 text-sm text-blue-900">
                            <div>
                                <h2 class="text-base font-bold">Como preencher a cifra</h2>
                                <p class="text-blue-800">Cole a cifra com colchetes ou no estilo Cifra Club. A previa abaixo mostra ao vivo como a versao com cifra e a leitura sem cifra vao ficar.</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="rounded-xl border border-blue-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Aceito</div>
                                    <p class="mt-2 text-sm text-gray-700">`[G]Quao grande e o meu Deus`</p>
                                </div>
                                <div class="rounded-xl border border-blue-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.16em] text-blue-600">Aceito</div>
                                    <p class="mt-2 text-sm text-gray-700">Linha de acorde acima da letra, estilo Cifra Club.</p>
                                </div>
                                <div class="rounded-xl border border-red-200 bg-white px-4 py-3">
                                    <div class="text-xs font-black uppercase tracking-[0.16em] text-red-600">Evite</div>
                                    <p class="mt-2 text-sm text-gray-700">Varios acordes soltos sem alinhamento com a letra.</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button type="button" class="rounded-full border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100" data-exemplo-toggle="colchetes">
                                    Ver exemplo com colchetes
                                </button>
                                <button type="button" class="rounded-full border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100" data-exemplo-toggle="cifraclub">
                                    Ver exemplo estilo Cifra Club
                                </button>
                                <button type="button" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100" data-exemplo-toggle="interno">
                                    Ver texto interno
                                </button>
                            </div>

                            <div class="hidden rounded-xl border border-blue-200 bg-white p-4" data-exemplo-painel="colchetes">
                                <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus</pre>
                            </div>

                            <div class="hidden rounded-xl border border-blue-200 bg-white p-4" data-exemplo-painel="cifraclub">
                                <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">   G
Quao grande e o meu Deus
      D/F#  Em7
Cantarei quao grande e o meu Deus</pre>
                            </div>

                            <div class="hidden rounded-xl border border-gray-200 bg-white p-4" data-exemplo-painel="interno">
                                <h3 class="text-sm font-bold text-gray-800 mb-2">Formato interno gerado</h3>
                                <pre id="preview_padrao_interno" class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800"></pre>
                            </div>

                            <div id="painel_validacao_cifras" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                <h3 class="font-bold mb-2">Acordes nao encontrados na biblioteca</h3>
                                <p id="lista_acordes_invalidos"></p>
                                <p class="mt-2 text-xs">O salvamento continua liberado, mas vale revisar esses acordes.</p>
                            </div>

                            <div id="painel_conversao_automatica" class="hidden rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                                O texto colado foi reconhecido em outro formato e sera convertido automaticamente para o padrao interno com colchetes. Revise o resultado antes de salvar.
                            </div>
                        </div>
                    </div>
                </details>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Letra com cifras</label>
                        <span class="text-xs text-gray-500">Aceita colchetes ou estilo Cifra Club. O sistema converte automaticamente e voce revisa antes de salvar.</span>
                    </div>
                    <textarea id="letra_com_cifras" name="letra_com_cifras" rows="18" required placeholder="[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus" class="{{ $classeInput }} font-mono text-sm">{{ $letraInicial }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $versaoMusical->ativo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Versao ativa</label>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Pre-visualizacao</h2>
            <div class="mb-4 flex flex-wrap gap-2">
                <button type="button" class="rounded-full bg-green-700 px-4 py-2 text-sm font-semibold text-white ring-2 ring-green-200" data-preview-toggle="com-cifras" aria-pressed="true">
                    Previa musico
                </button>
                <button type="button" class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700" data-preview-toggle="sem-cifras" aria-pressed="false">
                    Sem cifra
                </button>
            </div>

            <div class="space-y-4">
                <div data-preview-panel="com-cifras">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visao com cifra</h3>
                    <div id="preview_com_cifras" class="min-h-[320px] rounded-xl bg-slate-900 p-5 text-green-100 border border-slate-800 overflow-auto"></div>
                </div>

                <div class="hidden" data-preview-panel="sem-cifras">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visao sem cifra</h3>
                    <div id="preview_sem_cifras" class="min-h-[320px] rounded-xl bg-gray-50 p-5 text-gray-800 border border-gray-200"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-6">
            <h2 class="text-lg font-bold text-gray-800 mb-2">Biblioteca de acordes</h2>
            <p class="text-sm text-gray-500 mb-4">Clique em um acorde para inserir no cursor do editor.</p>

            <div class="flex flex-wrap gap-2 max-h-[420px] overflow-y-auto pr-1">
                @forelse ($acordes as $acorde)
                    <button
                        type="button"
                        class="botao-acorde px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-semibold border border-green-200 hover:bg-green-100"
                        data-acorde="{{ $acorde->nome }}"
                    >
                        {{ $acorde->nome }}
                    </button>
                @empty
                    <p class="text-sm text-gray-500">Nenhum acorde ativo cadastrado.</p>
                @endforelse
            </div>

            <div class="mt-5 pt-5 border-t border-gray-100 text-sm text-gray-500 space-y-2">
                <p><strong class="text-gray-700">Dica:</strong> insira os acordes apenas onde a troca acontece.</p>
                <p><strong class="text-gray-700">Exemplo:</strong> <code>[C]Santo, Santo, [G]Santo</code></p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const textarea = document.getElementById('letra_com_cifras');
        const formulario = textarea?.closest('form');
        const previewComCifras = document.getElementById('preview_com_cifras');
        const previewSemCifras = document.getElementById('preview_sem_cifras');
        const previewPadraoInterno = document.getElementById('preview_padrao_interno');
        const botoesAcorde = document.querySelectorAll('.botao-acorde');
        const painelConversaoAutomatica = document.getElementById('painel_conversao_automatica');
        const painelValidacaoCifras = document.getElementById('painel_validacao_cifras');
        const listaAcordesInvalidos = document.getElementById('lista_acordes_invalidos');
        const acordesValidos = @json($acordesValidos ?? []);
        const bibliotecaAcordes = new Set(acordesValidos.map((item) => String(item).toUpperCase()));
        const botoesPreview = document.querySelectorAll('[data-preview-toggle]');
        const paineisPreview = document.querySelectorAll('[data-preview-panel]');
        const botoesExemplo = document.querySelectorAll('[data-exemplo-toggle]');
        const paineisExemplo = document.querySelectorAll('[data-exemplo-painel]');

        if (!textarea || !previewComCifras || !previewSemCifras || !previewPadraoInterno) {
            return;
        }

        const ehAcorde = (valor) => {
            const texto = (valor || '').trim();

            if (!texto || texto.includes(' ')) {
                return false;
            }

            return /^[A-G](?:#|b)?(?:m|maj|min|dim|aug|sus|add|omit|no|M|º|°|-|\+|[0-9#b()])*(?:\/[A-G](?:#|b)?)?$/i.test(texto);
        };

        const ehLinhaTablatura = (linha) => {
            const texto = linha.trim();
            return /^[EABDGBe]\|/.test(texto) || texto.includes('|---') || texto.includes('Parte ');
        };

        const ehLinhaSomenteAcordes = (linha) => {
            const linhaOriginal = linha;
            const texto = linha.trim();

            if (!texto || ehLinhaTablatura(texto)) {
                return false;
            }

            const tokens = texto.split(/\s+/).filter(Boolean);

            if (!(tokens.length > 0 && tokens.every(ehAcorde))) {
                return false;
            }

            if (tokens.length === 1 && !/^\s+/.test(linhaOriginal)) {
                return false;
            }

            return true;
        };

        const localizarPosicaoSeguraNaLetra = (linhaLetra, offset) => {
            const palavras = Array.from(linhaLetra.matchAll(/\S+/gu));

            if (palavras.length === 0) {
                return 0;
            }

            for (let i = 0; i < palavras.length; i++) {
                const palavra = palavras[i][0];
                const inicio = palavras[i].index ?? 0;
                const fim = inicio + palavra.length;

                if (offset <= inicio) {
                    return inicio;
                }

                if (offset > inicio && offset < fim) {
                    return inicio;
                }
            }

            return palavras[palavras.length - 1]?.index ?? 0;
        };

        const combinarLinhaDeAcordesComLetra = (linhaAcordes, linhaLetra) => {
            const tokens = Array.from(linhaAcordes.matchAll(/\S+/g));
            let resultado = linhaLetra;

            tokens.reverse().forEach((token) => {
                const acorde = token[0];
                const posicao = localizarPosicaoSeguraNaLetra(resultado, token.index ?? 0);

                if (!ehAcorde(acorde)) {
                    return;
                }

                resultado = resultado.slice(0, posicao) + `[${acorde}]` + resultado.slice(posicao);
            });

            return resultado;
        };

        const normalizarFormato = (textoBruto) => {
            const texto = (textoBruto || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').trim();
            const linhas = texto.split('\n');
            const resultado = [];
            let houveConversao = false;

            for (let i = 0; i < linhas.length; i++) {
                const linhaAtual = linhas[i].replace(/\s+$/g, '');
                const proximaLinha = linhas[i + 1];

                if (
                    ehLinhaSomenteAcordes(linhaAtual) &&
                    typeof proximaLinha === 'string' &&
                    proximaLinha.trim() !== '' &&
                    !ehLinhaSomenteAcordes(proximaLinha) &&
                    !ehLinhaTablatura(proximaLinha)
                ) {
                    resultado.push(combinarLinhaDeAcordesComLetra(linhaAtual, proximaLinha.replace(/\s+$/g, '')));
                    houveConversao = true;
                    i++;
                    continue;
                }

                resultado.push(linhaAtual);
            }

            return {
                textoNormalizado: resultado.join('\n').trim(),
                houveConversao,
            };
        };

        const extrairAcordes = (texto) => {
            const acordes = [];
            const matches = texto.matchAll(/\[([^\[\]\r\n]+)\]/g);

            for (const match of matches) {
                const acorde = (match[1] || '').trim();

                if (ehAcorde(acorde)) {
                    acordes.push(acorde);
                }
            }

            return [...new Set(acordes)];
        };

        const removerCifras = (texto) => {
            return texto.replace(/\[([^\[\]\r\n]+)\]/g, (trechoCompleto, interno) => {
                return ehAcorde(interno) ? '' : trechoCompleto;
            }).trim();
        };

        const escaparHtml = (texto) => {
            return (texto || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const normalizarMarcacao = (texto) => String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

        const ehMarcacaoSecao = (texto) => {
            const normalizada = normalizarMarcacao(texto);
            return normalizada.length <= 32 && /^(refrao|entrada|final|ponte|estrofe|verso)(\b|$)/.test(normalizada);
        };

        const classeMarcacao = (texto, base = 'bg-slate-700/80 text-slate-100') => {
            const normalizada = normalizarMarcacao(texto);
            return normalizada.startsWith('refrao')
                ? 'bg-amber-200 text-slate-950 font-black'
                : base;
        };

        const renderizarLinhaComCifras = (linha) => {
            const linhaLimpa = linha.trim();

            if (!linhaLimpa) {
                return '<div class="h-5"></div>';
            }

            const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
            if (marcacao && !ehAcorde(marcacao[1])) {
                return `<div class="my-2 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classeMarcacao(marcacao[1])}">${escaparHtml(marcacao[1])}</div>`;
            }

            if (ehMarcacaoSecao(linhaLimpa)) {
                return `<div class="my-2 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classeMarcacao(linhaLimpa)}">${escaparHtml(linhaLimpa)}</div>`;
            }

            const html = linha.replace(/\[([^\[\]\r\n]+)\]/g, (trechoCompleto, interno) => {
                const acorde = String(interno || '').trim();

                if (!ehAcorde(acorde)) {
                    return `<span class="text-slate-300">${escaparHtml(trechoCompleto)}</span>`;
                }

                return `<span class="mx-[0.08rem] inline-flex rounded-md bg-slate-800 px-1.5 py-0.5 align-middle text-[0.95rem] font-extrabold leading-none text-orange-300">${escaparHtml(acorde)}</span>`;
            });

            return `<div class="mb-3 whitespace-pre-wrap break-words text-[1.02rem] leading-8 text-slate-100">${html}</div>`;
        };

        const renderizarComCifras = (texto) => {
            const linhas = (texto || '').split('\n');
            const html = linhas.map(renderizarLinhaComCifras).join('');

            return html || '<p class="text-sm text-slate-400">A previa com cifra aparecera aqui.</p>';
        };

        const renderizarSemCifras = (texto) => {
            const linhas = removerCifras(texto).split('\n');
            const blocos = [];

            linhas.forEach((linha) => {
                const linhaLimpa = linha.trim();

                if (!linhaLimpa) {
                    blocos.push('<div class="h-4"></div>');
                    return;
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                if (marcacao && !ehAcorde(marcacao[1])) {
                    const classe = normalizarMarcacao(marcacao[1]).startsWith('refrao')
                        ? 'bg-amber-100 text-amber-900 font-black'
                        : 'bg-indigo-100 text-indigo-700';
                    blocos.push(`<div class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(marcacao[1])}</div>`);
                    return;
                }

                if (ehMarcacaoSecao(linhaLimpa)) {
                    const classe = normalizarMarcacao(linhaLimpa).startsWith('refrao')
                        ? 'bg-amber-100 text-amber-900 font-black'
                        : 'bg-indigo-100 text-indigo-700';
                    blocos.push(`<div class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(linhaLimpa)}</div>`);
                    return;
                }

                blocos.push(`<p class="mb-3 whitespace-pre-wrap break-words text-[1.02rem] leading-8 text-gray-800">${escaparHtml(linhaLimpa)}</p>`);
            });

            return blocos.join('') || '<p class="text-sm text-gray-500">A previa sem cifra aparecera aqui.</p>';
        };

        const atualizarPreview = () => {
            const valor = textarea.value || '';
            const resultado = normalizarFormato(valor);
            const acordesEncontrados = extrairAcordes(resultado.textoNormalizado);
            const acordesInvalidos = acordesEncontrados.filter((acorde) => !bibliotecaAcordes.has(acorde.toUpperCase()));

            previewPadraoInterno.textContent = resultado.textoNormalizado;
            previewComCifras.innerHTML = renderizarComCifras(resultado.textoNormalizado);
            previewSemCifras.innerHTML = renderizarSemCifras(resultado.textoNormalizado);

            if (resultado.houveConversao) {
                painelConversaoAutomatica.classList.remove('hidden');
            } else {
                painelConversaoAutomatica.classList.add('hidden');
            }

            if (acordesInvalidos.length > 0) {
                painelValidacaoCifras.classList.remove('hidden');
                listaAcordesInvalidos.textContent = acordesInvalidos.join(', ');
            } else {
                painelValidacaoCifras.classList.add('hidden');
                listaAcordesInvalidos.textContent = '';
            }
        };

        const inserirNoCursor = (texto) => {
            const inicio = textarea.selectionStart ?? textarea.value.length;
            const fim = textarea.selectionEnd ?? textarea.value.length;
            const atual = textarea.value;

            textarea.value = atual.slice(0, inicio) + texto + atual.slice(fim);
            textarea.focus();

            const novaPosicao = inicio + texto.length;
            textarea.setSelectionRange(novaPosicao, novaPosicao);
            atualizarPreview();
        };

        textarea.addEventListener('input', atualizarPreview);

        formulario?.addEventListener('submit', () => {
            const resultado = normalizarFormato(textarea.value || '');
            textarea.value = resultado.textoNormalizado;
        });

        botoesAcorde.forEach((botao) => {
            botao.addEventListener('click', () => {
                inserirNoCursor(`[${botao.dataset.acorde}]`);
            });
        });

        const ativarPreview = (modo) => {
            botoesPreview.forEach((botao) => {
                const ativo = botao.dataset.previewToggle === modo;
                botao.classList.toggle('bg-green-700', ativo);
                botao.classList.toggle('text-white', ativo);
                botao.classList.toggle('ring-2', ativo);
                botao.classList.toggle('ring-green-200', ativo);
                botao.classList.toggle('border', !ativo);
                botao.classList.toggle('border-gray-200', !ativo);
                botao.classList.toggle('bg-white', !ativo);
                botao.classList.toggle('text-gray-700', !ativo);
                botao.setAttribute('aria-pressed', ativo ? 'true' : 'false');
            });

            paineisPreview.forEach((painel) => {
                painel.classList.toggle('hidden', painel.dataset.previewPanel !== modo);
            });
        };

        const ativarExemplo = (modo) => {
            botoesExemplo.forEach((botao) => {
                const ativo = botao.dataset.exemploToggle === modo;
                botao.classList.toggle('border-blue-500', ativo);
                botao.classList.toggle('bg-blue-100', ativo);
                botao.classList.toggle('text-blue-800', ativo);
            });

            paineisExemplo.forEach((painel) => {
                painel.classList.toggle('hidden', painel.dataset.exemploPainel !== modo);
            });
        };

        botoesPreview.forEach((botao) => {
            botao.addEventListener('click', () => ativarPreview(botao.dataset.previewToggle));
        });

        botoesExemplo.forEach((botao) => {
            botao.addEventListener('click', () => ativarExemplo(botao.dataset.exemploToggle));
        });

        ativarPreview('com-cifras');
        ativarExemplo('colchetes');
        atualizarPreview();
    })();
</script>
@endpush



