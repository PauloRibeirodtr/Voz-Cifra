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

        .missa-search-field {
            position: relative;
        }

        .missa-search-field svg {
            position: absolute;
            left: 1rem;
            top: 50%;
            width: 1.15rem;
            height: 1.15rem;
            transform: translateY(-50%);
            color: #8a6a35;
            pointer-events: none;
        }

        .missa-search-field input {
            padding-left: 2.85rem;
        }

        .missa-group-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.25rem;
            color: #111827;
        }

        .missa-reactivate-box {
            border-radius: 1rem;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            padding: 0.85rem;
        }

        .missa-card-actions {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 0.55rem;
            width: 100%;
        }

        .missa-card-actions > form,
        .missa-card-actions > .missa-reactivate-details { grid-column: 1 / -1; }

        .missa-more-actions,
        .missa-reactivate-details { position: relative; }

        .missa-more-actions summary,
        .missa-reactivate-details summary {
            display: inline-flex;
            width: 100%;
            min-height: 2.85rem;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            border: 1px solid #d7c0a0;
            border-radius: 0.75rem;
            background: #fff8ed;
            color: #6c4a21;
            padding: 0.7rem 0.9rem;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 750;
            list-style: none;
        }

        .missa-more-actions summary::-webkit-details-marker,
        .missa-reactivate-details summary::-webkit-details-marker { display: none; }

        .missa-more-actions summary::after,
        .missa-reactivate-details summary::after {
            content: "⌄";
            font-size: 1rem;
            transition: transform 0.18s ease;
        }

        .missa-more-actions[open] summary::after,
        .missa-reactivate-details[open] summary::after { transform: rotate(180deg); }

        .missa-more-actions__panel {
            position: absolute;
            z-index: 35;
            top: calc(100% + 0.45rem);
            right: 0;
            width: min(19rem, calc(100vw - 2rem));
            display: grid;
            gap: 0.4rem;
            border: 1px solid #e7d8c6;
            border-radius: 0.9rem;
            background: #fffdf9;
            padding: 0.45rem;
            box-shadow: 0 18px 40px rgba(28, 18, 12, 0.18);
        }

        .missa-more-actions__panel a {
            display: flex;
            min-height: 2.65rem;
            align-items: center;
            gap: 0.7rem;
            border-radius: 0.65rem;
            padding: 0.65rem 0.75rem;
            color: #4b3426;
            font-size: 0.875rem;
            font-weight: 750;
        }

        .missa-more-actions__panel a:hover,
        .missa-more-actions__panel a:focus-visible { background: #f5efe6; }

        .missa-reactivate-details[open] summary { margin-bottom: 0.55rem; }

        body.theme-dark .missa-group-title { color: #f8fafc !important; }
        body.theme-dark .missa-meta-chip {
            border-color: #475569 !important;
            background: #263244 !important;
            color: #e2e8f0 !important;
        }
        body.theme-dark .missa-reactivate-box {
            border-color: rgba(52, 211, 153, 0.42) !important;
            background: rgba(6, 78, 59, 0.35) !important;
            color: #d1fae5 !important;
        }
        body.theme-dark .missa-reactivate-box p { color: #d1fae5 !important; }
        body.theme-dark .missa-more-actions summary,
        body.theme-dark .missa-reactivate-details summary {
            border-color: #614735 !important;
            background: #281b17 !important;
            color: #fed7aa !important;
        }
        body.theme-dark .missa-more-actions__panel {
            border-color: #614735 !important;
            background: #211612 !important;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.42);
        }
        body.theme-dark .missa-more-actions__panel a { color: #f5e7d7 !important; }
        body.theme-dark .missa-more-actions__panel a:hover,
        body.theme-dark .missa-more-actions__panel a:focus-visible { background: #38251f !important; }

        @media (max-width: 639px) {
            .missa-card-actions { grid-template-columns: 1fr; }
            .missa-card-actions > * { grid-column: 1 !important; }
            .missa-more-actions__panel { position: static; width: 100%; margin-top: 0.45rem; }
        }

        @media (prefers-reduced-motion: reduce) {
            .missa-list-card,
            .missa-more-actions summary::after,
            .missa-reactivate-details summary::after { transition: none; }
            .missa-list-card:hover { transform: none; }
        }
    </style>
@endpush

@section('content')
    @php
        $hoje = now('America/Cuiaba')->toDateString();
        $missasHoje = $missas->filter(fn ($missa) => $missa->ativo && optional($missa->data_missa)->toDateString() === $hoje)->values();
        $proximasMissas = $missas->filter(fn ($missa) => $missa->ativo && optional($missa->data_missa)->toDateString() > $hoje)->values();
        $historicoMissas = $missas->filter(fn ($missa) => !$missa->ativo || optional($missa->data_missa)->toDateString() < $hoje)->values();
        $gruposMissas = [
            'Hoje' => $missasHoje,
            'Próximas' => $proximasMissas,
            'Histórico e inativas' => $historicoMissas,
        ];
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Missas da igreja</h1>
            <p class="mt-1 text-sm text-gray-500">Organize as celebrações e o repertório da {{ $igreja->nome }}.</p>
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
            <p class="mt-2 text-sm text-gray-500">Comece criando a primeira missa da igreja para depois organizar o repertório.</p>
            <a href="{{ route('local-admin.missas.create') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-[#6c4a21] px-4 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                Cadastrar primeira missa
            </a>
        </div>
    @else
        <div class="missa-search-panel mb-5 rounded-2xl border border-[#ead6b3] bg-[#fff8ed] p-4">
            <label for="buscar_missa" class="mb-2 block text-sm font-black uppercase tracking-[0.14em] text-[#8a5a23]">Buscar missa</label>
            <div class="missa-search-field">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                    <path d="M16.5 16.5 21 21" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2"></path>
                </svg>
                <input id="buscar_missa" type="search" class="w-full rounded-xl border border-[#ead6b3] bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-[#6c4a21] focus:ring-2 focus:ring-[#ead6b3]" placeholder="Digite nome, data, celebrante ou tempo litúrgico" data-missa-search>
            </div>
        </div>

        <div class="space-y-5" data-missa-list>
            @foreach ($gruposMissas as $tituloGrupo => $missasDoGrupo)
                @continue($missasDoGrupo->isEmpty())
                <section data-missa-group>
                    <div class="missa-group-title">
                        <h2 class="text-base font-black">{{ $tituloGrupo }}</h2>
                        <span class="missa-meta-chip">{{ $missasDoGrupo->count() }} missa(s)</span>
                    </div>

                    <div class="mt-3 space-y-4">
            @foreach ($missasDoGrupo as $missa)
                @php
                    $textoBuscaMissa = trim(implode(' ', [
                        $missa->titulo,
                        optional($missa->data_missa)->format('d/m/Y'),
                        substr((string) $missa->hora_inicio, 0, 5),
                        substr((string) $missa->hora_fim, 0, 5),
                        $missa->tempoLiturgico?->nome,
                        $missa->celebrante?->nome,
                        $missa->ativo ? 'ativa' : 'inativa',
                    ]));
                @endphp
                <article class="missa-list-card rounded-2xl border border-gray-100 bg-white p-5 shadow-sm" data-missa-card data-search="{{ \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($textoBuscaMissa)) }}">
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
                                <span class="missa-meta-chip">Repertório: {{ $missa->missa_musicas_count }} item(ns)</span>
                                <span class="missa-meta-chip {{ $missa->publica_para_fieis ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : '' }}">Fiéis: {{ $missa->publica_para_fieis ? 'publicada' : 'oculta' }}</span>
                                <span class="missa-meta-chip {{ $missa->publica_para_musicos ? 'bg-sky-50 text-sky-700 border-sky-100' : '' }}">Músicos: {{ $missa->publica_para_musicos ? 'publicada' : 'oculta' }}</span>
                            </div>
                        </div>

                        <div class="missa-card-actions lg:w-[380px]">
                            <a href="{{ route('local-admin.missas.show', $missa) }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                                Abrir missa
                            </a>

                            <details class="missa-more-actions">
                                <summary><i class="fa-solid fa-ellipsis" aria-hidden="true"></i> Mais ações</summary>
                                <div class="missa-more-actions__panel">
                                    <a href="{{ route('local-admin.missas.apresentacao', $missa) }}"><i class="fa-solid fa-display" aria-hidden="true"></i> Visualização</a>
                                    <a href="{{ route('local-admin.missas.edit', $missa) }}"><i class="fa-solid fa-pen" aria-hidden="true"></i> Editar missa</a>
                                    <a href="{{ route('local-admin.missas.pdf', $missa) }}"><i class="fa-solid fa-file-pdf" aria-hidden="true"></i> Baixar PDF</a>
                                </div>
                            </details>

                            @if ($missa->ativo)
                                <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100">
                                        Inativar missa
                                    </button>
                                </form>
                            @else
                                <details class="missa-reactivate-details">
                                    <summary><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Reativar missa</summary>
                                    <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST" class="space-y-3">
                                        @csrf
                                    <div class="missa-reactivate-box">
                                        <p class="mb-3 text-sm font-semibold text-emerald-900">Escolha a nova data e horário antes de reativar.</p>
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                                            <input type="date" name="data_missa" value="{{ old('data_missa', now('America/Cuiaba')->toDateString()) }}" class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm text-gray-900" aria-label="Nova data da missa">
                                            <input type="time" name="hora_inicio" value="{{ old('hora_inicio', substr((string) $missa->hora_inicio, 0, 5)) }}" class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm text-gray-900" aria-label="Novo horário de início">
                                            <input type="time" name="hora_fim" value="{{ old('hora_fim', substr((string) $missa->hora_fim, 0, 5)) }}" class="rounded-xl border border-emerald-200 bg-white px-3 py-2 text-sm text-gray-900" aria-label="Novo horário de término">
                                        </div>
                                    </div>
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">Confirmar reativação</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
                    </div>
                </section>
            @endforeach
        </div>
        <div class="mt-5 hidden rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-sm font-semibold text-gray-500" data-missa-empty-search>
            Nenhuma missa encontrada com esse termo.
        </div>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('js/local-admin/missas-index.js') }}"></script>
@endpush
