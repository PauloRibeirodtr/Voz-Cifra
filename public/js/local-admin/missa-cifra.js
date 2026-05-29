document.addEventListener('DOMContentLoaded', () => {
    const lerJson = (id, fallback = null) => {
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
    const preview = document.getElementById('letra_com_cifras_preview');
    const tomBadge = document.getElementById('tom_atual_badge');
    const capoBadge = document.getElementById('capotraste_badge');
    const controleCapotraste = document.getElementById('controle_capotraste');
    const textoOriginal = lerJson('missa-cifra-texto', '');
    const tomOriginal = lerJson('missa-cifra-tom', null);
    let transposicaoAtual = 0;
    let capotrasteAtual = 0;
    let fonteAtual = 18;

    if (!preview || !helper) {
        return;
    }

    const atualizarTomBadge = () => {
        if (!tomBadge) {
            return;
        }

        if (!tomOriginal || !helper.isChord(tomOriginal)) {
            tomBadge.textContent = 'Tom nao informado';
            return;
        }

        const tomReal = helper.transposeChord(tomOriginal, transposicaoAtual);
        const formaTocada = helper.transposeChord(tomOriginal, transposicaoAtual - capotrasteAtual);
        tomBadge.textContent = 'Tom ' + tomReal;

        if (capoBadge) {
            capoBadge.textContent = capotrasteAtual > 0
                ? 'Capo ' + capotrasteAtual + ' / tocar como ' + formaTocada
                : 'Sem capo';
        }
    };

    const renderizar = () => {
        preview.innerHTML = helper.renderChordSheetHtml(
            helper.transposeBracketedText(textoOriginal, transposicaoAtual - capotrasteAtual),
            { chordAttribute: 'data-acorde-hover' }
        );
        preview.style.setProperty('--escala-fonte', String(fonteAtual / 18));
        atualizarTomBadge();
    };

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

    controleCapotraste?.addEventListener('change', () => {
        capotrasteAtual = Math.max(0, Math.min(11, Number(controleCapotraste.value || 0)));
        renderizar();
    });

    document.querySelectorAll('[data-font]').forEach((botao) => {
        botao.addEventListener('click', () => {
            fonteAtual = Math.min(32, Math.max(14, fonteAtual + (Number(botao.dataset.font || 0) * 2)));
            renderizar();
        });
    });

    document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
        fonteAtual = 18;
        renderizar();
    });

    renderizar();
});
