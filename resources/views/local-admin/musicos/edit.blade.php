@extends('local-admin.layouts.admin')

@section('title', 'Editar músico | Voz & Cifra')
@section('mobile_title', 'Editar músico')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Editar músico</h1>
        <p class="mt-1 text-sm text-gray-500">Atualize os dados do músico sem sair do contexto da sua igreja.</p>
    </div>

    <form action="{{ route('local-admin.musicos.update', $musico) }}" method="POST">
        @csrf
        @method('PUT')
        @include('musicos._form', ['mostrarCampoIgreja' => false, 'rotaVoltar' => route('local-admin.musicos.index')])
    </form>
@endsection
