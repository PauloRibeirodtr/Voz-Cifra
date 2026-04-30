@extends('admin.layouts.admin')

@section('title', 'Acordes | Voz & Cifra')
@section('mobile_title', 'Acordes')

@push('styles')
<style>
    .fret-line { stroke: #9ca3af; stroke-width: 1.5; }
    .string-line { stroke: #d1d5db; opacity: 0.8; }
    .nut-rect { fill: #e5e7eb; opacity: 0.9; }
    .finger-dot { fill: #ea580c; }
    .finger-number { fill: white; font-size: 11px; font-weight: 800; text-anchor: middle; dominant-baseline: central; }
    .barre-path { stroke: #ea580c; stroke-width: 10; stroke-linecap: round; opacity: 0.95; }
    .marker-x { fill: #ef4444; font-weight: 900; font-size: 12px; text-anchor: middle; }
    .marker-o { stroke: #3b82f6; stroke-width: 2; fill: none; }
    .chord-card {
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }
    .chord-card:hover {
        transform: translateY(-4px) scale(1.015);
        border-color: rgba(180, 126, 51, 0.35);
        box-shadow: 0 18px 42px rgba(48, 30, 18, 0.16);
    }
</style>
@endpush

@section('content')
    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Biblioteca visual</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Acordes</h1>
                <p class="admin-page-copy mt-3 text-sm sm:text-base">Galeria visual de acordes cadastrados pelo admin master.</p>
            </div>
        </section>

        <section class="admin-filter-surface p-5">
            <form action="{{ route('admin.acordes.index') }}" method="GET" class="admin-form-grid lg:grid-cols-[minmax(0,1fr)_auto]">
                <div>
                    <label class="admin-label">Busca</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="Buscar por nome ou descricao"
                        value="{{ request('search') }}"
                        class="admin-input"
                    >
                </div>

                <div class="admin-page-actions lg:self-end">
                    <button type="submit" class="admin-btn admin-btn-warm">Buscar</button>
                    <a href="{{ route('admin.acordes.create') }}" class="admin-btn admin-btn-primary">Cadastrar acorde</a>
                </div>
            </form>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse($acordes as $acorde)
                <article class="admin-list-card chord-card overflow-hidden">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-4 text-center">
                        <h2 class="text-xl font-black text-gray-800">{{ $acorde->nome }}</h2>
                        <p class="text-xs italic text-gray-500">{{ $acorde->variation_name ?: 'Padrao' }}</p>
                    </div>

                    <div class="flex justify-center px-4 py-5">
                        <svg id="acorde-svg-{{ $acorde->id }}" width="120" height="160" viewBox="0 0 160 210"></svg>
                    </div>

                    <div class="border-t border-gray-100 px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs font-black uppercase tracking-[0.14em] text-gray-400">Casa {{ $acorde->base_fret }}&deg;</span>
                        </div>

                        <div class="admin-actions mt-4">
                            <a href="{{ route('admin.acordes.show', $acorde->id) }}" class="admin-btn border border-sky-200 bg-sky-50 text-sky-800 hover:bg-sky-100">Ver</a>
                            <a href="{{ route('admin.acordes.edit', $acorde->id) }}" class="admin-btn border border-[#ead6b3] bg-[#fff8ed] text-[#6c4a21] hover:bg-[#f8ecd7]">Editar</a>
                            <form action="{{ route('admin.acordes.destroy', $acorde->id) }}" method="POST" onsubmit="return confirm('Deseja inativar o acorde {{ $acorde->nome }}? Ele sera preservado no banco.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-btn admin-btn-danger">Inativar</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="admin-empty-state col-span-full">
                    <i class="fa-solid fa-music mb-4 text-4xl"></i>
                    <p>Nenhum acorde encontrado. <a href="{{ route('admin.acordes.create') }}" class="font-bold underline">Cadastre o primeiro.</a></p>
                </div>
            @endforelse
        </section>

        <div class="rounded-2xl bg-white/75 px-4 py-3 shadow-sm ring-1 ring-black/5">
            {{ $acordes->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const acordesData = @json($acordes->items());

    document.addEventListener('DOMContentLoaded', () => {
        acordesData.forEach(acorde => renderizarAcorde(acorde));
    });

    function renderizarAcorde(acorde) {
        const svg = document.getElementById(`acorde-svg-${acorde.id}`);
        if (!svg) return;

        const configuracao = { startX: 25, startY: 30, width: 110, height: 150, numStrings: 6, numFrets: 5 };
        const espacoCordas = configuracao.width / (configuracao.numStrings - 1);
        const espacoTrastes = configuracao.height / configuracao.numFrets;

        let shape = acorde.shape || {};
        if (typeof shape === 'string') {
            try { shape = JSON.parse(shape); } catch (e) { shape = {}; }
        }

        const baseFret = shape.baseFret || acorde.base_fret || 1;
        const positions = shape.positions || [];
        const barres = shape.barres || [];
        const topMarkers = shape.topMarkers || [null, null, null, null, null, null];

        let inner = '';
        inner += `<rect x="${configuracao.startX}" y="${configuracao.startY}" width="${configuracao.width}" height="${configuracao.height}" rx="3" fill="#2e1a12" stroke="#1a0f0a" stroke-width="1.5" />`;

        if (parseInt(baseFret) === 1) {
            inner += `<rect x="${configuracao.startX}" y="${configuracao.startY - 4}" width="${configuracao.width}" height="4" class="nut-rect" />`;
        } else {
            inner += `<text x="${configuracao.startX - 6}" y="${configuracao.startY + 16}" text-anchor="end" fill="#9ca3af" font-weight="bold" font-size="12">${baseFret}&#176;</text>`;
            inner += `<line x1="${configuracao.startX}" y1="${configuracao.startY}" x2="${configuracao.startX + configuracao.width}" y2="${configuracao.startY}" class="fret-line" />`;
        }

        for (let f = 1; f <= configuracao.numFrets; f++) {
            const y = configuracao.startY + (f * espacoTrastes);
            inner += `<line x1="${configuracao.startX}" y1="${y}" x2="${configuracao.startX + configuracao.width}" y2="${y}" class="fret-line" />`;
        }

        for (let s = 0; s < configuracao.numStrings; s++) {
            const x = configuracao.startX + (s * espacoCordas);
            const thickness = 0.5 + ((configuracao.numStrings - 1 - s) * 0.3);
            inner += `<line x1="${x}" y1="${configuracao.startY}" x2="${x}" y2="${configuracao.startY + configuracao.height}" class="string-line" stroke-width="${thickness}" />`;
        }

        for (let i = 0; i < configuracao.numStrings; i++) {
            const x = configuracao.startX + (i * espacoCordas);
            const y = configuracao.startY - 10;
            const status = topMarkers[i];
            if (status === 'muted') inner += `<text x="${x}" y="${y + 4}" class="marker-x">X</text>`;
            else if (status === 'open') inner += `<circle cx="${x}" cy="${y}" r="3" class="marker-o" />`;
        }

        barres.forEach(barre => {
            const fret = parseInt(barre.fret || 1);
            const from = parseInt(barre.fromString || 6);
            const to = parseInt(barre.toString || 1);
            const y = configuracao.startY + (fret * espacoTrastes) - (espacoTrastes / 2);
            const x1 = configuracao.startX + ((6 - from) * espacoCordas);
            const x2 = configuracao.startX + ((6 - to) * espacoCordas);
            inner += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" class="barre-path" />`;
        });

        positions.forEach(pos => {
            const stringNum = parseInt(pos.string) || 1;
            const fret = parseInt(pos.fret) || 1;
            const y = configuracao.startY + (fret * espacoTrastes) - (espacoTrastes / 2);
            const x = configuracao.startX + ((6 - stringNum) * espacoCordas);
            inner += `<circle cx="${x}" cy="${y}" r="8" class="finger-dot" />`;
            if (pos.finger) inner += `<text x="${x}" y="${y + 1}" class="finger-number">${pos.finger}</text>`;
        });

        svg.innerHTML = inner;
    }
</script>
@endpush
