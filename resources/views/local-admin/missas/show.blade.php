@extends('local-admin.layouts.admin')

@section('title', 'Missa | Voz & Cifra')
@section('mobile_title', 'Missa')

@php
    $tonsMusicais = config('musical.tons', []);
    $totalItensRepertorio = $missa->missaMusicas->count();
    $itensSemVersao = $missa->missaMusicas->filter(fn ($item) => $item->versaoMusical === null)->count();
    $itensSemMomento = $missa->missaMusicas->filter(fn ($item) => $item->momentoLiturgico === null)->count();
    $repertorioCompleto = $totalItensRepertorio > 0 && $itensSemVersao === 0 && $itensSemMomento === 0;
    $momentosRepertorio = $missa->missaMusicas
        ->map(fn ($item) => $item->momentoLiturgico?->nome)
        ->filter()
        ->map(fn ($nome) => mb_strtolower(\Illuminate\Support\Str::ascii($nome)))
        ->values();
    $momentosEssenciais = [
        'entrada' => 'Entrada',
        'salmo' => 'Salmo',
        'aclamacao' => 'Aclamacao',
        'ofertorio' => 'Ofertorio',
        'comunhao' => 'Comunhao',
        'final' => 'Final',
    ];
    $momentosEssenciaisDefinidos = collect(array_keys($momentosEssenciais))
        ->filter(fn ($momentoBase) => $momentosRepertorio->contains(fn ($nome) => str_contains($nome, $momentoBase)))
        ->count();
    $musicasDuplicadas = $missa->missaMusicas
        ->groupBy('musica_id')
        ->filter(fn ($grupo) => $grupo->count() > 1)
        ->count();
@endphp

@push('styles')
    <style>
        .missa-step {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            padding: 1rem;
        }

        .missa-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background: #6c4a21;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 900;
        }

        .repertorio-item-card {
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .repertorio-item-card:hover {
            border-color: #bbf7d0;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.07);
        }

        .repertorio-status-card {
            border-radius: 1.25rem;
            border: 1px solid #e5e7eb;
            background: linear-gradient(180deg, #ffffff, #f9fafb);
            padding: 1rem;
        }

        .repertorio-status-card strong {
            display: block;
            margin-top: 0.25rem;
            color: #111827;
            font-size: 1.45rem;
            line-height: 1;
        }

        .repertorio-status-ok {
            border-color: #bbf7d0;
            background: linear-gradient(180deg, #f0fdf4, #ffffff);
        }

        .repertorio-status-warn {
            border-color: #fde68a;
            background: linear-gradient(180deg, #fffbeb, #ffffff);
        }

        .musica-search-result {
            width: 100%;
            border: 0;
            border-radius: 0.95rem;
            background: transparent;
            padding: 0.85rem;
            text-align: left;
            transition: background-color 0.16s ease;
        }

        .musica-search-result:hover,
        .musica-search-result:focus {
            background: #fff8ed;
            outline: none;
        }

        .musica-search-result__top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .musica-search-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 1.6rem;
            border-radius: 999px;
            padding: 0 0.55rem;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .musica-search-badge--ok {
            background: #dcfce7;
            color: #166534;
        }

        .musica-search-badge--warn {
            background: #fef3c7;
            color: #92400e;
        }

        .repertorio-alert {
            border-radius: 1rem;
            border: 1px solid #fde68a;
            background: #fffbeb;
            color: #78350f;
            padding: 0.9rem 1rem;
        }

        .repertorio-action {
            display: inline-flex;
            width: 2.75rem;
            height: 2.75rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.85rem;
            transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between" id="missa-resumo">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">{{ $missa->titulo }}</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ optional($missa->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
            </p>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">
                Crie a missa, salve os dados principais e depois monte o repert&oacute;rio logo abaixo. Cada m&uacute;sica adicionada j&aacute; fica salva automaticamente.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:min-w-[23rem]">
            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7]">
                Editar missa
            </a>
            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 font-semibold text-sky-800 transition hover:bg-sky-100">
                Visualiza&ccedil;&atilde;o da missa
            </a>
            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-semibold text-amber-800 transition hover:bg-amber-100">
                Baixar PDF completo
            </a>
            <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST" onsubmit="return confirm('{{ $missa->ativo ? 'Deseja inativar esta missa? O repertório será preservado para consulta futura.' : 'Deseja reativar esta missa como parte do fluxo da igreja?' }}');">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border px-4 py-3 font-semibold transition {{ $missa->ativo ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $missa->ativo ? 'Inativar missa' : 'Reativar missa' }}
                </button>
            </form>
            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 sm:col-span-2">
                Voltar para as missas
            </a>
        </div>
    </div>

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

    <section class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-3">
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">1</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Dados da missa</p>
                    <p class="mt-1 text-xs text-gray-500">Data, horario, celebrante e publicacao.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">2</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Montagem do repertorio</p>
                    <p class="mt-1 text-xs text-gray-500">Adicione musicas, versoes, ordem e tom usado.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">3</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Visualizacao final</p>
                    <p class="mt-1 text-xs text-gray-500">Confira a missa antes de apresentar ou gerar PDF.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="repertorio-status-card {{ $totalItensRepertorio > 0 ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Repertorio</span>
            <strong>{{ $totalItensRepertorio }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $totalItensRepertorio === 1 ? 'musica adicionada' : 'musicas adicionadas' }}</p>
        </div>
        <div class="repertorio-status-card {{ $itensSemVersao === 0 ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Cifras</span>
            <strong>{{ $itensSemVersao === 0 ? 'OK' : $itensSemVersao }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $itensSemVersao === 0 ? 'todas com versao' : 'sem versao vinculada' }}</p>
        </div>
        <div class="repertorio-status-card {{ $repertorioCompleto ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Status</span>
            <strong>{{ $repertorioCompleto ? 'Completo' : 'Pendente' }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $repertorioCompleto ? 'pronto para revisar' : 'revise momentos e versoes' }}</p>
        </div>
    </section>

    <section class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Checklist da montagem</h2>
                <p class="mt-1 text-sm text-gray-500">Use como conferencia rapida antes de publicar ou apresentar a missa.</p>
            </div>
            <form action="{{ route('local-admin.missas.repertorio.corrigir-ordem', $missa) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7] sm:w-auto">
                    <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                    Corrigir ordem
                </button>
            </form>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border {{ $totalItensRepertorio > 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $totalItensRepertorio > 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Repertorio
                </strong>
                <p class="mt-2">{{ $totalItensRepertorio > 0 ? 'Ha musicas adicionadas.' : 'Adicione pelo menos uma musica.' }}</p>
            </div>
            <div class="rounded-xl border {{ $itensSemMomento === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $itensSemMomento === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Momentos
                </strong>
                <p class="mt-2">{{ $itensSemMomento === 0 ? 'Todos os itens tem momento.' : $itensSemMomento . ' item(ns) sem momento.' }}</p>
            </div>
            <div class="rounded-xl border {{ $itensSemVersao === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $itensSemVersao === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Cifras
                </strong>
                <p class="mt-2">{{ $itensSemVersao === 0 ? 'Tudo vinculado para os musicos.' : $itensSemVersao . ' item(ns) sem cifra.' }}</p>
            </div>
            <div class="rounded-xl border {{ $musicasDuplicadas === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-red-100 bg-red-50 text-red-800' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $musicasDuplicadas === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Duplicidade
                </strong>
                <p class="mt-2">{{ $musicasDuplicadas === 0 ? 'Sem musicas repetidas.' : $musicasDuplicadas . ' musica(s) repetida(s).' }}</p>
            </div>
        </div>

        <div class="mt-4 rounded-xl border border-gray-100 bg-gray-50 p-4">
            <div class="mb-2 text-xs font-black uppercase tracking-wider text-gray-500">Momentos principais encontrados</div>
            <div class="flex flex-wrap gap-2">
                @foreach ($momentosEssenciais as $chaveMomento => $nomeMomento)
                    @php
                        $momentoDefinido = $momentosRepertorio->contains(fn ($nome) => str_contains($nome, $chaveMomento));
                    @endphp
                    <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $momentoDefinido ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-gray-500 ring-1 ring-gray-200' }}">
                        <i class="fa-solid {{ $momentoDefinido ? 'fa-check' : 'fa-minus' }}" aria-hidden="true"></i>
                        {{ $nomeMomento }}
                    </span>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-500">{{ $momentosEssenciaisDefinidos }} de {{ count($momentosEssenciais) }} momentos principais ja aparecem no repertorio.</p>
        </div>
    </section>

    <section class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-900">Montar por momentos</h2>
            <p class="mt-1 text-sm text-gray-500">Clique em um momento para escolher a musica certa sem precisar procurar na tabela inteira.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($momentosLiturgicos as $momentoLiturgico)
                @php
                    $itensDoMomento = $missa->missaMusicas->filter(fn ($item) => (int) $item->momento_liturgico_id === (int) $momentoLiturgico->id);
                    $primeiroItemDoMomento = $itensDoMomento->first();
                @endphp
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-sm font-black text-gray-900">{{ $momentoLiturgico->nome }}</h3>
                            <p class="mt-1 text-xs text-gray-500">
                                @if ($primeiroItemDoMomento)
                                    {{ $primeiroItemDoMomento->musica?->titulo }}
                                    @if ($itensDoMomento->count() > 1)
                                        + {{ $itensDoMomento->count() - 1 }} item(ns)
                                    @endif
                                @else
                                    Nenhuma musica definida.
                                @endif
                            </p>
                        </div>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $itensDoMomento->isNotEmpty() ? 'bg-emerald-100 text-emerald-700' : 'bg-white text-gray-500 ring-1 ring-gray-200' }}">
                            {{ $itensDoMomento->isNotEmpty() ? 'OK' : 'Pendente' }}
                        </span>
                    </div>

                    <button
                        type="button"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#ead6b3] bg-white px-4 py-2 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#fff8ed]"
                        data-escolher-momento="{{ $momentoLiturgico->id }}"
                        data-momento-nome="{{ $momentoLiturgico->nome }}"
                    >
                        <i class="fa-solid fa-music" aria-hidden="true"></i>
                        Escolher musica
                    </button>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="missa-repertorio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Adicionar m&uacute;sica ao repert&oacute;rio</h2>
                        <p class="mt-1 text-sm text-gray-500">Busque por nome da m&uacute;sica, artista ou trecho da letra para localizar o canto com mais rapidez.</p>
                    </div>
                    <form action="{{ route('local-admin.missas.concluir-montagem', $missa) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 sm:w-auto">
                            Concluir montagem
                        </button>
                    </form>
                </div>

                @if ($totalItensRepertorio > 0 && !$repertorioCompleto)
                    <div class="repertorio-alert mb-4 text-sm">
                        <strong class="block">Antes de concluir, revise as pendencias.</strong>
                        @if ($itensSemVersao > 0)
                            <span>{{ $itensSemVersao }} item(ns) ainda nao tem versao/cifra vinculada.</span>
                        @endif
                        @if ($itensSemMomento > 0)
                            <span>{{ $itensSemMomento }} item(ns) ainda nao tem momento liturgico definido.</span>
                        @endif
                    </div>
                @endif

                <form action="{{ route('local-admin.repertorio.store', $missa) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="busca_musica" class="block text-sm font-medium text-gray-700">M&uacute;sica</label>
                        <input type="hidden" name="musica_id" id="musica_id" value="{{ old('musica_id') }}" required>
                        <div class="mt-1 rounded-2xl border border-gray-300 bg-white shadow-sm">
                            <input
                                type="text"
                                id="busca_musica"
                                class="block w-full rounded-2xl border-0 bg-transparent px-4 py-3 text-gray-800 focus:ring-2 focus:ring-[#ead6b3]"
                                placeholder="Digite o nome da m&uacute;sica, artista ou trecho da letra"
                                autocomplete="off"
                            >
                            <div id="resultado_busca_musica" class="hidden border-t border-gray-100 p-2"></div>
                        </div>
                        <p id="musica_selecionada_texto" class="mt-2 text-sm text-gray-500">Nenhuma m&uacute;sica selecionada.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Momento lit&uacute;rgico</label>
                        <select name="momento_liturgico_id" id="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Definir depois</option>
                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                <option value="{{ $momentoLiturgico->id }}" @selected(old('momento_liturgico_id') == $momentoLiturgico->id)>{{ $momentoLiturgico->nome }}</option>
                            @endforeach
                        </select>
                        <p id="momento_liturgico_hint" class="mt-1 text-xs text-gray-500">Ao escolher a musica, o sistema tenta sugerir o momento cadastrado nela. Voce pode trocar se precisar.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vers&atilde;o musical</label>
                        <select name="versao_musical_id" id="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]" disabled>
                            <option value="">Vincular depois</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tom usado na missa</label>
                        <select name="tom_usado" id="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Usar o tom original da vers&atilde;o</option>
                            @foreach ($tonsMusicais as $tomMusical)
                                <option value="{{ $tomMusical }}" @selected(old('tom_usado') === $tomMusical)>{{ $tomMusical }}</option>
                            @endforeach
                        </select>
                        <p id="tom_usado_hint" class="mt-1 text-xs text-gray-500">Selecione um tom apenas se a igreja for tocar em um tom diferente da vers&atilde;o original.</p>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#6c4a21] px-5 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                            Salvar e adicionar ao repert&oacute;rio
                        </button>
                        <a href="#missa-resumo" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                            Revisar dados da missa
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Repert&oacute;rio da missa</h2>
                    <p class="mt-1 text-sm text-gray-500">Organize a ordem dos cantos, defina o momento lit&uacute;rgico e ajuste a vers&atilde;o usada em cada item.</p>
                </div>

                @if ($missa->missaMusicas->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                        Ainda n&atilde;o existe m&uacute;sica cadastrada no repert&oacute;rio desta missa.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($missa->missaMusicas as $item)
                            <article class="repertorio-item-card rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ordem {{ $item->ordem }}</span>
                                            @if ($item->momentoLiturgico)
                                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">{{ $item->momentoLiturgico->nome }}</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Momento pendente</span>
                                            @endif
                                            @if (!$item->versaoMusical)
                                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700">Sem cifra vinculada</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $item->musica->titulo }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista nao informado' }}</p>
                                        <p class="mt-2 text-sm text-gray-600">
                                            Vers&atilde;o: {{ $item->versaoMusical?->titulo ?: 'Ainda nao vinculada' }}
                                            @if ($item->tom_exibicao)
                                                &bull; Tom da missa {{ $item->tom_exibicao }}
                                            @endif
                                            @if ($item->tom_usado && $item->versaoMusical?->tom_musical)
                                                &bull; Tom original {{ $item->versaoMusical->tom_musical }}
                                            @endif
                                            @if ($item->versaoMusical?->bpm)
                                                &bull; BPM {{ $item->versaoMusical->bpm }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                        <form action="{{ route('local-admin.repertorio.up', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="repertorio-action border border-gray-200 bg-white text-gray-700 hover:bg-gray-100" title="Subir musica" aria-label="Subir musica">
                                                <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('local-admin.repertorio.down', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="repertorio-action border border-gray-200 bg-white text-gray-700 hover:bg-gray-100" title="Descer musica" aria-label="Descer musica">
                                                <i class="fa-solid fa-arrow-down" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                        @if ($item->versaoMusical)
                                            <a href="{{ route('local-admin.repertorio.cifra', [$missa, $item]) }}" class="repertorio-action border border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100" title="Visualizar cifra" aria-label="Visualizar cifra">
                                                <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('local-admin.repertorio.destroy', [$missa, $item]) }}" method="POST" onsubmit="return confirm('Deseja remover esta m&uacute;sica do repert&oacute;rio da missa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="repertorio-action border border-red-200 bg-red-50 text-red-700 hover:bg-red-100" title="Remover item" aria-label="Remover item">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <form action="{{ route('local-admin.repertorio.update', [$missa, $item]) }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Momento lit&uacute;rgico</label>
                                        <select name="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Definir depois</option>
                                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                                <option value="{{ $momentoLiturgico->id }}" @selected((string) $item->momento_liturgico_id === (string) $momentoLiturgico->id)>
                                                    {{ $momentoLiturgico->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Vers&atilde;o musical</label>
                                        <select name="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">N&atilde;o vincular agora</option>
                                            @foreach ($item->musica->versoesMusicais as $versaoMusical)
                                                <option value="{{ $versaoMusical->id }}" @selected((string) $item->versao_musical_id === (string) $versaoMusical->id)>
                                                    {{ $versaoMusical->titulo ?: 'Vers&atilde;o principal' }}
                                                    @if ($versaoMusical->tom_musical)
                                                        &bull; Tom {{ $versaoMusical->tom_musical }}
                                                    @endif
                                                    @if ($versaoMusical->bpm)
                                                        &bull; BPM {{ $versaoMusical->bpm }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tom usado na missa</label>
                                        <select name="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Usar o tom original da vers&atilde;o</option>
                                            @foreach ($tonsMusicais as $tomMusical)
                                                <option value="{{ $tomMusical }}" @selected((string) old('tom_usado', $item->tom_usado) === (string) $tomMusical)>{{ $tomMusical }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#2a1b1b] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1f1414]">
                                            Salvar item
                                        </button>
                                    </div>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="space-y-6">
            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Dados da missa</h2>
                <div class="mt-4 space-y-4 text-sm text-gray-600">
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tempo lit&uacute;rgico</span><span>{{ $missa->tempoLiturgico?->nome ?: 'Ainda n&atilde;o definido' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Celebrante</span><span>{{ $missa->celebrante?->nome ?: 'Ainda n&atilde;o vinculado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publica&ccedil;&atilde;o para fi&eacute;is</span><span>{{ $missa->publica_para_fieis ? 'Ativa' : 'N&atilde;o publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publica&ccedil;&atilde;o para m&uacute;sicos</span><span>{{ $missa->publica_para_musicos ? 'Ativa' : 'N&atilde;o publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Observa&ccedil;&otilde;es</span><span>{{ $missa->observacoes ?: 'Nenhuma observa&ccedil;&atilde;o informada.' }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Links da igreja</h2>
                <p class="mt-2 text-sm text-gray-500">Use a p&aacute;gina p&uacute;blica da igreja como refer&ecirc;ncia para o acesso dos fi&eacute;is e dos m&uacute;sicos.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos fi&eacute;is</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" class="mt-1 block break-all text-sm font-semibold text-[#8c6933] hover:underline">
                            {{ $igreja->link_publico }}
                        </a>
                    </div>

                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos m&uacute;sicos</span>
                        <a href="{{ $igreja->link_publico_musicos }}" target="_blank" class="mt-1 block break-all text-sm font-semibold text-sky-800 hover:underline">
                            {{ $igreja->link_publico_musicos }}
                        </a>
                    </div>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    @php
        $musicasParaBusca = $musicas->map(function ($musica) {
            return [
                'id' => $musica->id,
                'titulo' => $musica->titulo,
                'artista' => $musica->artista,
                'letra' => $musica->letra,
                'texto_exibicao' => trim($musica->titulo . ($musica->artista ? ' - ' . $musica->artista : '')),
                'momento_liturgico_id' => $musica->momento_liturgico_id,
                'momento_liturgico_nome' => $musica->momentoLiturgico?->nome,
                'versoes_count' => $musica->versoesMusicais->count(),
                'versoes' => $musica->versoesMusicais->map(function ($versao) {
                    return [
                        'id' => $versao->id,
                        'titulo' => $versao->titulo ?: 'Versão principal',
                        'tom' => $versao->tom_musical,
                        'bpm' => $versao->bpm,
                    ];
                })->values(),
            ];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const musicas = @json($musicasParaBusca, JSON_UNESCAPED_UNICODE);
            const inputBusca = document.getElementById('busca_musica');
            const resultadoBusca = document.getElementById('resultado_busca_musica');
            const musicaId = document.getElementById('musica_id');
            const musicaSelecionadaTexto = document.getElementById('musica_selecionada_texto');
            const selectVersao = document.getElementById('versao_musical_id');
            const selectMomento = document.getElementById('momento_liturgico_id');
            const momentoHint = document.getElementById('momento_liturgico_hint');
            const campoTomUsado = document.getElementById('tom_usado');
            const tomUsadoHint = document.getElementById('tom_usado_hint');
            const oldVersaoId = @json(old('versao_musical_id'));
            const oldMomentoId = @json(old('momento_liturgico_id'));
            const formularioAdicionar = musicaId.form;
            let momentoAlteradoManualmente = Boolean(oldMomentoId);
            let momentoGuiaId = oldMomentoId ? String(oldMomentoId) : '';

            if (!inputBusca || !resultadoBusca || !musicaId || !musicaSelecionadaTexto || !selectVersao || !selectMomento || !campoTomUsado) {
                return;
            }

            const normalizarBusca = (valor) => String(valor || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();

            const atualizarOrientacaoTom = (versaoSelecionadaAtual = null) => {
                const primeiraOpcaoTom = campoTomUsado.querySelector('option[value=""]');

                if (!versaoSelecionadaAtual) {
                    if (primeiraOpcaoTom) {
                        primeiraOpcaoTom.textContent = 'Usar o tom original da versão';
                    }

                    if (tomUsadoHint) {
                        tomUsadoHint.textContent = 'Selecione um tom apenas se a igreja for tocar em um tom diferente da versão original.';
                    }

                    return;
                }

                if (primeiraOpcaoTom) {
                    primeiraOpcaoTom.textContent = versaoSelecionadaAtual.tom
                        ? 'Usar o tom original da versão (' + versaoSelecionadaAtual.tom + ')'
                        : 'Usar o tom original da versão';
                }

                if (tomUsadoHint) {
                    tomUsadoHint.textContent = versaoSelecionadaAtual.tom
                        ? 'Tom original sugerido: ' + versaoSelecionadaAtual.tom + '. Escolha outro apenas se a igreja for tocar em tom diferente.'
                        : 'Esta versão ainda não informa o tom original. Escolha um tom somente se necessário.';
                }
            };

            const preencherVersoes = (musicaSelecionada, versaoSelecionada = null) => {
                selectVersao.innerHTML = '<option value="">Vincular depois</option>';

                if (!musicaSelecionada || !Array.isArray(musicaSelecionada.versoes) || musicaSelecionada.versoes.length === 0) {
                    selectVersao.disabled = true;
                    atualizarOrientacaoTom();
                    return;
                }

                musicaSelecionada.versoes.forEach((versao, indice) => {
                    const option = document.createElement('option');
                    option.value = versao.id;

                    let texto = versao.titulo;
                    if (versao.tom) {
                        texto += ' - Tom ' + versao.tom;
                    }
                    if (versao.bpm) {
                        texto += ' - BPM ' + versao.bpm;
                    }

                    option.textContent = texto;

                    if (String(versao.id) === String(versaoSelecionada) || (!versaoSelecionada && indice === 0)) {
                        option.selected = true;
                    }

                    selectVersao.appendChild(option);
                });

                selectVersao.disabled = false;

                const versaoAtual = musicaSelecionada.versoes.find((versao) => String(versao.id) === String(selectVersao.value))
                    || musicaSelecionada.versoes.find((versao) => String(versao.id) === String(versaoSelecionada))
                    || musicaSelecionada.versoes[0];

                atualizarOrientacaoTom(versaoAtual || null);
            };

            const sugerirMomento = (musicaSelecionada) => {
                if (!musicaSelecionada || momentoAlteradoManualmente) {
                    return;
                }

                const momentoId = musicaSelecionada.momento_liturgico_id ? String(musicaSelecionada.momento_liturgico_id) : '';
                selectMomento.value = momentoId;

                if (momentoHint) {
                    momentoHint.textContent = momentoId
                        ? 'Momento sugerido pela musica: ' + (musicaSelecionada.momento_liturgico_nome || 'momento cadastrado') + '. Voce pode trocar se precisar.'
                        : 'Esta musica ainda nao tem momento cadastrado. Escolha manualmente ou deixe para definir depois.';
                    momentoHint.classList.toggle('text-emerald-700', Boolean(momentoId));
                    momentoHint.classList.toggle('font-semibold', Boolean(momentoId));
                }
            };

            const selecionarMusica = (musicaSelecionada, versaoSelecionada = null) => {
                musicaId.value = musicaSelecionada.id;
                inputBusca.value = musicaSelecionada.texto_exibicao;
                musicaSelecionadaTexto.textContent = musicaSelecionada.versoes_count > 0
                    ? 'Selecionada: ' + musicaSelecionada.texto_exibicao
                    : 'Selecionada sem versao/cifra cadastrada. Voce pode adicionar agora e vincular a cifra depois.';
                musicaSelecionadaTexto.classList.toggle('text-amber-700', musicaSelecionada.versoes_count === 0);
                inputBusca.setCustomValidity('');
                resultadoBusca.classList.add('hidden');
                resultadoBusca.innerHTML = '';
                sugerirMomento(musicaSelecionada);
                preencherVersoes(musicaSelecionada, versaoSelecionada);
            };

            const renderizarResultados = (termo) => {
                const busca = normalizarBusca(termo);

                if (busca.length < 2) {
                    resultadoBusca.classList.add('hidden');
                    resultadoBusca.innerHTML = '';
                    return;
                }

                const resultados = musicas
                    .filter((musica) => {
                        const textoBusca = normalizarBusca([musica.titulo || '', musica.artista || '', musica.letra || ''].join(' '));
                        return textoBusca.includes(busca);
                    })
                    .sort((primeira, segunda) => {
                        if (!momentoGuiaId) {
                            return 0;
                        }

                        const primeiraCombina = String(primeira.momento_liturgico_id || '') === String(momentoGuiaId);
                        const segundaCombina = String(segunda.momento_liturgico_id || '') === String(momentoGuiaId);

                        return Number(segundaCombina) - Number(primeiraCombina);
                    })
                    .slice(0, 12);

                resultadoBusca.replaceChildren();

                if (resultados.length === 0) {
                    const vazio = document.createElement('div');
                    vazio.className = 'rounded-xl px-3 py-3 text-sm text-gray-500';
                    vazio.textContent = 'Nenhuma música encontrada com esse termo.';
                    resultadoBusca.appendChild(vazio);
                    resultadoBusca.classList.remove('hidden');
                    return;
                }

                resultados.forEach((musica) => {
                    const trecho = (musica.letra || '').replace(/\s+/g, ' ').trim().slice(0, 90);
                    const subtitulo = musica.artista ? musica.artista : 'Artista não informado';
                    const botao = document.createElement('button');
                    const topo = document.createElement('div');
                    const texto = document.createElement('div');
                    const titulo = document.createElement('span');
                    const badge = document.createElement('span');
                    const artista = document.createElement('span');

                    botao.type = 'button';
                    botao.className = 'musica-search-result';
                    botao.dataset.musicaId = musica.id;

                    topo.className = 'musica-search-result__top';
                    texto.className = 'min-w-0';
                    titulo.className = 'block text-sm font-semibold text-gray-900';
                    titulo.textContent = musica.titulo || 'Música sem título';
                    artista.className = 'mt-1 block text-xs text-[#8c6933]';
                    artista.textContent = subtitulo;

                    badge.className = musica.versoes_count > 0
                        ? 'musica-search-badge musica-search-badge--ok'
                        : 'musica-search-badge musica-search-badge--warn';
                    badge.textContent = musica.versoes_count > 0
                        ? `${musica.versoes_count} versão(ões)`
                        : 'sem cifra';

                    texto.append(titulo, artista);
                    topo.append(texto, badge);
                    botao.appendChild(topo);

                    if (trecho) {
                        const trechoElemento = document.createElement('p');
                        trechoElemento.className = 'mt-2 text-xs text-gray-500';
                        trechoElemento.textContent = `${trecho}...`;
                        botao.appendChild(trechoElemento);
                    }

                    botao.addEventListener('click', () => {
                        const musicaSelecionada = musicas.find((item) => String(item.id) === String(botao.dataset.musicaId));
                        if (musicaSelecionada) {
                            selecionarMusica(musicaSelecionada);
                        }
                    });

                    resultadoBusca.appendChild(botao);
                });

                resultadoBusca.classList.remove('hidden');
            };

            inputBusca.addEventListener('input', (event) => {
                musicaId.value = '';
                musicaSelecionadaTexto.textContent = 'Selecione uma música na busca abaixo.';
                musicaSelecionadaTexto.classList.remove('text-amber-700');
                selectVersao.innerHTML = '<option value="">Vincular depois</option>';
                selectVersao.disabled = true;
                if (!momentoAlteradoManualmente) {
                    selectMomento.value = '';
                    if (momentoHint) {
                        momentoHint.textContent = 'Ao escolher a musica, o sistema tenta sugerir o momento cadastrado nela. Voce pode trocar se precisar.';
                        momentoHint.classList.remove('text-emerald-700', 'font-semibold');
                    }
                }
                atualizarOrientacaoTom();
                renderizarResultados(event.target.value);
            });

            selectMomento.addEventListener('change', () => {
                momentoAlteradoManualmente = true;
                momentoGuiaId = String(selectMomento.value || '');
                if (momentoHint) {
                    momentoHint.textContent = 'Momento ajustado manualmente para esta missa.';
                    momentoHint.classList.remove('text-emerald-700');
                    momentoHint.classList.add('font-semibold');
                }
            });

            selectVersao.addEventListener('change', () => {
                const musicaSelecionada = musicas.find((item) => String(item.id) === String(musicaId.value));
                if (!musicaSelecionada) {
                    atualizarOrientacaoTom();
                    return;
                }

                const versaoAtual = musicaSelecionada.versoes.find((versao) => String(versao.id) === String(selectVersao.value)) || null;
                atualizarOrientacaoTom(versaoAtual);
            });

            document.querySelectorAll('[data-escolher-momento]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    const momentoId = String(botao.dataset.escolherMomento || '');
                    const momentoNome = String(botao.dataset.momentoNome || 'momento escolhido');

                    selectMomento.value = momentoId;
                    momentoAlteradoManualmente = true;
                    momentoGuiaId = momentoId;

                    if (momentoHint) {
                        momentoHint.textContent = 'Montando o momento: ' + momentoNome + '. A busca vai priorizar musicas desse momento.';
                        momentoHint.classList.add('text-emerald-700', 'font-semibold');
                    }

                    inputBusca.focus();
                    inputBusca.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    if (inputBusca.value.trim().length >= 2) {
                        renderizarResultados(inputBusca.value);
                    }
                });
            });

            document.addEventListener('click', (event) => {
                if (!resultadoBusca.contains(event.target) && event.target !== inputBusca) {
                    resultadoBusca.classList.add('hidden');
                }
            });

            const musicaInicial = musicas.find((item) => String(item.id) === String(musicaId.value));
            if (musicaInicial) {
                selecionarMusica(musicaInicial, oldVersaoId);
            } else {
                atualizarOrientacaoTom();
            }

            formularioAdicionar?.addEventListener('submit', (event) => {
                if (musicaId.value !== '') {
                    inputBusca.setCustomValidity('');
                    return;
                }

                event.preventDefault();
                inputBusca.setCustomValidity('Digite e escolha uma música da lista.');
                inputBusca.reportValidity();
            });
        });
    </script>
@endpush
