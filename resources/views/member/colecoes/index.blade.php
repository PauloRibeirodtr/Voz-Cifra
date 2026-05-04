@extends('member.layouts.app')

@section('title', 'Playlists salvas | Voz & Cifra')
@section('mobile_title', 'Playlists salvas')
@section('desktop_subtitle', 'Colecoes de estudo do musico')

@section('header_actions')
    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Biblioteca musical
    </a>
@endsection

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
                <h1 class="text-3xl font-black text-gray-900">Playlists salvas</h1>
                <p class="mt-2 text-sm text-gray-500">Organize suas versoes por ensaio, missa ou momento musical.</p>
            </div>
            <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Explorar musicas
            </a>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <form action="{{ route('member.colecoes.store') }}" method="POST" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-end">
            @csrf
            <label class="block text-sm font-medium text-gray-700">
                Nova playlist
                <input type="text" name="nome" class="mt-2 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="Ex.: Ensaio de sabado" required maxlength="120">
            </label>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800">
                Criar playlist
            </button>
        </form>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
        @forelse ($colecoes as $colecao)
            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $colecao->nome }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $colecao->itens_count }} itens salvos</p>
                    </div>
                    <a href="{{ route('member.colecoes.show', $colecao) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        Abrir playlist
                    </a>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($colecao->itens as $item)
                        <div class="rounded-2xl bg-gray-50 px-4 py-4">
                            <p class="text-sm font-bold text-gray-900">{{ $item->musica?->titulo ?: 'Musica removida' }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ $item->versaoMusical?->titulo ?: 'Versao principal' }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500">Esta playlist ainda nao possui itens.</div>
                    @endforelse
                </div>
            </article>
        @empty
            <div class="xl:col-span-2 rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">
                Nenhuma playlist criada ainda. Abra uma musica e use o bloco "Salvar em playlist".
            </div>
        @endforelse
    </div>
@endsection
