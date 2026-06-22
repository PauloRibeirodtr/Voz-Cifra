document.addEventListener('DOMContentLoaded', () => {
    const campoBusca = document.querySelector('[data-missa-search]');
    const cards = Array.from(document.querySelectorAll('[data-missa-card]'));
    const grupos = Array.from(document.querySelectorAll('[data-missa-group]'));
    const vazio = document.querySelector('[data-missa-empty-search]');

    if (!campoBusca || cards.length === 0) {
        return;
    }

    const normalizar = (valor) => valor
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const aplicarBusca = () => {
        const termo = normalizar(campoBusca.value);
        let visiveis = 0;

        cards.forEach((card) => {
            const combina = termo === '' || (card.dataset.search || '').includes(termo);
            card.classList.toggle('hidden', !combina);

            if (combina) {
                visiveis += 1;
            }
        });

        grupos.forEach((grupo) => {
            const temCardVisivel = Array.from(grupo.querySelectorAll('[data-missa-card]'))
                .some((card) => !card.classList.contains('hidden'));
            grupo.classList.toggle('hidden', !temCardVisivel);
        });

        if (vazio) {
            vazio.classList.toggle('hidden', visiveis > 0);
        }
    };

    campoBusca.addEventListener('input', aplicarBusca);

    const menusAcoes = Array.from(document.querySelectorAll('.missa-more-actions'));
    menusAcoes.forEach((menu) => {
        menu.addEventListener('toggle', () => {
            if (!menu.open) return;
            menusAcoes.forEach((outroMenu) => {
                if (outroMenu !== menu) outroMenu.open = false;
            });
        });
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('.missa-more-actions')) return;
        menusAcoes.forEach((menu) => { menu.open = false; });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        menusAcoes.forEach((menu) => { menu.open = false; });
    });
});
