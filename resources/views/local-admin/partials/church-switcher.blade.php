@if (count($igrejasAdministradas ?? []) > 1)
    <section class="mb-6 rounded-2xl border border-[#ead6b3] bg-[#fff8ed] p-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.18em] text-[#8c6933]">Igrejas vinculadas</p>
                <h2 class="mt-2 text-xl font-black text-gray-900">Escolha a igreja que deseja administrar agora</h2>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">Cada cart&atilde;o muda o contexto do painel para a igreja selecionada. Isso ajuda a manter missas, repert&oacute;rios e dados operacionais sempre no lugar certo.</p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-2">
            @foreach ($igrejasAdministradas as $igrejaDisponivel)
                <article class="rounded-2xl border {{ $igrejaDisponivel->eh_ativa ? 'border-[#8c6933] bg-white shadow-sm' : 'border-[#ead6b3] bg-[#fffdf9]' }} p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900">{{ $igrejaDisponivel->nome }}</h3>
                                @if ($igrejaDisponivel->eh_ativa)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Igreja ativa</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ $igrejaDisponivel->cidade }} - {{ $igrejaDisponivel->estado }}</p>
                            <p class="mt-2 text-xs text-gray-500">CNPJ: {{ $igrejaDisponivel->cnpj }}</p>
                        </div>

                        <form action="{{ route('contexto.igreja-ativa.update') }}" method="POST" class="sm:min-w-[11rem]">
                            @csrf
                            <input type="hidden" name="igreja_id" value="{{ $igrejaDisponivel->id }}">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold transition {{ $igrejaDisponivel->eh_ativa ? 'border border-[#8c6933]/35 bg-[#fff8ed] text-[#6c4a21]' : 'bg-[#6c4a21] text-white hover:bg-[#5b3d1a]' }}">
                                {{ $igrejaDisponivel->eh_ativa ? 'Usando esta igreja' : 'Administrar esta igreja' }}
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
