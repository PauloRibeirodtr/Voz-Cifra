@extends('local-admin.layouts.admin')

@section('title', 'Pessoas da igreja | Voz & Cifra')
@section('mobile_title', 'Pessoas')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pessoas da igreja</h1>
            <p class="mt-1 text-sm text-gray-500">Veja apenas as pessoas vinculadas a {{ $igreja->nome }}, incluindo outros admins locais e musicos.</p>
        </div>

        <a href="{{ route('local-admin.musicos.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
            Novo musico
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($usuariosIgreja->isEmpty())
            <div class="p-8 text-center text-gray-500">Nenhuma pessoa cadastrada para esta igreja.</div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Pessoa</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Perfil</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Contato</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usuariosIgreja as $usuarioIgreja)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $usuarioIgreja->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $usuarioIgreja->cpf }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $usuarioIgreja->perfil_global === 'admin_local' ? 'bg-indigo-100 text-indigo-700' : 'bg-green-100 text-green-700' }}">
                                        {{ $usuarioIgreja->perfil_global === 'admin_local' ? 'Admin local' : 'Musico' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>{{ $usuarioIgreja->email }}</div>
                                    @if ($usuarioIgreja->telefone)
                                        <div class="text-xs text-gray-400">{{ $usuarioIgreja->telefone }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $usuarioIgreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $usuarioIgreja->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($usuarioIgreja->primeiro_acesso)
                                            <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Primeiro acesso
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($usuarioIgreja->perfil_global === 'member')
                                        <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('local-admin.musicos.edit', $usuarioIgreja) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Editar</a>
                                            <form action="{{ route('local-admin.musicos.password.reset', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja resetar a senha deste musico para o CPF e exigir troca no proximo acesso?');">
                                                @csrf
                                                <button type="submit" class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100">Resetar senha</button>
                                            </form>
                                            <form action="{{ route('local-admin.musicos.toggle', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste musico?');">
                                                @csrf
                                                <button type="submit" class="inline-flex rounded-lg px-4 py-2 text-sm font-medium {{ $usuarioIgreja->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                    {{ $usuarioIgreja->ativo ? 'Inativar' : 'Ativar' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('local-admin.musicos.destroy', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja excluir este musico?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100">Excluir</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">Somente leitura para outros admins locais.</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 md:hidden">
                @foreach ($usuariosIgreja as $usuarioIgreja)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-800">{{ $usuarioIgreja->nome }}</h2>
                                <p class="mt-1 break-all text-sm text-gray-600">{{ $usuarioIgreja->email }}</p>
                                @if ($usuarioIgreja->telefone)
                                    <p class="mt-1 text-xs text-gray-400">{{ $usuarioIgreja->telefone }}</p>
                                @endif
                                <p class="mt-2 text-xs font-semibold {{ $usuarioIgreja->perfil_global === 'admin_local' ? 'text-indigo-700' : 'text-green-700' }}">
                                    {{ $usuarioIgreja->perfil_global === 'admin_local' ? 'Admin local' : 'Musico' }}
                                </p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $usuarioIgreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $usuarioIgreja->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        @if ($usuarioIgreja->perfil_global === 'member')
                            <div class="mt-4 grid grid-cols-1 gap-2">
                                <a href="{{ route('local-admin.musicos.edit', $usuarioIgreja) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                                <form action="{{ route('local-admin.musicos.password.reset', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja resetar a senha deste musico para o CPF e exigir troca no proximo acesso?');">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">Resetar senha</button>
                                </form>
                                <form action="{{ route('local-admin.musicos.toggle', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste musico?');">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $usuarioIgreja->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                        {{ $usuarioIgreja->ativo ? 'Inativar' : 'Ativar' }}
                                    </button>
                                </form>
                                <form action="{{ route('local-admin.musicos.destroy', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja excluir este musico?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100">Excluir</button>
                                </form>
                            </div>
                        @else
                            <div class="mt-4 rounded-xl bg-gray-50 p-3 text-sm text-gray-500">
                                Este admin local tambem pertence a sua igreja. A visualizacao aqui e somente leitura.
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
