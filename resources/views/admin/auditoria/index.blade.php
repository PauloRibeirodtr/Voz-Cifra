@extends('admin.layouts.admin')

@section('title', 'Auditoria | Voz & Cifra')
@section('mobile_title', 'Auditoria')
@section('desktop_subtitle', 'Governanca de acoes sensiveis e rastreabilidade por protocolo')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">Central de auditoria</h1>
        <p class="mt-2 text-sm text-gray-500">Acompanhe quem executou a acao, para quem, em qual igreja e qual foi o resultado da notificacao.</p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Total</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['total'] }}</div>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">Hoje</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['hoje'] }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">Email enviado</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['email_enviado'] }}</div>
        </div>
        <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-red-500">Falhas</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['email_falhou'] }}</div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-amber-500">Operacoes</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['operacoes'] }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-6">
            <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar por protocolo, ator, alvo, email ou igreja" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 lg:col-span-2">

            <select name="categoria" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todas as categorias</option>
                @foreach ($categoriasDisponiveis as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['categoria'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="evento" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todos os eventos</option>
                @foreach ($eventosDisponiveis as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['evento'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="igreja_id" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todas as igrejas</option>
                @foreach ($igrejas as $igreja)
                    <option value="{{ $igreja->id }}" @selected((string) $filtros['igreja_id'] === (string) $igreja->id)>{{ $igreja->nome }}</option>
                @endforeach
            </select>

            <select name="resultado" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todos os resultados</option>
                @foreach ($resultadosDisponiveis as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['resultado'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="lg:col-span-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">Filtrar</button>
                <a href="{{ route('admin.auditoria.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpar</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($eventos as $item)
            <article class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $item->protocolo }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($item->categoria) }}</span>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $eventosDisponiveis[$item->evento] ?? $item->evento }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $item->resultado === 'email_falhou' ? 'bg-red-100 text-red-700' : ($item->resultado === 'email_enviado' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                                {{ $resultadosDisponiveis[$item->resultado] ?? $item->resultado }}
                            </span>
                        </div>

                        @if (filled($item->contexto['resumo'] ?? null))
                            <p class="mt-4 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                {{ $item->contexto['resumo'] }}
                            </p>
                        @endif

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Responsavel</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $item->ator_nome ?: 'Sistema' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->ator_funcao ?: 'Sem funcao' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Usuario alvo</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $item->alvo_nome ?: 'Nao informado' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->alvo_email ?: 'Sem email' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $item->igreja_nome ?: 'Nao informada' }}</div>
                                <div class="text-xs text-gray-500">Criado em {{ $item->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Entrega</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ optional($item->notificacao_enviada_em)->format('d/m/Y H:i') ?: 'Ainda nao enviada' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->ip ?: 'Sem IP registrado' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="xl:w-56 shrink-0">
                        <a href="{{ route('admin.auditoria.show', $item) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                            Ver detalhe
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-500 shadow-sm">
                Nenhum evento de auditoria encontrado com os filtros atuais.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $eventos->links() }}
    </div>
@endsection
