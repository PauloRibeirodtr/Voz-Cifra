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
    $totalPedidosTomPendentes = $missa->missaMusicas
        ->flatMap(fn ($item) => $item->solicitacoesMudancaTom)
        ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE)
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

        .repertorio-item-card.is-focused {
            border-color: #34d399;
            box-shadow: 0 18px 36px rgba(16, 185, 129, 0.14);
        }

        .repertorio-sequence {
            border: 1px solid #e5e7eb;
            border-radius: 1.25rem;
            background: linear-gradient(180deg, #ffffff, #fbfaf7);
            padding: 1rem;
        }

        .repertorio-sequence-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 0.75rem;
        }

        .repertorio-sequence-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 0.75rem;
            align-items: flex-start;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            padding: 0.85rem;
            color: #111827;
            text-decoration: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .repertorio-sequence-item:hover,
        .repertorio-sequence-item:focus,
        .repertorio-sequence-item.is-active {
            border-color: #34d399;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
            outline: none;
            transform: translateY(-1px);
        }

        .repertorio-sequence-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 999px;
            background: #0f766e;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 900;
        }

        .repertorio-sequence-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.55rem;
        }

        .repertorio-sequence-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            background: #f1f5f9;
            color: #475569;
            font-size: 0.68rem;
            font-weight: 800;
        }

        .repertorio-sequence-chip--warn {
            background: #fffbeb;
            color: #92400e;
        }

        .repertorio-sequence-chip--danger {
            background: #fef2f2;
            color: #b91c1c;
        }

        .repertorio-sequence-chip--info {
            background: #eff6ff;
            color: #1d4ed8;
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

        @media (max-width: 640px) {
            .missa-page-actions {
                grid-template-columns: 1fr;
            }

            .missa-page-actions a,
            .missa-page-actions button {
                min-height: 3rem;
                padding-block: 0.75rem;
            }

            .missa-support-panel {
                padding: 1rem !important;
            }

            .missa-support-panel summary {
                gap: 0.75rem;
            }

            .missa-support-panel summary span:first-child span:first-child,
            .missa-support-panel summary h2 {
                font-size: 1rem;
                line-height: 1.25;
            }

            .missa-support-panel summary span:first-child span:last-child,
            .missa-support-panel summary p {
                font-size: 0.78rem;
                line-height: 1.35;
            }

            .missa-step,
            .repertorio-status-card {
                padding: 0.8rem;
            }

            .missa-step-number {
                width: 1.75rem;
                height: 1.75rem;
            }

            .repertorio-sequence {
                padding: 0.8rem;
            }

            .repertorio-sequence-list {
                display: flex;
                gap: 0.7rem;
                overflow-x: auto;
                padding-bottom: 0.35rem;
                scroll-snap-type: x proximity;
            }

            .repertorio-sequence-item {
                min-width: 14rem;
                padding: 0.75rem;
                scroll-snap-align: start;
            }

            .repertorio-item-card {
                padding: 0.9rem !important;
            }

            .repertorio-action {
                width: 2.45rem;
                height: 2.45rem;
            }
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
                Crie a missa, salve os dados principais e depois monte o repertório logo abaixo. Cada música adicionada já fica salva automaticamente.
            </p>
            @if ($totalPedidosTomPendentes > 0)
                <div class="mt-4 inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-bold text-sky-800">
                    <i class="fa-solid fa-bell" aria-hidden="true"></i>
                    {{ $totalPedidosTomPendentes }} pedido(s) de mudanca de tom aguardando decisao.
                </div>
            @endif
        </div>

        <div class="missa-page-actions grid grid-cols-1 gap-3 sm:grid-cols-2 xl:min-w-[23rem]" data-guide-target="missa-acoes">
            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7]">
                Editar missa
            </a>
            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 font-semibold text-sky-800 transition hover:bg-sky-100">
                Visualização da missa
            </a>
            <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 font-semibold text-emerald-800 transition hover:bg-emerald-100">
                Ver como fiel
            </a>
            <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 font-semibold text-indigo-800 transition hover:bg-indigo-100">
                Ver como músico
            </a>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:col-span-2">
                <p class="mb-2 text-sm font-bold text-amber-900">Exportar repertório em PDF</p>
                <div class="grid gap-2 sm:grid-cols-3">
                    <a href="{{ route('local-admin.missas.pdf', ['missa' => $missa, 'formato' => 'letra']) }}" class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Somente letra</a>
                    <a href="{{ route('local-admin.missas.pdf', ['missa' => $missa, 'formato' => 'cifra']) }}" class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Letra com cifra</a>
                    <a href="{{ route('local-admin.missas.pdf', ['missa' => $missa, 'formato' => 'cifra_diagramas']) }}" class="inline-flex items-center justify-center rounded-lg border border-amber-200 bg-white px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100">Cifra + acordes</a>
                </div>
            </div>
            @php
                $confirmacaoAtivacao = $missa->ativo
                    ? 'Deseja inativar esta missa? O repertório será preservado para consulta futura.'
                    : 'Deseja reativar esta missa como parte do fluxo da igreja?';
            @endphp
            <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST" onsubmit="return confirm('{{ $confirmacaoAtivacao }}');">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border px-4 py-3 font-semibold transition {{ $missa->ativo ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $missa->ativo ? 'Inativar missa' : 'Reativar missa' }}
                </button>
            </form>
            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 sm:col-span-2">
                Voltar para as missas
            </a>
            @php
                $igrejasParaDuplicar = collect($igrejasAdministradas ?? [])
                    ->reject(fn ($igrejaDuplicacao) => (bool) ($igrejaDuplicacao->eh_ativa ?? false))
                    ->values();
                $dataMinimaDuplicacao = now('America/Cuiaba')->toDateString();
                $dataMaximaDuplicacao = now('America/Cuiaba')->addMonths(3)->toDateString();
            @endphp
            @if ($igrejasParaDuplicar->isNotEmpty() && $totalItensRepertorio > 0)
                <details class="rounded-2xl border border-[#ead6b3] bg-[#fff8ed] p-4 sm:col-span-2">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-black text-[#5b3d1a] [&::-webkit-details-marker]:hidden">
                        <span>Duplicar para outra igreja</span>
                        <span class="rounded-full bg-white px-3 py-1 text-xs text-[#6c4a21]">Abrir</span>
                    </summary>

                    <form action="{{ route('local-admin.missas.duplicar', $missa) }}" method="POST" class="mt-4 grid grid-cols-1 gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[#7c5628]">Igreja de destino</label>
                            <select name="igreja_destino_id" class="mt-1 w-full rounded-xl border border-[#ead6b3] bg-white px-3 py-2 text-sm font-bold text-gray-900" required>
                                <option value="">Escolha uma igreja</option>
                                @foreach ($igrejasParaDuplicar as $igrejaDestino)
                                    <option value="{{ $igrejaDestino->id }}">{{ $igrejaDestino->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-[#7c5628]">Titulo da nova missa</label>
                            <input name="titulo" value="{{ $missa->titulo }}" maxlength="255" class="mt-1 w-full rounded-xl border border-[#ead6b3] bg-white px-3 py-2 text-sm text-gray-900">
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-[#7c5628]">Data</label>
                                <input type="date" name="data_missa" min="{{ $dataMinimaDuplicacao }}" max="{{ $dataMaximaDuplicacao }}" class="mt-1 w-full rounded-xl border border-[#ead6b3] bg-white px-3 py-2 text-sm text-gray-900" required>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-[#7c5628]">Inicio</label>
                                <input type="time" name="hora_inicio" value="{{ substr((string) $missa->hora_inicio, 0, 5) }}" class="mt-1 w-full rounded-xl border border-[#ead6b3] bg-white px-3 py-2 text-sm text-gray-900" required>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-wider text-[#7c5628]">Termino</label>
                                <input type="time" name="hora_fim" value="{{ substr((string) $missa->hora_fim, 0, 5) }}" class="mt-1 w-full rounded-xl border border-[#ead6b3] bg-white px-3 py-2 text-sm text-gray-900" required>
                            </div>
                        </div>
                        <p class="text-xs font-semibold text-[#7c5628]">
                            A cópia nasce fechada para publicação. Revise dados, repertório e links antes de liberar para fiéis ou músicos.
                        </p>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#6c4a21] px-4 py-3 text-sm font-black text-white transition hover:bg-[#5b3d1a]">
                            Criar copia para revisar
                        </button>
                    </form>
                </details>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <strong class="block">{{ session('warning') }}</strong>
            @if (session('missa_pendencias'))
                <ul class="mt-2 list-disc pl-5">
                    @foreach (session('missa_pendencias') as $pendencia)
                        <li>{{ $pendencia }}</li>
                    @endforeach
                </ul>
            @endif
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

    <details class="missa-support-panel mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" data-guide-target="missa-conferencia">
        <summary class="flex cursor-pointer list-none flex-col gap-3 text-gray-900 sm:flex-row sm:items-center sm:justify-between [&::-webkit-details-marker]:hidden">
            <span>
                <span class="block text-lg font-bold">Conferência e pendências</span>
                <span class="mt-1 block text-sm text-gray-500">Abra somente quando quiser revisar status, ordem, cifras e duplicidades.</span>
            </span>
            <span class="flex flex-wrap gap-2 text-xs font-bold">
                <span class="rounded-full {{ $repertorioCompleto ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800' }} px-3 py-1">
                    {{ $repertorioCompleto ? 'Tudo certo' : 'Pendente' }}
                </span>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-gray-700">{{ $totalItensRepertorio }} música(s)</span>
                @if ($itensSemVersao > 0)
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ $itensSemVersao }} sem cifra</span>
                @endif
            </span>
        </summary>

        <div class="mt-5">
    <section class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-3">
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">1</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Dados da missa</p>
                    <p class="mt-1 text-xs text-gray-500">Data, horário, celebrante e publicação.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">2</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Montagem do repertório</p>
                    <p class="mt-1 text-xs text-gray-500">Adicione músicas, versões, ordem e tom usado.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">3</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Visualização final</p>
                    <p class="mt-1 text-xs text-gray-500">Confira a missa antes de apresentar ou gerar PDF.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-6 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="repertorio-status-card {{ $totalItensRepertorio > 0 ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Repertório</span>
            <strong>{{ $totalItensRepertorio }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $totalItensRepertorio === 1 ? 'música adicionada' : 'músicas adicionadas' }}</p>
        </div>
        <div class="repertorio-status-card {{ $itensSemVersao === 0 ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Cifras</span>
            <strong>{{ $itensSemVersao === 0 ? 'OK' : $itensSemVersao }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $itensSemVersao === 0 ? 'todas com versão' : 'sem versão vinculada' }}</p>
        </div>
        <div class="repertorio-status-card {{ $repertorioCompleto ? 'repertorio-status-ok' : 'repertorio-status-warn' }}">
            <span class="text-xs font-black uppercase tracking-wider text-gray-500">Status</span>
            <strong>{{ $repertorioCompleto ? 'Completo' : 'Pendente' }}</strong>
            <p class="mt-2 text-sm text-gray-600">{{ $repertorioCompleto ? 'pronto para revisar' : 'revise momentos e versões' }}</p>
        </div>
    </section>
        </div>
    </details>

    <details class="missa-support-panel mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" data-guide-target="missa-checklist">
        <summary class="flex cursor-pointer list-none flex-col gap-3 text-gray-900 lg:flex-row lg:items-center lg:justify-between [&::-webkit-details-marker]:hidden">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Checklist da montagem</h2>
                <p class="mt-1 text-sm text-gray-500">Conferência rápida, ordem e pendências. Abra quando precisar.</p>
            </div>
            <span class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm font-semibold text-gray-700 sm:w-auto">
                Abrir checklist
            </span>
        </summary>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border {{ $totalItensRepertorio > 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $totalItensRepertorio > 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Repertório
                </strong>
                <p class="mt-2">{{ $totalItensRepertorio > 0 ? 'Há músicas adicionadas.' : 'Adicione pelo menos uma música.' }}</p>
            </div>
            <div class="rounded-xl border {{ $itensSemMomento === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $itensSemMomento === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Momentos
                </strong>
                <p class="mt-2">{{ $itensSemMomento === 0 ? 'Todos os itens têm momento.' : $itensSemMomento . ' item(ns) sem momento.' }}</p>
            </div>
            <div class="rounded-xl border {{ $itensSemVersao === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-amber-100 bg-amber-50 text-amber-900' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $itensSemVersao === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Cifras
                </strong>
                <p class="mt-2">{{ $itensSemVersao === 0 ? 'Tudo vinculado para os músicos.' : $itensSemVersao . ' item(ns) sem cifra.' }}</p>
            </div>
            <div class="rounded-xl border {{ $musicasDuplicadas === 0 ? 'border-emerald-100 bg-emerald-50 text-emerald-800' : 'border-red-100 bg-red-50 text-red-800' }} p-4 text-sm">
                <strong class="flex items-center gap-2">
                    <i class="fa-solid {{ $musicasDuplicadas === 0 ? 'fa-check' : 'fa-triangle-exclamation' }}" aria-hidden="true"></i>
                    Duplicidade
                </strong>
                <p class="mt-2">{{ $musicasDuplicadas === 0 ? 'Sem músicas repetidas.' : $musicasDuplicadas . ' música(s) repetida(s).' }}</p>
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
            <p class="mt-3 text-xs text-gray-500">{{ $momentosEssenciaisDefinidos }} de {{ count($momentosEssenciais) }} momentos principais já aparecem no repertório.</p>
        </div>
    </details>

    <details class="missa-support-panel mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" data-guide-target="missa-momentos">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-gray-900 [&::-webkit-details-marker]:hidden">
            <span>
                <span class="block text-lg font-bold">Montar por momentos</span>
                <span class="mt-1 block text-sm text-gray-500">Opcional: use quando quiser escolher a musica pela etapa da missa.</span>
            </span>
            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">Abrir</span>
        </summary>

        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">
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
                                    Nenhuma música definida.
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
                        Escolher música
                    </button>
                </div>
            @endforeach
        </div>
    </details>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="missa-repertorio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm" data-guide-target="missa-repertorio-add">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Adicionar música ao repertório</h2>
                        <p class="mt-1 text-sm text-gray-500">Busque por nome da música, artista ou trecho da letra para localizar o canto com mais rapidez.</p>
                    </div>
                    <form action="{{ route('local-admin.missas.concluir-montagem', $missa) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 sm:w-auto">
                            Finalizar repertório
                        </button>
                    </form>
                </div>

                @if ($totalItensRepertorio > 0 && !$repertorioCompleto)
                    <div class="repertorio-alert mb-4 text-sm">
                        <strong class="block">Antes de concluir, revise as pendencias.</strong>
                        @if ($itensSemVersao > 0)
                            <span>{{ $itensSemVersao }} item(ns) ainda não tem versão/cifra vinculada.</span>
                        @endif
                        @if ($itensSemMomento > 0)
                            <span>{{ $itensSemMomento }} item(ns) ainda não tem momento litúrgico definido.</span>
                        @endif
                    </div>
                @endif

                <form action="{{ route('local-admin.repertorio.store', $missa) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="busca_musica" class="block text-sm font-medium text-gray-700">Música</label>
                        <input type="hidden" name="musica_id" id="musica_id" value="{{ old('musica_id') }}" required>
                        <div class="mt-1 rounded-2xl border border-gray-300 bg-white shadow-sm">
                            <input
                                type="text"
                                id="busca_musica"
                                class="block w-full rounded-2xl border-0 bg-transparent px-4 py-3 text-gray-800 focus:ring-2 focus:ring-[#ead6b3]"
                                placeholder="Digite o nome da música, artista ou trecho da letra"
                                autocomplete="off"
                            >
                            <div id="resultado_busca_musica" class="hidden border-t border-gray-100 p-2"></div>
                        </div>
                        <p id="musica_selecionada_texto" class="mt-2 text-sm text-gray-500">Nenhuma música selecionada.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Momento da missa</label>
                        <select name="momento_liturgico_id" id="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Definir depois</option>
                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                <option value="{{ $momentoLiturgico->id }}" @selected(old('momento_liturgico_id') == $momentoLiturgico->id)>{{ $momentoLiturgico->nome }}</option>
                            @endforeach
                        </select>
                        <p id="momento_liturgico_hint" class="mt-1 text-xs text-gray-500">Ao escolher a música, o sistema tenta preencher este campo. Você pode trocar se precisar.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cifra / versão</label>
                        <select name="versao_musical_id" id="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]" disabled>
                            <option value="">Escolher depois</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tom para tocar</label>
                        <select name="tom_usado" id="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Usar o tom original da versão</option>
                            @foreach ($tonsMusicais as $tomMusical)
                                <option value="{{ $tomMusical }}" @selected(old('tom_usado') === $tomMusical)>{{ $tomMusical }}</option>
                            @endforeach
                        </select>
                        <p id="tom_usado_hint" class="mt-1 text-xs text-gray-500">Selecione um tom apenas se a igreja for tocar em um tom diferente da versão original.</p>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#6c4a21] px-5 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                            Adicionar música
                        </button>
                        <a href="#missa-resumo" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                            Revisar dados da missa
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Repertório da missa</h2>
                    <p class="mt-1 text-sm text-gray-500">Organize a ordem dos cantos, defina o momento litúrgico e ajuste a versão usada em cada item.</p>
                </div>

                @if ($missa->missaMusicas->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                        Ainda não existe música cadastrada no repertório desta missa.
                    </div>
                @else
                    <div class="repertorio-sequence mb-5" data-guide-target="missa-sequencia">
                        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <h3 class="text-base font-black text-gray-900">Sequencia da celebracao</h3>
                                <p class="mt-1 text-sm text-gray-500">Use esta linha para conferir a ordem sem abrir todos os cards.</p>
                            </div>
                            <form action="{{ route('local-admin.missas.repertorio.corrigir-ordem', $missa) }}" method="POST" class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7] sm:w-auto">
                                    <i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i>
                                    Ordenar por momentos
                                </button>
                            </form>
                        </div>

                        <nav class="repertorio-sequence-list" aria-label="Sequencia do repertorio da missa">
                            @foreach ($missa->missaMusicas as $itemSequencia)
                                @php
                                    $pedidosTomSequencia = $itemSequencia->solicitacoesMudancaTom
                                        ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE)
                                        ->count();
                                @endphp
                                <a href="#repertorio-item-{{ $itemSequencia->id }}" class="repertorio-sequence-item" data-repertorio-sequence-link="{{ $itemSequencia->id }}">
                                    <span class="repertorio-sequence-number">{{ $loop->iteration }}</span>
                                    <span class="min-w-0">
                                        <strong class="block truncate text-sm font-black text-gray-900">{{ $itemSequencia->musica->titulo }}</strong>
                                        <span class="mt-1 block truncate text-xs font-bold uppercase tracking-wide text-gray-500">
                                            {{ $itemSequencia->momentoLiturgico?->nome ?: 'Momento pendente' }}
                                        </span>
                                        <span class="repertorio-sequence-meta">
                                            <span class="repertorio-sequence-chip">Tom {{ $itemSequencia->tom_exibicao ?: 'original' }}</span>
                                            @if (!$itemSequencia->momentoLiturgico)
                                                <span class="repertorio-sequence-chip repertorio-sequence-chip--warn">sem momento</span>
                                            @endif
                                            @if (!$itemSequencia->versaoMusical)
                                                <span class="repertorio-sequence-chip repertorio-sequence-chip--danger">sem cifra</span>
                                            @endif
                                            @if ($pedidosTomSequencia > 0)
                                                <span class="repertorio-sequence-chip repertorio-sequence-chip--info">{{ $pedidosTomSequencia }} pedido(s)</span>
                                            @endif
                                        </span>
                                    </span>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    <div class="space-y-4">
                        @foreach ($missa->missaMusicas as $item)
                            @php
                                $pedidosTomPendentes = $item->solicitacoesMudancaTom
                                    ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE);
                            @endphp
                            <article id="repertorio-item-{{ $item->id }}" class="repertorio-item-card scroll-mt-24 rounded-2xl border border-gray-200 bg-gray-50 p-4">
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
                                            @if ($pedidosTomPendentes->isNotEmpty())
                                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">{{ $pedidosTomPendentes->count() }} pedido(s) de tom</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $item->musica->titulo }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista não informado' }}</p>
                                        <p class="mt-2 text-sm text-gray-600">
                                            Versão: {{ $item->versaoMusical?->titulo ?: 'Ainda não vinculada' }}
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
                                        <form action="{{ route('local-admin.repertorio.destroy', [$missa, $item]) }}" method="POST" onsubmit="return confirm('Deseja remover esta música do repertório da missa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="repertorio-action border border-red-200 bg-red-50 text-red-700 hover:bg-red-100" title="Remover item" aria-label="Remover item">
                                                <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if ($pedidosTomPendentes->isNotEmpty())
                                    <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4">
                                        <div class="mb-3">
                                            <h4 class="text-sm font-black text-sky-950">Pedidos de mudanca de tom</h4>
                                            <p class="mt-1 text-xs text-sky-800">Aprove somente se esse tom realmente deve valer para todos os músicos desta missa.</p>
                                        </div>

                                        <div class="space-y-3">
                                            @foreach ($pedidosTomPendentes as $pedidoTom)
                                                <div class="rounded-xl border border-sky-100 bg-white p-3">
                                                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                                        <div class="min-w-0">
                                                            <p class="text-sm font-bold text-gray-900">
                                                                {{ $pedidoTom->usuario?->nome ?: 'Músico' }} pediu tom {{ $pedidoTom->tom_sugerido }}
                                                            </p>
                                                            <p class="mt-1 text-xs text-gray-500">
                                                                Atual: {{ $pedidoTom->tom_atual ?: 'não informado' }} &bull; {{ $pedidoTom->created_at?->diffForHumans() }}
                                                            </p>
                                                            @if ($pedidoTom->observacao)
                                                                <p class="mt-2 text-sm text-gray-600">{{ $pedidoTom->observacao }}</p>
                                                            @endif
                                                        </div>

                                                        <div class="flex flex-wrap gap-2">
                                                            <form action="{{ route('local-admin.repertorio.tom.aprovar', $pedidoTom) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">
                                                                    Aprovar
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('local-admin.repertorio.tom.recusar', $pedidoTom) }}" method="POST" class="flex flex-wrap gap-2">
                                                                @csrf
                                                                <input name="resposta" maxlength="500" class="min-w-0 rounded-xl border border-gray-200 px-3 py-2 text-sm" placeholder="Motivo opcional">
                                                                <button type="submit" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-100">
                                                                    Recusar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <details class="mt-4 rounded-xl border border-gray-200 bg-white p-3" @if (!$item->versaoMusical) open @endif>
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-bold text-gray-700 [&::-webkit-details-marker]:hidden">
                                        <span>Ajustar momento, cifra e tom</span>
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs text-gray-600">Abrir</span>
                                    </summary>

                                <form action="{{ route('local-admin.repertorio.update', [$missa, $item]) }}" method="POST" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Momento da missa</label>
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
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Cifra / versão</label>
                                        <select name="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Escolher depois</option>
                                            @foreach ($item->musica->versoesMusicais as $versaoMusical)
                                                <option value="{{ $versaoMusical->id }}" @selected((string) $item->versao_musical_id === (string) $versaoMusical->id)>
                                                    {{ $versaoMusical->titulo ?: 'Versão principal' }}
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
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tom para tocar</label>
                                        <select name="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Usar o tom original da versão</option>
                                            @foreach ($tonsMusicais as $tomMusical)
                                                <option value="{{ $tomMusical }}" @selected((string) old('tom_usado', $item->tom_usado) === (string) $tomMusical)>{{ $tomMusical }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#2a1b1b] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1f1414]">
                                            Salvar ajustes
                                        </button>
                                    </div>
                                </form>
                                </details>
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
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tempo litúrgico</span><span>{{ $missa->tempoLiturgico?->nome ?: 'Ainda não definido' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Celebrante</span><span>{{ $missa->celebrante?->nome ?: 'Ainda não vinculado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publicação para fiéis</span><span>{{ $missa->publica_para_fieis ? 'Ativa' : 'Não publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publicação para músicos</span><span>{{ $missa->publica_para_musicos ? 'Ativa' : 'Não publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Observações</span><span>{{ $missa->observacoes ?: 'Nenhuma observação informada.' }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Links da igreja</h2>
                <p class="mt-2 text-sm text-gray-500">Use a página pública da igreja como referência para o acesso dos fiéis e dos músicos.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos fiéis</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm font-semibold text-[#8c6933] hover:underline">
                            {{ $igreja->link_publico }}
                        </a>
                    </div>

                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos músicos</span>
                        <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="mt-1 block break-all text-sm font-semibold text-sky-800 hover:underline">
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
            const musicas = {{ Illuminate\Support\Js::from($musicasParaBusca) }};
            const inputBusca = document.getElementById('busca_musica');
            const resultadoBusca = document.getElementById('resultado_busca_musica');
            const musicaId = document.getElementById('musica_id');
            const musicaSelecionadaTexto = document.getElementById('musica_selecionada_texto');
            const selectVersao = document.getElementById('versao_musical_id');
            const selectMomento = document.getElementById('momento_liturgico_id');
            const momentoHint = document.getElementById('momento_liturgico_hint');
            const campoTomUsado = document.getElementById('tom_usado');
            const tomUsadoHint = document.getElementById('tom_usado_hint');
            const oldVersaoId = {{ Illuminate\Support\Js::from(old('versao_musical_id')) }};
            const oldMomentoId = {{ Illuminate\Support\Js::from(old('momento_liturgico_id')) }};
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
                selectVersao.innerHTML = '<option value="">Escolher depois</option>';

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
                        : 'Esta música ainda não tem momento cadastrado. Escolha manualmente ou deixe para definir depois.';
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
                selectVersao.innerHTML = '<option value="">Escolher depois</option>';
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

            document.querySelectorAll('[data-repertorio-sequence-link]').forEach((link) => {
                link.addEventListener('click', () => {
                    const itemId = String(link.dataset.repertorioSequenceLink || '');
                    const card = document.getElementById('repertorio-item-' + itemId);

                    document.querySelectorAll('[data-repertorio-sequence-link]').forEach((item) => item.classList.remove('is-active'));
                    document.querySelectorAll('.repertorio-item-card').forEach((item) => item.classList.remove('is-focused'));

                    link.classList.add('is-active');

                    if (card) {
                        card.classList.add('is-focused');
                        window.setTimeout(() => card.classList.remove('is-focused'), 2400);
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
