@extends('admin.layouts.admin')

@section('title', 'Editar tempo liturgico | Voz & Cifra')
@section('mobile_title', 'Editar tempo')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar tempo liturgico</h1>
            <p class="text-sm text-gray-500">Atualize os dados do tempo liturgico selecionado.</p>
        </div>

        <a href="{{ route('admin.tempos-liturgicos.index') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
            Voltar
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

    <form action="{{ route('admin.tempos-liturgicos.update', $tempoLiturgico) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $tempoLiturgico->nome) }}" required placeholder="Ex.: Advento" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Descricao</label>
                    <textarea name="descricao" rows="5" placeholder="Descreva brevemente este tempo liturgico." class="{{ $classeInput }}">{{ old('descricao', $tempoLiturgico->descricao) }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $tempoLiturgico->ativo) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Tempo liturgico ativo</label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.tempos-liturgicos.index') }}" class="px-5 py-3 bg-white border border-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="px-5 py-3 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-800">
                Atualizar tempo liturgico
            </button>
        </div>
    </form>
@endsection
