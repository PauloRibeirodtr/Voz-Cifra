document.addEventListener('DOMContentLoaded', () => {
    const campoCep = document.querySelector('[data-cep-input]');
    const campoEndereco = document.querySelector('[data-endereco-input]');
    const campoCidade = document.querySelector('[data-cidade-input]');
    const campoEstado = document.querySelector('[data-estado-input]');
    const camposCpf = document.querySelectorAll('[data-cpf-input], [name="admin_cpf"]');
    const campoCnpj = document.querySelector('[data-cnpj-input]');
    const camposTelefone = document.querySelectorAll('[data-telefone-input]');

    const aplicarMascaraCpf = (valor) => {
        valor = valor.replace(/\D/g, '').slice(0, 11);
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
        valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        return valor;
    };

    const aplicarMascaraCnpj = (valor) => {
        valor = valor.replace(/\D/g, '').slice(0, 14);
        valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
        valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        return valor;
    };

    const aplicarMascaraCep = (valor) => {
        valor = valor.replace(/\D/g, '').slice(0, 8);
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
        return valor;
    };

    const aplicarMascaraTelefone = (valor) => {
        valor = valor.replace(/\D/g, '').slice(0, 11);

        if (valor.length <= 10) {
            valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
            valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
        }

        return valor;
    };

    const preencherEnderecoPorCep = async () => {
        if (!campoCep || !campoEndereco || !campoCidade || !campoEstado) {
            return;
        }

        const cep = campoCep.value.replace(/\D/g, '');

        if (cep.length !== 8) {
            return;
        }

        try {
            const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const dados = await resposta.json();

            if (dados.erro) {
                return;
            }

            const enderecoCompleto = [dados.logradouro, dados.bairro].filter(Boolean).join(' - ');

            if (!campoEndereco.value.trim()) {
                campoEndereco.value = enderecoCompleto;
            }

            if (!campoCidade.value.trim()) {
                campoCidade.value = dados.localidade ?? '';
            }

            if (!campoEstado.value.trim()) {
                campoEstado.value = dados.uf ?? '';
            }
        } catch (erro) {
            console.warn('Nao foi possivel consultar o CEP agora.', erro);
        }
    };

    campoCep?.addEventListener('blur', preencherEnderecoPorCep);
    campoCep?.addEventListener('input', () => {
        campoCep.value = aplicarMascaraCep(campoCep.value);
    });

    camposCpf.forEach((campo) => {
        campo.addEventListener('input', () => {
            campo.value = aplicarMascaraCpf(campo.value);
        });
    });

    campoCnpj?.addEventListener('input', () => {
        campoCnpj.value = aplicarMascaraCnpj(campoCnpj.value);
    });

    camposTelefone.forEach((campo) => {
        campo.addEventListener('input', () => {
            campo.value = aplicarMascaraTelefone(campo.value);
        });
    });
});
