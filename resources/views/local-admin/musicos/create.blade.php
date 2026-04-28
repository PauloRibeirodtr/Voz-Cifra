@extends('local-admin.layouts.admin')

@php($rotaPrefixo = request()->routeIs('coordenador.*') ? 'coordenador' : 'local-admin')
@php($contextoTitulo = $rotaPrefixo === 'coordenador' ? 'Cadastrar musico da igreja' : 'Cadastrar musico')

@section('title', $contextoTitulo . ' | Voz & Cifra')
@section('mobile_title', $contextoTitulo)

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ $contextoTitulo }}</h1>
        <p class="mt-1 text-sm text-gray-500">
            Cadastre um musico vinculado a {{ $igreja->nome }}. Se a pessoa ja existir no sistema, os dados serao reaproveitados e o papel sera acumulado sem duplicar conta.
        </p>
    </div>

    <form action="{{ route($rotaPrefixo . '.musicos.store') }}" method="POST">
        @csrf
        @include('musicos._form', ['mostrarCampoIgreja' => false, 'rotaVoltar' => route($rotaPrefixo . '.musicos.index')])
    </form>
@endsection
