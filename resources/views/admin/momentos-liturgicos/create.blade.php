@extends('admin.layouts.admin')

@section('title', 'Cadastrar momento liturgico | Voz & Cifra')
@section('mobile_title', 'Cadastrar momento')

@section('content')
    @php
        $routePrefix = $routePrefix ?? 'admin';
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cadastrar momento liturgico</h1>
            <p class="text-sm text-gray-500">Adicione um novo momento liturgico central para uso no sistema.</p>
        </div>

        <a href="{{ route($routePrefix . '.momentos-liturgicos.index') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
            Ver momentos
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

    <form action="{{ route($routePrefix . '.momentos-liturgicos.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex.: Entrada" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descricao</label>
                    <textarea name="descricao" rows="5" placeholder="Descreva brevemente este momento liturgico." class="{{ $classeInput }}">{{ old('descricao') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Ordem de exibicao</label>
                    <input type="number" name="ordem_exibicao" value="{{ old('ordem_exibicao') }}" min="1" placeholder="Ex.: 1" class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Momento liturgico ativo</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route($routePrefix . '.momentos-liturgicos.index') }}" class="px-5 py-3 bg-white border border-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-3 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-800">
                Salvar momento liturgico
            </button>
        </div>
    </form>
@endsection
