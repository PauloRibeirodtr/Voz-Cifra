@php
    $usuarioContexto = auth()->user();
    $igrejasDisponiveis = $igrejasDisponiveis ?? ($usuarioContexto?->igrejasDisponiveisParaAtivacao() ?? collect());
    $igrejaAtual = $igrejaAtual ?? ($usuarioContexto?->igrejaAtiva() ?? $usuarioContexto?->igreja);
@endphp

@if ($igrejasDisponiveis->count() > 1)
    <details class="mt-6 rounded-3xl border border-amber-200 bg-white p-5 shadow-sm">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-700">Igreja ativa</p>
                <h2 class="mt-1 truncate text-lg font-black text-gray-950">{{ $igrejaAtual?->nome ?: 'Escolha uma igreja' }}</h2>
            </div>
            <span class="rounded-full bg-amber-50 px-4 py-2 text-xs font-black text-amber-800">Trocar</span>
        </summary>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach ($igrejasDisponiveis as $igrejaOpcao)
                @php($estaAtiva = (int) $igrejaOpcao->id === (int) ($igrejaAtual?->id ?? 0))
                <form action="{{ route('contexto.igreja-ativa.update') }}" method="POST" class="rounded-2xl border {{ $estaAtiva ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 bg-white' }} p-4">
                    @csrf
                    <input type="hidden" name="igreja_id" value="{{ $igrejaOpcao->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-gray-950">{{ $igrejaOpcao->nome }}</p>
                            <p class="mt-1 text-xs font-semibold text-gray-500">{{ $igrejaOpcao->cidade ?: 'Cidade nao informada' }}{{ $igrejaOpcao->estado ? ' - ' . $igrejaOpcao->estado : '' }}</p>
                        </div>
                        @if ($estaAtiva)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700">Atual</span>
                        @endif
                    </div>

                    <button type="submit" class="mt-4 inline-flex w-full items-center justify-center rounded-xl {{ $estaAtiva ? 'border border-emerald-200 bg-white text-emerald-700' : 'bg-[#6c4a21] text-white hover:bg-[#5b3d1a]' }} px-4 py-3 text-sm font-black transition">
                        {{ $estaAtiva ? 'Usando esta igreja' : 'Usar esta igreja' }}
                    </button>
                </form>
            @endforeach
        </div>

        <a href="{{ route('member.repertorio') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-black text-gray-700 hover:bg-white">
            Ver repertorio da igreja ativa
        </a>
    </details>
@endif
