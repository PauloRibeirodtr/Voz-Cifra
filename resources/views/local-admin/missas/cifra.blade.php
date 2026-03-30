@extends('local-admin.layouts.admin')

@section('title', 'Visualizacao com cifra | Voz & Cifra')
@section('mobile_title', 'Cifra')

@push('styles')
    @include('partials.cifra-viewer-styles')
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $itemRepertorio->musica->titulo }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Missa: {{ $missa->titulo }}
                @if ($itemRepertorio->momentoLiturgico)
                    • {{ $itemRepertorio->momentoLiturgico->nome }}
                @endif
            </p>
        </div>

        <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50">
            Voltar para a missa
        </a>
    </div>

    @if ($itemRepertorio->versaoMusical)
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-5 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full bg-gray-900 px-3 py-1 text-xs font-semibold text-white">Leitura da igreja</span>
                            <span id="tom_atual_badge" class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                Tom {{ $tomExibicao ?: 'Nao informado' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Tom</span>
                                <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">-</button>
                                <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Original</button>
                                <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">+</button>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Fonte</span>
                                <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">A-</button>
                                <button type="button" data-font-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Padrao</button>
                                <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">A+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                        {{ $itemRepertorio->versaoMusical->titulo ?: 'Versao principal' }}
                    </span>
                    @if ($itemRepertorio->versaoMusical->tom_musical)
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tom original {{ $itemRepertorio->versaoMusical->tom_musical }}</span>
                    @endif
                    @if ($itemRepertorio->tom_usado)
                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Tom da missa {{ $itemRepertorio->tom_usado }}</span>
                    @endif
                    @if ($itemRepertorio->versaoMusical->bpm)
                        <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">BPM {{ $itemRepertorio->versaoMusical->bpm }}</span>
                    @endif
                </div>

                <div class="rounded-2xl bg-gray-900 p-5 text-green-200 shadow-inner">
                    <div id="letra_com_cifras_preview" class="space-y-2"></div>
                </div>
            </section>

            <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Resumo da versao</h2>
                <div class="mt-4 space-y-4 text-sm text-gray-600">
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Musica</span><span>{{ $itemRepertorio->musica->titulo }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Versao</span><span>{{ $itemRepertorio->versaoMusical->titulo ?: 'Versao principal' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tom original</span><span>{{ $itemRepertorio->versaoMusical->tom_musical ?: 'Nao informado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tom da missa</span><span>{{ $tomExibicao ?: 'Nao informado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">BPM</span><span>{{ $itemRepertorio->versaoMusical->bpm ?: 'Nao informado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Momento liturgico</span><span>{{ $itemRepertorio->momentoLiturgico?->nome ?: 'Nao definido' }}</span></div>
                </div>
            </aside>
        </div>
    @else
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900 shadow-sm">
            <h2 class="text-lg font-bold">Nenhuma versao musical vinculada</h2>
            <p class="mt-2 text-sm leading-7">
                Este item do repertorio ainda nao possui uma versao musical com cifra vinculada. Por enquanto, a missa pode usar apenas a musica base.
            </p>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($itemRepertorio->versaoMusical)
        @include('partials.chord-transposer-script')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const helper = window.VozECifraChord;
                const preview = document.getElementById('letra_com_cifras_preview');
                const tomBadge = document.getElementById('tom_atual_badge');
                const textoOriginal = @json($textoCifraExibicao, JSON_UNESCAPED_UNICODE);
                const tomOriginal = @json($tomExibicao);
                let transposicaoAtual = 0;
                let fonteAtual = 14;

                if (!preview || !helper) {
                    return;
                }

                const atualizarTomBadge = () => {
                    if (!tomBadge) {
                        return;
                    }

                    if (!tomOriginal || !helper.isChord(tomOriginal)) {
                        tomBadge.textContent = 'Tom nao informado';
                        return;
                    }

                    tomBadge.textContent = 'Tom ' + helper.transposeChord(tomOriginal, transposicaoAtual);
                };

                const renderizar = () => {
                    preview.innerHTML = helper.renderChordSheetHtml(
                        helper.transposeBracketedText(textoOriginal, transposicaoAtual),
                        { chordAttribute: 'data-acorde-hover' }
                    );
                    preview.style.setProperty('--escala-fonte', String(fonteAtual / 14));
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
                        fonteAtual = Math.min(24, Math.max(12, fonteAtual + (Number(botao.dataset.font || 0) * 2)));
                        renderizar();
                    });
                });

                document.querySelector('[data-font-reset]')?.addEventListener('click', () => {
                    fonteAtual = 14;
                    renderizar();
                });

                renderizar();
            });
        </script>
    @endif
@endpush
