@extends('local-admin.layouts.admin')

@section('title', 'Novo músico | Voz & Cifra')
@section('mobile_title', 'Novo músico')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Novo músico</h1>
        <p class="mt-1 text-sm text-gray-500">Cadastre um músico vinculado à sua igreja.</p>
    </div>

    <form action="{{ route('local-admin.musicos.store') }}" method="POST">
        @csrf
        @include('musicos._form', ['mostrarCampoIgreja' => false, 'rotaVoltar' => route('local-admin.musicos.index')])
    </form>
@endsection
