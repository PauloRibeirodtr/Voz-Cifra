@extends('admin.layouts.admin')

@section('title', 'Músicos | Voz & Cifra')
@section('mobile_title', 'Músicos')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Músicos</h1>
            <p class="text-sm text-gray-500">Gerencie os músicos vinculados às igrejas do sistema.</p>
        </div>

        <a href="{{ route('admin.musicos.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800 sm:w-auto">
            Cadastrar musico
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($musicos->isEmpty())
            <div class="p-8 text-center text-gray-500">Nenhum músico cadastrado até o momento.</div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Músico</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Igreja</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Contato</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($musicos as $musico)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $musico->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $musico->cpf }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $musico->igreja?->nome ?: 'Sem igreja' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>{{ $musico->email }}</div>
                                    @if ($musico->telefone)
                                        <div class="text-xs text-gray-400">{{ $musico->telefone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $musico->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $musico->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($musico->primeiro_acesso)
                                            <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Primeiro acesso
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.musicos.edit', $musico) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Editar</a>
                                        <form action="{{ route('admin.musicos.password.reset', $musico) }}" method="POST" onsubmit="return confirm('Deseja resetar a senha deste músico para o CPF e exigir troca no próximo acesso?');">
                                            @csrf
                                            <button type="submit" class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100">Resetar senha</button>
                                        </form>
                                        <form action="{{ route('admin.musicos.toggle', $musico) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste músico?');">
                                            @csrf
                                            <button type="submit" class="inline-flex rounded-lg px-4 py-2 text-sm font-medium {{ $musico->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $musico->ativo ? 'Inativar' : 'Ativar' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.musicos.destroy', $musico) }}" method="POST" onsubmit="return confirm('Deseja excluir este músico?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">Excluir</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 md:hidden">
                @foreach ($musicos as $musico)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-800">{{ $musico->nome }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ $musico->igreja?->nome ?: 'Sem igreja' }}</p>
                                <p class="mt-1 break-all text-sm text-gray-600">{{ $musico->email }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $musico->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $musico->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2">
                            <a href="{{ route('admin.musicos.edit', $musico) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                            <form action="{{ route('admin.musicos.password.reset', $musico) }}" method="POST" onsubmit="return confirm('Deseja resetar a senha deste músico para o CPF e exigir troca no próximo acesso?');">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">Resetar senha</button>
                            </form>
                            <form action="{{ route('admin.musicos.toggle', $musico) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste músico?');">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $musico->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                    {{ $musico->ativo ? 'Inativar' : 'Ativar' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.musicos.destroy', $musico) }}" method="POST" onsubmit="return confirm('Deseja excluir este músico?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100">Excluir</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
