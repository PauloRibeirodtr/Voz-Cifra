@extends('admin.layouts.admin')

@section('title', 'Tempos liturgicos | Voz & Cifra')
@section('mobile_title', 'Tempos')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Tempos liturgicos</h1>
            <p class="text-sm text-gray-500">Gerencie os tempos liturgicos centrais do sistema.</p>
        </div>

        <a href="{{ route('admin.tempos-liturgicos.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800 sm:w-auto">
            Novo tempo
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($temposLiturgicos->isEmpty())
            <div class="p-8 text-center text-gray-500">
                Nenhum tempo liturgico cadastrado ate o momento.
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Nome</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Descricao</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($temposLiturgicos as $tempoLiturgico)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $tempoLiturgico->nome }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $tempoLiturgico->descricao ?: 'Sem descricao cadastrada.' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $tempoLiturgico->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $tempoLiturgico->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.tempos-liturgicos.edit', $tempoLiturgico) }}" class="inline-flex px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Editar
                                        </a>

                                        <form action="{{ route('admin.tempos-liturgicos.destroy', $tempoLiturgico) }}" method="POST" onsubmit="return confirm('Deseja excluir este tempo liturgico?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex px-4 py-2 bg-red-50 border border-red-200 rounded-lg text-sm font-medium text-red-700 hover:bg-red-100">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-4 p-4 md:hidden">
                @foreach ($temposLiturgicos as $tempoLiturgico)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-800">{{ $tempoLiturgico->nome }}</h2>
                                <p class="mt-2 text-sm text-gray-600">{{ $tempoLiturgico->descricao ?: 'Sem descricao cadastrada.' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $tempoLiturgico->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $tempoLiturgico->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <a href="{{ route('admin.tempos-liturgicos.edit', $tempoLiturgico) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                            <form action="{{ route('admin.tempos-liturgicos.destroy', $tempoLiturgico) }}" method="POST" onsubmit="return confirm('Deseja excluir este tempo liturgico?');">
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
