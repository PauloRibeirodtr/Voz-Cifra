<script>
    document.addEventListener('DOMContentLoaded', () => {
        const previews = document.querySelectorAll('[data-image-preview-input]');

        previews.forEach((input) => {
            const targetSelector = input.dataset.imagePreviewTarget;
            const target = targetSelector ? document.querySelector(targetSelector) : null;

            if (!target) {
                return;
            }

            const defaultSrc = input.dataset.defaultSrc || target.getAttribute('src') || '';
            let objectUrl = null;

            input.addEventListener('change', () => {
                const [file] = input.files || [];

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                    objectUrl = null;
                }

                if (!file) {
                    target.src = defaultSrc;
                    return;
                }

                objectUrl = URL.createObjectURL(file);
                target.src = objectUrl;
            });
        });
    });
</script>
