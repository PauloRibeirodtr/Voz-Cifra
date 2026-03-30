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

                    if (!item) {
                        return;
                    }

                    item.classList.toggle('text-green-700', valida);
                    item.classList.toggle('font-semibold', valida);
                    item.classList.toggle('text-gray-600', !valida);
                });

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
