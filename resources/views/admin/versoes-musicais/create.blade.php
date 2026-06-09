@extends('admin.layouts.admin')

@section('title', 'Cadastrar versao musical | Voz & Cifra')
@section('mobile_title', 'Cadastrar versao')

@section('content')
    @php
        $routePrefix = str_starts_with(Route::currentRouteName() ?? '', 'coordenador.') ? 'coordenador' : 'admin';
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cadastrar versao musical</h1>
            <p class="text-sm text-gray-500">Cadastre a versao com cifras da musica <strong>{{ $musica->titulo }}</strong>.</p>
        </div>

        <a href="{{ route($routePrefix . '.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Voltar para a musica
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route($routePrefix . '.versoes-musicais.store', $musica) }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.versoes-musicais._form', ['versaoMusical' => null])

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route($routePrefix . '.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Cadastrar versao musical
            </button>
        </div>
    </form>
@endsection
