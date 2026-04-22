@extends('local-admin.layouts.admin')

@php($rotaPrefixo = request()->routeIs('coordenador.*') ? 'coordenador' : 'local-admin')
@php($contextoTitulo = $rotaPrefixo === 'coordenador' ? 'Editar musico da igreja' : 'Editar musico')

@section('title', $contextoTitulo . ' | Voz & Cifra')
@section('mobile_title', $contextoTitulo)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $contextoTitulo }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            Atualize os dados operacionais sem perder o vinculo atual com {{ $igreja->nome }} nem criar cadastro duplicado.
        </p>
    </div>

    <form action="{{ route($rotaPrefixo . '.musicos.update', $musico) }}" method="POST">
        @csrf
        @method('PUT')
        @include('musicos._form', ['mostrarCampoIgreja' => false, 'rotaVoltar' => route($rotaPrefixo . '.musicos.index')])
    </form>
@endsection
