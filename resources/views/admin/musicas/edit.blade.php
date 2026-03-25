@extends('admin.layouts.admin')

@section('title', 'Editar musica | Voz & Cifra')
@section('mobile_title', 'Editar musica')

@section('content')
    <form action="{{ route('admin.musicas.update', $musica) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.musicas._form', ['modo' => 'edit', 'musica' => $musica])

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Atualizar musica
            </button>
        </div>
    </form>
@endsection
