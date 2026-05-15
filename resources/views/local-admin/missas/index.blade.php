@extends('local-admin.layouts.admin')

@section('title', 'Missas da igreja | Voz & Cifra')
@section('mobile_title', 'Missas')

@push('styles')
    <style>
        .missa-list-card {
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .missa-list-card:hover {
            border-color: rgba(140, 105, 51, 0.28);
            box-shadow: 0 18px 38px rgba(34, 20, 12, 0.08);
            transform: translateY(-1px);
        }

        .missa-meta-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 0.75rem;
            font-size: 0.78rem;
            font-weight: 800;
            color: #475569;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Missas da igreja</h1>
            <p class="mt-1 text-sm text-gray-500">Organize as celebra&ccedil;&otilde;es e o repert&oacute;rio da {{ $igreja->nome }}.</p>
        </div>

        <a href="{{ route('local-admin.missas.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[#6c4a21] px-4 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
            Cadastrar missa
        </a>
    </div>

    @include('local-admin.partials.church-switcher')

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($missas->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Nenhuma missa cadastrada</h2>
            <p class="mt-2 text-sm text-gray-500">Comece criando a primeira missa da igreja para depois organizar o repert&oacute;rio.</p>
            <a href="{{ route('local-admin.missas.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-[#6c4a21] px-4 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                Cadastrar primeira missa
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($missas as $missa)
                <article class="missa-list-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-lg font-bold text-gray-900">{{ $missa->titulo }}</h2>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-gray-500">
                                {{ optional($missa->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="missa-meta-chip">Tempo: {{ $missa->tempoLiturgico?->nome ?: 'A definir' }}</span>
                                <span class="missa-meta-chip">Celebrante: {{ $missa->celebrante?->nome ?: 'A definir' }}</span>
                                <span class="missa-meta-chip">Repertorio: {{ $missa->missa_musicas_count }} item(ns)</span>
                                <span class="missa-meta-chip {{ $missa->publica_para_fieis ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '' }}">Fieis: {{ $missa->publica_para_fieis ? 'publicada' : 'oculta' }}</span>
                                <span class="missa-meta-chip {{ $missa->publica_para_musicos ? 'bg-sky-50 text-sky-700 border-sky-100' : '' }}">Musicos: {{ $missa->publica_para_musicos ? 'publicada' : 'oculta' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-[380px]">
                            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                Abrir missa
                            </a>
                            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800 transition hover:bg-sky-100">
                                Visualiza&ccedil;&atilde;o
                            </a>
                            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7]">
                                Editar
                            </a>
                            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 transition hover:bg-amber-100">
                                Baixar PDF
                            </a>
                            <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST" class="sm:col-span-2">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border px-4 py-3 text-sm font-semibold transition {{ $missa->ativo ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                    {{ $missa->ativo ? 'Inativar missa' : 'Reativar missa' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
