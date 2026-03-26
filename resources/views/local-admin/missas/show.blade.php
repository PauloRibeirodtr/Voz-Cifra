@extends('local-admin.layouts.admin')

@section('title', 'Visualizar missa | Voz & Cifra')
@section('mobile_title', 'Missa')

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">{{ $missa->titulo }}</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ optional($missa->data_missa)->format('d/m/Y') }} • {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap">
            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50">Editar</a>
            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 font-medium text-indigo-800 hover:bg-indigo-100">Apresentacao</a>
            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-medium text-amber-800 hover:bg-amber-100">Baixar PDF</a>
            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-medium text-gray-700 hover:bg-gray-50 sm:col-span-2 xl:col-span-1">Voltar</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
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

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Adicionar musica ao repertorio</h2>
                    <p class="mt-1 text-sm text-gray-500">Busque por nome da musica, artista ou trecho da letra para encontrar mais rapido o canto certo.</p>
                </div>

                <form action="{{ route('local-admin.repertorio.store', $missa) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="busca_musica" class="block text-sm font-medium text-gray-700">Musica</label>
                        <input type="hidden" name="musica_id" id="musica_id" value="{{ old('musica_id') }}" required>
                        <div class="mt-1 rounded-2xl border border-gray-300 bg-white shadow-sm">
                            <input
                                type="text"
                                id="busca_musica"
                                class="block w-full rounded-2xl border-0 bg-transparent px-4 py-3 text-gray-800 focus:ring-2 focus:ring-green-100"
                                placeholder="Digite nome da musica, artista ou trecho da letra"
                                autocomplete="off"
                            >
                            <div id="resultado_busca_musica" class="hidden border-t border-gray-100 p-2"></div>
                        </div>
                        <p id="musica_selecionada_texto" class="mt-2 text-sm text-gray-500">Nenhuma musica selecionada.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Momento liturgico</label>
                        <select name="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Definir depois</option>
                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                <option value="{{ $momentoLiturgico->id }}">{{ $momentoLiturgico->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Versao musical</label>
                        <select name="versao_musical_id" id="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" disabled>
                            <option value="">Usar depois</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                            Adicionar ao repertorio
                        </button>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Repertorio da missa</h2>
                    <p class="mt-1 text-sm text-gray-500">Organize a ordem dos cantos e associe os momentos liturgicos da celebracao.</p>
                </div>

                @if ($missa->missaMusicas->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                        Ainda nao existe musica no repertorio desta missa.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($missa->missaMusicas as $item)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">Ordem {{ $item->ordem }}</span>
                                            @if ($item->momentoLiturgico)
                                                <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $item->momentoLiturgico->nome }}</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $item->musica->titulo }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista nao informado' }}</p>
                                        <p class="mt-2 text-sm text-gray-600">
                                            Versao: {{ $item->versaoMusical?->titulo ?: 'Nao vinculada' }}
                                            @if ($item->versaoMusical?->tom_musical)
                                                • Tom {{ $item->versaoMusical->tom_musical }}
                                            @endif
                                            @if ($item->versaoMusical?->bpm)
                                                • BPM {{ $item->versaoMusical->bpm }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:w-[320px]">
                                        <form action="{{ route('local-admin.repertorio.up', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                                Subir
                                            </button>
                                        </form>
                                        <form action="{{ route('local-admin.repertorio.down', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                                Descer
                                            </button>
                                        </form>
                                        <a href="{{ route('local-admin.repertorio.cifra', [$missa, $item]) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-800 hover:bg-amber-100">
                                            Ver cifra
                                        </a>
                                        <form action="{{ route('local-admin.repertorio.destroy', [$missa, $item]) }}" method="POST" onsubmit="return confirm('Deseja remover esta musica do repertorio?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                Remover
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <form action="{{ route('local-admin.repertorio.update', [$missa, $item]) }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Momento liturgico</label>
                                        <select name="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                            <option value="">Definir depois</option>
                                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                                <option value="{{ $momentoLiturgico->id }}" @selected((string) $item->momento_liturgico_id === (string) $momentoLiturgico->id)>
                                                    {{ $momentoLiturgico->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Versao musical</label>
                                        <select name="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                                            <option value="">Nao vincular agora</option>
                                            @foreach ($item->musica->versoesMusicais as $versaoMusical)
                                                <option value="{{ $versaoMusical->id }}" @selected((string) $item->versao_musical_id === (string) $versaoMusical->id)>
                                                    {{ $versaoMusical->titulo ?: 'Versao principal' }}
                                                    @if ($versaoMusical->tom_musical)
                                                        • Tom {{ $versaoMusical->tom_musical }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">
                                            Salvar item
                                        </button>
                                    </div>
                                </form>
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
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tempo liturgico</span><span>{{ $missa->tempoLiturgico?->nome ?: 'Nao definido' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Padre</span><span>{{ $missa->padre?->nome ?: 'Nao vinculado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Observacoes</span><span>{{ $missa->observacoes ?: 'Nenhuma observacao informada.' }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Atalho da igreja</h2>
                <p class="mt-2 text-sm text-gray-500">Use o mesmo link publico fixo da igreja como base para a missa ativa no futuro.</p>
                <a href="{{ $igreja->link_publico }}" target="_blank" class="mt-4 block break-all text-sm font-semibold text-green-700 hover:underline">
                    {{ $igreja->link_publico }}
                </a>
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
                'texto_exibicao' => trim($musica->titulo . ($musica->artista ? ' • ' . $musica->artista : '')),
                'versoes' => $musica->versoesMusicais->map(function ($versao) {
                    return [
                        'id' => $versao->id,
                        'titulo' => $versao->titulo ?: 'Versao principal',
                        'tom' => $versao->tom_musical,
                        'bpm' => $versao->bpm,
                    ];
                })->values(),
            ];
        })->values();
    @endphp

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const musicas = @json($musicasParaBusca, JSON_UNESCAPED_UNICODE);
            const inputBusca = document.getElementById('busca_musica');
            const resultadoBusca = document.getElementById('resultado_busca_musica');
            const musicaId = document.getElementById('musica_id');
            const musicaSelecionadaTexto = document.getElementById('musica_selecionada_texto');
            const selectVersao = document.getElementById('versao_musical_id');
            const oldVersaoId = @json(old('versao_musical_id'));

            if (!inputBusca || !resultadoBusca || !musicaId || !musicaSelecionadaTexto || !selectVersao) {
                return;
            }

            const preencherVersoes = (musicaSelecionada, versaoSelecionada = null) => {
                selectVersao.innerHTML = '<option value="">Usar depois</option>';

                if (!musicaSelecionada || !Array.isArray(musicaSelecionada.versoes) || musicaSelecionada.versoes.length === 0) {
                    selectVersao.disabled = true;
                    return;
                }

                musicaSelecionada.versoes.forEach((versao) => {
                    const option = document.createElement('option');
                    option.value = versao.id;

                    let texto = versao.titulo;
                    if (versao.tom) {
                        texto += ' • Tom ' + versao.tom;
                    }
                    if (versao.bpm) {
                        texto += ' • BPM ' + versao.bpm;
                    }

                    option.textContent = texto;

                    if (String(versao.id) === String(versaoSelecionada)) {
                        option.selected = true;
                    }

                    selectVersao.appendChild(option);
                });

                selectVersao.disabled = false;
            };

            const selecionarMusica = (musicaSelecionada, versaoSelecionada = null) => {
                musicaId.value = musicaSelecionada.id;
                inputBusca.value = musicaSelecionada.texto_exibicao;
                musicaSelecionadaTexto.textContent = 'Selecionada: ' + musicaSelecionada.texto_exibicao;
                resultadoBusca.classList.add('hidden');
                resultadoBusca.innerHTML = '';
                preencherVersoes(musicaSelecionada, versaoSelecionada);
            };

            const renderizarResultados = (termo) => {
                const busca = termo.trim().toLowerCase();

                if (busca.length < 2) {
                    resultadoBusca.classList.add('hidden');
                    resultadoBusca.innerHTML = '';
                    return;
                }

                const resultados = musicas
                    .filter((musica) => {
                        const textoBusca = [musica.titulo || '', musica.artista || '', musica.letra || ''].join(' ').toLowerCase();
                        return textoBusca.includes(busca);
                    })
                    .slice(0, 8);

                if (resultados.length === 0) {
                    resultadoBusca.innerHTML = '<div class="rounded-xl px-3 py-3 text-sm text-gray-500">Nenhuma musica encontrada com esse termo.</div>';
                    resultadoBusca.classList.remove('hidden');
                    return;
                }

                resultadoBusca.innerHTML = resultados.map((musica) => {
                    const trecho = (musica.letra || '').replace(/\s+/g, ' ').trim().slice(0, 90);
                    const subtitulo = musica.artista ? musica.artista : 'Artista nao informado';
                    const trechoHtml = trecho ? `<p class="mt-1 text-xs text-gray-500">${trecho}...</p>` : '';

                    return `
                        <button type="button" class="flex w-full flex-col rounded-xl px-3 py-3 text-left transition hover:bg-green-50" data-musica-id="${musica.id}">
                            <span class="text-sm font-semibold text-gray-900">${musica.titulo}</span>
                            <span class="mt-1 text-xs text-green-700">${subtitulo}</span>
                            ${trechoHtml}
                        </button>
                    `;
                }).join('');

                resultadoBusca.classList.remove('hidden');

                resultadoBusca.querySelectorAll('[data-musica-id]').forEach((botao) => {
                    botao.addEventListener('click', () => {
                        const musicaSelecionada = musicas.find((item) => String(item.id) === String(botao.dataset.musicaId));
                        if (musicaSelecionada) {
                            selecionarMusica(musicaSelecionada);
                        }
                    });
                });
            };

            inputBusca.addEventListener('input', (event) => {
                musicaId.value = '';
                musicaSelecionadaTexto.textContent = 'Selecione uma musica da busca abaixo.';
                selectVersao.innerHTML = '<option value="">Usar depois</option>';
                selectVersao.disabled = true;
                renderizarResultados(event.target.value);
            });

            document.addEventListener('click', (event) => {
                if (!resultadoBusca.contains(event.target) && event.target !== inputBusca) {
                    resultadoBusca.classList.add('hidden');
                }
            });

            const musicaInicial = musicas.find((item) => String(item.id) === String(musicaId.value));
            if (musicaInicial) {
                selecionarMusica(musicaInicial, oldVersaoId);
            }
        });
    </script>
@endpush
