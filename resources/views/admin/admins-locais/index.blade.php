@extends('admin.layouts.admin')

@section('title', 'Admins locais | Voz & Cifra')
@section('mobile_title', 'Admins locais')
@section('desktop_subtitle', 'Gestao global dos administradores de cada igreja')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">Admins locais globais</h1>
        <p class="mt-2 text-sm text-gray-500">Veja quem administra cada igreja, acompanhe o status e aja sem precisar entrar igreja por igreja.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-500">Total</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['total'] }}</div>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-emerald-500">Ativos</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['ativos'] }}</div>
        </div>
        <div class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-red-500">Inativos</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['inativos'] }}</div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-amber-600">Primeiro acesso</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['primeiro_acesso'] }}</div>
        </div>
    </div>

    <div class="mb-6 rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
        <form method="GET" class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <input type="text" name="q" value="{{ $filtros['q'] }}" placeholder="Buscar por nome, email, CPF ou igreja" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 lg:col-span-3">

            <select name="status" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                <option value="">Todos os status</option>
                <option value="ativo" @selected($filtros['status'] === 'ativo')>Ativos</option>
                <option value="inativo" @selected($filtros['status'] === 'inativo')>Inativos</option>
            </select>

            <div class="lg:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">Filtrar</button>
                <a href="{{ route('admin.admins-locais.index') }}" class="rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Limpar</a>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($adminsLocais as $adminLocal)
            <article class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $adminLocal->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                {{ $adminLocal->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                            @if ($adminLocal->primeiro_acesso)
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Primeiro acesso</span>
                            @endif
                        </div>

                        <h2 class="mt-4 text-lg font-black text-gray-800">{{ $adminLocal->nome }}</h2>
                        <div class="mt-2 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Contato</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $adminLocal->email }}</div>
                                <div class="text-xs text-gray-500">{{ $adminLocal->telefone ?: 'Sem telefone' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $adminLocal->igreja?->nome ?: 'Sem igreja' }}</div>
                                <div class="text-xs text-gray-500">{{ optional($adminLocal->igreja)->cidade }}{{ optional($adminLocal->igreja)->estado ? ' - ' . optional($adminLocal->igreja)->estado : '' }}</div>
                            </div>
                            <div class="rounded-2xl bg-gray-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Documento</div>
                                <div class="mt-1 text-sm font-semibold text-gray-700">{{ $adminLocal->cpf }}</div>
                                <div class="text-xs text-gray-500">ID {{ $adminLocal->id }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex w-full shrink-0 flex-col gap-3 xl:w-60">
                        @if ($adminLocal->igreja)
                            <a href="{{ route('admin.igrejas.edit', $adminLocal->igreja) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-50">
                                Ver igreja
                            </a>
                        @endif

                        <form action="{{ route('admin.admins-locais.password.reset', $adminLocal) }}" method="POST" onsubmit="return confirm('Deseja resetar a senha deste admin local para o CPF e exigir troca no proximo acesso?');">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                                Resetar senha
                            </button>
                        </form>

                        <form action="{{ route('admin.admins-locais.toggle', $adminLocal) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste admin local?');">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl px-4 py-3 text-sm font-semibold {{ $adminLocal->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                {{ $adminLocal->ativo ? 'Inativar' : 'Ativar' }}
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-sm text-gray-500 shadow-sm">
                Nenhum admin local encontrado com os filtros atuais.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $adminsLocais->links() }}
    </div>
@endsection
