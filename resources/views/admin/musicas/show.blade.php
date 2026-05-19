@extends('admin.layouts.admin')

@inject('renderizadorLetras', 'App\Services\RenderizadorLetrasHtmlService')

@section('title', 'Visualizar musica | Voz & Cifra')
@section('mobile_title', 'Musica')

@push('styles')
    <style>
        .lyrics {
            white-space: pre-wrap;
            color: #374151;
            font-size: 1rem;
            line-height: 1.65;
        }

        .lyrics p {
            margin: 0;
        }

        .lyrics-stanza {
            margin-bottom: 0.7rem;
            border-left: 3px solid #e5e7eb;
            background: #ffffff;
            padding: 0.45rem 0 0.45rem 1rem;
        }

        .lyrics-stanza--refrao {
            border-left-color: #f59e0b;
            background: linear-gradient(90deg, #fffbeb, #ffffff);
        }

        .lyrics-stanza--refrao p {
            color: #78350f;
            font-weight: 750;
        }

        .lyrics-space {
            height: 0.55rem;
        }

        .lyrics-section-label {
            display: inline-flex;
            align-items: center;
            margin: 0.85rem 0 0.5rem;
            padding: 0.32rem 0.72rem;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #f8fafc;
            color: #475569;
            font-size: 0.72rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .lyrics-section-label--refrao {
            border-color: #fde68a;
            background: #fffbeb;
            color: #92400e;
            font-weight: 950;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800">{{ $musica->titulo }}</h1>
            <p class="text-sm text-gray-500">{{ $musica->artista ?: 'Artista nao informado' }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap xl:justify-end">
            <a href="{{ route('admin.versoes-musicais.create', $musica) }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
                Cadastrar cifra
            </a>
            <a href="{{ route('admin.musicas.edit', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 text-[#6c4a21] font-medium hover:bg-[#f8ecd7]">
                <i class="fa-solid fa-pen mr-2"></i> Editar
            </a>
            <a href="{{ route('admin.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:col-span-2 xl:col-span-1">
                Voltar p/ Lista de Musicas
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-base font-bold text-green-900">Musica salva com sucesso</h2>
                    <p class="mt-1 text-sm text-green-800">{{ session('success') }}</p>
                </div>

                <a href="{{ route('admin.versoes-musicais.create', $musica) }}" class="inline-flex items-center justify-center rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                    Cadastrar cifra
                </a>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Letra</h2>
            <div class="lyrics max-w-none">{!! $renderizadorLetras->renderizarSemCifras($musica->letra) !!}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da musica</h2>

            <div class="space-y-4 text-sm text-gray-600">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tempo liturgico</span>
                    <span>{{ $musica->tempoLiturgico?->nome ?: '-' }}</span>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Momento liturgico</span>
                    <span>{{ $musica->momentoLiturgico?->nome ?: '-' }}</span>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Criado por</span>
                    <span>{{ $musica->criadoPor?->nome ?: '-' }}</span>
                </div>

                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Status</span>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $musica->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $musica->ativo ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>

                <div class="pt-4 border-t border-gray-100 text-xs text-gray-500">
                    Tom, bpm, observacoes e acordes serao tratados na etapa de versoes musicais.
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Versoes musicais</h2>
                <p class="text-sm text-gray-500">Aqui ficam tom, bpm, video e letra com cifras.</p>
            </div>

            <a href="{{ route('admin.versoes-musicais.create', $musica) }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
                Cadastrar cifra
            </a>
        </div>

        @if ($musica->versoesMusicais->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                Nenhuma versao musical cadastrada para esta musica.
            </div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Versao</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Tom</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">BPM</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($musica->versoesMusicais as $versaoMusical)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800">{{ $versaoMusical->titulo ?: 'Versao principal' }}</div>
                                    <div class="text-xs text-gray-500">Criada por {{ $versaoMusical->criadoPor?->nome ?: '-' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $versaoMusical->tom_musical ?: '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $versaoMusical->bpm ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $versaoMusical->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $versaoMusical->ativo ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.versoes-musicais.show', [$musica, $versaoMusical]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100" title="Ver versao" aria-label="Ver versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.versoes-musicais.edit', [$musica, $versaoMusical]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-[#ead6b3] bg-[#fff8ed] text-[#6c4a21] hover:bg-[#f8ecd7]" title="Editar versao" aria-label="Editar versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        @if ($versaoMusical->ativo)
                                            <form action="{{ route('admin.versoes-musicais.destroy', [$musica, $versaoMusical]) }}" method="POST" onsubmit="return confirm('Deseja inativar esta versao musical? Ela sera preservada no banco.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100" title="Inativar versao" aria-label="Inativar versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="space-y-4 md:hidden">
                @foreach ($musica->versoesMusicais as $versaoMusical)
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-gray-800">{{ $versaoMusical->titulo ?: 'Versao principal' }}</h3>
                                <p class="mt-1 text-xs text-gray-500">Criada por {{ $versaoMusical->criadoPor?->nome ?: '-' }}</p>
                            </div>
                            <span class="inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold {{ $versaoMusical->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $versaoMusical->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-gray-600">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Tom</span>
                                <div class="mt-1">{{ $versaoMusical->tom_musical ?: '-' }}</div>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">BPM</span>
                                <div class="mt-1">{{ $versaoMusical->bpm ?: '-' }}</div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('admin.versoes-musicais.show', [$musica, $versaoMusical]) }}" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100" title="Ver versao" aria-label="Ver versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.versoes-musicais.edit', [$musica, $versaoMusical]) }}" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] text-[#6c4a21] hover:bg-[#f8ecd7]" title="Editar versao" aria-label="Editar versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            @if ($versaoMusical->ativo)
                                <form action="{{ route('admin.versoes-musicais.destroy', [$musica, $versaoMusical]) }}" method="POST" onsubmit="return confirm('Deseja inativar esta versao musical? Ela sera preservada no banco.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100" title="Inativar versao" aria-label="Inativar versao {{ $versaoMusical->titulo ?: 'principal' }}">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
