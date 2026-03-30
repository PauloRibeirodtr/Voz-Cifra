@extends('member.layouts.app')

@section('title', ($versaoMusical->titulo ?: 'Versao musical') . ' | Voz & Cifra')
@section('mobile_title', 'Estudo da cifra')
@section('desktop_subtitle', 'Leitura musical para estudo, video e apoio')

@section('header_actions')
    <a href="{{ route('member.colecoes.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Playlists salvas
    </a>
    <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Voltar para biblioteca
    </a>
@endsection

@push('styles')
    @include('partials.cifra-viewer-styles')
    <style>
        .study-shell { display: grid; gap: 1.5rem; grid-template-columns: minmax(0, 1fr); }
        .study-panel { border-radius: 1.75rem; border: 1px solid rgba(148,163,184,.15); background: linear-gradient(180deg,#050816 0%,#0f172a 100%); color: #f8fafc; box-shadow: 0 20px 45px rgba(2,6,23,.28); }
        .study-surface { border-radius: 1.4rem; border: 1px solid rgba(148,163,184,.16); background: rgba(15,23,42,.88); }
        .study-control { display:inline-flex; align-items:center; justify-content:center; min-height:2.75rem; min-width:2.75rem; border-radius:1rem; border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.06); color:#fff; font-weight:700; transition:.2s ease; }
        .study-control:hover { background: rgba(255,255,255,.12); }
        .study-preview { max-height: 68vh; overflow-y: auto; }
        .diagrama-acorde svg, .tooltip-acorde svg { width:100%; height:auto; max-width:240px; }
        .tooltip-acorde { position:fixed; z-index:80; width:220px; pointer-events:none; border-radius:1rem; border:1px solid rgba(16,185,129,.35); background:rgba(15,23,42,.96); box-shadow:0 18px 50px rgba(2,6,23,.28); padding:.85rem; backdrop-filter:blur(8px); }
        .tooltip-acorde.hidden { display:none; }
        .playlist-card { border-radius:1.15rem; border:1px solid rgba(148,163,184,.15); background:rgba(15,23,42,.82); }
        .study-action-button { display:inline-flex; align-items:center; gap:.6rem; border-radius:1rem; border:1px solid rgba(16,185,129,.28); background:rgba(16,185,129,.14); color:#d1fae5; padding:.85rem 1rem; font-weight:700; transition:.2s ease; }
        .study-action-button:hover { background:rgba(16,185,129,.22); }
        .playlist-modal-backdrop { position:fixed; inset:0; background:rgba(2,6,23,.72); backdrop-filter:blur(3px); z-index:90; }
        .playlist-modal { position:fixed; inset:0; z-index:91; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .playlist-modal.hidden, .playlist-modal-backdrop.hidden { display:none; }
        .playlist-modal-card { width:min(100%, 42rem); max-height:min(88vh, 900px); overflow:auto; border-radius:1.5rem; border:1px solid rgba(148,163,184,.18); background:#0f172a; color:#f8fafc; box-shadow:0 24px 60px rgba(2,6,23,.35); }
        .playlist-existing-item { border-radius:1rem; border:1px solid rgba(148,163,184,.15); background:rgba(255,255,255,.04); }
        @media (min-width:1280px) { .study-shell { grid-template-columns: minmax(0, 1.2fr) 23rem; } }
        @media (max-width:767px) { .study-preview { max-height:none; } }
    </style>
@endpush

@section('content')
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

    <section class="study-panel px-6 py-6 lg:px-7">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="min-w-0">
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Modo estudo</p>
                <h1 class="mt-2 text-3xl font-black text-white">{{ $musica->titulo }}</h1>
                <p class="mt-2 text-sm text-emerald-100">{{ $versaoMusical->titulo ?: 'Versao principal' }} @if ($missaAtiva) â€¢ Missa ativa: {{ $missaAtiva->titulo }} @endif</p>
            </div>
            <div class="grid grid-cols-2 gap-3 sm:flex">
                <button type="button" id="abrir_modal_playlist" class="study-action-button px-4 text-sm">
                    <i class="fa-solid fa-plus"></i>
                    <span>Adicionar a playlist</span>
                </button>
                <a href="{{ route('member.repertorio') }}" class="study-control px-4 text-sm">Meu repertorio</a>
                <a href="{{ route('member.dashboard') }}" class="study-control px-4 text-sm">Painel</a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="study-surface p-4"><span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Tom exibido</span><span id="tom_atual_badge" class="mt-2 block text-2xl font-black text-white">{{ $tomExibicao ?: 'Nao informado' }}</span></div>
            <div class="study-surface p-4"><span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Tom original</span><span class="mt-2 block text-2xl font-black text-white">{{ $tomOriginal ?: 'Nao informado' }}</span></div>
            <div class="study-surface p-4"><span class="block text-xs font-black uppercase tracking-wider text-emerald-200">BPM</span><span class="mt-2 block text-2xl font-black text-white">{{ $versaoMusical->bpm ?: '-' }}</span></div>
            <div class="study-surface p-4"><span class="block text-xs font-black uppercase tracking-wider text-emerald-200">Contexto</span><span class="mt-2 block text-sm font-bold text-white">{{ $itemMissa ? 'Versao usada na missa da sua igreja' : 'Estudo livre da biblioteca musical' }}</span></div>
        </div>
    </section>

    <div class="mt-6 study-shell">
        <section class="space-y-6">
            <div class="rounded-[1.75rem] border border-gray-100 bg-white p-5 shadow-sm">
                <div class="grid grid-cols-1 gap-5 xl:grid-cols-[minmax(0,1fr)_340px]">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($itemMissa && $itemMissa->tom_usado)
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Tom da missa {{ $itemMissa->tom_usado }}</span>
                            @endif
                            @if ($tomOriginal)
                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tom original {{ $tomOriginal }}</span>
                            @endif
                            @if ($versaoMusical->bpm)
                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">BPM {{ $versaoMusical->bpm }}</span>
                            @endif
                        </div>

                        <div class="rounded-3xl border border-gray-100 bg-gray-50 p-4">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button" id="toggle_autorrolagem" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Iniciar auto rolagem</button>
                                    <div class="flex items-center gap-3">
                                        <label for="velocidade_rolagem" class="text-sm font-medium text-gray-600">Velocidade</label>
                                        <input id="velocidade_rolagem" type="range" min="0.25" max="6" value="1" step="0.25" class="accent-emerald-700">
                                        <span id="valor_velocidade" class="min-w-[2.5rem] text-sm font-semibold text-gray-700">1.00</span>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="button" id="toggle_metronomo" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">Iniciar metronomo</button>
                                    <div class="inline-flex items-center overflow-hidden rounded-xl border border-gray-200 bg-white">
                                        <button type="button" id="diminuir_bpm" class="h-11 w-11 text-lg font-bold text-gray-700 hover:bg-gray-50">-</button>
                                        <input id="controle_bpm" type="number" min="20" max="240" value="{{ $versaoMusical->bpm ?: 72 }}" class="w-20 border-0 text-center text-base font-bold text-gray-800 focus:ring-0">
                                        <button type="button" id="aumentar_bpm" class="h-11 w-11 text-lg font-bold text-gray-700 hover:bg-gray-50">+</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 flex flex-wrap items-center gap-3">
                                <span class="inline-flex rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700" id="indicador_tom_atual">Tom atual: {{ $tomExibicao ?: 'Nao informado' }}</span>
                                <button type="button" data-transpose="-1" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Tom -</button>
                                <button type="button" data-transpose-reset class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Tom original</button>
                                <button type="button" data-transpose="1" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Tom +</button>
                                <button type="button" data-font="-1" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">A-</button>
                                <button type="button" data-font-reset class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">Fonte padrao</button>
                                <button type="button" data-font="1" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">A+</button>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if ($versaoMusical->youtube_video_id)
                            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm">
                                <div class="aspect-video bg-slate-950">
                                    <iframe class="h-full w-full" src="https://www.youtube.com/embed/{{ $versaoMusical->youtube_video_id }}" title="Video de apoio" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>
                                <div class="border-t border-gray-100 px-4 py-3 text-sm text-gray-500">Video de apoio sincronizado com o estudo da musica.</div>
                            </div>
                        @else
                            <div class="rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-5 text-sm text-gray-500">Esta versao ainda nao possui video do YouTube vinculado.</div>
                        @endif
                    </div>
                </div>
            </div>

            <section class="study-panel p-5">
                <div class="study-preview rounded-[1.5rem] border border-white/5 bg-[#172138] p-6 text-emerald-100 shadow-inner" id="preview_musico_container">
                    <div id="letra_com_cifras_preview" class="space-y-2"></div>
                </div>
            </section>
        </section>

        <aside class="space-y-6">
            <section class="study-panel p-5">
                <h2 class="text-lg font-bold text-white">Dicionario de acordes</h2>
                <p class="mt-1 text-sm text-slate-300">Passe o mouse ou toque num acorde da cifra para ver o shape correspondente.</p>
                <div class="mt-4 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                    <div class="diagrama-acorde flex justify-center" id="painel_diagrama_acorde"></div>
                    <div class="mt-4 text-center"><div id="nome_acorde_ativo" class="text-lg font-black text-white">Nenhum acorde selecionado</div><p id="descricao_acorde_ativo" class="mt-1 text-sm text-slate-300">Selecione um acorde para visualizar o desenho.</p></div>
                    <div id="variacoes_acorde" class="mt-4 flex flex-wrap justify-center gap-2"></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2" id="lista_acordes_transpostos">
                    @foreach ($acordesDaVersao as $acorde)
                        <button type="button" class="study-control px-3 text-sm" data-acorde-card="{{ $acorde }}">{{ $acorde }}</button>
                    @endforeach
                </div>
            </section>
        </aside>
    </div>

    <div id="tooltip_acorde" class="tooltip-acorde hidden"><div class="text-center"><div id="tooltip_acorde_nome" class="text-sm font-black text-white">Acorde</div><div id="tooltip_acorde_diagrama" class="mt-3 diagrama-acorde"></div></div></div>
    <div id="playlist_modal_backdrop" class="playlist-modal-backdrop hidden"></div>
    <div id="playlist_modal" class="playlist-modal hidden" aria-hidden="true">
        <div class="playlist-modal-card p-5 sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-300">Playlist</p>
                    <h2 class="mt-2 text-2xl font-black text-white">Adicionar "{{ $musica->titulo }}"</h2>
                    <p class="mt-2 text-sm text-slate-300">Escolha uma playlist existente ou crie uma nova sem sair da tela de estudo.</p>
                </div>
                <button type="button" id="fechar_modal_playlist" class="study-control" aria-label="Fechar modal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                <section class="playlist-card p-4">
                    <h3 class="text-base font-bold text-white">Criar nova playlist</h3>
                    <p class="mt-1 text-sm text-slate-300">Perfeito para separar por ensaio, missa ou repertorio pessoal.</p>
                    <form action="{{ route('member.colecoes.store') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                        <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                        <input type="text" name="nome" class="block w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-emerald-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20" placeholder="Ex.: Ensaio de quarta" required>
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-500">
                            <i class="fa-solid fa-folder-plus mr-2"></i>
                            Criar e adicionar
                        </button>
                    </form>
                </section>

                <section class="playlist-card p-4">
                    <h3 class="text-base font-bold text-white">Adicionar em playlist existente</h3>
                    <p class="mt-1 text-sm text-slate-300">Toque numa playlist para incluir esta versao rapidamente.</p>
                    <div class="mt-4 space-y-3">
                        @forelse ($colecoes as $colecao)
                            <form action="{{ route('member.colecoes.itens.store', $colecao) }}" method="POST" class="playlist-existing-item flex items-center gap-3 px-3 py-3">
                                @csrf
                                <input type="hidden" name="musica_id" value="{{ $musica->id }}">
                                <input type="hidden" name="versao_musical_id" value="{{ $versaoMusical->id }}">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-white">{{ $colecao->nome }}</p>
                                    <p class="text-xs text-slate-400">{{ $colecao->itens_count }} itens</p>
                                </div>
                                @if ($colecaoIdsComVersao->contains($colecao->id))
                                    <span class="inline-flex items-center justify-center rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-3 py-2 text-xs font-semibold text-emerald-300">Ja adicionada</span>
                                @else
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-white/10 bg-white/10 px-3 py-2 text-xs font-semibold text-white hover:bg-white/20">
                                        <i class="fa-solid fa-plus mr-2"></i>
                                        Adicionar
                                    </button>
                                @endif
                            </form>
                        @empty
                            <div class="rounded-xl border border-dashed border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                                Nenhuma playlist criada ainda. Use o formulario ao lado para criar a primeira.
                            </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('partials.chord-transposer-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const helper = window.VozECifraChord;
            const preview = document.getElementById('letra_com_cifras_preview');
            const previewContainer = document.getElementById('preview_musico_container');
            const tomBadge = document.getElementById('tom_atual_badge');
            const tomIndicador = document.getElementById('indicador_tom_atual');
            const textoOriginal = @json($textoCifraExibicao, JSON_UNESCAPED_UNICODE);
            const tomBase = @json($tomExibicao);
            const bibliotecaAcordes = @json($bibliotecaAcordes);
            const bpmInicial = Number(@json($versaoMusical->bpm ?: 72));
            const gruposAcorde = helper ? helper.buildChordGroups(bibliotecaAcordes) : null;
            const painelDiagrama = document.getElementById('painel_diagrama_acorde');
            const nomeAcordeAtivo = document.getElementById('nome_acorde_ativo');
            const descricaoAcordeAtivo = document.getElementById('descricao_acorde_ativo');
            const variacoesAcorde = document.getElementById('variacoes_acorde');
            const tooltipAcorde = document.getElementById('tooltip_acorde');
            const tooltipAcordeNome = document.getElementById('tooltip_acorde_nome');
            const tooltipAcordeDiagrama = document.getElementById('tooltip_acorde_diagrama');
            const listaAcordes = document.getElementById('lista_acordes_transpostos');
            const botaoRolagem = document.getElementById('toggle_autorrolagem');
            const controleVelocidade = document.getElementById('velocidade_rolagem');
            const valorVelocidade = document.getElementById('valor_velocidade');
            const botaoMetronomo = document.getElementById('toggle_metronomo');
            const controleBpm = document.getElementById('controle_bpm');
            const botaoDiminuirBpm = document.getElementById('diminuir_bpm');
            const botaoAumentarBpm = document.getElementById('aumentar_bpm');
            const modalPlaylist = document.getElementById('playlist_modal');
            const modalPlaylistBackdrop = document.getElementById('playlist_modal_backdrop');
            const abrirModalPlaylist = document.getElementById('abrir_modal_playlist');
            const fecharModalPlaylist = document.getElementById('fechar_modal_playlist');
            let transposicaoAtual = 0;
            let fonteAtual = 18;
            let rolagemAtiva = false;
            let intervaloRolagem = null;
            let intervaloMetronomo = null;
            let contextoAudio = null;
            let bpmAtual = bpmInicial;
            if (!preview || !helper || !previewContainer) return;

            const renderizarDiagrama = (shape) => {
                if (!shape) return '<div class="text-sm text-slate-300">Sem desenho disponivel.</div>';
                const config = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
                const stringGap = config.width / (config.numStrings - 1);
                const fretGap = config.height / config.numFrets;
                const baseFret = shape.baseFret || 1;
                const positions = shape.positions || [];
                const barres = shape.barres || [];
                const topMarkers = shape.topMarkers || [null, null, null, null, null, null];
                let grid = '';
                let marks = '';
                if (baseFret === 1) { grid += `<rect x="${config.startX}" y="${config.startY - 6}" width="${config.width}" height="6" rx="2" fill="#e5e7eb" />`; }
                else { grid += `<text x="${config.startX - 10}" y="${config.startY + 25}" text-anchor="end" fill="#94a3b8" font-weight="bold" font-size="18">${baseFret}a</text>`; grid += `<line x1="${config.startX}" y1="${config.startY}" x2="${config.startX + config.width}" y2="${config.startY}" stroke="#94a3b8" stroke-width="2" />`; }
                for (let i = 1; i <= config.numFrets; i++) { const y = config.startY + (i * fretGap); grid += `<line x1="${config.startX}" y1="${y}" x2="${config.startX + config.width}" y2="${y}" stroke="#cbd5e1" stroke-width="2" />`; }
                for (let i = 0; i < config.numStrings; i++) { const x = config.startX + (i * stringGap); const thickness = 0.8 + ((5 - i) * 0.5); grid += `<line x1="${x}" y1="${config.startY}" x2="${x}" y2="${config.startY + config.height}" stroke="#e2e8f0" stroke-width="${thickness}" />`; }
                topMarkers.forEach((marker, i) => { const x = config.startX + (i * stringGap); const y = config.startY - 15; if (marker === 'muted') marks += `<text x="${x}" y="${y + 5}" fill="#ef4444" font-size="18" font-weight="900" text-anchor="middle">X</text>`; else if (marker === 'open') marks += `<circle cx="${x}" cy="${y}" r="5" stroke="#2563eb" stroke-width="2.5" fill="none" />`; });
                barres.forEach((barre) => { const y = config.startY + (barre.fret * fretGap) - (fretGap / 2); const x1 = config.startX + ((6 - barre.fromString) * stringGap); const x2 = config.startX + ((6 - barre.toString) * stringGap); marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" stroke="#f97316" stroke-width="14" stroke-linecap="round" opacity="0.95" />`; });
                positions.forEach((position) => { const y = config.startY + (position.fret * fretGap) - (fretGap / 2); const x = config.startX + ((6 - position.string) * stringGap); marks += `<circle cx="${x}" cy="${y}" r="12" fill="#f97316" />`; if (position.finger) { marks += `<text x="${x}" y="${y + 1}" fill="white" font-size="14" font-weight="800" text-anchor="middle" dominant-baseline="central">${position.finger}</text>`; } });
                return `<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#3b2418" stroke="#1f130d" stroke-width="2"></rect>${grid}${marks}</svg>`;
            };

            const preencherVariacoes = (nome, indiceAtivo = 0) => {
                const variacoes = helper.getChordMatches(gruposAcorde, nome);
                if (!variacoesAcorde) return;
                if (variacoes.length <= 1) { variacoesAcorde.innerHTML = ''; return; }
                variacoesAcorde.innerHTML = variacoes.map((variacao, indice) => `<button type="button" class="study-control px-3 text-xs ${indice === indiceAtivo ? 'bg-emerald-600 text-white' : ''}" data-variacao-acorde="${helper.escapeHtml(nome)}" data-variacao-indice="${indice}">${variacao.descricao ? helper.escapeHtml(variacao.descricao) : `Variacao ${indice + 1}`}</button>`).join('');
                variacoesAcorde.querySelectorAll('[data-variacao-acorde]').forEach((botao) => { botao.addEventListener('click', () => ativarAcorde(nome, Number(botao.dataset.variacaoIndice))); });
            };

            const ativarAcorde = (nome, indice = 0) => {
                const acorde = helper.getChordMatches(gruposAcorde, nome)[indice] || null;
                const assinaturaAtual = helper.getChordSignature(nome);
                document.querySelectorAll('[data-acorde-hover], [data-acorde-card]').forEach((elemento) => {
                    const valorElemento = elemento.dataset.acordeHover || elemento.dataset.acordeCard;
                    const assinaturaElemento = helper.getChordSignature(valorElemento);
                    const ativo = valorElemento === nome || (assinaturaElemento && assinaturaAtual && assinaturaElemento === assinaturaAtual);
                    elemento.classList.toggle('ring-2', ativo);
                    elemento.classList.toggle('ring-emerald-400', ativo);
                });
                if (!acorde) { if (painelDiagrama) painelDiagrama.innerHTML = '<div class="text-sm text-slate-300">Sem desenho disponivel.</div>'; if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = nome || 'Nenhum acorde selecionado'; if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = 'Esse acorde nao possui desenho cadastrado na biblioteca.'; return; }
                if (painelDiagrama) painelDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = nome;
                if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = acorde.descricao || 'Shape salvo na biblioteca de acordes.';
                preencherVariacoes(nome, indice);
            };

            const mostrarTooltipAcorde = (nome, x, y) => {
                const acorde = helper.getChordMatches(gruposAcorde, nome)[0] || null;
                if (!tooltipAcorde || !tooltipAcordeNome || !tooltipAcordeDiagrama || !acorde) return;
                tooltipAcordeNome.textContent = nome;
                tooltipAcordeDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                tooltipAcorde.classList.remove('hidden');
                tooltipAcorde.style.left = `${Math.max(12, Math.min(x + 14, window.innerWidth - 240))}px`;
                tooltipAcorde.style.top = `${Math.max(y - 220, 12)}px`;
            };

            const renderizarListaAcordes = (textoTransposto) => {
                if (!listaAcordes) return;
                const acordesAtuais = helper.extractChordsFromBracketedText(textoTransposto);
                listaAcordes.innerHTML = acordesAtuais.map((acorde) => `<button type="button" class="study-control px-3 text-sm" data-acorde-card="${helper.escapeHtml(acorde)}">${helper.escapeHtml(acorde)}</button>`).join('');
            };

            const atualizarTomBadge = () => {
                const valorAtual = !tomBase || !helper.isChord(tomBase) ? 'Nao informado' : helper.transposeChord(tomBase, transposicaoAtual);
                if (tomBadge) tomBadge.textContent = valorAtual;
                if (tomIndicador) tomIndicador.textContent = `Tom atual: ${valorAtual}`;
            };

            const renderizar = () => {
                const textoTransposto = helper.transposeBracketedText(textoOriginal, transposicaoAtual);
                preview.innerHTML = helper.renderChordSheetHtml(textoTransposto, { chordAttribute: 'data-acorde-hover' });
                preview.style.setProperty('--escala-fonte', String(fonteAtual / 18));
                renderizarListaAcordes(textoTransposto);
                atualizarTomBadge();
            };

            const pararRolagem = () => { if (intervaloRolagem) { window.clearInterval(intervaloRolagem); intervaloRolagem = null; } rolagemAtiva = false; if (botaoRolagem) botaoRolagem.textContent = 'Iniciar auto rolagem'; };
            const formatarVelocidade = (valor) => Number(valor || 0).toFixed(2);
            const iniciarRolagem = () => { const velocidade = Number(controleVelocidade?.value || 1); if (valorVelocidade) valorVelocidade.textContent = formatarVelocidade(velocidade); intervaloRolagem = window.setInterval(() => { previewContainer.scrollTop += velocidade * 0.18; if (previewContainer.scrollTop + previewContainer.clientHeight >= previewContainer.scrollHeight) pararRolagem(); }, 60); };
            const tocarPulso = () => { try { contextoAudio = contextoAudio || new (window.AudioContext || window.webkitAudioContext)(); const oscilador = contextoAudio.createOscillator(); const ganho = contextoAudio.createGain(); oscilador.type = 'square'; oscilador.frequency.value = 880; ganho.gain.setValueAtTime(0.0001, contextoAudio.currentTime); ganho.gain.exponentialRampToValueAtTime(0.08, contextoAudio.currentTime + 0.01); ganho.gain.exponentialRampToValueAtTime(0.0001, contextoAudio.currentTime + 0.12); oscilador.connect(ganho); ganho.connect(contextoAudio.destination); oscilador.start(); oscilador.stop(contextoAudio.currentTime + 0.13); } catch (error) { console.error(error); } };
            const atualizarBpm = (novoBpm) => { bpmAtual = Math.max(20, Math.min(240, Number(novoBpm) || 72)); if (controleBpm) controleBpm.value = String(bpmAtual); if (intervaloMetronomo) { window.clearInterval(intervaloMetronomo); intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpmAtual)); } };
            const abrirModal = () => { modalPlaylist?.classList.remove('hidden'); modalPlaylistBackdrop?.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); };
            const fecharModal = () => { modalPlaylist?.classList.add('hidden'); modalPlaylistBackdrop?.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); };

            document.querySelectorAll('[data-transpose]').forEach((botao) => { botao.addEventListener('click', () => { transposicaoAtual += Number(botao.dataset.transpose || 0); renderizar(); }); });
            document.querySelector('[data-transpose-reset]')?.addEventListener('click', () => { transposicaoAtual = 0; renderizar(); });
            document.querySelectorAll('[data-font]').forEach((botao) => { botao.addEventListener('click', () => { fonteAtual = Math.min(32, Math.max(14, fonteAtual + (Number(botao.dataset.font || 0) * 2))); renderizar(); }); });
            document.querySelector('[data-font-reset]')?.addEventListener('click', () => { fonteAtual = 18; renderizar(); });
            botaoRolagem?.addEventListener('click', () => { if (rolagemAtiva) { pararRolagem(); return; } rolagemAtiva = true; botaoRolagem.textContent = 'Parar auto rolagem'; iniciarRolagem(); });
            controleVelocidade?.addEventListener('input', () => { if (valorVelocidade) valorVelocidade.textContent = formatarVelocidade(controleVelocidade.value); if (rolagemAtiva) { window.clearInterval(intervaloRolagem); iniciarRolagem(); } });
            botaoMetronomo?.addEventListener('click', () => { if (intervaloMetronomo) { window.clearInterval(intervaloMetronomo); intervaloMetronomo = null; botaoMetronomo.textContent = 'Iniciar metronomo'; return; } tocarPulso(); intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpmAtual)); botaoMetronomo.textContent = 'Parar metronomo'; });
            botaoDiminuirBpm?.addEventListener('click', () => atualizarBpm(bpmAtual - 1));
            botaoAumentarBpm?.addEventListener('click', () => atualizarBpm(bpmAtual + 1));
            controleBpm?.addEventListener('input', () => atualizarBpm(controleBpm.value));
            abrirModalPlaylist?.addEventListener('click', abrirModal);
            fecharModalPlaylist?.addEventListener('click', fecharModal);
            modalPlaylistBackdrop?.addEventListener('click', fecharModal);
            document.addEventListener('mouseover', (event) => { const acorde = event.target.closest('[data-acorde-hover]'); if (!acorde) return; ativarAcorde(acorde.dataset.acordeHover); mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY); });
            document.addEventListener('mousemove', (event) => { const acorde = event.target.closest('[data-acorde-hover]'); if (!acorde) return; mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY); });
            document.addEventListener('mouseout', (event) => { if (event.target.closest('[data-acorde-hover]')) tooltipAcorde?.classList.add('hidden'); });
            document.addEventListener('click', (event) => { const acorde = event.target.closest('[data-acorde-hover], [data-acorde-card]'); if (acorde) ativarAcorde(acorde.dataset.acordeHover || acorde.dataset.acordeCard); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') fecharModal(); });
            atualizarBpm(bpmInicial);
            if (valorVelocidade && controleVelocidade) valorVelocidade.textContent = formatarVelocidade(controleVelocidade.value);
            renderizar();
        });
    </script>
@endpush
