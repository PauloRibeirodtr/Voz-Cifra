@extends('admin.layouts.admin')

@section('title', 'Musicas | Voz & Cifra')
@section('mobile_title', 'Musicas')

@php
    $sugestoesMusicasBase64 = base64_encode(json_encode($sugestoesMusicas ?? []));
@endphp

@push('styles')
    <style>
        .musicas-toolbar {
            border: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #ffffff, #f9fafb);
            border-radius: 1.25rem;
        }

        .musica-row-title {
            color: #111827;
        }

        .musica-row:hover .musica-row-title {
            color: #166534;
        }

        .musica-mobile-card {
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .musica-mobile-card:hover {
            border-color: #bbf7d0;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }
    </style>
@endpush

@section('content')
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Musicas</h1>
            <p class="text-sm text-gray-500">Gerencie o catalogo musical central do sistema.</p>
        </div>

        <div class="flex flex-col gap-3 md:items-end">
            <form action="{{ route('admin.musicas.index') }}" method="GET" class="flex flex-col gap-2 sm:flex-row">
                <div class="relative">
                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar por titulo, artista ou letra"
                        autocomplete="off"
                        class="min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100 sm:min-w-[260px]"
                        data-music-search-input
                    >

                    <div
                        class="absolute z-20 mt-2 hidden w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl"
                        data-music-suggestions
                    ></div>
                </div>

                <button type="submit" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 hover:bg-gray-50">
                    Buscar
                </button>
            </form>

            <a href="{{ route('admin.musicas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-medium text-white hover:bg-green-800">
                Cadastrar música
            </a>
        </div>
    </div>

    <div class="musicas-toolbar mb-6 flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-bold text-gray-900">{{ $musicas->total() }} musica(s) encontrada(s)</p>
            <p class="mt-1 text-xs text-gray-500">Abra uma musica para conferir a letra, cadastrar versoes e revisar cifras.</p>
        </div>

        @if (request('search'))
            <a href="{{ route('admin.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Limpar busca
            </a>
        @endif
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
                            <tr class="musica-row hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="musica-row-title font-semibold">{{ $musica->titulo }}</div>
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

                                        <form action="{{ route('admin.musicas.destroy', $musica) }}" method="POST" onsubmit="return confirm('Deseja inativar esta musica? Ela sera preservada no banco.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="inline-flex px-4 py-2 bg-red-50 border border-red-200 rounded-lg text-sm font-medium text-red-700 hover:bg-red-100">
                                                Inativar
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
                    <article class="musica-mobile-card rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
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
                            <a href="{{ route('admin.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Ver
                            </a>

                            <a href="{{ route('admin.musicas.edit', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar
                            </a>

                            <form action="{{ route('admin.musicas.destroy', $musica) }}" method="POST" onsubmit="return confirm('Deseja inativar esta musica? Ela sera preservada no banco.');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100">
                                    Inativar
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoBusca = document.querySelector('[data-music-search-input]');
            const sugestoesContainer = document.querySelector('[data-music-suggestions]');

            if (!campoBusca || !sugestoesContainer) {
                return;
            }

            const sugestoes = JSON.parse(atob(@json($sugestoesMusicasBase64)));

            const normalizar = (valor) => (valor || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const esconderSugestoes = () => {
                sugestoesContainer.classList.add('hidden');
                sugestoesContainer.innerHTML = '';
            };

            campoBusca.addEventListener('input', () => {
                const termo = normalizar(campoBusca.value.trim());

                if (termo.length < 2) {
                    esconderSugestoes();
                    return;
                }

                const resultados = sugestoes
                    .filter((musica) =>
                        normalizar(`${musica.titulo} ${musica.artista || ''}`).includes(termo)
                    )
                    .slice(0, 6);

                if (resultados.length === 0) {
                    esconderSugestoes();
                    return;
                }

                sugestoesContainer.innerHTML = '';

                resultados.forEach((musica) => {
                    const botao = document.createElement('button');
                    const titulo = document.createElement('span');
                    const artista = document.createElement('span');

                    botao.type = 'button';
                    botao.className = 'block w-full px-4 py-3 text-left hover:bg-green-50';

                    titulo.className = 'block text-sm font-semibold text-gray-900';
                    titulo.textContent = musica.titulo;

                    artista.className = 'block text-xs text-gray-500';
                    artista.textContent = musica.artista || 'Artista nao informado';

                    botao.append(titulo, artista);

                    botao.addEventListener('click', () => {
                        campoBusca.value = musica.titulo;
                        esconderSugestoes();
                        campoBusca.form?.submit();
                    });

                    sugestoesContainer.appendChild(botao);
                });

                sugestoesContainer.classList.remove('hidden');
            });

            document.addEventListener('click', (evento) => {
                if (!sugestoesContainer.contains(evento.target) && evento.target !== campoBusca) {
                    esconderSugestoes();
                }
            });
        });
    </script>
@endpush