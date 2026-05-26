@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $letraInicial = old('letra_com_cifras', $versaoMusical->letra_com_cifras ?? $musica->letra ?? '');
@endphp

@push('styles')
    <style>
        .editor-cifra-preview .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.18rem; margin-bottom: 0.72rem; }
        .editor-cifra-preview .cifra-linha--refrao { border-left: 4px solid #f59e0b; background: linear-gradient(90deg, rgba(255, 251, 235, 0.12), rgba(255, 251, 235, 0)); margin: 0.18rem 0 0.74rem; padding: 0.42rem 0 0.42rem 0.85rem; }
        .editor-cifra-preview .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.85rem; }
        .editor-cifra-preview .cifra-acordes { min-height: 1.1rem; margin-bottom: 0.02rem; color: #fb923c; font-weight: 900; font-size: 0.95rem; line-height: 1rem; white-space: pre; }
        .editor-cifra-preview .cifra-letra { color: #f8fafc; font-size: 1.06rem; line-height: 1.9rem; white-space: pre-wrap; }
        .editor-cifra-preview .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #334155; color: #f8fafc; font-size: 0.78rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
        .editor-cifra-preview .cifra-marcacao--refrao { background: #fef3c7; color: #92400e; }
        @media (max-width: 767px) {
            .editor-cifra-preview .cifra-linha { display: block; margin-bottom: 0.8rem; }
            .editor-cifra-preview .cifra-segmento { display: inline-flex; min-height: 2.25rem; max-width: 100%; }
        }

        @media (min-width: 1280px) {
            .preview-cifra-sticky { position: sticky; top: 1rem; }
        }
    </style>
@endpush

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

<div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_minmax(28rem,0.95fr)] gap-6">
    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-5">
                <div data-guide-target="cifra-editor">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Letra com cifras</label>
                        <span class="text-xs text-gray-500">Cole cifras com acordes em cima da letra ou entre colchetes. O sistema prepara o formato ao salvar.</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button type="button" class="rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-black text-amber-800 hover:bg-amber-100" data-inserir-marcacao="Refrão:\n">
                            Inserir Refrão
                        </button>
                        <button type="button" class="rounded-full border border-amber-300 bg-white px-4 py-2 text-xs font-black text-amber-900 hover:bg-amber-50" data-marcar-linha="Refrão:">
                            Marcar linha como Refrão
                        </button>
                        <button type="button" class="rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-xs font-black text-indigo-700 hover:bg-indigo-100" data-inserir-marcacao="[Primeira parte]\n">
                            Inserir Parte
                        </button>
                        <button type="button" class="rounded-full border border-indigo-300 bg-white px-4 py-2 text-xs font-black text-indigo-800 hover:bg-indigo-50" data-marcar-linha="[Primeira parte]">
                            Transformar linha em Parte
                        </button>
                        <button type="button" class="rounded-full border border-orange-200 bg-orange-50 px-4 py-2 text-xs font-black text-orange-700 hover:bg-orange-100" data-inserir-marcacao="[D] [C]\n">
                            Acordes antes da parte
                        </button>
                        <button type="button" class="rounded-full border border-sky-300 bg-sky-50 px-4 py-2 text-xs font-black text-sky-800 hover:bg-sky-100" data-cifra-club-mode>
                            Colar formato Cifra Club
                        </button>
                        <button type="button" class="rounded-full border border-green-700 bg-green-700 px-5 py-2 text-xs font-black text-white shadow-sm hover:bg-green-800" data-organizar-cifra-visual data-guide-target="cifra-organizar">
                            Arrumar cifra automaticamente
                        </button>
                    </div>
                    <div id="cifra_club_hint" class="mt-3 hidden rounded-xl border border-sky-200 bg-sky-50 p-3 text-sm text-sky-800">
                        Cole a cifra inteira abaixo. Depois clique em <strong>Arrumar cifra automaticamente</strong> para alinhar acordes, refrões e partes antes de salvar.
                    </div>
                    <textarea id="letra_com_cifras" name="letra_com_cifras" rows="18" required placeholder="[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus" class="{{ $classeInput }} font-mono text-sm">{{ $letraInicial }}</textarea>

                    <div class="mt-3 space-y-3">
                        <div id="painel_rascunho_cifra" class="hidden rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <span>Existe um rascunho salvo neste navegador para esta cifra.</span>
                                <button type="button" class="rounded-lg border border-blue-300 bg-white px-3 py-2 text-xs font-black text-blue-800 hover:bg-blue-100" data-descartar-rascunho-cifra>
                                    Descartar rascunho
                                </button>
                            </div>
                        </div>

                        <div id="painel_alertas_cifra" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" data-guide-target="cifra-alertas">
                            <h3 class="mb-2 font-black">Antes de salvar, vale revisar</h3>
                            <ul id="lista_alertas_cifra" class="list-disc space-y-1 pl-5"></ul>
                            <p class="mt-2 text-xs">Esses avisos nao bloqueiam o salvamento. Eles so ajudam a evitar cifra incompleta ou desalinhada.</p>
                        </div>

                        <div id="painel_validacao_cifras" class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            <h3 class="font-bold mb-2">Acordes nao encontrados na biblioteca</h3>
                            <p id="lista_acordes_invalidos"></p>
                            <p class="mt-2 text-xs">O salvamento continua liberado, mas vale revisar esses acordes.</p>
                        </div>

                        <div id="painel_conversao_automatica" class="hidden rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                            O texto colado foi reconhecido e sera salvo no padrao interno com colchetes. Revise a previa antes de salvar.
                        </div>

                        <pre id="preview_padrao_interno" class="hidden"></pre>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4" data-guide-target="cifra-musica-base">
                    <label class="block text-sm font-medium text-gray-700">Musica base</label>
                    <input type="text" value="{{ $musica->titulo }}" disabled class="{{ $classeInput }} bg-gray-50 text-gray-500 cursor-not-allowed" />
                </div>

                <div data-guide-target="cifra-titulo">
                    <label class="block text-sm font-medium text-gray-700">Titulo da versao</label>
                    <input type="text" name="titulo" value="{{ old('titulo', $versaoMusical->titulo ?? '') }}" placeholder="Ex.: Tom original, Versao para assembleia" class="{{ $classeInput }}" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" data-guide-target="cifra-tom-bpm">
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
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">BPM</label>
                        <input type="number" name="bpm" min="1" max="999" value="{{ old('bpm', $versaoMusical->bpm ?? '') }}" placeholder="Ex.: 72" class="{{ $classeInput }}" />
                    </div>
                </div>

                <div data-guide-target="cifra-youtube">
                    <label class="block text-sm font-medium text-gray-700">YouTube video ID</label>
                    <input type="text" name="youtube_video_id" value="{{ old('youtube_video_id', $versaoMusical->youtube_video_id ?? '') }}" placeholder="Ex.: dQw4w9WgXcQ ou cole a URL" class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $versaoMusical->ativo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Versao ativa</label>
                </div>
            </div>
        </div>

    </div>

    <div class="space-y-6">
        <div class="preview-cifra-sticky bg-white p-6 rounded-2xl shadow-sm border border-gray-100" data-guide-target="cifra-preview">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Pr&eacute;via da cifra</h2>
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
                    <div id="preview_com_cifras" class="editor-cifra-preview min-h-[520px] max-h-[72vh] rounded-xl bg-slate-900 p-5 text-green-100 border border-slate-800 overflow-auto"></div>
                </div>

                <div class="hidden" data-preview-panel="sem-cifras">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Visao sem cifra</h3>
                    <div id="preview_sem_cifras" class="min-h-[520px] max-h-[72vh] rounded-xl bg-gray-50 p-5 text-gray-800 border border-gray-200 overflow-auto"></div>
                </div>
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
        const painelAlertasCifra = document.getElementById('painel_alertas_cifra');
        const listaAlertasCifra = document.getElementById('lista_alertas_cifra');
        const painelRascunhoCifra = document.getElementById('painel_rascunho_cifra');
        const botaoDescartarRascunho = document.querySelector('[data-descartar-rascunho-cifra]');
        const dicaCifraClub = document.getElementById('cifra_club_hint');
        const acordesValidos = @json($acordesValidos ?? []);
        const bibliotecaAcordes = new Set(acordesValidos.map((item) => String(item).toUpperCase()));
        const botoesPreview = document.querySelectorAll('[data-preview-toggle]');
        const paineisPreview = document.querySelectorAll('[data-preview-panel]');
        const botoesExemplo = document.querySelectorAll('[data-exemplo-toggle]');
        const paineisExemplo = document.querySelectorAll('[data-exemplo-painel]');
        const botoesMarcacao = document.querySelectorAll('[data-inserir-marcacao]');
        const botoesMarcarLinha = document.querySelectorAll('[data-marcar-linha]');
        const botaoOrganizarCifra = document.querySelector('[data-organizar-cifra-visual]');
        const botaoCifraClub = document.querySelector('[data-cifra-club-mode]');
        const chaveRascunho = `voz-cifra-rascunho-cifra:${window.location.pathname}`;
        const valorInicialTextarea = textarea?.value || '';

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

        const normalizarTokenAcorde = (token) => {
            let texto = (token || '').trim();
            const entreColchetes = texto.match(/^\[([^\[\]\r\n]+)\]$/);

            if (entreColchetes) {
                texto = entreColchetes[1].trim();
            }

            return ehAcorde(texto) ? texto : null;
        };

        const ehLinhaSomenteAcordes = (linha) => {
            const linhaOriginal = linha;

            if (!ehLinhaApenasAcordes(linha)) {
                return false;
            }

            const tokens = linha.trim().split(/\s+/).filter(Boolean);

            if (tokens.length === 1 && !/^\s+/.test(linhaOriginal)) {
                return false;
            }

            return true;
        };

        const ehLinhaApenasAcordes = (linha) => {
            const texto = linha.trim();

            if (!texto || ehLinhaTablatura(texto)) {
                return false;
            }

            const tokens = texto.split(/\s+/).filter(Boolean);

            if (!(tokens.length > 0 && tokens.every((token) => normalizarTokenAcorde(token) !== null))) {
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

                if (offset < inicio) {
                    return inicio;
                }

                if (offset <= fim) {
                    return offset;
                }
            }

            return palavras[palavras.length - 1]?.index ?? 0;
        };

        const combinarLinhaDeAcordesComLetra = (linhaAcordes, linhaLetra) => {
            const tokens = Array.from(linhaAcordes.matchAll(/\S+/g));
            let resultado = linhaLetra;

            tokens.reverse().forEach((token) => {
                const acorde = normalizarTokenAcorde(token[0]);
                const posicao = localizarPosicaoSeguraNaLetra(resultado, token.index ?? 0);

                if (!acorde) {
                    return;
                }

                resultado = resultado.slice(0, posicao) + `[${acorde}]` + resultado.slice(posicao);
            });

            return resultado;
        };

        const converterLinhaSomenteAcordesParaCifras = (linhaAcordes) => {
            return linhaAcordes.replace(/\S+/g, (token) => {
                const acorde = normalizarTokenAcorde(token);
                return acorde ? `[${acorde}]` : token;
            });
        };

        const normalizarMarcacao = (texto) => String(texto || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();

        const normalizarMarcacaoLinha = (linha) => {
            let texto = String(linha || '').trim();
            const marcacao = texto.match(/^\[(.+)\]$/);

            if (marcacao && !ehAcorde(marcacao[1])) {
                texto = marcacao[1].trim();
            }

            const normalizada = normalizarMarcacao(texto);

            if (/^(refrao|refr\.?|ref)(?::|\s|$)/.test(normalizada)) {
                return 'Refrão:';
            }

            return null;
        };

        const ehMarcacaoSecao = (texto) => {
            const linhaLimpa = String(texto || '').trim();
            const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
            const textoMarcacao = marcacao && !ehAcorde(marcacao[1]) ? marcacao[1] : linhaLimpa;
            const normalizada = normalizarMarcacao(textoMarcacao);
            return normalizada.length <= 32 && /^(intro|refrao:?|pre[-\s]?refrao:?|refr\.?|ref:|entrada|final|ponte|estrofe|verso|primeira parte|segunda parte|terceira parte)(?:\s|$)/.test(normalizada);
        };

        const linhaAnteriorEhMarcacao = (linhas, indiceAtual) => {
            for (let indice = indiceAtual - 1; indice >= 0; indice--) {
                const linha = String(linhas[indice] || '').trim();

                if (linha === '') {
                    continue;
                }

                return ehMarcacaoSecao(linha);
            }

            return false;
        };

        const normalizarFormato = (textoBruto) => {
            const texto = (textoBruto || '')
                .replace(/\\n/g, '\n')
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n')
                .replace(/\n{3,}/g, '\n\n')
                .replace(/^\n+|\n+$/g, '');
            const linhas = texto.split('\n');
            const resultado = [];
            let houveConversao = false;

            for (let i = 0; i < linhas.length; i++) {
                const linhaAtual = linhas[i].replace(/\s+$/g, '');
                const proximaLinha = linhas[i + 1];
                const marcacaoNormalizada = normalizarMarcacaoLinha(linhaAtual);

                if (marcacaoNormalizada) {
                    resultado.push(marcacaoNormalizada);
                    houveConversao = marcacaoNormalizada !== linhaAtual.trim() || houveConversao;
                    continue;
                }

                if (ehLinhaApenasAcordes(linhaAtual) && linhaAnteriorEhMarcacao(linhas, i)) {
                    resultado.push(converterLinhaSomenteAcordesParaCifras(linhaAtual));
                    houveConversao = true;
                    continue;
                }

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

                if (ehLinhaSomenteAcordes(linhaAtual)) {
                    resultado.push(converterLinhaSomenteAcordesParaCifras(linhaAtual));
                    houveConversao = true;
                    continue;
                }

                resultado.push(linhaAtual);
            }

            return {
                textoNormalizado: resultado.join('\n').replace(/\n{3,}/g, '\n\n').replace(/^\n+|\n+$/g, ''),
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

        const detectarAlertasCifra = (textoNormalizado) => {
            const linhas = (textoNormalizado || '').split('\n');
            const alertas = [];
            let refraoVazio = false;
            let muitosAcordesSemLetra = false;
            let linhasTextoSemCifra = 0;

            linhas.forEach((linha, indice) => {
                const linhaLimpa = linha.trim();

                if (!linhaLimpa) {
                    return;
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                const textoMarcacao = marcacao && !ehAcorde(marcacao[1]) ? marcacao[1] : linhaLimpa;
                const normalizada = normalizarMarcacao(textoMarcacao);

                if (/^(refrao:?|refr\.?|ref:)(?:\s|$)/.test(normalizada)) {
                    let encontrouLetra = false;

                    for (let proxima = indice + 1; proxima < linhas.length; proxima++) {
                        const proximaLinha = String(linhas[proxima] || '').trim();

                        if (!proximaLinha) {
                            break;
                        }

                        if (ehMarcacaoSecao(proximaLinha)) {
                            break;
                        }

                        if (removerCifras(proximaLinha).trim() !== '') {
                            encontrouLetra = true;
                            break;
                        }
                    }

                    if (!encontrouLetra) {
                        refraoVazio = true;
                    }

                    return;
                }

                if (ehLinhaApenasAcordes(linha) && linhaLimpa.split(/\s+/).length >= 4) {
                    muitosAcordesSemLetra = true;
                    return;
                }

                if (!linha.includes('[') && !ehMarcacaoSecao(linhaLimpa) && removerCifras(linhaLimpa).trim().length >= 18) {
                    linhasTextoSemCifra += 1;
                }
            });

            if (refraoVazio) {
                alertas.push('Existe Refrão sem letra logo abaixo.');
            }

            if (muitosAcordesSemLetra) {
                alertas.push('Ha uma linha com muitos acordes seguidos sem letra. Confira se e uma preparacao ou se faltou alinhar com a frase.');
            }

            if (linhasTextoSemCifra >= 4) {
                alertas.push('Varias linhas de texto nao possuem cifra. Se isso for proposital, pode salvar normalmente.');
            }

            return alertas;
        };

        const colocarTextoEmLinha = (linha, posicao, texto) => {
            const caracteres = linha.split('');
            let inicio = Math.max(0, posicao);

            while (inicio > 0 && caracteres.slice(inicio, inicio + texto.length).some((caractere) => caractere && caractere !== ' ')) {
                inicio++;
            }

            for (let i = 0; i < texto.length; i++) {
                caracteres[inicio + i] = texto[i];
            }

            return caracteres.join('');
        };

        const converterLinhaParaEdicaoVisual = (linha) => {
            if (!linha.includes('[')) {
                return linha;
            }

            const linhaLimpa = linha.trim();
            const marcacao = linhaLimpa.match(/^\[(.+)\]$/);

            if (marcacao && !ehAcorde(marcacao[1])) {
                return linha;
            }

            let textoLetra = '';
            let linhaAcordes = '';
            let acordesPendentes = [];
            let posicaoAtual = 0;
            const matches = Array.from(linha.matchAll(/\[([^\[\]\r\n]+)\]/g));

            matches.forEach((match) => {
                const textoAntes = linha.slice(posicaoAtual, match.index);

                if (textoAntes !== '') {
                    if (acordesPendentes.length > 0) {
                        linhaAcordes = colocarTextoEmLinha(linhaAcordes.padEnd(textoLetra.length, ' '), textoLetra.length, acordesPendentes.join(' '));
                        acordesPendentes = [];
                    }

                    textoLetra += textoAntes;
                }

                const conteudo = String(match[1] || '').trim();

                if (ehAcorde(conteudo)) {
                    acordesPendentes.push(conteudo);
                } else {
                    if (acordesPendentes.length > 0) {
                        linhaAcordes = colocarTextoEmLinha(linhaAcordes.padEnd(textoLetra.length, ' '), textoLetra.length, acordesPendentes.join(' '));
                        acordesPendentes = [];
                    }

                    textoLetra += match[0];
                }

                posicaoAtual = (match.index || 0) + match[0].length;
            });

            const textoFinal = linha.slice(posicaoAtual);

            if (acordesPendentes.length > 0) {
                linhaAcordes = colocarTextoEmLinha(linhaAcordes.padEnd(textoLetra.length, ' '), textoLetra.length, acordesPendentes.join(' '));
            }

            textoLetra += textoFinal;

            if (linhaAcordes.trim() === '') {
                return textoLetra;
            }

            return `${linhaAcordes.replace(/\s+$/g, '')}\n${textoLetra.replace(/\s+$/g, '')}`;
        };

        const converterTextoParaEdicaoVisual = (texto) => {
            return (texto || '')
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n')
                .split('\n')
                .map(converterLinhaParaEdicaoVisual)
                .join('\n')
                .replace(/\n{4,}/g, '\n\n\n')
                .replace(/^\n+|\n+$/g, '');
        };

        const removerCifras = (texto) => {
            return texto.replace(/\[([^\[\]\r\n]+)\]/g, (trechoCompleto, interno) => {
                return ehAcorde(interno) ? '' : trechoCompleto;
            }).replace(/\n{3,}/g, '\n\n').trim();
        };

        const escaparHtml = (texto) => {
            return (texto || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const classeMarcacao = (texto, base = 'bg-slate-700/80 text-slate-100') => {
            const normalizada = normalizarMarcacao(texto);
            return /^(refrao:?|refr\.?|ref:)(?:\s|$)/.test(normalizada)
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
                return `<div class="cifra-marcacao ${normalizarMarcacao(marcacao[1]).startsWith('refrao') ? 'cifra-marcacao--refrao' : ''}">${escaparHtml(marcacao[1])}</div>`;
            }

            if (ehMarcacaoSecao(linhaLimpa)) {
                return `<div class="cifra-marcacao ${normalizarMarcacao(linhaLimpa).startsWith('refrao') ? 'cifra-marcacao--refrao' : ''}">${escaparHtml(linhaLimpa)}</div>`;
            }

            const segmentos = [];
            let acordesPendentes = [];
            let posicaoAtual = 0;
            const matches = Array.from(linha.matchAll(/\[([^\[\]\r\n]+)\]/g));

            matches.forEach((match) => {
                const textoAntes = linha.slice(posicaoAtual, match.index);

                if (textoAntes !== '') {
                    segmentos.push({
                        acordes: acordesPendentes,
                        texto: textoAntes,
                    });
                    acordesPendentes = [];
                }

                const conteudo = String(match[1] || '').trim();

                if (ehAcorde(conteudo)) {
                    acordesPendentes.push(conteudo);
                } else {
                    segmentos.push({
                        acordes: [],
                        texto: match[0],
                    });
                }

                posicaoAtual = (match.index || 0) + match[0].length;
            });

            const textoFinal = linha.slice(posicaoAtual);

            if (textoFinal !== '' || acordesPendentes.length > 0) {
                segmentos.push({
                    acordes: acordesPendentes,
                    texto: textoFinal || ' ',
                });
            }

            const html = segmentos.map((segmento) => {
                const acordes = segmento.acordes.map((acorde) => `<span>${escaparHtml(acorde)}</span>`).join(' ');

                return `<span class="cifra-segmento"><span class="cifra-acordes">${acordes}</span><span class="cifra-letra">${escaparHtml(segmento.texto)}</span></span>`;
            }).join('');

            return `<div class="cifra-linha">${html}</div>`;
        };

        const renderizarComCifras = (texto) => {
            const linhas = (texto || '').split('\n');
            let proximaLinhaRefrao = false;
            let blocoAtualRefrao = false;
            const html = linhas.map((linha) => {
                const linhaLimpa = linha.trim();

                if (!linhaLimpa) {
                    blocoAtualRefrao = false;
                    return renderizarLinhaComCifras(linha);
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                const textoMarcacao = marcacao && !ehAcorde(marcacao[1])
                    ? marcacao[1]
                    : (ehMarcacaoSecao(linhaLimpa) ? linhaLimpa : null);

                if (textoMarcacao) {
                    blocoAtualRefrao = false;
                    proximaLinhaRefrao = normalizarMarcacao(textoMarcacao).startsWith('refrao') || normalizarMarcacao(textoMarcacao).startsWith('ref:');
                    return renderizarLinhaComCifras(linha);
                }

                if (proximaLinhaRefrao) {
                    blocoAtualRefrao = true;
                    proximaLinhaRefrao = false;
                }

                const classeRefrao = blocoAtualRefrao ? ' cifra-linha--refrao' : '';

                return `<div class="${classeRefrao}">${renderizarLinhaComCifras(linha)}</div>`;
            }).join('');

            return html || '<p class="text-sm text-slate-400">A previa com cifra aparecera aqui.</p>';
        };

        const renderizarSemCifras = (texto) => {
            const linhas = removerCifras(texto).split('\n');
            const blocos = [];
            let proximoBlocoRefrao = false;
            let blocoAtualRefrao = false;

            linhas.forEach((linha) => {
                const linhaLimpa = linha.trim();

                if (!linhaLimpa) {
                    blocoAtualRefrao = false;
                    blocos.push('<div class="h-4"></div>');
                    return;
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                if (marcacao && !ehAcorde(marcacao[1])) {
                    const classe = normalizarMarcacao(marcacao[1]).startsWith('refrao')
                        ? 'bg-amber-100 text-amber-900 font-black'
                        : 'bg-indigo-100 text-indigo-700';
                    blocos.push(`<div class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(marcacao[1])}</div>`);
                    proximoBlocoRefrao = normalizarMarcacao(marcacao[1]).startsWith('refrao') || normalizarMarcacao(marcacao[1]).startsWith('ref:');
                    return;
                }

                if (ehMarcacaoSecao(linhaLimpa)) {
                    const classe = normalizarMarcacao(linhaLimpa).startsWith('refrao')
                        ? 'bg-amber-100 text-amber-900 font-black'
                        : 'bg-indigo-100 text-indigo-700';
                    blocos.push(`<div class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(linhaLimpa)}</div>`);
                    proximoBlocoRefrao = normalizarMarcacao(linhaLimpa).startsWith('refrao') || normalizarMarcacao(linhaLimpa).startsWith('ref:');
                    return;
                }

                if (proximoBlocoRefrao) {
                    blocoAtualRefrao = true;
                    proximoBlocoRefrao = false;
                }

                const classeBloco = blocoAtualRefrao
                    ? 'border-amber-200 bg-amber-50 text-amber-950 font-bold'
                    : 'border-gray-200 bg-gray-50 text-gray-800';
                blocos.push(`<div class="mb-3 rounded-xl border px-4 py-3 ${classeBloco}"><p class="whitespace-pre-wrap break-words text-[1.02rem] leading-8">${escaparHtml(linhaLimpa)}</p></div>`);
            });

            return blocos.join('') || '<p class="text-sm text-gray-500">A previa sem cifra aparecera aqui.</p>';
        };

        const atualizarPreview = () => {
            const valor = textarea.value || '';
            const resultado = normalizarFormato(valor);
            const acordesEncontrados = extrairAcordes(resultado.textoNormalizado);
            const acordesInvalidos = acordesEncontrados.filter((acorde) => !bibliotecaAcordes.has(acorde.toUpperCase()));
            const alertas = detectarAlertasCifra(resultado.textoNormalizado);

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

            if (alertas.length > 0 && painelAlertasCifra && listaAlertasCifra) {
                painelAlertasCifra.classList.remove('hidden');
                listaAlertasCifra.innerHTML = alertas.map((alerta) => `<li>${escaparHtml(alerta)}</li>`).join('');
            } else if (painelAlertasCifra && listaAlertasCifra) {
                painelAlertasCifra.classList.add('hidden');
                listaAlertasCifra.innerHTML = '';
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

        const substituirLinhaAtual = (texto) => {
            const inicioSelecao = textarea.selectionStart ?? 0;
            const fimSelecao = textarea.selectionEnd ?? inicioSelecao;
            const valor = textarea.value;
            const inicioLinha = valor.lastIndexOf('\n', Math.max(0, inicioSelecao - 1)) + 1;
            const fimLinhaEncontrado = valor.indexOf('\n', fimSelecao);
            const fimLinha = fimLinhaEncontrado === -1 ? valor.length : fimLinhaEncontrado;
            const textoComQuebra = texto.endsWith('\n') ? texto : `${texto}\n`;

            textarea.value = `${valor.slice(0, inicioLinha)}${textoComQuebra}${valor.slice(fimLinha + (fimLinhaEncontrado === -1 ? 0 : 1))}`;
            textarea.focus();
            const novaPosicao = inicioLinha + textoComQuebra.length;
            textarea.setSelectionRange(novaPosicao, novaPosicao);
            atualizarPreview();
        };

        const salvarRascunhoLocal = () => {
            const valorAtual = textarea.value || '';

            if (valorAtual !== valorInicialTextarea) {
                localStorage.setItem(chaveRascunho, valorAtual);
            }
        };

        const rascunhoSalvo = localStorage.getItem(chaveRascunho);
        if (rascunhoSalvo && rascunhoSalvo !== valorInicialTextarea) {
            textarea.value = rascunhoSalvo;
            painelRascunhoCifra?.classList.remove('hidden');
        }

        textarea.addEventListener('input', () => {
            atualizarPreview();
            salvarRascunhoLocal();
        });

        formulario?.addEventListener('submit', () => {
            const resultado = normalizarFormato(textarea.value || '');
            textarea.value = resultado.textoNormalizado;
            localStorage.removeItem(chaveRascunho);
        });

        botaoDescartarRascunho?.addEventListener('click', () => {
            localStorage.removeItem(chaveRascunho);
            painelRascunhoCifra?.classList.add('hidden');
        });

        botoesAcorde.forEach((botao) => {
            botao.addEventListener('click', () => {
                inserirNoCursor(`[${botao.dataset.acorde}]`);
            });
        });

        botoesMarcacao.forEach((botao) => {
            botao.addEventListener('click', () => {
                inserirNoCursor(String(botao.dataset.inserirMarcacao || '').replace(/\\n/g, '\n'));
            });
        });

        botoesMarcarLinha.forEach((botao) => {
            botao.addEventListener('click', () => {
                substituirLinhaAtual(String(botao.dataset.marcarLinha || ''));
            });
        });

        botaoOrganizarCifra?.addEventListener('click', () => {
            const confirmar = window.confirm('Vou converter acordes acima da letra, padronizar refroes e preparar a cifra para salvar. Deseja continuar?');

            if (!confirmar) {
                return;
            }

            textarea.value = converterTextoParaEdicaoVisual(textarea.value || '');
            textarea.focus();
            atualizarPreview();
            salvarRascunhoLocal();
        });

        botaoCifraClub?.addEventListener('click', () => {
            dicaCifraClub?.classList.toggle('hidden');
            textarea.focus();
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

        textarea.value = converterTextoParaEdicaoVisual(textarea.value || '');
        ativarPreview('com-cifras');
        ativarExemplo('colchetes');
        atualizarPreview();
    })();
</script>
@endpush
