@extends('local-admin.layouts.admin')

@section('title', 'Missas da igreja | Voz & Cifra')
@section('mobile_title', 'Missas')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Missas da igreja</h1>
            <p class="mt-1 text-sm text-gray-500">Organize as celebracoes e o repertorio da {{ $igreja->nome }}.</p>
        </div>

        <a href="{{ route('local-admin.missas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
            Nova missa
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($missas->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Nenhuma missa cadastrada</h2>
            <p class="mt-2 text-sm text-gray-500">Comece criando a primeira missa da igreja para depois organizar o repertorio.</p>
            <a href="{{ route('local-admin.missas.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
                Criar primeira missa
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($missas as $missa)
                <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900">{{ $missa->titulo }}</h2>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ optional($missa->data_missa)->format('d/m/Y') }} • {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-3 text-sm text-gray-600">
                                <span>Tempo: {{ $missa->tempoLiturgico?->nome ?: 'Nao definido' }}</span>
                                <span>Padre: {{ $missa->padre?->nome ?: 'Nao vinculado' }}</span>
                                <span>Repertorio: {{ $missa->missa_musicas_count }} item(ns)</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-[360px]">
                            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Abrir missa
                            </a>
                            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 text-sm font-semibold text-indigo-800 hover:bg-indigo-100">
                                Apresentacao
                            </a>
                            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Editar
                            </a>
                            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                                Baixar PDF
                            </a>
                            <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl {{ $missa->ativo ? 'bg-gray-800 hover:bg-black' : 'bg-green-700 hover:bg-green-800' }} px-4 py-3 text-sm font-semibold text-white">
                                    {{ $missa->ativo ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
