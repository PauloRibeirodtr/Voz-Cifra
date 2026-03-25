@extends('admin.layouts.admin')

@section('title', 'Visualizar versao musical | Voz & Cifra')
@section('mobile_title', 'Versao musical')

@push('styles')
    <style>
        .aba-modo.ativa { background: #166534; color: #fff; box-shadow: 0 8px 18px rgba(22, 101, 52, 0.18); }
        .painel-modo.hidden { display: none; }
        .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.15rem; margin-bottom: 0.65rem; }
        .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 3.5rem; }
        .cifra-acordes { min-height: 1.4rem; margin-bottom: 0.1rem; color: #f97316; font-weight: 800; font-size: 0.95rem; line-height: 1.1rem; letter-spacing: 0.01em; white-space: pre; }
        .cifra-acorde { display: inline-block; cursor: pointer; padding: 0 0.05rem; border-radius: 0.35rem; transition: background-color 0.15s ease, color 0.15s ease; }
        .cifra-acorde:hover, .cifra-acorde.ativa { background: rgba(249, 115, 22, 0.14); color: #c2410c; }
        .cifra-letra { color: #111827; font-size: 1.08rem; line-height: 1.85rem; white-space: pre-wrap; }
        .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #e5e7eb; color: #374151; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
        .preview-musico-scroll { max-height: 68vh; overflow-y: auto; scroll-behavior: smooth; }
        .preview-fiel p { margin-bottom: 0.95rem; color: #1f2937; font-size: 1.03rem; line-height: 1.95rem; }
        .preview-fiel .marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #eef2ff; color: #4338ca; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1.25rem 0 0.8rem; }
        .acorde-mini-card.ativo { border-color: #f97316; background: #fff7ed; color: #9a3412; }
        .diagrama-acorde svg { width: 100%; height: auto; max-width: 240px; }
        .tooltip-acorde { position: fixed; z-index: 80; width: 240px; pointer-events: none; border-radius: 1rem; border: 1px solid #fed7aa; background: rgba(255,255,255,0.98); box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16); padding: 0.85rem; backdrop-filter: blur(8px); }
        .tooltip-acorde.hidden { display: none; }
        .tooltip-acorde svg { width: 100%; height: auto; }
        .variacao-acorde.ativa { background: #166534; color: #fff; border-color: #166534; }
        .painel-musico-topo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .controle-pill { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 9999px; border: 1px solid #d1fae5; background: #ffffff; color: #047857; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.85rem; }
        .video-musico-compacto { max-width: 420px; width: 100%; margin-left: auto; }
        .controle-estudo { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; border-radius: 1rem; border: 1px solid #e5e7eb; background: #fff; padding: 1rem; }
        .bpm-box { display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 0.85rem; overflow: hidden; background: #fff; }
        .bpm-box button { width: 2.2rem; height: 2.2rem; font-weight: 800; color: #374151; background: #f9fafb; }
        .bpm-box input { width: 4rem; text-align: center; border: 0; outline: none; font-weight: 700; color: #111827; }
        .musico-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        @media (min-width: 1280px) {
            .painel-musico-topo { grid-template-columns: minmax(0, 1fr) 380px; align-items: start; }
            .musico-layout { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 767px) {
            .preview-musico-scroll { max-height: none; }
            .cifra-acordes { font-size: 0.88rem; }
            .cifra-letra { font-size: 1rem; line-height: 1.75rem; }
            .tooltip-acorde { width: 180px; padding: 0.65rem; border-radius: 0.9rem; }
            .tooltip-acorde svg { max-width: 140px; margin: 0 auto; display: block; }
        }
    </style>
@endpush

@section('content')
    @php
        $linhasSemCifra = preg_split('/\r\n|\r|\n/', $letraSemCifras) ?: [];
        $blocosSemCifra = [];
        $paragrafoAtual = [];

        foreach ($linhasSemCifra as $linhaSemCifra) {
            $linhaLimpa = trim($linhaSemCifra);

            if ($linhaLimpa === '') {
                if ($paragrafoAtual !== []) {
                    $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual)];
                    $paragrafoAtual = [];
                }

                continue;
            }

            if (preg_match('/^\[(.+)\]$/u', $linhaLimpa, $matches) === 1) {
                if ($paragrafoAtual !== []) {
                    $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual)];
                    $paragrafoAtual = [];
                }

                $blocosSemCifra[] = ['tipo' => 'marcacao', 'texto' => $matches[1]];
                continue;
            }

            $paragrafoAtual[] = $linhaLimpa;
        }

        if ($paragrafoAtual !== []) {
            $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual)];
        }
    @endphp

    @if (session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 text-sm rounded">
            {{ session('info') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 p-4 mb-6 text-sm rounded">
            {{ session('warning') }}
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-gray-800">{{ $versaoMusical->titulo ?: 'Versao principal' }}</h1>
            <p class="text-sm text-gray-500">Musica base: {{ $musica->titulo }}</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex lg:flex-wrap lg:justify-end">
            <a href="{{ route('admin.versoes-musicais.edit', [$musica, $versaoMusical]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Editar
            </a>
            <a href="{{ route('admin.musicas.show', $musica) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Voltar para a musica
            </a>
        </div>
    </div>

    <div class="mb-6 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Modos de visualizacao</h2>
                <p class="text-sm text-gray-500">Compare como a mesma versao aparece para administracao, leitura pratica do musico e leitura sem cifras.</p>
            </div>

            <div class="inline-flex w-full flex-wrap gap-2 rounded-2xl border border-gray-200 bg-gray-50 p-2 sm:w-auto">
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 transition" data-modo="admin">Previa admin</button>
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 transition" data-modo="musico">Previa musico</button>
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 transition" data-modo="fiel">Previa sem cifra</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-6">
            <section data-painel-modo="admin" class="painel-modo space-y-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Visao do admin master</h2>
                            <p class="text-sm text-gray-500">Conferencia completa da versao com cifras, dados tecnicos e resultado salvo.</p>
                        </div>

                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $versaoMusical->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $versaoMusical->ativo ? 'Versao ativa' : 'Versao inativa' }}
                        </span>
                    </div>

                    <pre class="whitespace-pre-wrap break-words rounded-xl bg-gray-900 text-green-200 p-5 text-sm leading-7">{{ $versaoMusical->letra_com_cifras }}</pre>
                </div>

                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="text-base font-bold text-gray-800 mb-4">Resumo tecnico</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tom musical</span><span>{{ $versaoMusical->tom_musical ?: '-' }}</span></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">BPM</span><span>{{ $versaoMusical->bpm ?: '-' }}</span></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">YouTube</span><span>{{ $versaoMusical->youtube_video_id ?: '-' }}</span></div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-4"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Criado por</span><span>{{ $versaoMusical->criadoPor?->nome ?: '-' }}</span></div>
                    </div>
                </div>
            </section>

            <section data-painel-modo="musico" class="painel-modo hidden space-y-6">
                <div class="bg-gradient-to-br from-white via-white to-green-50 p-6 rounded-2xl shadow-sm border border-green-100">
                    <div class="painel-musico-topo">
                        <div>
                            <h2 class="text-xl font-black text-gray-900">Visao do musico</h2>
                            <p class="mt-1 text-sm text-gray-500">Modo de estudo com leitura mais limpa, apoio de video, auto rolagem e metronomo ajustavel.</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($versaoMusical->tom_musical)
                                    <span class="controle-pill">Tom: {{ $versaoMusical->tom_musical }}</span>
                                @endif
                                <span class="controle-pill">BPM inicial: {{ $versaoMusical->bpm ?: '-' }}</span>
                                <span class="controle-pill">Voz &amp; Cifra</span>
                            </div>
                        </div>

                        @if ($versaoMusical->youtube_video_id)
                            <div class="video-musico-compacto rounded-2xl overflow-hidden border border-gray-200 bg-black shadow-sm">
                                <div class="aspect-video">
                                    <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $versaoMusical->youtube_video_id }}" title="Video de apoio" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 controle-estudo">
                        <button type="button" id="toggle_autorrolagem" class="rounded-lg bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">Iniciar auto rolagem</button>

                        <div class="flex items-center gap-3">
                            <label for="velocidade_rolagem" class="text-sm font-medium text-gray-600">Velocidade</label>
                            <input id="velocidade_rolagem" type="range" min="1" max="12" value="4" class="accent-green-700" />
                            <span id="valor_velocidade" class="text-sm font-semibold text-gray-700">4</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-600">BPM</span>
                            <div class="bpm-box">
                                <button type="button" id="diminuir_bpm">-</button>
                                <input type="number" id="controle_bpm" min="20" max="240" value="{{ $versaoMusical->bpm ?: 72 }}">
                                <button type="button" id="aumentar_bpm">+</button>
                            </div>
                        </div>

                        <button type="button" id="toggle_metronomo" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Iniciar metronomo</button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="preview-musico-scroll p-6" id="preview_musico_container">
                        <div id="preview_musico_render" class="space-y-2"></div>
                    </div>
                </div>
            </section>

            <section data-painel-modo="fiel" class="painel-modo hidden">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="mb-6">
                        <h2 class="text-xl font-black text-gray-900">Letra</h2>
                        <p class="text-sm text-gray-500">Versao limpa para leitura, removendo apenas os acordes e preservando marcacoes relevantes do texto.</p>
                    </div>

                    <div class="preview-fiel">
                        @foreach ($blocosSemCifra as $blocoSemCifra)
                            @if ($blocoSemCifra['tipo'] === 'marcacao')
                                <div class="marcacao">{{ $blocoSemCifra['texto'] }}</div>
                            @else
                                <p>{{ $blocoSemCifra['texto'] }}</p>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        </div>

        <div class="xl:col-span-4 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da versao</h2>
                <div class="space-y-4 text-sm text-gray-600">
                    <div><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tom musical</span><span>{{ $versaoMusical->tom_musical ?: '-' }}</span></div>
                    <div><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">BPM</span><span>{{ $versaoMusical->bpm ?: '-' }}</span></div>
                    <div><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">YouTube video ID</span><span>{{ $versaoMusical->youtube_video_id ?: '-' }}</span></div>
                    <div class="dado-admin-only"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Criado por</span><span>{{ $versaoMusical->criadoPor?->nome ?: '-' }}</span></div>
                    <div class="dado-admin-only"><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Status</span><span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $versaoMusical->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $versaoMusical->ativo ? 'Ativa' : 'Inativa' }}</span></div>
                    <div><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Acordes encontrados</span><span>{{ $acordesEncontrados !== [] ? implode(', ', $acordesEncontrados) : '-' }}</span></div>
                    <div><span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Acordes nao encontrados</span><span class="{{ $acordesInvalidos !== [] ? 'text-amber-700 font-semibold' : '' }}">{{ $acordesInvalidos !== [] ? implode(', ', $acordesInvalidos) : 'Nenhum' }}</span></div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Dicionario de acordes</h2>
                        <p class="text-sm text-gray-500">Passe o mouse ou clique num acorde da previa do musico para ver o shape.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <div class="diagrama-acorde flex justify-center" id="painel_diagrama_acorde"></div>
                    <div class="mt-4 text-center">
                        <div id="nome_acorde_ativo" class="text-lg font-black text-gray-800">Nenhum acorde selecionado</div>
                        <p id="descricao_acorde_ativo" class="mt-1 text-sm text-gray-500">Selecione um acorde para visualizar o desenho.</p>
                    </div>
                    <div id="variacoes_acorde" class="mt-4 flex flex-wrap justify-center gap-2"></div>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @php
                        $acordesAgrupados = [];
                        foreach ($acordesDaVersao as $acordeDaVersao) {
                            $acordesAgrupados[$acordeDaVersao['nome']][] = $acordeDaVersao;
                        }
                    @endphp

                    @forelse ($acordesAgrupados as $nomeAcorde => $variacoesAcorde)
                        <button type="button" class="acorde-mini-card rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:border-orange-300 hover:bg-orange-50" data-acorde-card="{{ $nomeAcorde }}">
                            {{ $nomeAcorde }}
                            @if (count($variacoesAcorde) > 1)
                                <span class="ml-2 rounded-full bg-orange-100 px-2 py-0.5 text-[10px] font-bold text-orange-700">{{ count($variacoesAcorde) }}x</span>
                            @endif
                        </button>
                    @empty
                        <p class="text-sm text-gray-500">Nenhum acorde encontrado na biblioteca para esta versao.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Acoes</h2>
                <div class="space-y-3">
                    <a href="{{ route('admin.versoes-musicais.edit', [$musica, $versaoMusical]) }}" class="block w-full text-center px-4 py-3 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-800">Editar versao</a>
                    <form action="{{ route('admin.versoes-musicais.destroy', [$musica, $versaoMusical]) }}" method="POST" onsubmit="return confirm('Deseja excluir esta versao musical?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">Excluir versao</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="tooltip_acorde" class="tooltip-acorde hidden">
        <div class="text-center">
            <div id="tooltip_acorde_nome" class="text-sm font-black text-gray-800">Acorde</div>
            <div id="tooltip_acorde_diagrama" class="mt-3 diagrama-acorde"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const abas = document.querySelectorAll('.aba-modo');
            const paineis = document.querySelectorAll('.painel-modo');
            const previewMusicoRender = document.getElementById('preview_musico_render');
            const containerMusico = document.getElementById('preview_musico_container');
            const textoComCifras = @json($versaoMusical->letra_com_cifras);
            const acordesDaVersao = @json($acordesDaVersao);
            const botaoRolagem = document.getElementById('toggle_autorrolagem');
            const controleVelocidade = document.getElementById('velocidade_rolagem');
            const valorVelocidade = document.getElementById('valor_velocidade');
            const botaoMetronomo = document.getElementById('toggle_metronomo');
            const controleBpm = document.getElementById('controle_bpm');
            const botaoDiminuirBpm = document.getElementById('diminuir_bpm');
            const botaoAumentarBpm = document.getElementById('aumentar_bpm');
            const bpmInicial = Number(@json($versaoMusical->bpm ?: 72));
            let rolagemAtiva = false;
            let intervaloRolagem = null;
            let intervaloMetronomo = null;
            let contextoAudio = null;
            let bpmAtual = bpmInicial;

            const gruposAcorde = acordesDaVersao.reduce((grupos, acorde) => {
                if (!grupos[acorde.nome]) {
                    grupos[acorde.nome] = [];
                }

                grupos[acorde.nome].push(acorde);
                return grupos;
            }, {});
            const ehAcorde = (texto) => /^[A-G](?:#|b)?(?:[a-zA-Z0-9º°+\-]*(?:\([^)]+\))?)?(?:\/[A-G](?:#|b)?)?$/.test((texto || '').trim());
            const escaparHtml = (texto) => (texto || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            const ativarModo = (modo) => {
                abas.forEach((aba) => aba.classList.toggle('ativa', aba.dataset.modo === modo));
                paineis.forEach((painel) => painel.classList.toggle('hidden', painel.dataset.painelModo !== modo));
                document.querySelectorAll('.dado-admin-only').forEach((elemento) => {
                    elemento.classList.toggle('hidden', modo !== 'admin');
                });
            };

            abas.forEach((aba) => aba.addEventListener('click', () => ativarModo(aba.dataset.modo)));

            const renderizarPreviewMusico = () => {
                if (!previewMusicoRender) return;
                const linhas = (textoComCifras || '').replace(/\r\n/g, '\n').replace(/\r/g, '\n').split('\n');
                previewMusicoRender.innerHTML = linhas.map((linha) => {
                    const linhaLimpa = linha.trim();
                    if (linhaLimpa === '') return '<div class="h-4"></div>';
                    const marcacao = linhaLimpa.match(/^\[(.+)\]$/u);
                    if (marcacao && !ehAcorde(marcacao[1])) return `<div class="cifra-marcacao">${escaparHtml(marcacao[1])}</div>`;

                    const regex = /\[([^\[\]\r\n]+)\]/g;
                    let ultimoIndice = 0;
                    let acordesPendentes = [];
                    let match;
                    let segmentos = '';

                    while ((match = regex.exec(linha)) !== null) {
                        const textoAntes = linha.slice(ultimoIndice, match.index);
                        if (textoAntes !== '') {
                            segmentos += `<span class="cifra-segmento"><span class="cifra-acordes">${acordesPendentes.map((acorde) => `<span class="cifra-acorde" data-acorde-hover="${escaparHtml(acorde)}">${escaparHtml(acorde)}</span>`).join(' ')}</span><span class="cifra-letra">${escaparHtml(textoAntes)}</span></span>`;
                            acordesPendentes = [];
                        }
                        if (ehAcorde(match[1])) acordesPendentes.push(match[1].trim());
                        else segmentos += `<span class="cifra-segmento"><span class="cifra-acordes"></span><span class="cifra-letra">${escaparHtml(match[0])}</span></span>`;
                        ultimoIndice = regex.lastIndex;
                    }

                    const textoFinal = linha.slice(ultimoIndice);
                    if (textoFinal !== '' || acordesPendentes.length > 0) {
                        segmentos += `<span class="cifra-segmento"><span class="cifra-acordes">${acordesPendentes.map((acorde) => `<span class="cifra-acorde" data-acorde-hover="${escaparHtml(acorde)}">${escaparHtml(acorde)}</span>`).join(' ')}</span><span class="cifra-letra">${escaparHtml(textoFinal || ' ')}</span></span>`;
                    }

                    return `<div class="cifra-linha">${segmentos}</div>`;
                }).join('');
            };

            const renderizarDiagrama = (shape) => {
                if (!shape) return '<div class="text-sm text-gray-400">Sem desenho disponivel.</div>';
                const config = { startX: 30, startY: 40, width: 180, height: 240, numStrings: 6, numFrets: 5 };
                const stringGap = config.width / (config.numStrings - 1);
                const fretGap = config.height / config.numFrets;
                const baseFret = shape.baseFret || 1;
                const positions = shape.positions || [];
                const barres = shape.barres || [];
                const topMarkers = shape.topMarkers || [null, null, null, null, null, null];
                let grid = '';
                let marks = '';
                if (baseFret === 1) grid += `<rect x="${config.startX}" y="${config.startY - 6}" width="${config.width}" height="6" rx="2" fill="#e5e7eb" />`;
                else {
                    grid += `<text x="${config.startX - 10}" y="${config.startY + 25}" text-anchor="end" fill="#6b7280" font-weight="bold" font-size="18">${baseFret}a</text>`;
                    grid += `<line x1="${config.startX}" y1="${config.startY}" x2="${config.startX + config.width}" y2="${config.startY}" stroke="#9ca3af" stroke-width="2" />`;
                }
                for (let i = 1; i <= config.numFrets; i++) {
                    const y = config.startY + (i * fretGap);
                    grid += `<line x1="${config.startX}" y1="${y}" x2="${config.startX + config.width}" y2="${y}" stroke="#9ca3af" stroke-width="2" />`;
                }
                for (let i = 0; i < config.numStrings; i++) {
                    const x = config.startX + (i * stringGap);
                    const thickness = 0.8 + ((5 - i) * 0.5);
                    grid += `<line x1="${x}" y1="${config.startY}" x2="${x}" y2="${config.startY + config.height}" stroke="#d1d5db" stroke-width="${thickness}" />`;
                }
                topMarkers.forEach((marker, i) => {
                    const x = config.startX + (i * stringGap);
                    const y = config.startY - 15;
                    if (marker === 'muted') marks += `<text x="${x}" y="${y + 5}" fill="#ef4444" font-size="18" font-weight="900" text-anchor="middle">X</text>`;
                    else if (marker === 'open') marks += `<circle cx="${x}" cy="${y}" r="5" stroke="#2563eb" stroke-width="2.5" fill="none" />`;
                });
                barres.forEach((barre) => {
                    const y = config.startY + (barre.fret * fretGap) - (fretGap / 2);
                    const x1 = config.startX + ((6 - barre.fromString) * stringGap);
                    const x2 = config.startX + ((6 - barre.toString) * stringGap);
                    marks += `<line x1="${x1}" y1="${y}" x2="${x2}" y2="${y}" stroke="#ea580c" stroke-width="14" stroke-linecap="round" opacity="0.95" />`;
                });
                positions.forEach((position) => {
                    const y = config.startY + (position.fret * fretGap) - (fretGap / 2);
                    const x = config.startX + ((6 - position.string) * stringGap);
                    marks += `<circle cx="${x}" cy="${y}" r="12" fill="#ea580c" />`;
                    if (position.finger) marks += `<text x="${x}" y="${y + 1}" fill="white" font-size="14" font-weight="800" text-anchor="middle" dominant-baseline="central">${position.finger}</text>`;
                });
                return `<svg viewBox="0 0 240 300" aria-label="Diagrama do acorde"><rect x="30" y="40" width="180" height="240" rx="4" fill="#2e1a12" stroke="#1a0f0a" stroke-width="2"></rect>${grid}${marks}</svg>`;
            };

            const painelDiagrama = document.getElementById('painel_diagrama_acorde');
            const nomeAcordeAtivo = document.getElementById('nome_acorde_ativo');
            const descricaoAcordeAtivo = document.getElementById('descricao_acorde_ativo');
            const variacoesAcorde = document.getElementById('variacoes_acorde');
            const tooltipAcorde = document.getElementById('tooltip_acorde');
            const tooltipAcordeNome = document.getElementById('tooltip_acorde_nome');
            const tooltipAcordeDiagrama = document.getElementById('tooltip_acorde_diagrama');

            const preencherVariacoes = (nome, indiceAtivo = 0) => {
                const variacoes = gruposAcorde[nome] || [];

                if (!variacoesAcorde) {
                    return;
                }

                if (variacoes.length <= 1) {
                    variacoesAcorde.innerHTML = '';
                    return;
                }

                variacoesAcorde.innerHTML = variacoes.map((variacao, indice) => `
                    <button
                        type="button"
                        class="variacao-acorde rounded-lg border border-gray-200 bg-white px-3 py-1 text-xs font-semibold text-gray-700"
                        data-variacao-acorde="${nome}"
                        data-variacao-indice="${indice}"
                    >
                        ${variacao.descricao ? escaparHtml(variacao.descricao) : `Variacao ${indice + 1}`}
                    </button>
                `).join('');

                variacoesAcorde.querySelectorAll('[data-variacao-acorde]').forEach((botao) => {
                    botao.classList.toggle('ativa', Number(botao.dataset.variacaoIndice) === indiceAtivo);
                    botao.addEventListener('click', () => ativarAcorde(nome, Number(botao.dataset.variacaoIndice)));
                });
            };

            const mostrarTooltipAcorde = (nome, x, y, indice = 0) => {
                const acorde = (gruposAcorde[nome] || [])[indice] || null;

                if (!tooltipAcorde || !tooltipAcordeNome || !tooltipAcordeDiagrama || !acorde) {
                    return;
                }

                tooltipAcordeNome.textContent = acorde.nome;
                tooltipAcordeDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                tooltipAcorde.classList.remove('hidden');
                const larguraTooltip = window.innerWidth <= 767 ? 180 : 240;
                const offsetVertical = window.innerWidth <= 767 ? 165 : 220;
                tooltipAcorde.style.left = `${Math.max(12, Math.min(x + 14, window.innerWidth - larguraTooltip - 12))}px`;
                tooltipAcorde.style.top = `${Math.max(y - offsetVertical, 12)}px`;
            };

            const ocultarTooltipAcorde = () => {
                tooltipAcorde?.classList.add('hidden');
            };

            const ativarAcorde = (nome, indice = 0) => {
                const acorde = (gruposAcorde[nome] || [])[indice] || null;
                document.querySelectorAll('[data-acorde-hover], [data-acorde-card]').forEach((elemento) => {
                    const ativo = (elemento.dataset.acordeHover || elemento.dataset.acordeCard) === nome;
                    elemento.classList.toggle('ativa', ativo);
                });
                if (!acorde) {
                    if (painelDiagrama) painelDiagrama.innerHTML = '<div class="text-sm text-gray-400">Sem desenho disponivel.</div>';
                    if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = nome || 'Nenhum acorde selecionado';
                    if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = 'Esse acorde nao possui desenho cadastrado na biblioteca.';
                    return;
                }
                if (painelDiagrama) painelDiagrama.innerHTML = renderizarDiagrama(acorde.shape);
                if (nomeAcordeAtivo) nomeAcordeAtivo.textContent = acorde.nome;
                if (descricaoAcordeAtivo) descricaoAcordeAtivo.textContent = acorde.descricao || 'Shape salvo na biblioteca de acordes.';
                preencherVariacoes(nome, indice);
            };

            const iniciarAutoRolagem = () => {
                if (!containerMusico) return;
                const velocidade = Number(controleVelocidade?.value || 4);
                if (valorVelocidade) {
                    valorVelocidade.textContent = String(velocidade);
                }
                intervaloRolagem = window.setInterval(() => {
                    containerMusico.scrollTop += velocidade * 0.6;
                    if (containerMusico.scrollTop + containerMusico.clientHeight >= containerMusico.scrollHeight) pararAutoRolagem();
                }, 60);
            };

            const pararAutoRolagem = () => {
                if (intervaloRolagem) {
                    window.clearInterval(intervaloRolagem);
                    intervaloRolagem = null;
                }
                rolagemAtiva = false;
                if (botaoRolagem) botaoRolagem.textContent = 'Iniciar auto rolagem';
            };

            botaoRolagem?.addEventListener('click', () => {
                if (rolagemAtiva) return pararAutoRolagem();
                rolagemAtiva = true;
                botaoRolagem.textContent = 'Parar auto rolagem';
                iniciarAutoRolagem();
            });

            controleVelocidade?.addEventListener('input', () => {
                if (valorVelocidade) {
                    valorVelocidade.textContent = controleVelocidade.value;
                }

                if (rolagemAtiva) {
                    window.clearInterval(intervaloRolagem);
                    iniciarAutoRolagem();
                }
            });

            const tocarPulso = () => {
                try {
                    contextoAudio = contextoAudio || new (window.AudioContext || window.webkitAudioContext)();
                    const oscilador = contextoAudio.createOscillator();
                    const ganho = contextoAudio.createGain();
                    oscilador.type = 'square';
                    oscilador.frequency.value = 880;
                    ganho.gain.setValueAtTime(0.0001, contextoAudio.currentTime);
                    ganho.gain.exponentialRampToValueAtTime(0.08, contextoAudio.currentTime + 0.01);
                    ganho.gain.exponentialRampToValueAtTime(0.0001, contextoAudio.currentTime + 0.12);
                    oscilador.connect(ganho);
                    ganho.connect(contextoAudio.destination);
                    oscilador.start();
                    oscilador.stop(contextoAudio.currentTime + 0.13);
                } catch (error) {
                    console.error(error);
                }
            };

            botaoMetronomo?.addEventListener('click', () => {
                if (intervaloMetronomo) {
                    window.clearInterval(intervaloMetronomo);
                    intervaloMetronomo = null;
                    botaoMetronomo.textContent = 'Iniciar metronomo';
                    return;
                }
                const bpm = Number(bpmAtual || 0);
                if (!bpm) {
                    botaoMetronomo.textContent = 'BPM nao informado';
                    window.setTimeout(() => { botaoMetronomo.textContent = 'Iniciar metronomo'; }, 1600);
                    return;
                }
                tocarPulso();
                intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpm));
                botaoMetronomo.textContent = 'Parar metronomo';
            });

            const atualizarBpm = (novoBpm) => {
                bpmAtual = Math.max(20, Math.min(240, Number(novoBpm) || 72));

                if (controleBpm) {
                    controleBpm.value = String(bpmAtual);
                }

                if (intervaloMetronomo) {
                    window.clearInterval(intervaloMetronomo);
                    intervaloMetronomo = window.setInterval(tocarPulso, Math.round(60000 / bpmAtual));
                }
            };

            botaoDiminuirBpm?.addEventListener('click', () => atualizarBpm(bpmAtual - 1));
            botaoAumentarBpm?.addEventListener('click', () => atualizarBpm(bpmAtual + 1));
            controleBpm?.addEventListener('input', () => atualizarBpm(controleBpm.value));

            renderizarPreviewMusico();
            atualizarBpm(bpmInicial);

            if (valorVelocidade && controleVelocidade) {
                valorVelocidade.textContent = controleVelocidade.value;
            }

            document.addEventListener('mouseover', (event) => {
                const acorde = event.target.closest('[data-acorde-hover]');
                if (acorde) {
                    ativarAcorde(acorde.dataset.acordeHover);
                    mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
                }
            });

            document.addEventListener('mousemove', (event) => {
                const acorde = event.target.closest('[data-acorde-hover]');

                if (!acorde) {
                    return;
                }

                mostrarTooltipAcorde(acorde.dataset.acordeHover, event.clientX, event.clientY);
            });

            document.addEventListener('mouseout', (event) => {
                if (event.target.closest('[data-acorde-hover]')) {
                    ocultarTooltipAcorde();
                }
            });

            document.addEventListener('click', (event) => {
                const acorde = event.target.closest('[data-acorde-hover], [data-acorde-card]');
                if (acorde) ativarAcorde(acorde.dataset.acordeHover || acorde.dataset.acordeCard);
            });

            if (acordesDaVersao.length > 0) ativarAcorde(acordesDaVersao[0].nome);
            ativarModo('admin');
        });
    </script>
@endpush
