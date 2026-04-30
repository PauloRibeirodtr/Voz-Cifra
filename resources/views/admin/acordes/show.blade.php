@extends('admin.layouts.admin')

@section('title', 'Visualizar acorde - ' . $acorde->nome)
@section('mobile_title', 'Acorde')

@push('styles')
<style>
    .nut-rect { fill: #e5e7eb; opacity: 0.9; }
    .fret-line { stroke: #9ca3af; stroke-width: 2; }
    .string-line { stroke: #d1d5db; opacity: 0.8; }
    .finger-dot { fill: #ea580c; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); }
    .finger-number { fill: white; font-size: 14px; font-weight: 800; text-anchor: middle; dominant-baseline: central; }
    .barre-path { stroke: #ea580c; stroke-width: 14; stroke-linecap: round; opacity: 0.95; }
    .marker-x { fill: #ef4444; font-weight: 900; font-size: 18px; text-anchor: middle; }
    .marker-o { stroke: #3b82f6; stroke-width: 2.5; fill: none; }
</style>
@endpush

@section('content')
<div class="admin-page-shell">
    <div class="admin-list-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <h1 class="text-3xl font-black text-gray-800">{{ $acorde->nome }}</h1>
                <p class="text-sm text-gray-500">{{ $acorde->variation_name ?? 'Padrao' }}</p>
            </div>

            <div class="admin-actions">
                <a href="{{ route('admin.acordes.edit', $acorde->id) }}" class="admin-btn border border-[#ead6b3] bg-[#fff8ed] text-[#6c4a21] hover:bg-[#f8ecd7]">Editar</a>
                <a href="{{ route('admin.acordes.index') }}" class="admin-btn admin-btn-secondary">Voltar</a>
                <form action="{{ route('admin.acordes.destroy', $acorde->id) }}" method="POST" onsubmit="return confirm('Deseja inativar este acorde? Ele sera preservado no banco.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="admin-btn admin-btn-danger">Inativar</button>
                </form>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/2 flex items-center justify-center bg-gray-50 rounded-xl py-8">
                <svg id="acordeSVG" width="240" height="300" viewBox="0 0 240 300">
                    <rect x="30" y="40" width="180" height="240" rx="4" fill="#2e1a12" stroke="#1a0f0a" stroke-width="2"/>
                    <g id="grid-layer"></g>
                    <g id="marks-layer"></g>
                </svg>
            </div>

            <div class="md:w-1/2">
                <h3 class="font-bold text-lg mb-4 text-gray-800 border-b pb-2">Detalhes do acorde</h3>
                <ul class="text-gray-600 space-y-3">
                    <li><strong class="text-gray-800">Descricao:</strong> {{ $acorde->descricao ?: 'Nao informada' }}</li>
                    <li><strong class="text-gray-800">Casa base do shape:</strong> {{ $acorde->base_fret }}&deg; casa</li>
                    <li><strong class="text-gray-800">Criado em:</strong> {{ $acorde->created_at->format('d/m/Y H:i') }}</li>
                    <li><strong class="text-gray-800">ID no sistema:</strong> #{{ $acorde->id }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CONFIG = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
const stringGap = CONFIG.width / (CONFIG.numStrings - 1);
const fretGap = CONFIG.height / CONFIG.numFrets;

let shapeData = @json($acorde->shape);
if (typeof shapeData === 'string') {
    try { shapeData = JSON.parse(shapeData); } catch (e) { shapeData = {}; }
}

let shape = shapeData || {};
if (!shape.positions) shape.positions = [];
if (!shape.barres) shape.barres = [];
if (!shape.topMarkers) shape.topMarkers = [null, null, null, null, null, null];

function render() {
    const gridGroup = document.getElementById('grid-layer');
    const marksGroup = document.getElementById('marks-layer');
    if (!gridGroup || !marksGroup) return;

    let grid = '';
    let marks = '';
    let baseFret = shape.baseFret || {{ $acorde->base_fret }} || 1;

    if (baseFret === 1) {
        grid += `<rect x="${CONFIG.startX}" y="${CONFIG.startY-6}" width="${CONFIG.width}" height="6" class="nut-rect" />`;
    } else {
        grid += `<text x="${CONFIG.startX-10}" y="${CONFIG.startY+25}" text-anchor="end" fill="#9ca3af" font-weight="bold" font-size="18">${baseFret}&#176;</text>`;
        grid += `<line x1="${CONFIG.startX}" y1="${CONFIG.startY}" x2="${CONFIG.startX+CONFIG.width}" y2="${CONFIG.startY}" class="fret-line" />`;
    }

    for (let i = 1; i <= CONFIG.numFrets; i++) {
        let y = CONFIG.startY + (i * fretGap);
        grid += `<line x1="${CONFIG.startX}" y1="${y}" x2="${CONFIG.startX+CONFIG.width}" y2="${y}" class="fret-line" />`;
    }

    for (let i = 0; i < CONFIG.numStrings; i++) {
        let x = CONFIG.startX + (i * stringGap);
        let thickness = 0.8 + ((5 - i) * 0.5);
        grid += `<line x1="${x}" y1="${CONFIG.startY}" x2="${x}" y2="${CONFIG.startY+CONFIG.height}" class="string-line" stroke-width="${thickness}" />`;
    }
    gridGroup.innerHTML = grid;

    (shape.topMarkers || []).forEach((m, i) => {
        let x = CONFIG.startX + (i * stringGap);
        let y = CONFIG.startY - 15;
        if (m === 'muted') marks += `<text x="${x}" y="${y+5}" class="marker-x">X</text>`;
        else if (m === 'open') marks += `<circle cx="${x}" cy="${y}" r="5" class="marker-o" />`;
    });

    (shape.barres || []).forEach(b => {
        let y = CONFIG.startY + (b.fret * fretGap) - (fretGap/2);
        let x1 = CONFIG.startX + ((6 - b.fromString) * stringGap);
        let x2 = CONFIG.startX + ((6 - b.toString) * stringGap);
        marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" class="barre-path" />`;
    });

    (shape.positions || []).forEach(p => {
        let y = CONFIG.startY + (p.fret * fretGap) - (fretGap/2);
        let x = CONFIG.startX + ((6 - p.string) * stringGap);
        marks += `<circle cx="${x}" cy="${y}" r="12" class="finger-dot" />`;
        if (p.finger) marks += `<text x="${x}" y="${y+1}" class="finger-number">${p.finger}</text>`;
    });

    marksGroup.innerHTML = marks;
}

document.addEventListener('DOMContentLoaded', render);
</script>
@endpush
