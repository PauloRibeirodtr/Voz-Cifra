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
                        <input type="text" name="tom_musical" value="{{ old('tom_musical', $versaoMusical->tom_musical ?? '') }}" placeholder="Ex.: G, Dm, F#m" class="{{ $classeInput }}" />
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

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-white">
                            <i class="fa-solid fa-circle-info"></i>
                        </div>

                        <div class="flex-1 space-y-3 text-sm text-blue-900">
                            <div>
                                <h2 class="text-base font-bold">Como preencher a cifra</h2>
                                <p class="text-blue-800">Voce pode colar a cifra em formato com colchetes ou estilo Cifra Club. O sistema tenta converter automaticamente para o formato interno com colchetes. Depois da conversao, revise a posicao dos acordes antes de salvar, pois alguns casos podem exigir ajuste manual.</p>
                            </div>

                            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                                <div class="rounded-xl border border-green-200 bg-white p-4">
                                    <h3 class="text-sm font-bold text-green-700 mb-3">Formato 1: com colchetes</h3>
                                    <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus</pre>
                                    <p class="mt-3 text-xs text-green-700">Esse ja e o formato interno do sistema.</p>
                                </div>

                                <div class="rounded-xl border border-blue-200 bg-white p-4">
                                    <h3 class="text-sm font-bold text-blue-700 mb-3">Formato 2: estilo Cifra Club</h3>
                                    <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">   G
Quao grande e o meu Deus
      D/F#  Em7
Cantarei quao grande e o meu Deus</pre>
                                    <p class="mt-3 text-xs text-blue-700">Esse formato tambem e aceito. O sistema tenta converter para colchetes, mas vale revisar o resultado antes de salvar.</p>
                                </div>
                            </div>

                            <div class="rounded-xl border border-amber-200 bg-white p-4">
                                <h3 class="text-sm font-bold text-amber-700 mb-3">Exemplo real: Quao grande e o meu Deus</h3>
                                <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">   G
Quao grande e o meu Deus
      D/F#  Em7
Cantarei quao grande e o meu Deus
         Em/D   C9
E todos hao de ver
       C/D           G   C/D
Quao grande e o meu Deus</pre>
                                <p class="mt-3 text-xs text-amber-700">Se colar nesse formato, o sistema tenta converter para a marcacao com colchetes e prioriza um resultado legivel.</p>
                            </div>

                            <div class="rounded-xl border border-red-200 bg-white p-4">
                                <h3 class="text-sm font-bold text-red-700 mb-3">Formato que deve ser evitado</h3>
                                <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800">G D/F# Em7 C9
Quao grande e o meu Deus</pre>
                                <p class="mt-3 text-xs text-red-700">Evite colocar varios acordes soltos na mesma linha sem alinhamento com a letra, porque isso prejudica a conversao automatica.</p>
                            </div>

                            <div class="space-y-1 text-blue-800">
                                <p>O sistema usa os <strong>colchetes</strong> para reconhecer a cifra no formato interno.</p>
                                <p>O preview sem cifras remove automaticamente apenas os acordes reconhecidos.</p>
                                <p>Os acordes sao comparados com a biblioteca cadastrada, mas acordes invalidos nao bloqueiam o salvamento nesta etapa.</p>
                                <p>A biblioteca de acordes ao lado pode ser usada como apoio visual.</p>
                                <p>Ao clicar em um acorde da biblioteca, ele e inserido no cursor do editor.</p>
                                <p>Revise sempre o resultado da conversao antes de salvar.</p>
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
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700">
                    <h3 class="font-semibold text-gray-800 mb-2">Padrao interno gerado pelo sistema</h3>
                    <p class="mb-3 text-xs text-gray-500">Confira se os acordes ficaram em posicoes legiveis antes de salvar.</p>
                    <pre id="preview_padrao_interno" class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-gray-800"></pre>
                                </div>

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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visao interna com cifras</h3>
                    <pre id="preview_com_cifras" class="min-h-[260px] rounded-xl bg-gray-900 text-green-200 p-4 whitespace-pre-wrap break-words text-sm leading-7"></pre>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visao futura sem cifras</h3>
                    <pre id="preview_sem_cifras" class="min-h-[260px] rounded-xl bg-gray-50 text-gray-700 p-4 whitespace-pre-wrap break-words text-sm leading-7 border border-gray-200"></pre>
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

        if (!textarea || !previewComCifras || !previewSemCifras || !previewPadraoInterno) {
            return;
        }

        const ehAcorde = (valor) => {
            const texto = (valor || '').trim();

            if (!texto || texto.includes(' ')) {
                return false;
            }

            return /^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^)]+\\))*(?:\\/[A-G](?:#|b)?)?$/.test(texto);
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

        const atualizarPreview = () => {
            const valor = textarea.value || '';
            const resultado = normalizarFormato(valor);
            const acordesEncontrados = extrairAcordes(resultado.textoNormalizado);
            const acordesInvalidos = acordesEncontrados.filter((acorde) => !acordesValidos.map((item) => item.toUpperCase()).includes(acorde.toUpperCase()));

            previewPadraoInterno.textContent = resultado.textoNormalizado;
            previewComCifras.textContent = resultado.textoNormalizado;
            previewSemCifras.textContent = removerCifras(resultado.textoNormalizado);

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

        atualizarPreview();
    })();
</script>
@endpush


