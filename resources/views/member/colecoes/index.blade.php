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
