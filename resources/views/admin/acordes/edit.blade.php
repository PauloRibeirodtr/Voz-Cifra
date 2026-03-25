@extends('admin.layouts.admin')

@section('title', 'Editar acorde - ' . $acorde->nome)
@section('mobile_title', 'Editar acorde')

@push('styles')
<style>
    .fret-line { stroke: #9ca3af; stroke-width: 2; }
    .nut-rect { fill: #e5e7eb; opacity: 0.9; }
    .finger-dot { fill: #ea580c; transition: all 0.2s; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); }
    .finger-number { fill: white; font-size: 14px; font-weight: 800; pointer-events: none; text-anchor: middle; dominant-baseline: central; }
    .barre-path { stroke: #ea580c; stroke-width: 14; stroke-linecap: round; opacity: 0.95; }
    .marker-x { fill: #ef4444; font-weight: 900; font-size: 18px; text-anchor: middle; }
    .marker-o { stroke: #3b82f6; stroke-width: 2.5; fill: none; }
    svg { cursor: pointer; }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white w-full rounded-3xl shadow p-6 flex flex-col md:flex-row gap-8">
        <div class="w-full md:w-1/2">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-800">Editar acorde</h2>
                    <p class="text-sm text-gray-500">Altere as informacoes e o desenho no braco.</p>
                </div>
                <a href="{{ route('admin.acordes.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg font-bold transition">Voltar</a>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                    <p class="font-bold">Nao foi possivel salvar as alteracoes.</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="acordeForm" action="{{ route('admin.acordes.update', $acorde->id) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nome do acorde *</label>
                    <input type="text" name="nome" id="input-nome" value="{{ old('nome', $acorde->nome) }}" required class="w-full bg-gray-50 border {{ $errors->has('nome') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-lg px-4 py-3 text-lg font-bold focus:ring-2 focus:ring-orange-500 outline-none">
                    @error('nome')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Descricao visual</label>
                    <input type="text" id="input-variacao" value="{{ old('descricao', $acorde->variation_name) }}" placeholder="Ex.: Pestana 3a casa" class="w-full bg-gray-50 border {{ $errors->has('descricao') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-lg px-4 py-3 focus:ring-2 focus:ring-orange-500 outline-none">
                    @error('descricao')
                        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Casa inicial do shape</label>
                    <select id="input-base-fret" class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 font-bold text-gray-700 focus:ring-2 focus:ring-orange-500 outline-none">
                        @for ($casa = 1; $casa <= 12; $casa++)
                            <option value="{{ $casa }}" {{ (int) $acorde->base_fret === $casa ? 'selected' : '' }}>{{ $casa }}° casa</option>
                        @endfor
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Use isso quando o desenho comecar acima da primeira casa. O editor continua mostrando 5 trastes a partir da casa escolhida.</p>
                </div>

                <input type="hidden" name="shape" id="final-json">

                <div class="pt-6 border-t flex flex-col gap-3">
                    <button type="button" id="btnAddPestana" class="py-3 bg-orange-100 hover:bg-orange-200 text-orange-800 font-bold rounded-xl transition border border-orange-200">
                        <i class="fa-solid fa-minus mr-2"></i> Adicionar pestana
                    </button>
                    <button type="button" id="btnClear" class="py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition">
                        <i class="fa-solid fa-eraser mr-2"></i> Limpar desenho
                    </button>
                    <button type="submit" class="py-4 mt-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-black text-lg transition shadow-lg transform hover:-translate-y-1">
                        Salvar alteracoes
                    </button>
                </div>
            </form>
        </div>

        <div class="w-full md:w-1/2 flex flex-col items-center justify-center bg-gray-50 rounded-2xl py-8 border border-gray-100">
            <div class="text-center mb-6">
                <h1 id="preview-nome" class="text-4xl font-black text-blue-600 font-serif">{{ $acorde->nome }}</h1>
                <p id="preview-variacao" class="text-gray-500 italic">{{ $acorde->variation_name ?? 'Shape padrao' }}</p>
            </div>

            <div class="filter drop-shadow-xl relative">
                <button type="button" onclick="limparDedos()" class="absolute -top-10 right-0 text-xs bg-white px-2 py-1 rounded shadow text-gray-500 hover:text-red-500">
                    Limpar dedos
                </button>

                <svg id="acordeSVG" width="300" height="380" viewBox="0 0 240 300" class="select-none touch-none">
                    <defs>
                        <linearGradient id="woodGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                            <stop offset="0%" style="stop-color:#2e1a12;stop-opacity:1" />
                            <stop offset="50%" style="stop-color:#3d261a;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#2e1a12;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <rect x="30" y="40" width="180" height="240" rx="4" fill="url(#woodGradient)" stroke="#1a0f0a" stroke-width="2" />
                    <g id="grid-layer"></g>
                    <g id="marks-layer"></g>
                    <rect x="0" y="0" width="240" height="300" fill="transparent" onclick="handleGridClick(event)" />
                </svg>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CONFIG = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
const stringGap = CONFIG.width / (CONFIG.numStrings - 1);
const fretGap = CONFIG.height / CONFIG.numFrets;

let shapeData = @json($acorde->shape);
if (typeof shapeData === 'string') {
    try { shapeData = JSON.parse(shapeData); } catch (e) { shapeData = {}; }
}

let state = shapeData || {};
state.baseFret = parseInt({{ $acorde->base_fret }}) || 1;
state.variation_name = @json($acorde->variation_name);
if (!state.positions) state.positions = [];
if (!state.barres) state.barres = [];
if (!state.topMarkers) state.topMarkers = [null, null, null, null, null, null];

document.addEventListener('DOMContentLoaded', () => {
    renderAll();
    setupInputs();
});

function setupInputs() {
    document.getElementById('input-nome').addEventListener('input', e => document.getElementById('preview-nome').innerText = e.target.value || '--');
    document.getElementById('input-variacao').addEventListener('input', e => {
        state.variation_name = e.target.value || null;
        document.getElementById('preview-variacao').innerText = e.target.value || 'Shape padrao';
    });
    document.getElementById('input-base-fret').addEventListener('change', e => {
        state.baseFret = Number(e.target.value || 1);
        renderAll();
    });
    document.getElementById('btnClear').addEventListener('click', () => {
        state.positions = [];
        state.barres = [];
        state.topMarkers = [null, null, null, null, null, null];
        renderAll();
    });
    document.getElementById('btnAddPestana').addEventListener('click', promptPestana);
    document.getElementById('acordeForm').addEventListener('submit', () => {
        document.getElementById('final-json').value = JSON.stringify(state);
    });
}

function renderAll() {
    const gridGroup = document.getElementById('grid-layer');
    const marksGroup = document.getElementById('marks-layer');
    if (!gridGroup || !marksGroup) return;

    let gridSVG = '';
    let marksSVG = '';

    if (Number(state.baseFret) <= 1) {
        gridSVG += `<rect x="${CONFIG.startX}" y="${CONFIG.startY-6}" width="${CONFIG.width}" height="6" class="nut-rect" />`;
    } else {
        gridSVG += `<text x="${CONFIG.startX-10}" y="${CONFIG.startY+25}" text-anchor="end" fill="#6b7280" font-weight="bold" font-size="18">${state.baseFret}a</text>`;
        gridSVG += `<line x1="${CONFIG.startX}" y1="${CONFIG.startY}" x2="${CONFIG.startX+CONFIG.width}" y2="${CONFIG.startY}" class="fret-line" />`;
    }

    for (let i = 1; i <= CONFIG.numFrets; i++) {
        let y = CONFIG.startY + (i * fretGap);
        gridSVG += `<line x1="${CONFIG.startX}" y1="${y}" x2="${CONFIG.startX+CONFIG.width}" y2="${y}" class="fret-line" />`;
    }

    for (let i = 0; i < CONFIG.numStrings; i++) {
        let x = CONFIG.startX + (i * stringGap);
        let thickness = 0.8 + ((5 - i) * 0.5);
        gridSVG += `<line x1="${x}" y1="${CONFIG.startY}" x2="${x}" y2="${CONFIG.startY+CONFIG.height}" stroke="#d1d5db" stroke-width="${thickness}" opacity="0.8" />`;
    }

    gridGroup.innerHTML = gridSVG;

    state.topMarkers.forEach((status, i) => {
        let x = CONFIG.startX + (i * stringGap);
        let y = CONFIG.startY - 15;
        if (status === 'muted') marksSVG += `<text x="${x}" y="${y+6}" class="marker-x">X</text>`;
        else if (status === 'open') marksSVG += `<circle cx="${x}" cy="${y}" r="5" class="marker-o" />`;
    });

    state.barres.forEach(barre => {
        let y = CONFIG.startY + (barre.fret * fretGap) - (fretGap / 2);
        let x1 = CONFIG.startX + ((6 - barre.fromString) * stringGap);
        let x2 = CONFIG.startX + ((6 - barre.toString) * stringGap);
        marksSVG += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" class="barre-path" />`;
    });

    state.positions.forEach(pos => {
        let y = CONFIG.startY + (pos.fret * fretGap) - (fretGap / 2);
        let x = CONFIG.startX + ((6 - pos.string) * stringGap);
        marksSVG += `<circle cx="${x}" cy="${y}" r="12" class="finger-dot" />`;
        if (pos.finger) marksSVG += `<text x="${x}" y="${y+1}" class="finger-number">${pos.finger}</text>`;
    });

    marksGroup.innerHTML = marksSVG;
}

window.handleGridClick = function(e) {
    const svg = document.getElementById('acordeSVG');
    const rect = svg.getBoundingClientRect();
    const scaleX = 240 / rect.width;
    const scaleY = 300 / rect.height;
    const mouseX = (e.clientX - rect.left) * scaleX;
    const mouseY = (e.clientY - rect.top) * scaleY;

    let colIndex = Math.round((mouseX - CONFIG.startX) / stringGap);
    colIndex = Math.max(0, Math.min(5, colIndex));

    if (mouseY < CONFIG.startY) {
        toggleTopMarker(colIndex);
    } else {
        let fretIndex = Math.ceil((mouseY - CONFIG.startY) / fretGap);
        fretIndex = Math.max(1, Math.min(CONFIG.numFrets, fretIndex));
        let stringNum = 6 - colIndex;

        const clickedBarreIdx = state.barres.findIndex(b => b.fret === fretIndex && stringNum <= b.fromString && stringNum >= b.toString);
        if (clickedBarreIdx !== -1) {
            state.barres.splice(clickedBarreIdx, 1);
            renderAll();
            return;
        }

        addFinger(stringNum, fretIndex);
    }
};

function toggleTopMarker(colIndex) {
    const current = state.topMarkers[colIndex];
    if (current === null) state.topMarkers[colIndex] = 'open';
    else if (current === 'open') state.topMarkers[colIndex] = 'muted';
    else state.topMarkers[colIndex] = null;

    let stringNum = 6 - colIndex;
    state.positions = state.positions.filter(p => p.string !== stringNum);
    renderAll();
}

function addFinger(string, fret) {
    const existingIdx = state.positions.findIndex(p => p.string === string && p.fret === fret);
    if (existingIdx !== -1) {
        state.positions.splice(existingIdx, 1);
        renderAll();
        return;
    }

    let colIndex = 6 - string;
    state.topMarkers[colIndex] = null;
    state.positions = state.positions.filter(p => p.string !== string);

    Swal.fire({
        title: 'Qual dedo?',
        input: 'select',
        inputOptions: {'1': '1 (Indicador)', '2': '2 (Medio)', '3': '3 (Anelar)', '4': '4 (Minguinho)'},
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Colocar dedo',
        confirmButtonColor: '#ea580c',
    }).then(result => {
        if (result.isConfirmed) {
            state.positions.push({ string: string, fret: fret, finger: result.value });
            renderAll();
        }
    });
}

function promptPestana() {
    Swal.fire({
        title: 'Adicionar pestana',
        html: `
            <div class="flex flex-col gap-4 text-left text-sm mt-4">
                <div>
                    <label class="font-bold text-gray-700">Em qual casa?</label>
                    <select id="swal-fret" class="w-full border rounded-lg p-3 bg-gray-50 mt-1">
                        <option value="1">Casa 1</option>
                        <option value="2">Casa 2</option>
                        <option value="3">Casa 3</option>
                        <option value="4">Casa 4</option>
                        <option value="5">Casa 5</option>
                    </select>
                </div>
                <div class="flex gap-4">
                    <div class="w-1/2">
                        <label class="font-bold text-gray-700">Da corda...</label>
                        <select id="swal-from" class="w-full border rounded-lg p-3 bg-gray-50 mt-1">
                            <option value="6">6a</option>
                            <option value="5">5a</option>
                            <option value="4">4a</option>
                            <option value="3">3a</option>
                            <option value="2">2a</option>
                        </select>
                    </div>
                    <div class="w-1/2">
                        <label class="font-bold text-gray-700">Ate a corda...</label>
                        <select id="swal-to" class="w-full border rounded-lg p-3 bg-gray-50 mt-1">
                            <option value="1">1a</option>
                            <option value="2">2a</option>
                            <option value="3">3a</option>
                            <option value="4">4a</option>
                            <option value="5">5a</option>
                        </select>
                    </div>
                </div>
            </div>`,
        confirmButtonText: 'Criar pestana',
        confirmButtonColor: '#ea580c',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        preConfirm: () => {
            return {
                fret: parseInt(document.getElementById('swal-fret').value),
                from: parseInt(document.getElementById('swal-from').value),
                to: parseInt(document.getElementById('swal-to').value)
            }
        }
    }).then(res => {
        if (res.isConfirmed) {
            state.barres.push({
                fret: res.value.fret,
                fromString: Math.max(res.value.from, res.value.to),
                toString: Math.min(res.value.from, res.value.to)
            });
            renderAll();
        }
    });
}

function limparDedos() {
    state.positions = [];
    renderAll();
}
</script>
@endpush
