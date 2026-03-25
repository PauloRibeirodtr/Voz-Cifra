@extends('admin.layouts.admin')

@section('title', 'Novo acorde | Voz & Cifra')
@section('mobile_title', 'Novo acorde')

@push('styles')
<style>
    .nut-rect { fill: #e5e7eb; opacity: 0.9; }
    .fret-line { stroke: #9ca3af; stroke-width: 2; }
    .string-line { stroke: #d1d5db; opacity: 0.8; }
    .finger-dot { fill: #ea580c; filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3)); transition: all 0.2s; }
    .finger-number { fill: white; font-size: 14px; font-weight: 800; text-anchor: middle; dominant-baseline: central; }
    .barre-path { stroke: #ea580c; stroke-width: 14; stroke-linecap: round; opacity: 0.95; }
    .marker-x { fill: #ef4444; font-weight: 900; font-size: 18px; text-anchor: middle; cursor: pointer; }
    .marker-o { stroke: #3b82f6; stroke-width: 2.5; fill: none; cursor: pointer; }
    .click-area-top { fill: transparent; cursor: pointer; }
    svg { cursor: pointer; }
</style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <div class="bg-white w-full rounded-[2rem] shadow-lg overflow-hidden">
                <div class="p-6 md:p-10">
                    <div class="mb-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-orange-700 text-xs font-bold uppercase tracking-wide mb-3">
                            <i class="fa-solid fa-pen-nib"></i> Editor visual
                        </div>
                        <h2 class="text-3xl font-black text-gray-800 tracking-tight">Novo acorde</h2>
                        <p class="text-gray-400 text-sm mt-1">Preencha os dados e desenhe o shape ao lado.</p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-sm text-red-700">
                            <p class="font-bold">Nao foi possivel salvar o acorde.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $erro)
                                    <li>{{ $erro }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="acordeForm" action="{{ route('admin.acordes.store') }}" method="POST" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1 ml-1">Nome do acorde *</label>
                            <input type="text" id="input-nome" name="nome" value="{{ old('nome') }}" placeholder="Ex.: C#m7(9)" required class="w-full bg-gray-50 border {{ $errors->has('nome') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-xl px-4 py-3 text-xl font-bold text-gray-800 placeholder-gray-300 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            @error('nome')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1 ml-1">Descricao visual</label>
                            <input type="text" id="input-variacao" value="{{ old('descricao') }}" placeholder="Ex.: Shape de E" class="w-full bg-gray-50 border {{ $errors->has('descricao') ? 'border-red-400 ring-2 ring-red-100' : 'border-gray-200' }} rounded-xl px-4 py-3 font-medium text-gray-600 placeholder-gray-300 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            @error('descricao')
                                <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1 ml-1">Casa inicial do shape</label>
                            <select id="input-base-fret" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 font-bold text-gray-700 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                @for ($casa = 1; $casa <= 12; $casa++)
                                    <option value="{{ $casa }}">{{ $casa }}° casa</option>
                                @endfor
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Use quando o desenho do acorde comecar acima da primeira casa. O braco continua mostrando 5 trastes, mas passa a representar a partir da casa escolhida.</p>
                        </div>

                        <input type="hidden" id="final-json" name="shape">

                        <div class="pt-6 flex flex-col gap-3">
                            <button type="button" id="btnAddPestana" class="group w-full py-3.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl font-bold shadow-lg transition-all flex items-center justify-center gap-2">
                                <i class="fa-solid fa-grip-lines-vertical"></i> Adicionar pestana
                            </button>

                            <button type="button" id="btnClear" class="w-full py-3.5 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl font-bold transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-eraser"></i> Limpar desenho
                            </button>

                            <div class="h-px bg-gray-100 my-2"></div>

                            <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white font-black text-lg rounded-xl shadow-xl transition-all flex items-center justify-center gap-3">
                                <span>Salvar acorde</span> <i class="fa-solid fa-check"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] shadow-lg sticky top-8 border border-gray-100 flex flex-col items-center">
                <div class="text-center mb-6 w-full">
                    <h1 id="preview-nome" class="text-6xl font-black text-blue-600 font-serif leading-none tracking-tight mb-2">--</h1>
                    <p id="preview-variacao" class="text-gray-400 italic font-serif text-lg">Shape...</p>
                </div>

                <div class="bg-orange-50 text-orange-600 text-xs font-bold px-4 py-2 rounded-lg mb-6 text-center w-full max-w-[280px]">
                    <i class="fa-solid fa-lightbulb"></i> Clique acima do braco para alternar entre corda solta (O) e mutada (X).
                </div>

                <div class="relative filter drop-shadow-2xl">
                    <svg id="acordeSVG" width="280" height="340" viewBox="0 0 240 300" class="select-none touch-none">
                        <defs>
                            <linearGradient id="woodGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" style="stop-color:#2e1a12;stop-opacity:1" />
                                <stop offset="100%" style="stop-color:#1a0f0a;stop-opacity:1" />
                            </linearGradient>
                        </defs>
                        <rect x="20" y="0" width="200" height="40" class="click-area-top" />
                        <rect x="30" y="40" width="180" height="240" rx="4" fill="url(#woodGradient)" stroke="#1a0f0a" stroke-width="2"/>
                        <g id="grid-layer"></g>
                        <g id="marks-layer"></g>
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

let state = { baseFret: 1, variation_name: null, positions: [], barres: [], topMarkers: [null, null, null, null, null, null] };

document.addEventListener('DOMContentLoaded', () => {
    state.baseFret = Number(@json(old('base_fret', 1))) || 1;

    document.getElementById('input-nome')?.addEventListener('input', e => {
        document.getElementById('preview-nome').innerText = e.target.value || '--';
    });

    document.getElementById('input-variacao')?.addEventListener('input', e => {
        state.variation_name = e.target.value || null;
        document.getElementById('preview-variacao').innerText = e.target.value || 'Shape...';
    });

    document.getElementById('input-base-fret')?.addEventListener('change', e => {
        state.baseFret = Number(e.target.value || 1);
        renderAll();
    });

    if (document.getElementById('input-base-fret')) {
        document.getElementById('input-base-fret').value = String(state.baseFret);
    }

    document.getElementById('acordeSVG')?.addEventListener('click', handleGridClick);
    document.getElementById('btnClear')?.addEventListener('click', () => {
        state.positions = [];
        state.barres = [];
        state.topMarkers = [null, null, null, null, null, null];
        renderAll();
    });
    document.getElementById('btnAddPestana')?.addEventListener('click', promptPestana);
    document.getElementById('acordeForm')?.addEventListener('submit', () => {
        document.getElementById('final-json').value = JSON.stringify(state);
    });

    const valorInicialNome = document.getElementById('input-nome')?.value;
    const valorInicialVariacao = document.getElementById('input-variacao')?.value;

    if (valorInicialNome) {
        document.getElementById('preview-nome').innerText = valorInicialNome;
    }

    if (valorInicialVariacao) {
        state.variation_name = valorInicialVariacao;
        document.getElementById('preview-variacao').innerText = valorInicialVariacao;
    }

    renderAll();
});

function renderAll() {
    const gridGroup = document.getElementById('grid-layer');
    const marksGroup = document.getElementById('marks-layer');
    if (!gridGroup || !marksGroup) return;

    let gridSVG = '';
    let marksSVG = '';

    if (Number(state.baseFret) <= 1) {
        gridSVG += `<rect x="${CONFIG.startX}" y="${CONFIG.startY - 6}" width="${CONFIG.width}" height="6" class="nut-rect" />`;
    } else {
        gridSVG += `<text x="${CONFIG.startX - 12}" y="${CONFIG.startY + 25}" text-anchor="end" fill="#6b7280" font-weight="900" font-size="18">${state.baseFret}a</text>`;
        gridSVG += `<line x1="${CONFIG.startX}" y1="${CONFIG.startY}" x2="${CONFIG.startX + CONFIG.width}" y2="${CONFIG.startY}" class="fret-line" />`;
    }

    for (let i = 1; i <= CONFIG.numFrets; i++) {
        let y = CONFIG.startY + (i * fretGap);
        gridSVG += `<line x1="${CONFIG.startX}" y1="${y}" x2="${CONFIG.startX + CONFIG.width}" y2="${y}" class="fret-line" />`;
    }

    for (let i = 0; i < CONFIG.numStrings; i++) {
        let x = CONFIG.startX + (i * stringGap);
        let thickness = 0.8 + ((5 - i) * 0.5);
        gridSVG += `<line x1="${x}" y1="${CONFIG.startY}" x2="${x}" y2="${CONFIG.startY + CONFIG.height}" class="string-line" stroke-width="${thickness}" />`;
    }

    gridGroup.innerHTML = gridSVG;

    state.topMarkers.forEach((status, i) => {
        let x = CONFIG.startX + (i * stringGap);
        let y = CONFIG.startY - 15;
        if (status === 'muted') marksSVG += `<text x="${x}" y="${y + 5}" class="marker-x">X</text>`;
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
        if (pos.finger) marksSVG += `<text x="${x}" y="${y + 1}" class="finger-number">${pos.finger}</text>`;
    });

    marksGroup.innerHTML = marksSVG;
}

window.handleGridClick = function(e) {
    const svg = document.getElementById('acordeSVG');
    if (!svg) return;

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
        toggleFinger(stringNum, fretIndex);
    }
}

function toggleTopMarker(colIndex) {
    const current = state.topMarkers[colIndex];
    if (current === null) state.topMarkers[colIndex] = 'open';
    else if (current === 'open') state.topMarkers[colIndex] = 'muted';
    else state.topMarkers[colIndex] = null;

    state.positions = state.positions.filter(p => p.string !== (6 - colIndex));
    renderAll();
}

function toggleFinger(string, fret) {
    const existingIdx = state.positions.findIndex(p => p.string === string && p.fret === fret);
    if (existingIdx !== -1) {
        state.positions.splice(existingIdx, 1);
        renderAll();
        return;
    }

    window.fingerVal = null;

    Swal.fire({
        title: 'Qual dedo?',
        html: `
            <div class="flex justify-center gap-3 mt-2">
                <button onclick="window.fingerVal='1'; Swal.clickConfirm();" class="w-12 h-12 rounded-full bg-orange-600 text-white font-bold text-xl hover:bg-orange-700 transition">1</button>
                <button onclick="window.fingerVal='2'; Swal.clickConfirm();" class="w-12 h-12 rounded-full bg-orange-600 text-white font-bold text-xl hover:bg-orange-700 transition">2</button>
                <button onclick="window.fingerVal='3'; Swal.clickConfirm();" class="w-12 h-12 rounded-full bg-orange-600 text-white font-bold text-xl hover:bg-orange-700 transition">3</button>
                <button onclick="window.fingerVal='4'; Swal.clickConfirm();" class="w-12 h-12 rounded-full bg-orange-600 text-white font-bold text-xl hover:bg-orange-700 transition">4</button>
            </div>
        `,
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        width: 300,
        padding: '1.5em'
    }).then((res) => {
        if (res.isConfirmed && window.fingerVal) {
            state.topMarkers[6 - string] = null;
            state.positions = state.positions.filter(p => p.string !== string);
            state.positions.push({ string: string, fret: fret, finger: window.fingerVal });
            renderAll();
        }
    });
}

function promptPestana() {
    Swal.fire({
        title: '<span class="text-gray-700 font-bold text-xl">Configurar pestana</span>',
        html: `
            <div class="flex flex-col gap-4 text-left pt-2">
                <div>
                    <label class="text-xs font-bold text-gray-400 uppercase">Em qual casa?</label>
                    <select id="swal-fret" class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg p-3 text-gray-700 font-bold">
                        <option value="1">Casa 1</option>
                        <option value="2">Casa 2</option>
                        <option value="3">Casa 3</option>
                        <option value="4">Casa 4</option>
                        <option value="5">Casa 5</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <div class="w-1/2">
                        <label class="text-xs font-bold text-gray-400 uppercase">Da corda:</label>
                        <select id="swal-from" class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm">
                            <option value="6">6 (Mizao)</option>
                            <option value="5">5 (La)</option>
                            <option value="4">4 (Re)</option>
                            <option value="3">3 (Sol)</option>
                            <option value="2">2 (Si)</option>
                        </select>
                    </div>
                    <div class="w-1/2">
                        <label class="text-xs font-bold text-gray-400 uppercase">Ate a corda:</label>
                        <select id="swal-to" class="w-full mt-1 bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm">
                            <option value="1">1 (Mizinha)</option>
                            <option value="2">2 (Si)</option>
                            <option value="3">3 (Sol)</option>
                            <option value="4">4 (Re)</option>
                            <option value="5">5 (La)</option>
                        </select>
                    </div>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Aplicar pestana',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ea580c',
        cancelButtonColor: '#9ca3af',
        reverseButtons: true,
        focusConfirm: false,
        preConfirm: () => {
            return {
                fret: parseInt(document.getElementById('swal-fret').value),
                from: parseInt(document.getElementById('swal-from').value),
                to: parseInt(document.getElementById('swal-to').value)
            }
        }
    }).then((res) => {
        if (res.isConfirmed) {
            state.barres.push({ fret: res.value.fret, fromString: Math.max(res.value.from, res.value.to), toString: Math.min(res.value.from, res.value.to) });
            renderAll();
        }
    });
}
</script>
@endpush
