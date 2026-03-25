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
</style>
@endpush

@section('content')
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-800">Acordes</h1>
            <p class="text-sm text-gray-500">Galeria visual de acordes cadastrados pelo admin master.</p>
        </div>

        <form action="{{ route('admin.acordes.index') }}" method="GET" class="flex gap-2 w-full md:w-auto">
            <input
                type="text"
                name="search"
                placeholder="Buscar por nome ou descricao"
                value="{{ request('search') }}"
                class="border border-gray-300 rounded-lg px-4 py-2 w-full md:w-72 focus:ring-2 focus:ring-orange-500 outline-none"
            >

            <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-lg hover:bg-orange-700 transition">
                <i class="fa-solid fa-search"></i>
            </button>

            <a href="{{ route('admin.acordes.create') }}" class="bg-green-700 text-white px-4 py-2 rounded-lg hover:bg-green-800 transition font-semibold">
                Novo acorde
            </a>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between">
            {{ session('success') }}
            <button onclick="this.parentElement.remove()" class="text-green-900 font-bold">X</button>
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @forelse($acordes as $acorde)
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-1 group relative">
                <div class="p-3 bg-gray-50 border-b border-gray-100 text-center">
                    <h2 class="text-xl font-black text-gray-800">{{ $acorde->nome }}</h2>
                    <p class="text-xs text-gray-500 font-serif italic">{{ $acorde->variation_name ?: 'Padrao' }}</p>
                </div>

                <div class="flex justify-center py-5 bg-white">
                    <svg id="acorde-svg-{{ $acorde->id }}" width="120" height="160" viewBox="0 0 160 210"></svg>
                </div>

                <div class="absolute inset-0 bg-black bg-opacity-70 flex items-center justify-center gap-3 opacity-0 group-hover:opacity-100 transition duration-300">
                    <a href="{{ route('admin.acordes.show', $acorde->id) }}" class="bg-white text-gray-900 p-3 rounded-full hover:bg-gray-200 transition transform hover:scale-110" title="Visualizar">
                        <i class="fa-solid fa-eye"></i>
                    </a>

                    <a href="{{ route('admin.acordes.edit', $acorde->id) }}" class="bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition transform hover:scale-110" title="Editar">
                        <i class="fa-solid fa-pen"></i>
                    </a>

                    <form action="{{ route('admin.acordes.destroy', $acorde->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o acorde {{ $acorde->nome }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white p-3 rounded-full hover:bg-red-700 transition transform hover:scale-110" title="Excluir">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>

                <div class="p-2 bg-gray-50 text-center text-xs font-bold text-gray-400 border-t border-gray-100">
                    CASA {{ $acorde->base_fret }}
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-20 text-gray-400">
                <i class="fa-solid fa-music text-4xl mb-4"></i>
                <p>Nenhum acorde encontrado. <a href="{{ route('admin.acordes.create') }}" class="text-orange-600 underline font-bold">Cadastre o primeiro.</a></p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $acordes->appends(request()->query())->links() }}
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
            inner += `<text x="${configuracao.startX - 6}" y="${configuracao.startY + 16}" text-anchor="end" fill="#9ca3af" font-weight="bold" font-size="12">${baseFret}ª</text>`;
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
