document.addEventListener('DOMContentLoaded', () => {
    const list = document.querySelector('[data-sortable-repertorio]');
    if (!list) return;

    const status = document.querySelector('[data-reorder-status]');
    let dragged = null;
    let moved = false;
    let originalOrder = [];

    const items = () => Array.from(list.querySelectorAll('[data-sortable-item]'));
    const ids = () => items().map((item) => Number(item.dataset.sortableItem));
    const setStatus = (message, error = false) => {
        if (!status) return;
        status.textContent = message;
        status.classList.remove('hidden', 'text-emerald-700', 'text-red-700');
        status.classList.add(error ? 'text-red-700' : 'text-emerald-700');
    };
    const refreshNumbers = () => {
        items().forEach((item, index) => {
            const number = item.querySelector('.repertorio-sequence-number');
            if (number) number.textContent = String(index + 1);
            const card = document.getElementById(`repertorio-item-${item.dataset.sortableItem}`);
            const badge = card?.querySelector('[data-item-order-badge]');
            if (badge) badge.textContent = `Ordem ${index + 1}`;
        });
    };
    const restoreOrder = () => {
        originalOrder.forEach((id) => {
            const item = list.querySelector(`[data-sortable-item="${id}"]`);
            if (item) list.appendChild(item);
        });
        refreshNumbers();
    };

    items().forEach((item) => {
        item.addEventListener('dragstart', (event) => {
            dragged = item;
            moved = false;
            originalOrder = ids();
            item.classList.add('is-dragging');
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', item.dataset.sortableItem);
        });
        item.addEventListener('dragend', () => {
            item.classList.remove('is-dragging');
            dragged = null;
        });
        item.addEventListener('click', (event) => {
            if (moved) {
                event.preventDefault();
                moved = false;
            }
        });
    });

    list.addEventListener('dragover', (event) => {
        if (!dragged) return;
        event.preventDefault();
        const target = event.target.closest('[data-sortable-item]');
        if (!target || target === dragged) return;
        const rect = target.getBoundingClientRect();
        const after = event.clientY > rect.top + rect.height / 2
            || (Math.abs(event.clientY - (rect.top + rect.height / 2)) < rect.height / 3 && event.clientX > rect.left + rect.width / 2);
        list.insertBefore(dragged, after ? target.nextSibling : target);
        moved = true;
        refreshNumbers();
    });

    list.addEventListener('drop', async (event) => {
        if (!dragged || !moved) return;
        event.preventDefault();
        const currentItems = items();
        currentItems.forEach((item) => item.classList.add('is-saving'));
        setStatus('Salvando nova ordem...');

        try {
            const response = await fetch(list.dataset.reorderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': list.dataset.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ itens: ids() }),
            });
            if (!response.ok) throw new Error('Falha ao salvar a ordem.');
            setStatus('Ordem salva com sucesso.');
        } catch (error) {
            restoreOrder();
            setStatus('Não foi possível salvar. A ordem anterior foi restaurada.', true);
        } finally {
            currentItems.forEach((item) => item.classList.remove('is-saving'));
        }
    });
});
