@extends('local-admin.layouts.admin')

@section('title', 'Nova missa | Voz & Cifra')
@section('mobile_title', 'Nova missa')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nova missa</h1>
        <p class="mt-1 text-sm text-gray-500">Cadastre a celebracao da igreja e prepare a base do repertorio.</p>
    </div>

    <form action="{{ route('local-admin.missas.store') }}" method="POST">
        @csrf
        @include('local-admin.missas._form', ['modoCriacao' => true])
    </form>
@endsection
