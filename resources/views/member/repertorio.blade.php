@extends('member.layouts.app')

@section('title', 'Meu repertorio | Voz & Cifra')
@section('mobile_title', 'Repertorio')
@section('desktop_subtitle', 'Missa publicada da sua igreja para tocar em sequencia')

@php
    $tonsMusicais = config('musical.tons', []);
@endphp

@section('header_actions')
    <a href="{{ route('member.musicas.index') }}" class="music-btn">
        Consultar musicas
    </a>
@endsection

@push('styles')
    @include('partials.cifra-viewer-styles')
@endpush

@section('content')
    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] text-emerald-700">Modo missa</p>
                <h1 class="mt-2 text-3xl font-black text-gray-900">Meu repertorio</h1>
                <p class="mt-2 max-w-3xl text-sm text-gray-500">Abra a celebracao publicada, siga as musicas em ordem e use a rolagem automatica para tocar sem ficar mexendo na tela.</p>
            </div>
            <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Painel</a>
        </div>
    </section>

    @include('member.partials.church-switcher', ['igrejaAtual' => $igreja ?? null])

    @if (!$missa)
        <div class="mt-6 rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Ainda nao existe missa com repertorio disponivel</h2>
            <p class="mt-2 text-sm text-gray-500">Assim que a igreja publicar a celebracao, as musicas vao aparecer aqui para estudo e leitura.</p>
        </div>
    @else
        @if (session('success'))
            <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 px-5 py-4 text-sm font-bold text-sky-800">
                {{ session('info') }}
            </div>
        @endif

        <details class="missa-card mt-6" data-missa-card open>
            <summary class="p-5 sm:p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">{{ $missa->ativo ? 'Missa publicada' : 'Proxima missa' }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">{{ $missa->missaMusicas->count() }} musicas</span>
                        </div>
                        <h2 class="mt-3 text-2xl font-black text-gray-900">{{ $missa->titulo }}</h2>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ optional($missa->data_missa)->format('d/m/Y') }} as {{ substr((string) $missa->hora_inicio, 0, 5) }}
                            @if($missa->tempoLiturgico)
                                &bull; {{ $missa->tempoLiturgico->nome }}
                            @endif
                        </p>
                    </div>
                    <div class="inline-flex items-center justify-center gap-3 rounded-xl border border-[#8c6933]/20 bg-white px-4 py-3 text-sm font-black text-[#6c4a21]">
                        <span>Abrir repertorio</span>
                        <i class="fa-solid fa-chevron-down missa-toggle-icon"></i>
                    </div>
                </div>
            </summary>

            <div class="border-t border-[#8c6933]/10 bg-white p-4 sm:p-6">
                <div class="mb-5 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    <strong>Sequencia da celebracao:</strong> as musicas aparecem na ordem da missa. Toque em uma musica para abrir ou fechar a cifra.
                </div>

                @if ($missa->missaMusicas->isNotEmpty())
                    <nav class="repertorio-flow mb-6" aria-label="Sequencia do repertorio">
                        @foreach ($missa->missaMusicas as $itemSequencia)
                            <a href="#repertorio-item-{{ $itemSequencia->id }}" class="repertorio-flow-item" data-repertorio-flow-link="{{ $itemSequencia->id }}">
                                <span class="repertorio-flow-number">{{ $itemSequencia->ordem }}</span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-black">{{ $itemSequencia->musica->titulo }}</span>
                                    <span class="mt-1 block truncate text-xs font-bold text-gray-500">
                                        {{ $itemSequencia->momentoLiturgico?->nome ?: 'Momento nao definido' }}
                                    </span>
                                </span>
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-800">
                                    {{ $itemSequencia->tom_exibicao ? 'Tom ' . $itemSequencia->tom_exibicao : 'Tom original' }}
                                </span>
                            </a>
                        @endforeach
                    </nav>
                @endif

                <div class="space-y-4" data-musicas-lista>
                    @forelse ($missa->missaMusicas as $item)
                        @php
                            $textoCifra = (string) ($item->versaoMusical?->letra_com_cifras ?? '');
                            $pedidoTomPendente = $item->solicitacoesMudancaTom
                                ->where('usuario_id', auth()->id())
                                ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE)
                                ->first();
                        @endphp

                        <details id="repertorio-item-{{ $item->id }}" class="musica-item scroll-mt-24" data-musica-item open>
                            <summary class="p-4 sm:p-5">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">Ordem {{ $item->ordem }}</span>
                                            @if ($item->momentoLiturgico)
                                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $item->momentoLiturgico->nome }}</span>
                                            @endif
                                            @if ($item->versaoMusical?->bpm)
                                                <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">BPM {{ $item->versaoMusical->bpm }}</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-xl font-black text-gray-900">{{ $item->musica->titulo }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista nao informado' }}</p>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 lg:justify-end">
                                        @if ($item->tom_exibicao)
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Tom {{ $item->tom_exibicao }}</span>
                                        @endif
                                        @if ($pedidoTomPendente)
                                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">Pedido: {{ $pedidoTomPendente->tom_sugerido }}</span>
                                        @endif
                                        <span class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700">
                                            <i class="fa-solid fa-chevron-down missa-toggle-icon"></i>
                                        </span>
                                    </div>
                                </div>
                            </summary>

                            @if ($item->versaoMusical)
                                <div class="cifra-palco p-4 sm:p-6">
                                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] font-black uppercase tracking-[0.2em] text-emerald-700">Cifra da missa</p>
                                            <p class="mt-1 text-sm text-gray-600">{{ $item->versaoMusical->titulo ?: 'Versao principal' }}</p>
                                        </div>
                                        <a href="{{ route('member.versoes.show', [$item->musica, $item->versaoMusical]) }}" class="inline-flex rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                            Modo estudo
                                        </a>
                                    </div>

                                    <div class="repertorio-controls mb-4" data-repertorio-controls>
                                        <span class="rounded-full bg-amber-50 px-3 py-2 text-xs font-black text-amber-800" data-item-tom-label>Tom {{ $item->tom_exibicao ?: ($item->versaoMusical?->tom_musical ?: 'nao informado') }}</span>
                                        <button type="button" class="repertorio-control-btn" data-item-transpose="-1">- Tom</button>
                                        <button type="button" class="repertorio-control-btn" data-item-transpose-reset>Original</button>
                                        <button type="button" class="repertorio-control-btn" data-item-transpose="1">+ Tom</button>
                                        <label class="inline-flex items-center gap-2 text-xs font-black text-gray-500">
                                            Capotraste
                                            <select class="repertorio-control-select" data-item-capo>
                                                <option value="0">Sem capo</option>
                                                @for ($casaCapotraste = 1; $casaCapotraste <= 11; $casaCapotraste++)
                                                    <option value="{{ $casaCapotraste }}">{{ $casaCapotraste }} casa</option>
                                                @endfor
                                            </select>
                                        </label>
                                        <button type="button" class="repertorio-control-btn" data-item-font="-1">A-</button>
                                        <button type="button" class="repertorio-control-btn" data-item-font-reset>Padrao</button>
                                        <button type="button" class="repertorio-control-btn" data-item-font="1">A+</button>
                                    </div>

                                    <details class="mb-4 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black text-gray-900 [&::-webkit-details-marker]:hidden">
                                            <span>{{ $pedidoTomPendente ? 'Pedido de tom em analise' : 'Sugerir mudanca de tom' }}</span>
                                            <span class="rounded-full bg-white px-3 py-1 text-xs text-gray-600">{{ $pedidoTomPendente ? $pedidoTomPendente->tom_sugerido : 'Abrir' }}</span>
                                        </summary>

                                        @if ($pedidoTomPendente)
                                            <p class="mt-3 text-sm text-gray-600">Seu pedido para tocar em {{ $pedidoTomPendente->tom_sugerido }} foi enviado. A equipe da igreja precisa aprovar antes de mudar o repertorio.</p>
                                        @else
                                            <form action="{{ route('member.repertorio.tom.solicitar', $item) }}" method="POST" class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-[12rem_1fr_auto] sm:items-end">
                                                @csrf
                                                <div>
                                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500">Novo tom</label>
                                                    <select name="tom_sugerido" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-bold text-slate-900">
                                                        <option value="">Escolha</option>
                                                        @foreach ($tonsMusicais as $tomMusical)
                                                            <option value="{{ $tomMusical }}">{{ $tomMusical }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-black uppercase tracking-wider text-gray-500">Motivo opcional</label>
                                                    <input name="observacao" maxlength="500" class="mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-slate-900" placeholder="Ex.: fica melhor para as vozes">
                                                </div>
                                                <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white hover:bg-emerald-700">
                                                    Enviar
                                                </button>
                                            </form>
                                        @endif
                                    </details>

                                    <div class="mb-4 flex flex-wrap gap-2" data-repertorio-acordes></div>
                                    <div
                                        data-repertorio-cifra
                                        data-texto-cifra-id="repertorio-cifra-texto-{{ $item->id }}"
                                        data-tom-base="{{ $item->tom_exibicao ?: $item->versaoMusical?->tom_musical }}"
                                        class="space-y-2 text-base"
                                    ></div>
                                    <script type="application/json" id="repertorio-cifra-texto-{{ $item->id }}">{!! json_encode($textoCifra, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
                                </div>
                            @else
                                <div class="border-t border-gray-100 bg-gray-50 px-5 py-5 text-sm text-gray-500">
                                    Esta musica ainda nao possui cifra vinculada.
                                </div>
                            @endif
                        </details>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">O repertorio desta missa ainda nao possui musicas cadastradas.</div>
                    @endforelse
                </div>
            </div>
        </details>

        <div class="scroll-dock p-3" data-scroll-dock>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button type="button" class="rounded-xl bg-emerald-700 px-4 py-3 text-sm font-black text-white hover:bg-emerald-800" data-scroll-toggle>
                    Iniciar rolagem
                </button>
                <div class="flex flex-1 items-center gap-3">
                    <span class="text-xs font-bold text-gray-600">Velocidade</span>
                    <input type="range" min="0.4" max="2.4" step="0.2" value="1" class="w-full accent-emerald-700" data-scroll-speed>
                    <span class="min-w-12 text-sm font-black text-gray-800" data-scroll-speed-label>1.0x</span>
                </div>
                <button type="button" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50" data-scroll-top>
                    Topo
                </button>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @include('partials.chord-transposer-script')
    <script src="{{ asset('js/member/repertorio.js') }}"></script>
@endpush
