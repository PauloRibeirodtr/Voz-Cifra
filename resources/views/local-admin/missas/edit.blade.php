@extends('local-admin.layouts.admin')

@section('title', 'Editar missa | Voz & Cifra')
@section('mobile_title', 'Editar missa')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Editar missa</h1>
        <p class="mt-1 text-sm text-gray-500">Atualize os dados da celebra&ccedil;&atilde;o, a publica&ccedil;&atilde;o e o status operacional da missa.</p>
    </div>

    <form action="{{ route('local-admin.missas.update', $missa) }}" method="POST">
        @csrf
        @method('PUT')
        @include('local-admin.missas._form')
    </form>
@endsection
