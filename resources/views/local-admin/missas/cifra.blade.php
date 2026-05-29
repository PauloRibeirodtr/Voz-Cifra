@extends('local-admin.layouts.admin')

@section('title', 'Visualiza&ccedil;&atilde;o com cifra | Voz & Cifra')
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

        <div class="flex flex-wrap gap-3">
            @if ($itemRepertorio->versaoMusical)
                <a href="{{ route('local-admin.repertorio.print', [$missa, $itemRepertorio]) }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 font-medium text-emerald-800 hover:bg-emerald-100">
                    Imprimir
                </a>
                <a href="{{ route('local-admin.repertorio.pdf', [$missa, $itemRepertorio]) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-medium text-amber-800 hover:bg-amber-100">
                    Baixar PDF
                </a>
            @endif
            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50">
                Voltar para a missa
            </a>
        </div>
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
                            <span id="capotraste_badge" class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                Sem capo
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Tom</span>
                                <button type="button" data-transpose="-1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">-</button>
                                <button type="button" data-transpose-reset class="inline-flex rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Original</button>
                                <button type="button" data-transpose="1" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-lg font-bold text-gray-700 hover:bg-gray-100">+</button>
                            </div>

                            <label class="flex items-center gap-2">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Capo</span>
                                <select id="controle_capotraste" class="min-h-10 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 focus:border-amber-400 focus:ring-amber-400">
                                    <option value="0">Sem capo</option>
                                    @for ($casaCapotraste = 1; $casaCapotraste <= 11; $casaCapotraste++)
                                        <option value="{{ $casaCapotraste }}">{{ $casaCapotraste }} casa</option>
                                    @endfor
                                </select>
                            </label>

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

                <div class="leitura-clara rounded-2xl border border-[#8c6933]/15 bg-gradient-to-b from-[#fffdfa] to-[#f7efe3] p-5 text-gray-800 shadow-inner">
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
        <script type="application/json" id="missa-cifra-texto">{!! json_encode($textoCifraExibicao, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
        <script type="application/json" id="missa-cifra-tom">{!! json_encode($tomExibicao, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
        <script src="{{ asset('js/local-admin/missa-cifra.js') }}"></script>
    @endif
@endpush
