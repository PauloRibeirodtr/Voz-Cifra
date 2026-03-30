<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $versaoMusical->titulo ?: 'Versao musical' }} | Voz & Cifra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @include('partials.cifra-viewer-styles')
    </style>
</head>
<body class="min-h-screen bg-gray-950 text-white">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-300">Modo estudo</p>
                <h1 class="mt-2 text-3xl font-black">{{ $musica->titulo }}</h1>
                <p class="mt-2 text-sm text-green-100">{{ $versaoMusical->titulo ?: 'Versao principal' }} @if($missaAtiva) • Missa ativa: {{ $missaAtiva->titulo }} @endif</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Repertorio</a>
                <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Biblioteca</a>
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Painel</a>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-green-200">Tom exibido</span>
                <span id="tom_atual_badge" class="mt-2 block text-xl font-black text-white">{{ $tomExibicao ?: 'Nao informado' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-green-200">Tom original</span>
                <span class="mt-2 block text-xl font-black text-white">{{ $tomOriginal ?: 'Nao informado' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-green-200">BPM</span>
                <span class="mt-2 block text-xl font-black text-white">{{ $versaoMusical->bpm ?: '-' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-green-200">Contexto</span>
                <span class="mt-2 block text-sm font-bold text-white">{{ $itemMissa ? 'Versao usada na missa da sua igreja' : 'Estudo livre da biblioteca musical' }}</span>
            </div>
        </div>

        <section class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($itemMissa && $itemMissa->tom_usado)
                        <span class="inline-flex rounded-full bg-emerald-400/20 px-3 py-1 text-xs font-semibold text-emerald-200">Tom da missa {{ $itemMissa->tom_usado }}</span>
                    @endif
                    @if ($tomOriginal)
                        <span class="inline-flex rounded-full bg-amber-400/20 px-3 py-1 text-xs font-semibold text-amber-200">Tom original {{ $tomOriginal }}</span>
                    @endif
                    @if ($versaoMusical->bpm)
                        <span class="inline-flex rounded-full bg-blue-400/20 px-3 py-1 text-xs font-semibold text-blue-200">BPM {{ $versaoMusical->bpm }}</span>
                    @endif
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-200">Tom</span>
                        <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-bold text-white hover:bg-white/20">-</button>
                        <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">Original</button>
                        <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-bold text-white hover:bg-white/20">+</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-green-200">Fonte</span>
                        <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-bold text-white hover:bg-white/20">A-</button>
                        <button type="button" data-font-reset class="inline-flex rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">Padrao</button>
                        <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-bold text-white hover:bg-white/20">A+</button>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-gray-900 p-5 text-green-200 shadow-inner">
                <div id="letra_com_cifras_preview" class="space-y-2"></div>
            </div>
        </section>
    </div>

    @include('partials.chord-transposer-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const helper = window.VozECifraChord;
            const preview = document.getElementById('letra_com_cifras_preview');
            const tomBadge = document.getElementById('tom_atual_badge');
            const textoOriginal = @json($textoCifraExibicao, JSON_UNESCAPED_UNICODE);
            const tomBase = @json($tomExibicao);
            let transposicaoAtual = 0;
            let fonteAtual = 16;

            if (!preview || !helper) {
                return;
            }

            const atualizarTomBadge = () => {
                if (!tomBadge) {
                    return;
                }

                if (!tomBase || !helper.isChord(tomBase)) {
                    tomBadge.textContent = 'Nao informado';
                    return;
                }

                tomBadge.textContent = helper.transposeChord(tomBase, transposicaoAtual);
            };

            const renderizar = () => {
                preview.innerHTML = helper.renderChordSheetHtml(
                    helper.transposeBracketedText(textoOriginal, transposicaoAtual),
                    { chordAttribute: 'data-acorde-hover' }
                );
                preview.style.setProperty('--escala-fonte', String(fonteAtual / 16));
                atualizarTomBadge();
            };

            document.querySelectorAll('[data-transpose]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    transposicaoAtual += Number(botao.dataset.transpose || 0);
                    renderizar();
                });
            });

            document.querySelector('[data-transpose-reset]')?.addEventListener('click', () => {
                transposicaoAtual = 0;
                renderizar();
            });

            document.querySelectorAll('[data-font]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    fonteAtual = Math.min(26, Math.max(12, fonteAtual + (Number(botao.dataset.font || 0) * 2)));
                    renderizar();
                });
            });

            document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
                fonteAtual = 16;
                renderizar();
            });

            renderizar();
        });
    </script>
</body>
</html>
