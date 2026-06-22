const offlineStatusId = 'public-offline-status';

const updateOfflineStatus = () => {
    let status = document.getElementById(offlineStatusId);

    if (!navigator.onLine) {
        if (!status) {
            status = document.createElement('div');
            status.id = offlineStatusId;
            status.setAttribute('role', 'status');
            status.textContent = 'Modo offline: exibindo a última versão salva neste aparelho.';
            Object.assign(status.style, {
                position: 'fixed', left: '50%', bottom: '1rem', zIndex: '9999',
                transform: 'translateX(-50%)', maxWidth: 'calc(100% - 2rem)',
                borderRadius: '999px', padding: '.7rem 1rem', background: '#422006',
                color: '#fef3c7', fontWeight: '800', fontSize: '.82rem', textAlign: 'center',
                boxShadow: '0 12px 30px rgba(0,0,0,.28)',
            });
            document.body.appendChild(status);
        }
        return;
    }

    status?.remove();
};

window.addEventListener('online', updateOfflineStatus);
window.addEventListener('offline', updateOfflineStatus);
document.addEventListener('DOMContentLoaded', updateOfflineStatus);

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js').catch((error) => {
            console.warn('Não foi possível ativar o modo offline.', error);
        });
    });
}
