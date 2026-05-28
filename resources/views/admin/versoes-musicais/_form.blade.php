@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $letraInicial = old('letra_com_cifras', $versaoMusical->letra_com_cifras ?? $musica->letra ?? '');
@endphp

@push('styles')
    <style>
        .editor-cifra-preview .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.18rem; margin-bottom: 0.72rem; }
        .editor-cifra-preview .cifra-linha--refrao { border-left: 3px solid #d6ad6c; background: linear-gradient(90deg, rgba(214, 173, 108, 0.1), rgba(255, 255, 255, 0)); margin: 0.18rem 0 0.74rem; padding: 0.35rem 0 0.35rem 0.75rem; }
        .editor-cifra-preview .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.85rem; }
        .editor-cifra-preview .cifra-acordes { min-height: 1.1rem; margin-bottom: 0.02rem; color: #d97706; font-weight: 900; font-size: 0.95rem; line-height: 1rem; white-space: pre; }
        .editor-cifra-preview .cifra-letra { color: #172033; font-size: 1.06rem; line-height: 1.9rem; white-space: pre-wrap; }
        .editor-cifra-preview .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #eef2f7; color: #334155; font-size: 0.78rem; font-weight: 900; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
        .editor-cifra-preview .cifra-marcacao--refrao { background: #f7ead4; color: #6c4a21; }
        .editor-cifra-preview [data-preview-line] { scroll-margin: 1rem; transition: background-color 0.18s ease, box-shadow 0.18s ease; }
        .editor-cifra-preview [data-preview-line].is-current-line { border-radius: 0.85rem; background: rgba(16, 185, 129, 0.08); box-shadow: inset 3px 0 0 rgba(16, 185, 129, 0.65); }
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
                    <textarea id="letra_com_cifras" name="letra_com_cifras" rows="18" required spellcheck="false" autocomplete="off" autocapitalize="off" placeholder="[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver
[C9]Quao grande e o meu Deus" class="{{ $classeInput }} font-mono text-sm">{{ $letraInicial }}</textarea>

                    <div class="mt-3">
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
                    <div id="preview_com_cifras" class="editor-cifra-preview min-h-[520px] max-h-[72vh] rounded-xl border border-[#ead6b3] bg-white p-5 text-gray-900 overflow-auto" style="background:#ffffff;color:#172033;"></div>
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
        const dicaCifraClub = document.getElementById('cifra_club_hint');
        const botoesPreview = document.querySelectorAll('[data-preview-toggle]');
        const paineisPreview = document.querySelectorAll('[data-preview-panel]');
        const botoesExemplo = document.querySelectorAll('[data-exemplo-toggle]');
        const paineisExemplo = document.querySelectorAll('[data-exemplo-painel]');
        const botoesMarcacao = document.querySelectorAll('[data-inserir-marcacao]');
        const botoesMarcarLinha = document.querySelectorAll('[data-marcar-linha]');
        const botaoOrganizarCifra = document.querySelector('[data-organizar-cifra-visual]');
        const botaoCifraClub = document.querySelector('[data-cifra-club-mode]');
        let previewSyncTimer = null;
        let ignorarScrollPreview = false;

        if (!textarea || !previewComCifras || !previewSemCifras || !previewPadraoInterno) {
            return;
        }

        const limparLinhaAcordes = (linha) => {
            return String(linha || '')
                .trim()
                .replace(/^\(\s*/, '')
                .replace(/\s*\)$/, '')
                .replace(/[|]+$/g, '')
                .trim();
        };

        const ehAcorde = (valor) => {
            const texto = (valor || '').trim();

            if (!texto || texto.includes(' ')) {
                return false;
            }

            return /^[A-G](?:#|b)?[A-Za-z0-9#bº°()+\-\/]*(?:\/[A-G](?:#|b)?[A-Za-z0-9#bº°()+\-\/]*)?$/i.test(texto);
        };

        const ehLinhaTablatura = (linha) => {
            const texto = linha.trim();
            return /^[EABDGBe]\|/.test(texto) || texto.includes('|---') || texto.includes('Parte ');
        };

        const normalizarTokenAcorde = (token) => {
            let texto = (token || '').trim().replace(/^[([]+/, '').replace(/[)\],.;]+$/, '');
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
            const texto = limparLinhaAcordes(linha);

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
            return limparLinhaAcordes(linhaAcordes).replace(/\S+/g, (token) => {
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

        const separarMarcacaoEAcordes = (linha) => {
            const match = String(linha || '').trim().match(/^\[([^\[\]\r\n]+)\]\s+(.+)$/);

            if (!match || ehAcorde(match[1])) {
                return null;
            }

            const resto = match[2] || '';

            if (!ehLinhaApenasAcordes(resto)) {
                return null;
            }

            return {
                marcacao: `[${match[1].trim()}]`,
                acordes: converterLinhaSomenteAcordesParaCifras(resto),
            };
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
                const marcacaoEAcordes = separarMarcacaoEAcordes(linhaAtual);
                const marcacaoNormalizada = normalizarMarcacaoLinha(linhaAtual);

                if (marcacaoEAcordes) {
                    resultado.push(marcacaoEAcordes.marcacao);
                    resultado.push(marcacaoEAcordes.acordes);
                    houveConversao = true;
                    continue;
                }

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
            const html = linhas.map((linha, indiceLinha) => {
                const linhaLimpa = linha.trim();
                const abrirLinha = `<div data-preview-line="${indiceLinha}">`;
                const fecharLinha = '</div>';

                if (!linhaLimpa) {
                    blocoAtualRefrao = false;
                    return `${abrirLinha}${renderizarLinhaComCifras(linha)}${fecharLinha}`;
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                const textoMarcacao = marcacao && !ehAcorde(marcacao[1])
                    ? marcacao[1]
                    : (ehMarcacaoSecao(linhaLimpa) ? linhaLimpa : null);

                if (textoMarcacao) {
                    blocoAtualRefrao = false;
                    proximaLinhaRefrao = normalizarMarcacao(textoMarcacao).startsWith('refrao') || normalizarMarcacao(textoMarcacao).startsWith('ref:');
                    return `${abrirLinha}${renderizarLinhaComCifras(linha)}${fecharLinha}`;
                }

                if (proximaLinhaRefrao) {
                    blocoAtualRefrao = true;
                    proximaLinhaRefrao = false;
                }

                const classeRefrao = blocoAtualRefrao ? ' cifra-linha--refrao' : '';

                return `<div data-preview-line="${indiceLinha}" class="${classeRefrao}">${renderizarLinhaComCifras(linha)}</div>`;
            }).join('');

            return html || '<p class="text-sm text-slate-400">A previa com cifra aparecera aqui.</p>';
        };

        const renderizarSemCifras = (texto) => {
            const linhas = removerCifras(texto).split('\n');
            const blocos = [];
            let proximoBlocoRefrao = false;
            let blocoAtualRefrao = false;
            const ehMarcacaoInstrumental = (textoMarcacao) => {
                const normalizada = normalizarMarcacao(textoMarcacao);
                return /^(intro|introducao|final|solo|instrumental)(?:\s|$)/.test(normalizada);
            };

            linhas.forEach((linha, indiceLinha) => {
                const linhaLimpa = linha.trim();
                const atributoLinha = `data-preview-line="${indiceLinha}"`;

                if (!linhaLimpa) {
                    blocoAtualRefrao = false;
                    blocos.push(`<div ${atributoLinha} class="h-4"></div>`);
                    return;
                }

                const marcacao = linhaLimpa.match(/^\[(.+)\]$/);
                if (marcacao && !ehAcorde(marcacao[1])) {
                    if (ehMarcacaoInstrumental(marcacao[1])) {
                        blocoAtualRefrao = false;
                        proximoBlocoRefrao = false;
                        return;
                    }

                    const classe = normalizarMarcacao(marcacao[1]).startsWith('refrao')
                        ? 'bg-[#f7ead4] text-[#6c4a21] font-black'
                        : 'bg-gray-100 text-gray-700';
                    blocos.push(`<div ${atributoLinha} class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(marcacao[1])}</div>`);
                    proximoBlocoRefrao = normalizarMarcacao(marcacao[1]).startsWith('refrao') || normalizarMarcacao(marcacao[1]).startsWith('ref:');
                    return;
                }

                if (ehMarcacaoSecao(linhaLimpa)) {
                    if (ehMarcacaoInstrumental(linhaLimpa)) {
                        blocoAtualRefrao = false;
                        proximoBlocoRefrao = false;
                        return;
                    }

                    const classe = normalizarMarcacao(linhaLimpa).startsWith('refrao')
                        ? 'bg-[#f7ead4] text-[#6c4a21] font-black'
                        : 'bg-gray-100 text-gray-700';
                    blocos.push(`<div ${atributoLinha} class="my-4 inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[0.16em] ${classe}">${escaparHtml(linhaLimpa)}</div>`);
                    proximoBlocoRefrao = normalizarMarcacao(linhaLimpa).startsWith('refrao') || normalizarMarcacao(linhaLimpa).startsWith('ref:');
                    return;
                }

                if (proximoBlocoRefrao) {
                    blocoAtualRefrao = true;
                    proximoBlocoRefrao = false;
                }

                const classeBloco = blocoAtualRefrao
                    ? 'border-[#ead6b3] bg-[#fff8ed] text-gray-900 font-semibold'
                    : 'border-gray-200 bg-gray-50 text-gray-800';
                blocos.push(`<div ${atributoLinha} class="mb-3 rounded-xl border px-4 py-3 ${classeBloco}"><p class="whitespace-pre-wrap break-words text-[1.02rem] leading-8">${escaparHtml(linhaLimpa)}</p></div>`);
            });

            return blocos.join('') || '<p class="text-sm text-gray-500">A previa sem cifra aparecera aqui.</p>';
        };

        const obterLinhaAtualTextarea = () => {
            const inicio = textarea.selectionStart ?? 0;
            return textarea.value.slice(0, inicio).split('\n').length - 1;
        };

        const destacarLinhaPreview = (indiceLinha, rolar = false) => {
            const paineis = [previewComCifras, previewSemCifras];

            paineis.forEach((painel) => {
                const linhas = painel.querySelectorAll('[data-preview-line]');
                let alvo = painel.querySelector(`[data-preview-line="${indiceLinha}"]`);

                if (!alvo && linhas.length > 0) {
                    alvo = linhas[Math.min(indiceLinha, linhas.length - 1)];
                }

                linhas.forEach((linha) => linha.classList.toggle('is-current-line', linha === alvo));

                if (rolar && alvo) {
                    ignorarScrollPreview = true;
                    painel.scrollTo({
                        top: Math.max(0, alvo.offsetTop - painel.clientHeight * 0.22),
                        behavior: 'smooth',
                    });
                    window.setTimeout(() => {
                        ignorarScrollPreview = false;
                    }, 260);
                }
            });
        };

        const sincronizarPreviewComCursor = (rolar = true) => {
            window.clearTimeout(previewSyncTimer);
            previewSyncTimer = window.setTimeout(() => {
                destacarLinhaPreview(obterLinhaAtualTextarea(), rolar);
            }, rolar ? 40 : 0);
        };

        const sincronizarPreviewComScrollEditor = () => {
            if (ignorarScrollPreview) {
                return;
            }

            const proporcao = textarea.scrollTop / Math.max(1, textarea.scrollHeight - textarea.clientHeight);

            [previewComCifras, previewSemCifras].forEach((painel) => {
                painel.scrollTop = proporcao * Math.max(1, painel.scrollHeight - painel.clientHeight);
            });
        };

        const atualizarPreview = () => {
            const valor = textarea.value || '';
            const resultado = normalizarFormato(valor);

            previewPadraoInterno.textContent = resultado.textoNormalizado;
            previewComCifras.innerHTML = renderizarComCifras(resultado.textoNormalizado);
            previewSemCifras.innerHTML = renderizarSemCifras(resultado.textoNormalizado);

            sincronizarPreviewComCursor(false);
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

        textarea.addEventListener('input', () => {
            atualizarPreview();
            sincronizarPreviewComCursor();
        });

        textarea.addEventListener('click', () => sincronizarPreviewComCursor());
        textarea.addEventListener('keyup', () => sincronizarPreviewComCursor());
        textarea.addEventListener('scroll', sincronizarPreviewComScrollEditor);

        formulario?.addEventListener('submit', () => {
            const resultado = normalizarFormato(textarea.value || '');
            textarea.value = resultado.textoNormalizado;
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

            const resultadoOrganizado = normalizarFormato(textarea.value || '');
            textarea.value = converterTextoParaEdicaoVisual(resultadoOrganizado.textoNormalizado);
            textarea.focus();
            atualizarPreview();
            sincronizarPreviewComCursor();
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
        sincronizarPreviewComCursor(false);
    })();
</script>
@endpush
