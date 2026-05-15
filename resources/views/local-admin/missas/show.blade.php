@extends('local-admin.layouts.admin')

@section('title', 'Missa | Voz & Cifra')
@section('mobile_title', 'Missa')

@php
    $tonsMusicais = config('musical.tons', []);
@endphp

@push('styles')
    <style>
        .missa-step {
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: #ffffff;
            padding: 1rem;
        }

        .missa-step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background: #6c4a21;
            color: #ffffff;
            font-size: 0.8rem;
            font-weight: 900;
        }

        .repertorio-item-card {
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .repertorio-item-card:hover {
            border-color: #bbf7d0;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.07);
        }
    </style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between" id="missa-resumo">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="text-2xl font-black text-gray-900 sm:text-3xl">{{ $missa->titulo }}</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-700' }}">
                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                </span>
            </div>
            <p class="mt-2 text-sm text-gray-500">
                {{ optional($missa->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missa->hora_inicio, 0, 5) }} - {{ substr((string) $missa->hora_fim, 0, 5) }}
            </p>
            <p class="mt-2 max-w-2xl text-sm text-gray-600">
                Crie a missa, salve os dados principais e depois monte o repert&oacute;rio logo abaixo. Cada m&uacute;sica adicionada j&aacute; fica salva automaticamente.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:min-w-[23rem]">
            <a href="{{ route('local-admin.missas.edit', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-[#ead6b3] bg-[#fff8ed] px-4 py-3 font-semibold text-[#6c4a21] transition hover:bg-[#f8ecd7]">
                Editar missa
            </a>
            <a href="{{ route('local-admin.missas.apresentacao', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 font-semibold text-sky-800 transition hover:bg-sky-100">
                Visualiza&ccedil;&atilde;o da missa
            </a>
            <a href="{{ route('local-admin.missas.pdf', $missa) }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 font-semibold text-amber-800 transition hover:bg-amber-100">
                Baixar PDF completo
            </a>
            <form action="{{ route('local-admin.missas.toggle', $missa) }}" method="POST" onsubmit="return confirm('{{ $missa->ativo ? 'Deseja inativar esta missa? O repertório será preservado para consulta futura.' : 'Deseja reativar esta missa como parte do fluxo da igreja?' }}');">
                @csrf
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border px-4 py-3 font-semibold transition {{ $missa->ativo ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $missa->ativo ? 'Inativar missa' : 'Reativar missa' }}
                </button>
            </form>
            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:bg-gray-50 sm:col-span-2">
                Voltar para as missas
            </a>
        </div>
    </div>

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

    <section class="mb-6 grid grid-cols-1 gap-3 lg:grid-cols-3">
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">1</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Dados da missa</p>
                    <p class="mt-1 text-xs text-gray-500">Data, horario, celebrante e publicacao.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">2</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Montagem do repertorio</p>
                    <p class="mt-1 text-xs text-gray-500">Adicione musicas, versoes, ordem e tom usado.</p>
                </div>
            </div>
        </div>
        <div class="missa-step">
            <div class="flex items-center gap-3">
                <span class="missa-step-number">3</span>
                <div>
                    <p class="text-sm font-black text-gray-900">Visualizacao final</p>
                    <p class="mt-1 text-xs text-gray-500">Confira a missa antes de apresentar ou gerar PDF.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <section id="missa-repertorio" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Adicionar m&uacute;sica ao repert&oacute;rio</h2>
                        <p class="mt-1 text-sm text-gray-500">Busque por nome da m&uacute;sica, artista ou trecho da letra para localizar o canto com mais rapidez.</p>
                    </div>
                    <form action="{{ route('local-admin.missas.concluir-montagem', $missa) }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 sm:w-auto">
                            Concluir montagem
                        </button>
                    </form>
                </div>

                <form action="{{ route('local-admin.repertorio.store', $missa) }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="busca_musica" class="block text-sm font-medium text-gray-700">M&uacute;sica</label>
                        <input type="hidden" name="musica_id" id="musica_id" value="{{ old('musica_id') }}" required>
                        <div class="mt-1 rounded-2xl border border-gray-300 bg-white shadow-sm">
                            <input
                                type="text"
                                id="busca_musica"
                                class="block w-full rounded-2xl border-0 bg-transparent px-4 py-3 text-gray-800 focus:ring-2 focus:ring-[#ead6b3]"
                                placeholder="Digite o nome da m&uacute;sica, artista ou trecho da letra"
                                autocomplete="off"
                            >
                            <div id="resultado_busca_musica" class="hidden border-t border-gray-100 p-2"></div>
                        </div>
                        <p id="musica_selecionada_texto" class="mt-2 text-sm text-gray-500">Nenhuma m&uacute;sica selecionada.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Momento lit&uacute;rgico</label>
                        <select name="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Definir depois</option>
                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                <option value="{{ $momentoLiturgico->id }}">{{ $momentoLiturgico->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vers&atilde;o musical</label>
                        <select name="versao_musical_id" id="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]" disabled>
                            <option value="">Vincular depois</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tom usado na missa</label>
                        <select name="tom_usado" id="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                            <option value="">Usar o tom original da vers&atilde;o</option>
                            @foreach ($tonsMusicais as $tomMusical)
                                <option value="{{ $tomMusical }}" @selected(old('tom_usado') === $tomMusical)>{{ $tomMusical }}</option>
                            @endforeach
                        </select>
                        <p id="tom_usado_hint" class="mt-1 text-xs text-gray-500">Selecione um tom apenas se a igreja for tocar em um tom diferente da vers&atilde;o original.</p>
                    </div>

                    <div class="md:col-span-2 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#6c4a21] px-5 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                            Salvar e adicionar ao repert&oacute;rio
                        </button>
                        <a href="#missa-resumo" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                            Revisar dados da missa
                        </a>
                    </div>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-lg font-bold text-gray-900">Repert&oacute;rio da missa</h2>
                    <p class="mt-1 text-sm text-gray-500">Organize a ordem dos cantos, defina o momento lit&uacute;rgico e ajuste a vers&atilde;o usada em cada item.</p>
                </div>

                @if ($missa->missaMusicas->isEmpty())
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                        Ainda n&atilde;o existe m&uacute;sica cadastrada no repert&oacute;rio desta missa.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($missa->missaMusicas as $item)
                            <article class="repertorio-item-card rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Ordem {{ $item->ordem }}</span>
                                            @if ($item->momentoLiturgico)
                                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-bold text-sky-700">{{ $item->momentoLiturgico->nome }}</span>
                                            @endif
                                        </div>
                                        <h3 class="mt-3 text-lg font-bold text-gray-900">{{ $item->musica->titulo }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $item->musica->artista ?: 'Artista n&atilde;o informado' }}</p>
                                        <p class="mt-2 text-sm text-gray-600">
                                            Vers&atilde;o: {{ $item->versaoMusical?->titulo ?: 'Ainda n&atilde;o vinculada' }}
                                            @if ($item->tom_exibicao)
                                                &bull; Tom da missa {{ $item->tom_exibicao }}
                                            @endif
                                            @if ($item->tom_usado && $item->versaoMusical?->tom_musical)
                                                &bull; Tom original {{ $item->versaoMusical->tom_musical }}
                                            @endif
                                            @if ($item->versaoMusical?->bpm)
                                                &bull; BPM {{ $item->versaoMusical->bpm }}
                                            @endif
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:w-[360px]">
                                        <form action="{{ route('local-admin.repertorio.up', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                                Subir
                                            </button>
                                        </form>
                                        <form action="{{ route('local-admin.repertorio.down', [$missa, $item]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-100">
                                                Descer
                                            </button>
                                        </form>
                                        <a href="{{ route('local-admin.repertorio.cifra', [$missa, $item]) }}" class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-800 transition hover:bg-sky-100">
                                            Visualizar cifra
                                        </a>
                                        <form action="{{ route('local-admin.repertorio.destroy', [$missa, $item]) }}" method="POST" onsubmit="return confirm('Deseja remover esta m&uacute;sica do repert&oacute;rio da missa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100">
                                                Remover item
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <form action="{{ route('local-admin.repertorio.update', [$missa, $item]) }}" method="POST" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Momento lit&uacute;rgico</label>
                                        <select name="momento_liturgico_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Definir depois</option>
                                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                                <option value="{{ $momentoLiturgico->id }}" @selected((string) $item->momento_liturgico_id === (string) $momentoLiturgico->id)>
                                                    {{ $momentoLiturgico->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Vers&atilde;o musical</label>
                                        <select name="versao_musical_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">N&atilde;o vincular agora</option>
                                            @foreach ($item->musica->versoesMusicais as $versaoMusical)
                                                <option value="{{ $versaoMusical->id }}" @selected((string) $item->versao_musical_id === (string) $versaoMusical->id)>
                                                    {{ $versaoMusical->titulo ?: 'Vers&atilde;o principal' }}
                                                    @if ($versaoMusical->tom_musical)
                                                        &bull; Tom {{ $versaoMusical->tom_musical }}
                                                    @endif
                                                    @if ($versaoMusical->bpm)
                                                        &bull; BPM {{ $versaoMusical->bpm }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tom usado na missa</label>
                                        <select name="tom_usado" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]">
                                            <option value="">Usar o tom original da vers&atilde;o</option>
                                            @foreach ($tonsMusicais as $tomMusical)
                                                <option value="{{ $tomMusical }}" @selected((string) old('tom_usado', $item->tom_usado) === (string) $tomMusical)>{{ $tomMusical }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="md:col-span-3">
                                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#2a1b1b] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#1f1414]">
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
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Tempo lit&uacute;rgico</span><span>{{ $missa->tempoLiturgico?->nome ?: 'Ainda n&atilde;o definido' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Celebrante</span><span>{{ $missa->celebrante?->nome ?: 'Ainda n&atilde;o vinculado' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publica&ccedil;&atilde;o para fi&eacute;is</span><span>{{ $missa->publica_para_fieis ? 'Ativa' : 'N&atilde;o publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Publica&ccedil;&atilde;o para m&uacute;sicos</span><span>{{ $missa->publica_para_musicos ? 'Ativa' : 'N&atilde;o publicada' }}</span></div>
                    <div><span class="block text-xs font-black uppercase tracking-wider text-gray-400">Observa&ccedil;&otilde;es</span><span>{{ $missa->observacoes ?: 'Nenhuma observa&ccedil;&atilde;o informada.' }}</span></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Links da igreja</h2>
                <p class="mt-2 text-sm text-gray-500">Use a p&aacute;gina p&uacute;blica da igreja como refer&ecirc;ncia para o acesso dos fi&eacute;is e dos m&uacute;sicos.</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos fi&eacute;is</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" class="mt-1 block break-all text-sm font-semibold text-[#8c6933] hover:underline">
                            {{ $igreja->link_publico }}
                        </a>
                    </div>

                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos m&uacute;sicos</span>
                        <a href="{{ $igreja->link_publico_musicos }}" target="_blank" class="mt-1 block break-all text-sm font-semibold text-sky-800 hover:underline">
                            {{ $igreja->link_publico_musicos }}
                        </a>
                    </div>
                </div>
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
                'texto_exibicao' => trim($musica->titulo . ($musica->artista ? ' - ' . $musica->artista : '')),
                'versoes' => $musica->versoesMusicais->map(function ($versao) {
                    return [
                        'id' => $versao->id,
                        'titulo' => $versao->titulo ?: 'Versão principal',
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
            const campoTomUsado = document.getElementById('tom_usado');
            const tomUsadoHint = document.getElementById('tom_usado_hint');
            const oldVersaoId = @json(old('versao_musical_id'));

            if (!inputBusca || !resultadoBusca || !musicaId || !musicaSelecionadaTexto || !selectVersao || !campoTomUsado) {
                return;
            }

            const atualizarOrientacaoTom = (versaoSelecionadaAtual = null) => {
                const primeiraOpcaoTom = campoTomUsado.querySelector('option[value=""]');

                if (!versaoSelecionadaAtual) {
                    if (primeiraOpcaoTom) {
                        primeiraOpcaoTom.textContent = 'Usar o tom original da versão';
                    }

                    if (tomUsadoHint) {
                        tomUsadoHint.textContent = 'Selecione um tom apenas se a igreja for tocar em um tom diferente da versão original.';
                    }

                    return;
                }

                if (primeiraOpcaoTom) {
                    primeiraOpcaoTom.textContent = versaoSelecionadaAtual.tom
                        ? 'Usar o tom original da versão (' + versaoSelecionadaAtual.tom + ')'
                        : 'Usar o tom original da versão';
                }

                if (tomUsadoHint) {
                    tomUsadoHint.textContent = versaoSelecionadaAtual.tom
                        ? 'Tom original sugerido: ' + versaoSelecionadaAtual.tom + '. Escolha outro apenas se a igreja for tocar em tom diferente.'
                        : 'Esta versão ainda não informa o tom original. Escolha um tom somente se necessário.';
                }
            };

            const preencherVersoes = (musicaSelecionada, versaoSelecionada = null) => {
                selectVersao.innerHTML = '<option value="">Vincular depois</option>';

                if (!musicaSelecionada || !Array.isArray(musicaSelecionada.versoes) || musicaSelecionada.versoes.length === 0) {
                    selectVersao.disabled = true;
                    atualizarOrientacaoTom();
                    return;
                }

                musicaSelecionada.versoes.forEach((versao) => {
                    const option = document.createElement('option');
                    option.value = versao.id;

                    let texto = versao.titulo;
                    if (versao.tom) {
                        texto += ' - Tom ' + versao.tom;
                    }
                    if (versao.bpm) {
                        texto += ' - BPM ' + versao.bpm;
                    }

                    option.textContent = texto;

                    if (String(versao.id) === String(versaoSelecionada)) {
                        option.selected = true;
                    }

                    selectVersao.appendChild(option);
                });

                selectVersao.disabled = false;

                const versaoAtual = musicaSelecionada.versoes.find((versao) => String(versao.id) === String(selectVersao.value))
                    || musicaSelecionada.versoes.find((versao) => String(versao.id) === String(versaoSelecionada))
                    || musicaSelecionada.versoes[0];

                atualizarOrientacaoTom(versaoAtual || null);
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
                    resultadoBusca.innerHTML = '<div class="rounded-xl px-3 py-3 text-sm text-gray-500">Nenhuma música encontrada com esse termo.</div>';
                    resultadoBusca.classList.remove('hidden');
                    return;
                }

                resultadoBusca.innerHTML = resultados.map((musica) => {
                    const trecho = (musica.letra || '').replace(/\s+/g, ' ').trim().slice(0, 90);
                    const subtitulo = musica.artista ? musica.artista : 'Artista não informado';
                    const trechoHtml = trecho ? `<p class="mt-1 text-xs text-gray-500">${trecho}...</p>` : '';

                    return `
                        <button type="button" class="flex w-full flex-col rounded-xl px-3 py-3 text-left transition hover:bg-[#fff8ed]" data-musica-id="${musica.id}">
                            <span class="text-sm font-semibold text-gray-900">${musica.titulo}</span>
                            <span class="mt-1 text-xs text-[#8c6933]">${subtitulo}</span>
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
                musicaSelecionadaTexto.textContent = 'Selecione uma música na busca abaixo.';
                selectVersao.innerHTML = '<option value="">Vincular depois</option>';
                selectVersao.disabled = true;
                atualizarOrientacaoTom();
                renderizarResultados(event.target.value);
            });

            selectVersao.addEventListener('change', () => {
                const musicaSelecionada = musicas.find((item) => String(item.id) === String(musicaId.value));
                if (!musicaSelecionada) {
                    atualizarOrientacaoTom();
                    return;
                }

                const versaoAtual = musicaSelecionada.versoes.find((versao) => String(versao.id) === String(selectVersao.value)) || null;
                atualizarOrientacaoTom(versaoAtual);
            });

            document.addEventListener('click', (event) => {
                if (!resultadoBusca.contains(event.target) && event.target !== inputBusca) {
                    resultadoBusca.classList.add('hidden');
                }
            });

            const musicaInicial = musicas.find((item) => String(item.id) === String(musicaId.value));
            if (musicaInicial) {
                selecionarMusica(musicaInicial, oldVersaoId);
            } else {
                atualizarOrientacaoTom();
            }
        });
    </script>
@endpush
