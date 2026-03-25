@extends('admin.layouts.admin')

@section('title', 'Musicas | Voz & Cifra')
@section('mobile_title', 'Musicas')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Musicas</h1>
            <p class="text-sm text-gray-500">Gerencie o catalogo musical central do sistema.</p>
        </div>

        <div class="flex flex-col gap-3 md:items-end">
            <form action="{{ route('admin.musicas.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por titulo, artista ou letra"
                    class="min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100 sm:min-w-[260px]"
                >
                <button type="submit" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 hover:bg-gray-50">
                    Buscar
                </button>
            </form>

            <a href="{{ route('admin.musicas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800">
                Nova musica
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($musicas->isEmpty())
            <div class="p-8 text-center text-gray-500">
                Nenhuma musica cadastrada ate o momento.
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Musica</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Tempo liturgico</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Momento liturgico</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($musicas as $musica)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $musica->titulo }}</div>
                                    <div class="text-sm text-gray-500">{{ $musica->artista ?: 'Artista nao informado' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $musica->tempoLiturgico?->nome ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $musica->momentoLiturgico?->nome ?: '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $musica->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $musica->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.musicas.show', $musica) }}" class="inline-flex px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Ver
                                        </a>
                                        <a href="{{ route('admin.musicas.edit', $musica) }}" class="inline-flex px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                                            Editar
                                        </a>
                                        <form action="{{ route('admin.musicas.destroy', $musica) }}" method="POST" onsubmit="return confirm('Deseja excluir esta musica?');">
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
                @foreach ($musicas as $musica)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="break-words text-base font-bold text-gray-800">{{ $musica->titulo }}</h2>
                                <p class="mt-1 text-sm text-gray-500">{{ $musica->artista ?: 'Artista nao informado' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $musica->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $musica->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-gray-600">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Tempo liturgico</span>
                                <div class="mt-1">{{ $musica->tempoLiturgico?->nome ?: '-' }}</div>
                            </div>

                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Momento liturgico</span>
                                <div class="mt-1">{{ $musica->momentoLiturgico?->nome ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <a href="{{ route('admin.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Ver</a>
                            <a href="{{ route('admin.musicas.edit', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                            <form action="{{ route('admin.musicas.destroy', $musica) }}" method="POST" onsubmit="return confirm('Deseja excluir esta musica?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="px-6 py-4 border-t border-gray-100">
                {{ $musicas->links() }}
            </div>
        @endif
    </div>
@endsection
