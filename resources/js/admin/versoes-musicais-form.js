(function () {
        const textarea = document.getElementById('letra_com_cifras');
        const formulario = textarea?.closest('form');
        const previewComCifras = document.getElementById('preview_com_cifras');
        const previewSemCifras = document.getElementById('preview_sem_cifras');
        const previewPadraoInterno = document.getElementById('preview_padrao_interno');
        const botoesAcorde = document.querySelectorAll('.botao-acorde');
        const botoesPreview = document.querySelectorAll('[data-preview-toggle]');
        const paineisPreview = document.querySelectorAll('[data-preview-panel]');
        const botoesExemplo = document.querySelectorAll('[data-exemplo-toggle]');
        const paineisExemplo = document.querySelectorAll('[data-exemplo-painel]');
        const botoesMarcacao = document.querySelectorAll('[data-inserir-marcacao]');
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

            return /^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|\u00ba|\u00b0|\+|-|[0-9#b])|\([^\)\]]+\))*(?:\/[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|\u00ba|\u00b0|\+|-|[0-9#b])|\([^\)\]]+\))*)?$/i.test(texto);
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

        const acordesDeLinhaComColchetes = (linha) => {
            const texto = String(linha || '').trim();

            if (!texto || !/^(?:\[[^\[\]\r\n]+\]\s*)+$/u.test(texto)) {
                return null;
            }

            const acordes = Array.from(texto.matchAll(/\[([^\[\]\r\n]+)\]/gu))
                .map((match) => String(match[1] || '').trim());

            return acordes.length > 0 && acordes.every((acorde) => ehAcorde(acorde)) ? acordes : null;
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
            const linhaLimpa = limparLinhaAcordes(linhaAcordes);
            const indentacao = linhaLimpa.match(/^\s*/)?.[0] || '';
            const tokens = linhaLimpa.trim().split(/\s+/).filter(Boolean);

            return indentacao + tokens.map((token) => {
                const acorde = normalizarTokenAcorde(token);
                return acorde ? `[${acorde}]` : token;
            }).join(' ');
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
            })
                .split('\n')
                .filter((linha) => !ehLinhaApenasAcordes(linha))
                .join('\n')
                .replace(/\n{3,}/g, '\n\n')
                .trim();
        };

        const escaparHtml = (texto) => {
            return (texto || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const renderizarTokensDeAcordes = (linha, destacarAcordesSoltos = false) => {
            let html = '';
            let posicaoAtual = 0;
            const matches = Array.from(String(linha || '').matchAll(/\S+/g));

            matches.forEach((match) => {
                const token = match[0];
                const indice = match.index || 0;
                const acordeComColchetes = token.match(/^\[([^\[\]\r\n]+)\]$/);
                const acorde = acordeComColchetes ? acordeComColchetes[1] : token;

                html += escaparHtml(linha.slice(posicaoAtual, indice));

                if (acordeComColchetes || (destacarAcordesSoltos && ehAcorde(acorde))) {
                    html += `<span class="cifra-token-acorde">${escaparHtml(token)}</span>`;
                } else {
                    html += escaparHtml(token);
                }

                posicaoAtual = indice + token.length;
            });

            html += escaparHtml(linha.slice(posicaoAtual));

            return html;
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
                return '<div class="cifra-preview-line cifra-preview-line--empty">&nbsp;</div>';
            }

            const acordesLinha = acordesDeLinhaComColchetes(linhaLimpa);
            if (acordesLinha) {
                const indentacao = linha.match(/^\s*/)?.[0] || '';
                const acordes = acordesLinha.map((acorde) => `[${acorde}]`).join(' ');
                return `<div class="cifra-preview-line cifra-preview-line--chords">${escaparHtml(indentacao)}${renderizarTokensDeAcordes(acordes, true)}</div>`;
            }

            const marcacao = linhaLimpa.match(/^\[([^\[\]\r\n]+)\]$/);
            if (marcacao && !ehAcorde(marcacao[1])) {
                return `<div class="cifra-marcacao ${normalizarMarcacao(marcacao[1]).startsWith('refrao') ? 'cifra-marcacao--refrao' : ''}">${escaparHtml(marcacao[1])}</div>`;
            }

            if (ehMarcacaoSecao(linhaLimpa)) {
                return `<div class="cifra-marcacao ${normalizarMarcacao(linhaLimpa).startsWith('refrao') ? 'cifra-marcacao--refrao' : ''}">${escaparHtml(linhaLimpa)}</div>`;
            }

            const classeAcordes = ehLinhaApenasAcordes(linhaLimpa) ? ' cifra-preview-line--chords' : '';

            return `<div class="cifra-preview-line${classeAcordes}">${renderizarTokensDeAcordes(linha, Boolean(classeAcordes))}</div>`;
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

            return html || '<p class="text-sm text-slate-400">A prévia com cifra aparecerá aqui.</p>';
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

            return blocos.join('') || '<p class="text-sm text-gray-500">A prévia sem cifra aparecerá aqui.</p>';
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
            previewComCifras.innerHTML = renderizarComCifras(valor);
            previewSemCifras.innerHTML = renderizarSemCifras(valor);

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

        botaoOrganizarCifra?.addEventListener('click', () => {
            const confirmar = window.confirm('Deseja organizar a cifra agora? O sistema vai alinhar acordes, partes e refrões antes de mostrar a prévia.');

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
            const resultadoOrganizado = normalizarFormato(textarea.value || '');
            textarea.value = converterTextoParaEdicaoVisual(resultadoOrganizado.textoNormalizado);
            textarea.focus();
            atualizarPreview();
            sincronizarPreviewComCursor();
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

