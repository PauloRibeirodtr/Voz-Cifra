document.addEventListener('DOMContentLoaded', () => {
    const lerJson = (id, fallback = []) => {
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

    const helper = window.VozECifraChord;
    const itens = lerJson('missa-apresentacao-itens', []);
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

    if (!helper || !letra || !container || itens.length === 0) {
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
        subtitulo.textContent = [item.artista || 'Artista nao informado', item.versao].filter(Boolean).join(' - ');
        ordem.textContent = 'Ordem ' + item.ordem;
        momento.textContent = item.momento || 'Momento ainda nao definido';
        letra.innerHTML = helper.renderChordSheetHtml(
            helper.transposeBracketedText(item.letra || '', transposicaoAtual),
            { chordAttribute: 'data-acorde-hover' }
        );
        letra.style.setProperty('--escala-fonte', String(fonteAtual / 18));
        tomBadge.textContent = 'Tom ' + (
            item.tom_exibicao && helper.isChord(item.tom_exibicao)
                ? helper.transposeChord(item.tom_exibicao, transposicaoAtual)
                : 'Nao informado'
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
