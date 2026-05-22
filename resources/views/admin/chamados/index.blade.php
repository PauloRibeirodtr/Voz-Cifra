@extends('admin.layouts.admin')

@section('title', 'Chamados | Voz & Cifra')
@section('mobile_title', 'Chamados')
@section('desktop_subtitle', 'Suporte central e fila operacional do admin master')

@section('content')
    @php($routePrefix = $routePrefix ?? 'admin')

    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">Chamados de suporte</h1>
        <p class="mt-2 text-sm text-gray-500">Acompanhe protocolos, priorize atendimentos e centralize as respostas da equipe.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-red-500">Abertos</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['abertos'] }}</div>
        </div>
        <div class="rounded-2xl border border-blue-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-blue-500">Em andamento</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['em_andamento'] }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">Resolvidos</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['resolvidos'] }}</div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-amber-600">Alta prioridade</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['alta_prioridade'] }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar por protocolo, nome, email ou igreja" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 lg:col-span-2">

            <select name="status" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todos os status</option>
                @foreach ($statusOptions as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['status'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="prioridade" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todas as prioridades</option>
                @foreach ($prioridadeOptions as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['prioridade'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="categoria" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todas as categorias</option>
                @foreach ($categoriaOptions as $valor => $label)
                    <option value="{{ $valor }}" @selected($filtros['categoria'] === $valor)>{{ $label }}</option>
                @endforeach
            </select>

            <div class="lg:col-span-5 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">Filtrar</button>
                <a href="{{ route($routePrefix . '.chamados.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpar</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($chamados as $chamado)
            <article class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $chamado->protocolo }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->statusBadgeClass($chamado->status) }}">
                                {{ $supportService->statusLabel($chamado->status) }}
                            </span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->prioridadeBadgeClass($chamado->prioridade) }}">
                                {{ $supportService->prioridadeLabel($chamado->prioridade) }}
                            </span>
                        </div>

                        <h2 class="mt-4 text-lg font-black text-gray-800">{{ $chamado->titulo }}</h2>
                        <p class="mt-2 text-sm text-gray-600">{{ $chamado->descricao }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Solicitante</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->solicitante_nome ?: 'Nao informado' }}</div>
                                <div class="text-xs text-gray-500">{{ $chamado->solicitante_email ?: 'Sem email' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->igreja_nome ?: 'Nao informada' }}</div>
                                <div class="text-xs text-gray-500">{{ $supportService->categoriaLabel($chamado->categoria) }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Responsavel</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->responsavel?->nome ?: 'Nao atribuido' }}</div>
                                <div class="text-xs text-gray-500">Ultima interacao: {{ optional($chamado->ultima_interacao_em)->format('d/m/Y H:i') ?: 'Nao registrada' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Criado em</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->created_at->format('d/m/Y H:i') }}</div>
                                <div class="text-xs text-gray-500">Canal: {{ $chamado->canal_origem }} · {{ $chamado->mensagens_count }} mensagens</div>
                            </div>
                        </div>
                    </div>

                    <div class="xl:w-56 shrink-0">
                        <a href="{{ route($routePrefix . '.chamados.show', $chamado) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                            Abrir atendimento
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-500 shadow-sm">
                Nenhum chamado encontrado com os filtros atuais.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $chamados->links() }}
    </div>
@endsection
