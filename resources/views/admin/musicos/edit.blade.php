@extends('admin.layouts.admin')

@section('title', 'Editar músico | Voz & Cifra')
@section('mobile_title', 'Editar músico')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Editar músico</h1>
        <p class="mt-1 text-sm text-gray-500">Atualize os dados do músico sem alterar o restante do sistema.</p>
    </div>

    <form action="{{ route('admin.musicos.update', $musico) }}" method="POST">
        @csrf
        @method('PUT')
        @include('musicos._form', ['mostrarCampoIgreja' => true, 'rotaVoltar' => route('admin.musicos.index')])
    </form>
@endsection
