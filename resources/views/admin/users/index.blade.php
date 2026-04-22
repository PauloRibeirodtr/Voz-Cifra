@extends('admin.layouts.admin')

@section('title', 'Usuarios | Voz & Cifra')
@section('mobile_title', 'Usuarios')

@section('content')
    @php
        $badgeClasse = static fn (string $cor): string => match ($cor) {
            'verde' => 'bg-emerald-100 text-emerald-700',
            'azul' => 'bg-sky-100 text-sky-700',
            'ambar' => 'bg-amber-100 text-amber-700',
            'roxo' => 'bg-violet-100 text-violet-700',
            'cinza' => 'bg-slate-100 text-slate-700',
            default => 'bg-slate-100 text-slate-700',
        };
    @endphp

    <div class="admin-page-intro flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="admin-page-kicker">Gestao central</p>
            <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Usuarios</h1>
            <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                Gestao central das contas do sistema. Aqui o admin master cria usuarios, acompanha quem ja esta vinculado a igrejas
                e identifica quem ainda precisa receber papel por igreja.
            </p>
        </div>

        <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#6f4726] px-5 py-3 text-sm font-semibold text-white hover:bg-[#5d3b1f]">
            Novo usuario
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Total</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['total'] }}</div>
        </div>
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Admins master</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['admins_master'] }}</div>
        </div>
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-violet-500">Coordenadores</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['coordenadores'] }}</div>
        </div>
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-emerald-500">Admins locais</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['admins_locais'] }}</div>
        </div>
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-amber-500">Musicos</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['musicos'] }}</div>
        </div>
        <div class="admin-stat-card p-5">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sem vinculo</div>
            <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['sem_vinculo'] }}</div>
        </div>
    </div>

    <div class="admin-highlight-surface mb-6 rounded-3xl p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.usuarios.index') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Busca</label>
                <input type="text" name="q" value="{{ $filtros['q'] }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-800" placeholder="Nome, e-mail ou CPF">
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Tipo</label>
                <select name="tipo" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-800">
                    <option value="">Todos</option>
                    <option value="admin_master" @selected($filtros['tipo'] === 'admin_master')>Admin master</option>
                    <option value="coordenador" @selected($filtros['tipo'] === 'coordenador')>Coordenador</option>
                    <option value="admin_local" @selected($filtros['tipo'] === 'admin_local')>Admin local</option>
                    <option value="musico" @selected($filtros['tipo'] === 'musico')>Musico</option>
                    <option value="padre" @selected($filtros['tipo'] === 'padre')>Padre</option>
                    <option value="sem_vinculo" @selected($filtros['tipo'] === 'sem_vinculo')>Sem vinculo</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Status</label>
                <select name="status" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-800">
                    <option value="">Todos</option>
                    <option value="ativo" @selected($filtros['status'] === 'ativo')>Ativo</option>
                    <option value="inativo" @selected($filtros['status'] === 'inativo')>Inativo</option>
                </select>
            </div>

            <div class="lg:col-span-4 flex flex-wrap gap-3">
                <button type="submit" class="inline-flex items-center rounded-xl bg-[#6f4726] px-4 py-3 text-sm font-semibold text-white hover:bg-[#5d3b1f]">
                    Filtrar
                </button>
                <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <div class="rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <h2 class="text-lg font-bold text-gray-800">Base de usuarios</h2>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse ($usuarios as $usuario)
                <article class="px-5 py-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-base font-bold text-gray-800">{{ $usuario->nome }}</h3>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $usuario->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                                @if ($usuario->primeiro_acesso)
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasse('ambar') }}">
                                        Primeiro acesso
                                    </span>
                                @endif
                            </div>

                            <div class="mt-2 flex flex-col gap-1 text-sm text-gray-500">
                                <span>{{ $usuario->email }}</span>
                                <span>CPF: {{ $usuario->cpf }}</span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if ($usuario->ehAdminMaster())
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasse('azul') }}">
                                        Admin master nivel {{ $usuario->nivelGlobal() }}
                                    </span>
                                @endif

                                @if ($usuario->ehPadre())
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasse('roxo') }}">
                                        Padre
                                    </span>
                                @endif

                                @php
                                    $vinculosAtivos = $usuario->vinculosIgreja->where('ativo', true);
                                @endphp

                                @forelse ($vinculosAtivos as $vinculo)
                                    @foreach ($vinculo->listarPapeisAtivos() as $papel)
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasse('verde') }}">
                                            {{ $papel->label() }} em {{ $vinculo->igreja?->nome }}
                                        </span>
                                    @endforeach
                                @empty
                                    @if (!$usuario->ehAdminMaster())
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasse('cinza') }}">
                                            Sem papel por igreja
                                        </span>
                                    @endif
                                @endforelse
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 xl:justify-end">
                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar
                            </a>

                            <form action="{{ route('admin.usuarios.password.reset', $usuario) }}" method="POST" onsubmit="return confirm('Deseja redefinir a senha provisoria deste usuario?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                                    Resetar senha
                                </button>
                            </form>

                            <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" onsubmit="return confirm('Confirma alterar o status desta conta?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center rounded-xl px-4 py-2 text-sm font-semibold text-white {{ $usuario->ativo ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                                    {{ $usuario->ativo ? 'Inativar' : 'Reativar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="px-5 py-10 text-center text-sm text-gray-500">
                    Nenhum usuario encontrado com os filtros atuais.
                </div>
            @endforelse
        </div>

        @if ($usuarios->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
@endsection
