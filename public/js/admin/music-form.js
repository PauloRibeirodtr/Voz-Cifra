document.addEventListener('DOMContentLoaded', () => {
    const campoLetra = document.getElementById('letra');
    const alertaCifras = document.getElementById('alerta_cifras');
    const contadorLinhas = document.getElementById('contador_linhas');
    const contadorCaracteres = document.getElementById('contador_caracteres');
    const formulario = campoLetra?.closest('form');

    if (!campoLetra) {
        return;
    }

    const regexAcorde = /^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\+|-|[0-9#b])|\([^)]+\))*(?:\/[A-G](?:#|b)?)?$/;

    const linhaContemApenasAcordes = (linha) => {
        const texto = linha.trim();

        if (!texto) {
            return false;
        }

        const tokens = texto.split(/\s+/).filter(Boolean);

        if (tokens.length === 0) {
            return false;
        }

        return tokens.every((token) => regexAcorde.test(token));
    };

    const possuiCifras = (texto) => {
        if (/\[[^\]]+\]/.test(texto)) {
            return true;
        }

        return texto
            .split(/\r\n|\r|\n/)
            .some((linha) => linhaContemApenasAcordes(linha));
    };

    const atualizarResumo = () => {
        const valor = campoLetra.value || '';
        const linhas = valor.length === 0 ? 0 : valor.split(/\r\n|\r|\n/).length;
        const caracteres = valor.length;
        const encontrouCifras = possuiCifras(valor);

        if (contadorLinhas) {
            contadorLinhas.textContent = linhas;
        }

        if (contadorCaracteres) {
            contadorCaracteres.textContent = caracteres;
        }

        alertaCifras?.classList.toggle('hidden', !encontrouCifras);
    };

    campoLetra.addEventListener('input', atualizarResumo);

    formulario?.addEventListener('submit', (event) => {
        if (!possuiCifras(campoLetra.value || '')) {
            return;
        }

        event.preventDefault();
        alertaCifras?.classList.remove('hidden');
        campoLetra.focus();
        campoLetra.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    atualizarResumo();
});
