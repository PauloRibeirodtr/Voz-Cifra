@php
    $normalizarToastMessages = static function (string $type, mixed $messages): array {
        if (blank($messages)) {
            return [];
        }

        if (is_string($messages)) {
            return [['type' => $type, 'message' => $messages]];
        }

        if (is_iterable($messages)) {
            return collect($messages)
                ->filter(fn (mixed $message): bool => filled($message))
                ->map(fn (mixed $message): array => ['type' => $type, 'message' => (string) $message])
                ->values()
                ->all();
        }

        return [['type' => $type, 'message' => (string) $messages]];
    };

    $toastMessages = collect()
        ->merge($normalizarToastMessages('success', session('success')))
        ->merge($normalizarToastMessages('info', session('status')))
        ->merge($normalizarToastMessages('info', session('info')))
        ->merge($normalizarToastMessages('warning', session('warning')))
        ->merge($normalizarToastMessages('error', session('error')))
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
        class="pointer-events-none fixed inset-x-3 top-3 z-[120] flex flex-col gap-3 sm:inset-x-auto sm:right-6 sm:top-6 sm:w-[min(25rem,calc(100vw-2rem))]"
        aria-live="polite"
        aria-atomic="true"
    >
        @foreach ($toastMessages as $toast)
            @php
                $type = $toast['type'];
                $palette = match ($type) {
                    'success' => 'border-emerald-200/80 bg-emerald-50 text-emerald-950 shadow-emerald-950/10',
                    'info' => 'border-sky-200/80 bg-sky-50 text-sky-950 shadow-sky-950/10',
                    'warning' => 'border-amber-200/80 bg-amber-50 text-amber-950 shadow-amber-950/10',
                    default => 'border-red-200/80 bg-red-50 text-red-950 shadow-red-950/10',
                };
                $title = match ($type) {
                    'success' => 'Tudo certo',
                    'info' => 'Aviso',
                    'warning' => 'Atenção',
                    default => 'Algo precisa de atenção',
                };
            @endphp

            <div
                class="flash-toast pointer-events-auto translate-y-2 overflow-hidden rounded-2xl border px-4 py-3 opacity-0 shadow-2xl transition duration-200 dark:border-white/10 dark:bg-[#201414] dark:text-[#fff7ee] {{ $palette }}"
                role="{{ $type === 'error' ? 'alert' : 'status' }}"
                data-flash-toast
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 inline-flex h-9 w-9 flex-none items-center justify-center rounded-full bg-white/75 dark:bg-white/10">
                        @if ($type === 'success')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m5 12.5 4.2 4.2L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @elseif ($type === 'warning')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 8v5m0 4h.01M10.2 4.6 2.7 17.5A2 2 0 0 0 4.4 20h15.2a2 2 0 0 0 1.7-2.5L13.8 4.6a2 2 0 0 0-3.6 0Z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @elseif ($type === 'error')
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
                            </svg>
                        @else
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="M12 11v5m0-8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-black uppercase tracking-[0.18em] opacity-75">{{ $title }}</p>
                        <p class="mt-0.5 text-sm font-bold leading-relaxed">{{ $toast['message'] }}</p>
                    </div>
                    <button
                        type="button"
                        class="-mr-1 inline-flex h-8 w-8 flex-none items-center justify-center rounded-full bg-white/60 transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-current/30 dark:bg-white/10 dark:hover:bg-white/15"
                        aria-label="Fechar aviso"
                        data-flash-toast-close
                    >
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="m7 7 10 10M17 7 7 17" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
                <span class="flash-toast__bar mt-3 block h-1 rounded-full bg-current/20" aria-hidden="true"></span>
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
