@extends('member.layouts.app')

@section('title', 'Biblioteca musical | Voz & Cifra')
@section('mobile_title', 'Biblioteca musical')
@section('desktop_subtitle', 'Estudo livre de musicas e versoes ativas')

@section('header_actions')
    <a href="{{ route('member.colecoes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Playlists salvas
    </a>
    <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Meu repertorio
    </a>
@endsection

@push('styles')
    <style>
        .library-card {
            position: relative;
            overflow: hidden;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .library-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 4px;
            background: #059669;
            opacity: 0;
            transition: opacity 0.18s ease;
        }

        .library-card:hover {
            border-color: #bbf7d0;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.08);
            transform: translateY(-1px);
        }

        .library-card:hover::before {
            opacity: 1;
        }

        .library-excerpt {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .version-strip {
            border: 1px solid #e5e7eb;
            background: #ffffff;
        }

        .version-strip:hover {
            border-color: #bbf7d0;
            background: #f8fffb;
        }
    </style>
@endpush

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">Biblioteca musical</h1>
                <p class="mt-2 text-sm text-gray-500">Estude musicas e versoes ativas fora do contexto de uma missa especifica.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Painel</a>
                <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Meu repertorio</a>
            </div>
        </div>

        <form action="{{ route('member.musicas.index') }}" method="GET" class="mt-6 rounded-3xl border border-gray-100 bg-gray-50 p-4">
            <label class="block text-sm font-medium text-gray-700">Buscar musica</label>
            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                <input type="text" name="busca" value="{{ $busca }}" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="Titulo, artista, trecho, tempo ou momento liturgico">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800">Buscar</button>
            </div>
            <details class="mt-4 rounded-2xl border border-gray-200 bg-white px-4 py-3">
                <summary class="cursor-pointer text-sm font-bold text-gray-700">Filtros opcionais</summary>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <label class="block text-sm font-medium text-gray-700">
                        Tempo liturgico
                        <select name="tempo_liturgico_id" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-gray-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Todos</option>
                            @foreach ($temposLiturgicos as $tempo)
                                <option value="{{ $tempo->id }}" @selected((string) $tempo->id === $tempoSelecionado)>{{ $tempo->nome }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Momento liturgico
                        <select name="momento_liturgico_id" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-gray-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Todos</option>
                            @foreach ($momentosLiturgicos as $momento)
                                <option value="{{ $momento->id }}" @selected((string) $momento->id === $momentoSelecionado)>{{ $momento->nome }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block text-sm font-medium text-gray-700">
                        Tom
                        <input type="text" name="tom" value="{{ $tomSelecionado }}" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-gray-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="Ex.: C, D, Em">
                    </label>
                </div>
            </details>

            @if ($busca || $tempoSelecionado || $momentoSelecionado || $tomSelecionado)
                <div class="mt-4 flex flex-col gap-2 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm font-semibold text-emerald-900">Filtros aplicados nesta busca.</p>
                    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-bold text-emerald-800 hover:bg-emerald-100">
                        Limpar filtros
                    </a>
                </div>
            @endif
        </form>
    </section>

    @if ($colecoes->isNotEmpty())
        <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Playlists do musico</h2>
                    <p class="mt-1 text-sm text-gray-500">Acesse rapido suas colecoes para ensaio e estudo.</p>
                </div>
                <a href="{{ route('member.colecoes.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Ver todas
                </a>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
                @foreach ($colecoes as $colecao)
                    <a href="{{ route('member.colecoes.show', $colecao) }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-4 transition hover:border-emerald-200 hover:bg-emerald-50">
                        <p class="text-base font-bold text-gray-900">{{ $colecao->nome }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ $colecao->itens_count }} itens</p>
                        @if ($colecao->itens->isNotEmpty())
                            <p class="mt-3 text-sm text-gray-600">
                                {{ $colecao->itens->pluck('musica.titulo')->filter()->take(2)->join(' • ') }}
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
        @forelse ($musicas as $musica)
            @php($musicaJaAdicionada = $musicasJaAdicionadas->contains($musica->id))
            <article class="library-card rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-black text-gray-900">{{ $musica->titulo }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $musica->artista ?: 'Artista nao informado' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-semibold">
                        @if ($musica->tempoLiturgico)
                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">{{ $musica->tempoLiturgico->nome }}</span>
                        @endif
                        @if ($musica->momentoLiturgico)
                            <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-indigo-700">{{ $musica->momentoLiturgico->nome }}</span>
                        @endif
                    </div>
                </div>

                <p class="library-excerpt mt-4 text-sm leading-6 text-gray-600">{{ $musica->trecho_letra ?: 'Letra nao informada.' }}</p>

                <div class="mt-5 space-y-3">
                    @foreach ($musica->versoesMusicais as $versao)
                        <div class="version-strip rounded-2xl px-4 py-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-bold text-gray-900">{{ $versao->titulo ?: 'Versao principal' }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs font-semibold">
                                        @if ($versao->tom_musical)
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-amber-700">Tom {{ $versao->tom_musical }}</span>
                                        @endif
                                        @if ($versao->bpm)
                                            <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">BPM {{ $versao->bpm }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2 sm:items-end">
                                    <a href="{{ route('member.versoes.show', [$musica, $versao]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">Estudar cifra</a>
                                    @if ($musicaJaAdicionada)
                                        <span class="inline-flex items-center justify-center rounded-xl bg-emerald-100 px-4 py-2 text-xs font-bold text-emerald-700">Ja adicionada</span>
                                    @elseif ($colecoes->isNotEmpty())
                                        <details class="relative">
                                            <summary class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Adicionar a playlist</summary>
                                            <div class="mt-2 w-full rounded-2xl border border-gray-200 bg-white p-2 shadow-sm sm:absolute sm:right-0 sm:z-10 sm:w-64">
                                                @foreach ($colecoes as $colecao)
                                                    <form action="{{ route('member.colecoes.itens.store', $colecao) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                                                        <input type="hidden" name="versao_musical_id" value="{{ $versao->id }}">
                                                        <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-sm font-semibold text-gray-700 hover:bg-emerald-50 hover:text-emerald-800">
                                                            {{ $colecao->nome }}
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="xl:col-span-2 rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">Nenhuma musica encontrada com esse filtro.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $musicas->links() }}
    </div>
@endsection
