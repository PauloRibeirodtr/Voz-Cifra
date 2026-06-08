@extends('local-admin.layouts.admin')

@section('title', 'Visualização da missa | Voz & Cifra')
@section('mobile_title', 'Visualização')

@push('styles')
    @include('partials.cifra-viewer-styles')
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">Visualização da missa</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ $missa->titulo }} &bull; {{ optional($missa->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missa->hora_inicio, 0, 5) }}
            </p>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">Esta tela funciona como uma prévia de leitura para fiéis e músicos, com foco em clareza, espaçamento e acompanhamento da celebração.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap">
            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50">
                Voltar para a missa
            </a>
            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-medium text-amber-800 hover:bg-amber-100">
                Baixar PDF completo
            </a>
        </div>
    </div>

    @if ($itensApresentacao->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Nenhuma cifra pronta para visualização</h2>
            <p class="mt-2 text-sm text-gray-500">Vincule versões musicais aos itens do repertório para usar este modo contínuo da missa.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Sequência da missa</h2>
                <p class="mt-1 text-sm text-gray-500">Avance pela celebração sem perder a ordem dos cantos.</p>

                <div class="mt-4 space-y-2">
                    @foreach ($itensApresentacao as $indice => $item)
                        <button
                            type="button"
                            class="botao-item-apresentacao flex w-full flex-col rounded-2xl border border-gray-200 px-4 py-3 text-left transition hover:border-sky-200 hover:bg-sky-50"
                            data-item-indice="{{ $indice }}"
                        >
                            <span class="text-xs font-black uppercase tracking-wider text-gray-400">Ordem {{ $item['ordem'] }}</span>
                            <span class="mt-1 text-sm font-bold text-gray-900">{{ $item['titulo'] }}</span>
                            <span class="mt-1 text-xs text-gray-500">{{ $item['momento'] ?: 'Momento ainda não definido' }}</span>
                        </button>
                    @endforeach
                </div>
            </aside>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="apresentacao_ordem" class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Ordem</span>
                                <span id="apresentacao_momento" class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Momento</span>
                            </div>
                            <h2 id="apresentacao_titulo" class="mt-3 text-2xl font-black text-gray-900"></h2>
                            <p id="apresentacao_subtitulo" class="mt-2 text-sm text-gray-500"></p>
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
                                <button type="button" data-font="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100">A-</button>
                                <button type="button" data-font-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Padrão</button>
                                <button type="button" data-font="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-bold text-gray-700 hover:bg-gray-100">A+</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" id="apresentacao_anterior" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">Anterior</button>
                            <button type="button" id="apresentacao_proxima" class="inline-flex items-center justify-center rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white hover:bg-sky-700">Próxima</button>
                            <span id="apresentacao_tom_badge" class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tom</span>
                            <span id="apresentacao_bpm_badge" class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">BPM</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3">
                            <button type="button" id="toggle_autorrolagem_apresentacao" class="rounded-lg bg-[#6c4a21] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5b3d1a]">Iniciar auto-rolagem</button>
                            <label for="velocidade_apresentacao" class="text-sm font-medium text-gray-600">Velocidade</label>
                            <input id="velocidade_apresentacao" type="range" min="0.25" max="6" value="0.75" step="0.25" class="accent-[#8c6933]">
                            <span id="velocidade_apresentacao_valor" class="text-sm font-semibold text-gray-700">0.75</span>
                        </div>
                    </div>

                    <div id="apresentacao_container" class="apresentacao-cifra-box max-h-[68vh] overflow-y-auto p-5">
                        <div id="apresentacao_letra" class="space-y-2"></div>
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($itensApresentacao->isNotEmpty())
        @include('partials.chord-transposer-script')
        <script type="application/json" id="missa-apresentacao-itens">{!! json_encode($itensApresentacao, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
        <script src="{{ asset('js/local-admin/missa-apresentacao.js') }}"></script>
    @endif
@endpush
