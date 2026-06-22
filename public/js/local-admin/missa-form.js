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

    const missasAnteriores = lerJson('missa-form-reaproveitar-dados', []);
    const preencherAoCarregar = Boolean(lerJson('missa-form-preencher-ao-carregar', false));
    const radios = document.querySelectorAll('[name="reaproveitar_repertorio"]');
    const bloco = document.getElementById('bloco_reaproveitar_repertorio');
    const select = document.querySelector('[name="missa_origem_id"]');
    const resumo = document.getElementById('resumo_missa_origem');
    const campos = {
        titulo: document.querySelector('[name="titulo"]'),
        dataMissa: document.querySelector('[name="data_missa"]'),
        tempoLiturgico: document.querySelector('[name="tempo_liturgico_id"]'),
        horaInicio: document.querySelector('[name="hora_inicio"]'),
        horaFim: document.querySelector('[name="hora_fim"]'),
        padre: document.querySelector('[name="padre_id"]'),
        observacoes: document.querySelector('[name="observacoes"]'),
    };

    if (!bloco || !select || radios.length === 0) {
        return;
    }

    const encontrarMissa = () => missasAnteriores.find((item) => item.id === select.value);

    const preencherDadosDaMissa = () => {
        const missa = encontrarMissa();

        if (!missa) {
            return;
        }

        if (campos.titulo && !campos.titulo.value.trim()) campos.titulo.value = missa.titulo || '';
        if (campos.tempoLiturgico) campos.tempoLiturgico.value = missa.tempo_liturgico_id || '';
        if (campos.horaInicio) campos.horaInicio.value = missa.hora_inicio || '';
        if (campos.horaFim) campos.horaFim.value = missa.hora_fim || '';
        if (campos.padre) campos.padre.value = missa.padre_id || '';
        if (campos.observacoes && !campos.observacoes.value.trim()) campos.observacoes.value = missa.observacoes || '';

        campos.dataMissa?.focus();
    };

    const atualizarResumo = () => {
        if (!resumo) {
            return;
        }

        const missa = encontrarMissa();

        if (!missa) {
            resumo.classList.add('hidden');
            resumo.textContent = '';
            return;
        }

        const musicas = missa.musicas.length > 0
            ? missa.musicas.slice(0, 6).join(', ') + (missa.musicas.length > 6 ? '...' : '')
            : 'Nenhuma música no repertório anterior.';

        resumo.textContent = [
            `Missa: ${missa.titulo}`,
            `Igreja: ${missa.igreja_nome || 'Igreja atual'}`,
            `Data e horário: ${missa.data_missa || 'Sem data'} - ${missa.hora_inicio || '--:--'} às ${missa.hora_fim || '--:--'}`,
            `Tempo litúrgico: ${missa.tempo_liturgico_nome}`,
            `Celebrante: ${missa.celebrante_nome}`,
            `Músicas copiadas: ${musicas}`,
        ].join('\n');
        resumo.classList.remove('hidden');
    };

    const atualizarBloco = () => {
        const desejaReaproveitar = document.querySelector('[name="reaproveitar_repertorio"]:checked')?.value === '1';
        bloco.style.display = desejaReaproveitar ? 'block' : 'none';

        if (!desejaReaproveitar) {
            select.value = '';
            atualizarResumo();
        }
    };

    select.addEventListener('change', preencherDadosDaMissa);
    select.addEventListener('change', atualizarResumo);

    radios.forEach((radio) => {
        radio.addEventListener('change', atualizarBloco);
    });

    atualizarBloco();

    if (select.value && preencherAoCarregar) {
        preencherDadosDaMissa();
    }

    atualizarResumo();
});
