@extends('member.layouts.app')

@section('title', $colecao->nome . ' | Voz & Cifra')
@section('mobile_title', 'Playlist')
@section('desktop_subtitle', 'Playlist de estudo do musico')

@section('header_actions')
    <a href="{{ route('member.colecoes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Voltar para playlists
    </a>
@endsection

@section('content')
    <section class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h1 class="text-3xl font-black text-gray-900">{{ $colecao->nome }}</h1>
                <p class="mt-2 text-sm text-gray-500">{{ $colecao->itens->count() }} itens organizados nesta playlist.</p>
            </div>
            <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Adicionar mais musicas
            </a>
        </div>
    </section>

    <div class="mt-6 space-y-4">
        @forelse ($colecao->itens as $item)
            <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $item->musica?->titulo ?: 'Musica removida' }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $item->versaoMusical?->titulo ?: 'Versao principal' }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @if ($item->musica && $item->versaoMusical)
                            <a href="{{ route('member.versoes.show', [$item->musica, $item->versaoMusical]) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">
                                Abrir estudo
                            </a>
                        @endif
                        <form action="{{ route('member.colecoes.itens.destroy', [$colecao, $item->id]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-100">
                                Remover
                            </button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 shadow-sm">
                Esta playlist ainda nao possui itens.
            </div>
        @endforelse
    </div>
@endsection
