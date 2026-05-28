@extends('admin.layouts.admin')

@section('title', 'Visualizar versao musical | Voz & Cifra')
@section('mobile_title', 'Versao musical')

@push('styles')
    <style>
        .abas-modo { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; }
        .aba-modo { display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; min-height: 2.75rem; border: 1px solid #e5e7eb; background: #fff; color: #4b5563; }
        .aba-modo.ativa { background: #166534; border-color: #166534; color: #fff; box-shadow: 0 8px 18px rgba(22, 101, 52, 0.18); }
        .aba-modo__status { display: none; border-radius: 9999px; background: rgba(255, 255, 255, 0.18); padding: 0.12rem 0.45rem; font-size: 0.68rem; font-weight: 900; text-transform: uppercase; }
        .aba-modo.ativa .aba-modo__status { display: inline-flex; }
        .painel-modo.hidden { display: none; }
        .preview-admin-box { --admin-escala-fonte: 1; }
        .preview-musico-scroll { --escala-fonte: 1; max-height: 82vh; min-height: 58vh; overflow-y: auto; scroll-behavior: smooth; background: linear-gradient(180deg, #ffffff, #fffdf9); }
        .preview-fiel { --escala-fonte: 1; }
        .cifra-linha { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 0.18rem; margin-bottom: 0.72rem; }
        .cifra-linha--refrao { border-left: 4px solid #f59e0b; background: linear-gradient(90deg, rgba(255, 251, 235, 0.9), rgba(255, 251, 235, 0)); margin: 0.18rem 0 0.74rem; padding: 0.42rem 0 0.42rem 0.85rem; border-radius: 0; }
        .cifra-linha--refrao .cifra-letra { font-weight: 800; color: #111827; }
        .cifra-segmento { display: inline-flex; flex-direction: column; align-items: flex-start; justify-content: flex-end; min-height: 2.85rem; }
        .cifra-acordes { min-height: 1.1rem; margin-bottom: 0.02rem; color: #f97316; font-weight: 800; font-size: calc(0.95rem * var(--escala-fonte)); line-height: calc(1rem * var(--escala-fonte)); letter-spacing: 0.01em; white-space: pre; }
        .cifra-acorde { display: inline-block; cursor: pointer; padding: 0 0.05rem; border-radius: 0.35rem; transition: background-color 0.15s ease, color 0.15s ease; }
        .cifra-acorde:hover, .cifra-acorde.ativa { background: rgba(249, 115, 22, 0.14); color: #c2410c; }
        .cifra-letra { color: #111827; font-size: calc(1.12rem * var(--escala-fonte)); line-height: calc(1.9rem * var(--escala-fonte)); white-space: pre-wrap; }
        .cifra-marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #e5e7eb; color: #374151; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1rem 0 0.75rem; }
        .cifra-marcacao--refrao { background: #fef3c7; color: #92400e; font-weight: 950; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.22); }
        .preview-fiel { display: grid; gap: 0.65rem; }
        .preview-fiel .lyrics-stanza { border-left: 3px solid #e5e7eb; background: #ffffff; border-radius: 0; padding: 0.45rem 0 0.45rem 1rem; }
        .preview-fiel .lyrics-stanza--refrao { border-color: #f59e0b; background: linear-gradient(90deg, #fffbeb, #ffffff); box-shadow: none; }
        .preview-fiel .lyrics-stanza--refrao p { font-weight: 850; color: #78350f; }
        .preview-fiel p { margin: 0; color: #111827; font-size: calc(1.08rem * var(--escala-fonte)); line-height: calc(2rem * var(--escala-fonte)); }
        .preview-fiel .marcacao { display: inline-flex; align-items: center; border-radius: 9999px; background: #eef2ff; color: #4338ca; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; padding: 0.45rem 0.85rem; margin: 1.1rem 0 0.35rem; }
        .preview-fiel .marcacao--refrao { background: #fef3c7; color: #92400e; font-weight: 950; box-shadow: inset 0 0 0 1px rgba(217, 119, 6, 0.22); }
        .acorde-mini-card.ativo { border-color: #f97316; background: #fff7ed; color: #9a3412; }
        .diagrama-acorde svg { width: 100%; height: auto; max-width: 240px; }
        .tooltip-acorde { position: fixed; z-index: 80; width: 240px; pointer-events: none; border-radius: 1rem; border: 1px solid #fed7aa; background: rgba(255,255,255,0.98); box-shadow: 0 18px 50px rgba(15, 23, 42, 0.16); padding: 0.85rem; backdrop-filter: blur(8px); }
        .tooltip-acorde.hidden { display: none; }
        .tooltip-acorde svg { width: 100%; height: auto; }
        .variacao-acorde.ativa { background: #166534; color: #fff; border-color: #166534; }
        .painel-musico-topo { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .controle-pill { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 9999px; border: 1px solid #d1fae5; background: #ffffff; color: #047857; font-size: 0.75rem; font-weight: 700; padding: 0.4rem 0.85rem; }
        .video-musico-compacto { max-width: 420px; width: 100%; margin-left: auto; }
        .bpm-box { display: inline-flex; align-items: center; border: 1px solid #d1d5db; border-radius: 0.85rem; overflow: hidden; background: #fff; }
        .bpm-box button { width: 2.2rem; height: 2.2rem; font-weight: 800; color: #374151; background: #f9fafb; }
        .bpm-box input { width: 4rem; text-align: center; border: 0; outline: none; font-weight: 700; color: #111827; }
        .botao-pill { display: inline-flex; align-items: center; justify-content: center; min-width: 2.35rem; height: 2.35rem; padding: 0 0.8rem; border-radius: 9999px; border: 1px solid #d1d5db; background: #fff; color: #374151; font-size: 0.85rem; font-weight: 700; transition: all 0.15s ease; }
        .botao-pill:hover { border-color: #fdba74; background: #fff7ed; color: #c2410c; }
        .pill-info { display: inline-flex; align-items: center; gap: 0.35rem; min-height: 2.35rem; padding: 0 0.85rem; border-radius: 9999px; background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; font-size: 0.82rem; font-weight: 700; }
        .musico-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
        .admin-musico-stage { border: 1px solid rgba(15, 23, 42, .14); border-radius: 1.35rem; background: #08111f; padding: 1rem; }
        .admin-musico-reader { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-items: start; }
        .admin-musico-rail { display: flex; gap: .55rem; overflow-x: auto; padding-bottom: .35rem; scrollbar-width: none; }
        .admin-musico-rail::-webkit-scrollbar { display: none; }
        .admin-musico-tool { flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: .45rem; min-height: 2.7rem; border: 1px solid rgba(148, 163, 184, .22); border-radius: .9rem; background: rgba(248, 250, 252, .96); color: #334155; padding: .65rem .85rem; font-size: .82rem; font-weight: 900; }
        .admin-musico-tool--primary { background: #f97316; border-color: #fb923c; color: #fff; }
        .admin-musico-autoscroll { flex: 0 0 14rem; border: 1px solid rgba(148, 163, 184, .22); border-radius: .9rem; background: rgba(248, 250, 252, .96); padding: .7rem; color: #334155; }
        .admin-musico-autoscroll button { display: flex; width: 100%; align-items: center; gap: .45rem; border: 0; background: transparent; color: #0f172a; font-size: .82rem; font-weight: 950; }
        .admin-musico-speed { margin-top: .55rem; display: grid; grid-template-columns: 1fr auto; align-items: center; gap: .5rem; }
        .admin-musico-speed input { width: 100%; accent-color: #059669; }
        .admin-musico-speed span { min-width: 3.25rem; border-radius: 999px; background: #ecfdf5; color: #047857; padding: .25rem .45rem; text-align: center; font-size: .72rem; font-weight: 950; }
        .admin-musico-cifra { max-height: 68vh; min-height: 56vh; overflow-y: auto; border: 1px solid rgba(148, 163, 184, .18); border-radius: 1.15rem; background: #101a2d; padding: 1.15rem; color: #d1fae5; scroll-behavior: smooth; }
        .admin-musico-cifra .cifra-letra { color: #d1fae5; }
        .admin-musico-cifra .cifra-linha--acordes { display: block; padding-left: var(--cifra-indent, 0); margin: .12rem 0 .42rem; }
        .admin-musico-cifra .cifra-linha--acordes .cifra-acordes { display: inline-flex; flex-wrap: wrap; gap: .75rem; min-height: auto; line-height: 1.35; }
        .admin-musico-cifra .cifra-marcacao { background: rgba(16, 185, 129, .16); color: #a7f3d0; }
        .preview-admin-texto { font-size: calc(0.95rem * var(--admin-escala-fonte)); line-height: calc(1.75rem * var(--admin-escala-fonte)); }
        @media (min-width: 1280px) {
            .painel-musico-topo { grid-template-columns: minmax(0, 1fr) 380px; align-items: start; }
            .musico-layout { grid-template-columns: minmax(0, 1fr); }
            .admin-musico-reader { grid-template-columns: 10rem minmax(0, 1fr); }
            .admin-musico-rail { position: sticky; top: 1rem; display: grid; overflow: visible; padding: 0; }
            .admin-musico-tool, .admin-musico-autoscroll { width: 100%; justify-content: flex-start; }
        }
        @media (max-width: 767px) {
            .abas-modo { grid-template-columns: 1fr; width: 100%; }
            .preview-musico-scroll { max-height: none; }
            .cifra-linha { display: block; margin-bottom: 0.8rem; }
            .cifra-segmento { display: inline-flex; min-height: 2.25rem; max-width: 100%; }
            .cifra-acordes { font-size: calc(0.88rem * var(--escala-fonte)); }
            .cifra-letra { font-size: calc(1rem * var(--escala-fonte)); line-height: calc(1.62rem * var(--escala-fonte)); }
            .tooltip-acorde { width: 180px; padding: 0.65rem; border-radius: 0.9rem; }
            .tooltip-acorde svg { max-width: 140px; margin: 0 auto; display: block; }
        }
    </style>
@endpush

@push('scripts')
    @include('partials.chord-transposer-script')
@endpush

@section('content')
    @php
        $linhasSemCifra = preg_split('/\r\n|\r|\n/', $letraSemCifras) ?: [];
        $blocosSemCifra = [];
        $paragrafoAtual = [];
        $normalizarMarcacao = function (string $texto): string {
            return \Illuminate\Support\Str::of($texto)->ascii()->lower()->trim()->toString();
        };
        $ehMarcacaoSecao = function (string $texto) use ($normalizarMarcacao): bool {
            $normalizado = $normalizarMarcacao($texto);

            return strlen($normalizado) <= 32
                && preg_match('/^(intro|refrao|pre[-\s]?refrao|entrada|final|ponte|estrofe|verso|primeira parte|segunda parte|terceira parte)(\b|$)/', $normalizado) === 1;
        };
        $classeMarcacaoSemCifra = function (string $texto) use ($normalizarMarcacao): string {
            return str_starts_with($normalizarMarcacao($texto), 'refrao')
                ? 'marcacao marcacao--refrao'
                : 'marcacao';
        };
        $ehMarcacaoRefrao = function (string $texto) use ($normalizarMarcacao): bool {
            return str_starts_with($normalizarMarcacao($texto), 'refrao')
                || str_starts_with($normalizarMarcacao($texto), 'ref:');
        };
        $proximoBlocoRefrao = false;

        foreach ($linhasSemCifra as $linhaSemCifra) {
            $linhaLimpa = trim($linhaSemCifra);

            if ($linhaLimpa === '') {
                if ($paragrafoAtual !== []) {
                    $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual), 'refrao' => $proximoBlocoRefrao];
                    $paragrafoAtual = [];
                    $proximoBlocoRefrao = false;
                }

                continue;
            }

            if (preg_match('/^\[(.+)\]$/u', $linhaLimpa, $matches) === 1) {
                if ($paragrafoAtual !== []) {
                    $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual)];
                    $paragrafoAtual = [];
                }

                $blocosSemCifra[] = ['tipo' => 'marcacao', 'texto' => $matches[1]];
                $proximoBlocoRefrao = $ehMarcacaoRefrao($matches[1]);
                continue;
            }

            if ($ehMarcacaoSecao($linhaLimpa)) {
                if ($paragrafoAtual !== []) {
                    $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual)];
                    $paragrafoAtual = [];
                }

                $blocosSemCifra[] = ['tipo' => 'marcacao', 'texto' => $linhaLimpa];
                $proximoBlocoRefrao = $ehMarcacaoRefrao($linhaLimpa);
                continue;
            }

            $paragrafoAtual[] = $linhaLimpa;
        }

        if ($paragrafoAtual !== []) {
            $blocosSemCifra[] = ['tipo' => 'paragrafo', 'texto' => implode(' ', $paragrafoAtual), 'refrao' => $proximoBlocoRefrao];
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

            <div class="abas-modo w-full rounded-2xl border border-gray-200 bg-gray-50 p-2 sm:w-auto" role="tablist" aria-label="Modos de pre-visualizacao">
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold transition" data-modo="admin" aria-controls="painel_modo_admin" aria-pressed="false">
                    Previa admin <span class="aba-modo__status">ativa</span>
                </button>
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold transition" data-modo="musico" aria-controls="painel_modo_musico" aria-pressed="false">
                    Previa musico <span class="aba-modo__status">ativa</span>
                </button>
                <button type="button" class="aba-modo rounded-xl px-4 py-2 text-sm font-semibold transition" data-modo="fiel" aria-controls="painel_modo_fiel" aria-pressed="false">
                    Sem cifra <span class="aba-modo__status">ativa</span>
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-6">
            <section id="painel_modo_admin" data-painel-modo="admin" class="painel-modo space-y-6">
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

                    <div class="preview-musico-scroll rounded-xl border border-slate-800 bg-slate-900 p-6" id="preview_admin_container">
                        <div id="preview_admin_render" class="space-y-2"></div>
                    </div>
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

            <section id="painel_modo_musico" data-painel-modo="musico" class="painel-modo hidden space-y-6">
                <div class="admin-musico-stage">
                    <div class="mb-4">
                        <h2 class="text-xl font-black text-white">Previa do musico</h2>
                        <p class="mt-1 text-sm text-slate-300">Mesmo parser da tela de estudo: confira leitura, tom, fonte e acordes sem sair do admin.</p>
                        <p class="mt-3 text-xs font-bold text-slate-400">
                            <span id="indicador_tom_atual_musico">Tom {{ $versaoMusical->tom_musical ?: 'Nao informado' }}</span>
                            <span aria-hidden="true">/</span>
                            <span>BPM {{ $versaoMusical->bpm ?: '-' }}</span>
                        </p>
                    </div>

                    <div class="admin-musico-reader">
                        <nav class="admin-musico-rail" aria-label="Ferramentas da previa do musico">
                            @if ($versaoMusical->youtube_video_id)
                                <a href="https://www.youtube.com/watch?v={{ $versaoMusical->youtube_video_id }}" target="_blank" rel="noopener" class="admin-musico-tool admin-musico-tool--primary">
                                    <i class="fa-solid fa-play"></i>
                                    Video
                                </a>
                            @endif
                            <div class="admin-musico-autoscroll" aria-label="Auto rolagem">
                                <button type="button" id="toggle_autorrolagem" aria-pressed="false">
                                    <i class="fa-solid fa-angles-down"></i>
                                    Auto rolagem
                                </button>
                                <div class="admin-musico-speed">
                                    <input id="velocidade_rolagem" type="range" min="1" max="3" value="2" step="1" aria-label="Velocidade da auto rolagem">
                                    <span id="valor_velocidade">Normal</span>
                                </div>
                            </div>
                            <button type="button" class="admin-musico-tool" data-font-step="-1"><i class="fa-solid fa-minus"></i> Texto</button>
                            <button type="button" class="admin-musico-tool" data-font-step="1"><i class="fa-solid fa-plus"></i> Texto</button>
                            <button type="button" class="admin-musico-tool" data-transpose-step="-1"><i class="fa-solid fa-minus"></i> Tom</button>
                            <button type="button" class="admin-musico-tool" data-transpose-step="1"><i class="fa-solid fa-plus"></i> Tom</button>
                        </nav>

                        <div class="admin-musico-cifra" id="preview_musico_container">
                            <div id="preview_musico_render" class="space-y-1"></div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="painel_modo_fiel" data-painel-modo="fiel" class="painel-modo hidden">
                <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="mb-6">
                        <h2 class="text-xl font-black text-gray-900">Letra</h2>
                        <p class="text-sm text-gray-500">Versao limpa para leitura, removendo apenas os acordes e preservando marcacoes relevantes do texto.</p>
                    </div>

                    <div class="preview-fiel">
                        @foreach ($blocosSemCifra as $blocoSemCifra)
                            @if ($blocoSemCifra['tipo'] === 'marcacao')
                                <div class="{{ $classeMarcacaoSemCifra($blocoSemCifra['texto']) }}">{{ $blocoSemCifra['texto'] }}</div>
                            @else
                                <div class="lyrics-stanza {{ !empty($blocoSemCifra['refrao']) ? 'lyrics-stanza--refrao' : '' }}">
                                    <p>{{ $blocoSemCifra['texto'] }}</p>
                                </div>
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

                <div class="mt-5 flex flex-wrap gap-2" id="lista_acordes_transpostos">
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
                    <a href="{{ route('admin.versoes-musicais.edit', [$musica, $versaoMusical]) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
                        <i class="fa-solid fa-pen"></i>
                        Editar versao
                    </a>
                    <form action="{{ route('admin.versoes-musicais.destroy', [$musica, $versaoMusical]) }}" method="POST" onsubmit="return confirm('Deseja inativar esta versao musical? Ela sera preservada no banco.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700">Inativar versao</button>
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
            const helper = window.VozECifraChord;
            const abas = document.querySelectorAll('.aba-modo');
            const paineis = document.querySelectorAll('.painel-modo');
            const previewAdminRender = document.getElementById('preview_admin_render');
            const previewMusicoRender = document.getElementById('preview_musico_render');
            const containerMusico = document.getElementById('preview_musico_container');
            const textoComCifras = @json($versaoMusical->letra_com_cifras);
            const acordesDaVersao = @json($acordesDaVersao);
            const bibliotecaAcordes = @json($bibliotecaAcordes);
            const tomOriginal = @json($versaoMusical->tom_musical);
            const botaoRolagem = document.getElementById('toggle_autorrolagem');
            const controleVelocidade = document.getElementById('velocidade_rolagem');
            const valorVelocidade = document.getElementById('valor_velocidade');
            let rolagemAtiva = false;
            let intervaloRolagem = null;
            let fonteAtual = 18;
            let transposicaoAtual = 0;

            const gruposAcorde = helper ? helper.buildChordGroups(bibliotecaAcordes) : null;
            const escaparHtml = (texto) => (texto || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            const velocidadeConfig = {
                1: { label: 'Lenta', passo: 0.9 },
                2: { label: 'Normal', passo: 1.8 },
                3: { label: 'Rapida', passo: 3.2 },
            };

            const ativarModo = (modo) => {
                abas.forEach((aba) => {
                    const ativo = aba.dataset.modo === modo;
                    aba.classList.toggle('ativa', ativo);
                    aba.setAttribute('aria-pressed', ativo ? 'true' : 'false');
                });

                paineis.forEach((painel) => {
                    const ativo = painel.dataset.painelModo === modo;
                    painel.classList.toggle('hidden', !ativo);
                    painel.hidden = !ativo;
                });

                document.querySelectorAll('.dado-admin-only').forEach((elemento) => {
                    elemento.classList.toggle('hidden', modo !== 'admin');
                });
            };

            abas.forEach((aba) => aba.addEventListener('click', () => ativarModo(aba.dataset.modo)));

            const renderizarPreviewMusico = () => {
                const textoTransposto = helper.transposeBracketedText(textoComCifras || '', transposicaoAtual);
                const html = helper.renderChordSheetHtml(textoTransposto, { chordAttribute: 'data-acorde-hover' });

                if (previewMusicoRender) {
                    previewMusicoRender.innerHTML = html;
                    previewMusicoRender.style.setProperty('--escala-fonte', String(fonteAtual / 18));
                }

                if (previewAdminRender) {
                    previewAdminRender.innerHTML = html;
                    previewAdminRender.style.setProperty('--escala-fonte', String(fonteAtual / 18));
                }

                const indicadorTom = document.getElementById('indicador_tom_atual_musico');
                if (indicadorTom) {
                    indicadorTom.textContent = 'Tom ' + (
                        tomOriginal && helper.isChord(tomOriginal)
                            ? helper.transposeChord(tomOriginal, transposicaoAtual)
                            : 'Nao informado'
                    );
                }
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
                const variacoes = helper.getChordMatches(gruposAcorde, nome);

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
                const acorde = helper.getChordMatches(gruposAcorde, nome)[indice] || null;

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
                const acorde = helper.getChordMatches(gruposAcorde, nome)[indice] || null;
                const assinaturaAtual = helper.getChordSignature(nome);
                document.querySelectorAll('[data-acorde-hover], [data-acorde-card]').forEach((elemento) => {
                    const valorElemento = elemento.dataset.acordeHover || elemento.dataset.acordeCard;
                    const assinaturaElemento = helper.getChordSignature(valorElemento);
                    const ativo = valorElemento === nome || (assinaturaElemento && assinaturaAtual && assinaturaElemento === assinaturaAtual);
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
                const velocidade = velocidadeConfig[Number(controleVelocidade?.value || 2)] || velocidadeConfig[2];
                if (valorVelocidade) {
                    valorVelocidade.textContent = velocidade.label;
                }
                intervaloRolagem = window.setInterval(() => {
                    containerMusico.scrollTop += velocidade.passo;
                    if (containerMusico.scrollTop + containerMusico.clientHeight >= containerMusico.scrollHeight) pararAutoRolagem();
                }, 50);
            };

            const pararAutoRolagem = () => {
                if (intervaloRolagem) {
                    window.clearInterval(intervaloRolagem);
                    intervaloRolagem = null;
                }
                rolagemAtiva = false;
                if (botaoRolagem) {
                    botaoRolagem.innerHTML = '<i class="fa-solid fa-angles-down"></i> Auto rolagem';
                    botaoRolagem.setAttribute('aria-pressed', 'false');
                }
            };

            botaoRolagem?.addEventListener('click', () => {
                if (rolagemAtiva) return pararAutoRolagem();
                rolagemAtiva = true;
                botaoRolagem.innerHTML = '<i class="fa-solid fa-pause"></i> Parar rolagem';
                botaoRolagem.setAttribute('aria-pressed', 'true');
                iniciarAutoRolagem();
            });

            controleVelocidade?.addEventListener('input', () => {
                const velocidade = velocidadeConfig[Number(controleVelocidade.value || 2)] || velocidadeConfig[2];
                if (valorVelocidade) {
                    valorVelocidade.textContent = velocidade.label;
                }

                if (rolagemAtiva) {
                    window.clearInterval(intervaloRolagem);
                    iniciarAutoRolagem();
                }
            });

            document.querySelectorAll('[data-font-step]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    fonteAtual = Math.min(32, Math.max(14, fonteAtual + (Number(botao.dataset.fontStep || 0) * 2)));
                    renderizarPreviewMusico();
                });
            });

            document.querySelectorAll('[data-transpose-step]').forEach((botao) => {
                botao.addEventListener('click', () => {
                    transposicaoAtual += Number(botao.dataset.transposeStep || 0);
                    renderizarPreviewMusico();
                });
            });

            renderizarPreviewMusico();

            if (valorVelocidade && controleVelocidade) {
                valorVelocidade.textContent = (velocidadeConfig[Number(controleVelocidade.value || 2)] || velocidadeConfig[2]).label;
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
