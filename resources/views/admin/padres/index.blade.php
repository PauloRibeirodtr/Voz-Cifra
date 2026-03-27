@extends('admin.layouts.admin')

@section('title', 'Padres | Voz & Cifra')
@section('mobile_title', 'Padres')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Padres</h1>
            <p class="text-sm text-gray-500">Cadastre, vincule e acompanhe os padres usados nas celebracoes do sistema.</p>
        </div>

        <a href="{{ route('admin.padres.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800 sm:w-auto">
            Novo padre
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($padres->isEmpty())
            <div class="p-8 text-center text-gray-500">
                Nenhum padre cadastrado ate o momento.
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 hidden md:table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Padre</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Igreja</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">CPF</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($padres as $padre)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $padre->nome }}</div>
                                    <div class="text-xs text-gray-400">ID interno: {{ $padre->id }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $padre->igreja?->nome ?: 'Sem vinculo especifico' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $padre->cpf }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $padre->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $padre->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.padres.edit', $padre) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Editar
                                        </a>
                                        <form action="{{ route('admin.padres.toggle', $padre) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste padre?');">
                                            @csrf
                                            <button type="submit" class="inline-flex rounded-lg px-4 py-2 text-sm font-medium {{ $padre->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                                {{ $padre->ativo ? 'Inativar' : 'Ativar' }}
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
                @foreach ($padres as $padre)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-800">{{ $padre->nome }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ $padre->igreja?->nome ?: 'Sem vinculo especifico' }}</p>
                                <p class="mt-1 text-xs text-gray-400">CPF: {{ $padre->cpf }}</p>
                            </div>
                            <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $padre->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $padre->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <a href="{{ route('admin.padres.edit', $padre) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar
                            </a>
                            <form action="{{ route('admin.padres.toggle', $padre) }}" method="POST" onsubmit="return confirm('Deseja alterar o status deste padre?');">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $padre->ativo ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                                    {{ $padre->ativo ? 'Inativar' : 'Ativar' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
