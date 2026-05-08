<script>
    document.addEventListener('DOMContentLoaded', () => {
        const grupos = document.querySelectorAll('[data-password-strength]');

        const avaliarSenha = (senha) => ({
            length: senha.length >= 8,
            lower: /[a-z]/.test(senha),
            upper: /[A-Z]/.test(senha),
            number: /\d/.test(senha),
            symbol: /[^a-zA-Z\d]/.test(senha),
        });

        const obterNivel = (total, preenchida) => {
            if (!preenchida || total <= 2) {
                return { texto: 'Fraca', cor: 'bg-red-500', textoCor: 'text-red-600', largura: '33%' };
            }

            if (total <= 4) {
                return { texto: 'Media', cor: 'bg-amber-500', textoCor: 'text-amber-600', largura: '66%' };
            }

            return { texto: 'Forte', cor: 'bg-green-600', textoCor: 'text-green-700', largura: '100%' };
        };

        grupos.forEach((grupo) => {
            const container = grupo.closest('[data-password-strength-container]') ?? grupo.parentElement;
            const campoSenha = container?.querySelector('[data-password-strength-input]');
            const formulario = campoSenha?.form;
            const campoConfirmacao = container?.querySelector('[data-password-confirmation-input]')
                ?? formulario?.querySelector('[data-password-confirmation-input]');
            const botoesSubmit = formulario ? formulario.querySelectorAll('button[type="submit"]') : [];

            if (!campoSenha) {
                return;
            }

            const label = grupo.querySelector('[data-password-strength-label]');
            const barra = grupo.querySelector('[data-password-strength-bar]');
            const itemConfirmacao = grupo.querySelector('[data-password-match]');
            const textoConfirmacao = grupo.querySelector('[data-password-match-text]');
            const iconeConfirmacao = grupo.querySelector('[data-password-match-icon]');
            const required = grupo.dataset.passwordRequired === 'true';

            const atualizarEstadoEnvio = (senhaValida, senhaPreenchida) => {
                if (!formulario || !botoesSubmit.length) {
                    return;
                }

                const confirmacaoOk = !campoConfirmacao || campoConfirmacao.value === campoSenha.value;
                const bloquear = required ? !senhaValida || !confirmacaoOk : (senhaPreenchida && (!senhaValida || !confirmacaoOk));

                botoesSubmit.forEach((botao) => {
                    botao.disabled = bloquear;
                    botao.classList.toggle('opacity-60', bloquear);
                    botao.classList.toggle('cursor-not-allowed', bloquear);
                });
            };

            const atualizar = () => {
                const senha = campoSenha.value;
                const regras = avaliarSenha(senha);
                const total = Object.values(regras).filter(Boolean).length;
                const preenchida = senha.length > 0;
                const senhaValida = Object.values(regras).every(Boolean);
                const nivel = obterNivel(total, preenchida);

                Object.entries(regras).forEach(([regra, valida]) => {
                    const item = grupo.querySelector(`[data-password-rule="${regra}"]`);
                    const icone = item?.querySelector('[data-password-rule-icon]');

                    if (!item) {
                        return;
                    }

                    item.classList.toggle('text-green-700', valida);
                    item.classList.toggle('font-semibold', valida);
                    item.classList.toggle('text-gray-600', !valida);
                    item.classList.toggle('opacity-75', valida);

                    if (icone) {
                        icone.textContent = valida ? '✓' : '•';
                        icone.classList.toggle('border-green-600', valida);
                        icone.classList.toggle('bg-green-600', valida);
                        icone.classList.toggle('text-white', valida);
                        icone.classList.toggle('border-gray-300', !valida);
                    }
                });

                if (itemConfirmacao && campoConfirmacao) {
                    const confirmacaoPreenchida = campoConfirmacao.value.length > 0;
                    const confirmacaoOk = confirmacaoPreenchida && campoConfirmacao.value === campoSenha.value;

                    itemConfirmacao.hidden = !preenchida && !confirmacaoPreenchida;
                    itemConfirmacao.classList.toggle('text-green-700', confirmacaoOk);
                    itemConfirmacao.classList.toggle('font-semibold', confirmacaoOk);
                    itemConfirmacao.classList.toggle('text-red-600', confirmacaoPreenchida && !confirmacaoOk);
                    itemConfirmacao.classList.toggle('text-gray-600', !confirmacaoPreenchida);

                    if (textoConfirmacao) {
                        textoConfirmacao.textContent = confirmacaoOk
                            ? 'As senhas conferem'
                            : 'As senhas precisam conferir';
                    }

                    if (iconeConfirmacao) {
                        iconeConfirmacao.textContent = confirmacaoOk ? '✓' : '•';
                        iconeConfirmacao.classList.toggle('border-green-600', confirmacaoOk);
                        iconeConfirmacao.classList.toggle('bg-green-600', confirmacaoOk);
                        iconeConfirmacao.classList.toggle('text-white', confirmacaoOk);
                        iconeConfirmacao.classList.toggle('border-gray-300', !confirmacaoOk);
                    }
                }

                if (label) {
                    label.textContent = nivel.texto;
                    label.className = `text-sm font-semibold ${nivel.textoCor}`;
                }

                if (barra) {
                    barra.style.width = preenchida ? nivel.largura : '0%';
                    barra.className = `h-full rounded-full transition-all duration-200 ${nivel.cor}`;
                }

                atualizarEstadoEnvio(senhaValida, preenchida);
            };

            campoSenha.addEventListener('input', atualizar);
            campoConfirmacao?.addEventListener('input', atualizar);
            atualizar();
        });
    });
</script>
