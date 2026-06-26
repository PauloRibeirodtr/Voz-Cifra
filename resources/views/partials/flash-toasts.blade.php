@php
    $toastMessages = collect([
        ['type' => 'success', 'message' => session('success')],
        ['type' => 'info', 'message' => session('info')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'error', 'message' => session('error')],
    ])
        ->filter(fn (array $toast): bool => filled($toast['message']))
        ->values();

    if ($errors->any()) {
        $toastMessages->push([
            'type' => 'error',
            'message' => $errors->count() === 1
                ? $errors->first()
                : 'Revise os campos destacados antes de continuar.',
        ]);
    }
@endphp

@if ($toastMessages->isNotEmpty())
    <div
        id="flash_toast_stack"
        class="fixed right-4 top-4 z-[120] flex w-[min(24rem,calc(100vw-2rem))] flex-col gap-3 sm:right-6 sm:top-6"
        aria-live="polite"
        aria-atomic="true"
    >
        @foreach ($toastMessages as $toast)
            @php
                $type = $toast['type'];
                $palette = match ($type) {
                    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-950',
                    'info' => 'border-sky-200 bg-sky-50 text-sky-950',
                    'warning' => 'border-amber-200 bg-amber-50 text-amber-950',
                    default => 'border-red-200 bg-red-50 text-red-950',
                };
                $icon = match ($type) {
                    'success' => 'fa-check',
                    'info' => 'fa-circle-info',
                    'warning' => 'fa-triangle-exclamation',
                    default => 'fa-xmark',
                };
            @endphp

            <div
                class="flash-toast translate-y-2 rounded-2xl border px-4 py-3 opacity-0 shadow-2xl shadow-slate-950/10 transition duration-200 {{ $palette }}"
                role="{{ $type === 'error' ? 'alert' : 'status' }}"
                data-flash-toast
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-white/70">
                        <i class="fa-solid {{ $icon }} text-sm" aria-hidden="true"></i>
                    </span>
                    <p class="min-w-0 flex-1 text-sm font-bold leading-relaxed">{{ $toast['message'] }}</p>
                    <button
                        type="button"
                        class="-mr-1 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-white/60 transition hover:bg-white"
                        aria-label="Fechar aviso"
                        data-flash-toast-close
                    >
                        <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        (() => {
            const toasts = Array.from(document.querySelectorAll('[data-flash-toast]'));

            const hideToast = (toast) => {
                toast.classList.add('translate-y-2', 'opacity-0');
                window.setTimeout(() => toast.remove(), 220);
            };

            toasts.forEach((toast, index) => {
                window.setTimeout(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 80 + (index * 70));

                toast.querySelector('[data-flash-toast-close]')?.addEventListener('click', () => hideToast(toast));

                window.setTimeout(() => hideToast(toast), 5200 + (index * 500));
            });
        })();
    </script>
@endif
