@extends('local-admin.layouts.admin')

@section('title', 'Cadastrar missa | Voz & Cifra')
@section('mobile_title', 'Cadastrar missa')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Cadastrar missa</h1>
        <p class="mt-1 text-sm text-gray-500">Cadastre a celebra&ccedil;&atilde;o da igreja e, em seguida, abra o repert&oacute;rio para adicionar as m&uacute;sicas.</p>
    </div>

    @include('local-admin.partials.church-switcher')

    <form action="{{ route('local-admin.missas.store') }}" method="POST">
        @csrf
        @include('local-admin.missas._form', ['modoCriacao' => true])
    </form>
@endsection
