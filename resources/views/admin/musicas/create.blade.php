@extends('admin.layouts.admin')

@section('title', 'Cadastrar musica | Voz & Cifra')
@section('mobile_title', 'Cadastrar musica')

@section('content')
    @php($routePrefix = str_starts_with(Route::currentRouteName() ?? '', 'coordenador.') ? 'coordenador' : 'admin')
    <form action="{{ route($routePrefix . '.musicas.store') }}" method="POST" class="space-y-6" data-draft-form="musica-create">
        @csrf
        @include('partials.submission-token')
        @include('admin.musicas._form', ['modo' => 'create', 'musica' => null])

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route($routePrefix . '.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Salvar musica
            </button>
        </div>
    </form>
@endsection
