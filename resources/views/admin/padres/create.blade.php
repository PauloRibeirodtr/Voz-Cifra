@extends('admin.layouts.admin')

@section('title', 'Novo padre | Voz & Cifra')
@section('mobile_title', 'Novo padre')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Novo padre</h1>
            <p class="text-sm text-gray-500">Cadastre um padre para uso administrativo nas missas do sistema.</p>
        </div>

        <a href="{{ route('admin.padres.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Ver padres
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

    <form action="{{ route('admin.padres.store') }}" method="POST" class="space-y-6">
        @csrf

        @include('admin.padres._form')

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.padres.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Salvar padre
            </button>
        </div>
    </form>
@endsection
