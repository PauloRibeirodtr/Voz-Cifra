@extends('member.layouts.app')

@section('title', ($versaoMusical->titulo ?: 'Versao musical') . ' | Voz & Cifra')
@section('mobile_title', 'Estudo da cifra')
@section('desktop_subtitle', 'Leitura musical para estudo e apoio')

@section('header_actions')
    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Voltar para biblioteca
    </a>
@endsection

@push('styles')
    <style>
        @include('partials.cifra-viewer-styles')
    </style>
@endpush

@section('content')
    <section class="rounded-[2rem] bg-gradient-to-r from-slate-950 via-slate-900 to-emerald-950 px-6 py-7 text-white shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Modo estudo</p>
                <h1 class="mt-2 text-3xl font-black">{{ $musica->titulo }}</h1>
                <p class="mt-2 text-sm text-emerald-100">{{ $versaoMusical->titulo ?: 'Versao principal' }} @if($missaAtiva) • Missa ativa: {{ $missaAtiva->titulo }} @endif</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Meu repertorio</a>
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Painel</a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Tom exibido</span>
                <span id="tom_atual_badge" class="mt-2 block text-xl font-black text-white">{{ $tomExibicao ?: 'Nao informado' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Tom original</span>
                <span class="mt-2 block text-xl font-black text-white">{{ $tomOriginal ?: 'Nao informado' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-200">BPM</span>
                <span class="mt-2 block text-xl font-black text-white">{{ $versaoMusical->bpm ?: '-' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Contexto</span>
                <span class="mt-2 block text-sm font-bold text-white">{{ $itemMissa ? 'Versao usada na missa da sua igreja' : 'Estudo livre da biblioteca musical' }}</span>
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-gray-100 bg-slate-950 p-6 shadow-sm text-white">
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
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Tom</span>
                    <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-bold text-white hover:bg-white/20">-</button>
                    <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">Original</button>
                    <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-lg font-bold text-white hover:bg-white/20">+</button>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-200">Fonte</span>
                    <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-bold text-white hover:bg-white/20">A-</button>
                    <button type="button" data-font-reset class="inline-flex rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-sm font-semibold text-white hover:bg-white/20">Padrao</button>
                    <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 text-sm font-bold text-white hover:bg-white/20">A+</button>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-900 p-5 text-green-200 shadow-inner">
            <div id="letra_com_cifras_preview" class="space-y-2"></div>
        </div>
    </section>
@endsection

@push('scripts')
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
@endpush

