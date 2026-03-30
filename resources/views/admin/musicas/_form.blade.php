@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $musicaAtual = $musica ?? null;
    $tituloPagina = $modo === 'create' ? 'Cadastrar musica' : 'Editar musica';
    $descricaoPagina = $modo === 'create'
        ? 'Cadastre apenas a musica base. Cifras, tom, bpm e video entram depois nas versoes musicais.'
        : 'Atualize apenas os dados da musica base. Cifras continuam sendo tratadas nas versoes musicais.';
@endphp

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $tituloPagina }}</h1>
        <p class="text-sm text-gray-500">{{ $descricaoPagina }}</p>
    </div>

    <a href="{{ route('admin.musicas.index') }}" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
        Ver musicas
    </a>
</div>

@if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Titulo da musica base</label>
                    <input
                        type="text"
                        name="titulo"
                        value="{{ old('titulo', $musicaAtual->titulo ?? '') }}"
                        required
                        placeholder="Ex.: Vinde, Espirito Santo"
                        class="{{ $classeInput }}"
                    />
                    <p class="text-xs text-gray-500 mt-1">Use o nome principal pelo qual a musica e conhecida no ministerio.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Artista ou ministerio</label>
                    <input
                        type="text"
                        name="artista"
                        value="{{ old('artista', $musicaAtual->artista ?? '') }}"
                        placeholder="Ex.: Colo de Deus, Ministerio Amor e Adoracao, Pe. Zezinho"
                        class="{{ $classeInput }}"
                    />
                    <p class="text-xs text-gray-500 mt-1">Campo opcional. Informe apenas se ajudar a identificar melhor a musica.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tempo liturgico</label>
                        <select name="tempo_liturgico_id" class="{{ $classeInput }}">
                            <option value="">Nao vincular agora</option>
                            @foreach ($temposLiturgicos as $tempoLiturgico)
                                <option value="{{ $tempoLiturgico->id }}" @selected(old('tempo_liturgico_id', $musicaAtual->tempo_liturgico_id ?? null) == $tempoLiturgico->id)>
                                    {{ $tempoLiturgico->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Escolha quando a musica tiver uso liturgico predominante.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Momento liturgico</label>
                        <select name="momento_liturgico_id" class="{{ $classeInput }}">
                            <option value="">Nao vincular agora</option>
                            @foreach ($momentosLiturgicos as $momentoLiturgico)
                                <option value="{{ $momentoLiturgico->id }}" @selected(old('momento_liturgico_id', $musicaAtual->momento_liturgico_id ?? null) == $momentoLiturgico->id)>
                                    {{ $momentoLiturgico->nome }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Escolha quando a musica for claramente usada em um momento especifico.</p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Letra base</label>
                        <span class="text-xs text-gray-500"><span id="contador_linhas">0</span> linhas â€¢ <span id="contador_caracteres">0</span> caracteres</span>
                    </div>
                    <textarea
                        id="letra"
                        name="letra"
                        rows="16"
                        required
                        placeholder="Digite apenas a letra da musica (sem cifras)

Exemplo:
Quao grande e o meu Deus
Cantarei quao grande e o meu Deus
E todos hao de ver
Quao grande e o meu Deus"
                        class="{{ $classeInput }}"
                    >{{ old('letra', $musicaAtual->letra ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">Nao coloque cifras aqui. Elas devem ser cadastradas depois na versao musical.</p>
                    <div id="alerta_cifras" class="hidden mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Voce inseriu cifras na letra. Cadastre apenas a letra aqui. As cifras devem ser adicionadas na versao musical.
                    </div>

                    <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                            <h3 class="text-sm font-bold text-green-800 mb-3">Exemplo correto de letra base</h3>
                            <pre class="whitespace-pre-wrap break-words font-sans text-sm leading-7 text-green-900">Quao grande e o meu Deus
Cantarei quao grande e o meu Deus
E todos hao de ver
Quao grande e o meu Deus</pre>
                            <p class="mt-3 text-xs text-green-700">Aqui entra apenas a letra limpa, sem tom, sem bpm e sem acordes.</p>
                        </div>

                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <h3 class="text-sm font-bold text-red-800 mb-3">Exemplo errado nesta tela</h3>
                            <pre class="whitespace-pre-wrap break-words font-mono text-sm leading-7 text-red-900">[G]Quao grande e o meu Deus
[D/F#]Cantarei quao grande e o meu Deus
[Em7]E todos hao de ver</pre>
                            <p class="mt-3 text-xs text-red-700">Esse formato com cifras deve ser cadastrado depois, na versao musical da musica.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $musicaAtual->ativo ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Musica ativa</label>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-3">Guia rapido de cadastro</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <p>Use esta tela apenas para a <strong>musica base</strong>.</p>
                <p>A musica base deve guardar o texto principal e a classificacao liturgica.</p>
            </div>

            <div class="mt-5 space-y-3">
                <div class="rounded-xl border border-green-100 bg-green-50 p-4">
                    <h3 class="text-sm font-semibold text-green-800 mb-2">Entra aqui</h3>
                    <ul class="space-y-1 text-sm text-green-700">
                        <li>- titulo</li>
                        <li>- artista ou ministerio</li>
                        <li>- letra sem cifras</li>
                        <li>- tempo liturgico</li>
                        <li>- momento liturgico</li>
                    </ul>
                </div>

                <div class="rounded-xl border border-amber-100 bg-amber-50 p-4">
                    <h3 class="text-sm font-semibold text-amber-800 mb-2">Nao entra aqui</h3>
                    <ul class="space-y-1 text-sm text-amber-700">
                        <li>- cifras como <code>[G]</code> ou <code>[Am]</code></li>
                        <li>- tom musical</li>
                        <li>- bpm</li>
                        <li>- video do YouTube</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-3">Fluxo correto</h2>
            <ol class="space-y-3 text-sm text-gray-600">
                <li><strong class="text-gray-800">1.</strong> Cadastre a musica base com a letra limpa.</li>
                <li><strong class="text-gray-800">2.</strong> Salve a musica.</li>
                <li><strong class="text-gray-800">3.</strong> Entre na musica e cadastre uma versao musical.</li>
                <li><strong class="text-gray-800">4.</strong> Na versao musical, adicione tom, bpm e letra com cifras.</li>
            </ol>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoLetra = document.getElementById('letra');
            const alertaCifras = document.getElementById('alerta_cifras');
            const contadorLinhas = document.getElementById('contador_linhas');
            const contadorCaracteres = document.getElementById('contador_caracteres');
            const formulario = campoLetra?.closest('form');

            if (!campoLetra) {
                return;
            }

            const regexAcorde = /^[A-G](?:#|b)?(?:(?:maj|min|dim|aug|sus|add|omit|no|m|M|º|°|\\+|-|[0-9#b])|\\([^)]+\\))*(?:\\/[A-G](?:#|b)?)?$/;

            const linhaContemApenasAcordes = (linha) => {
                const texto = linha.trim();

                if (!texto) {
                    return false;
                }

                const tokens = texto.split(/\s+/).filter(Boolean);

                if (tokens.length === 0) {
                    return false;
                }

                return tokens.every((token) => regexAcorde.test(token));
            };

            const possuiCifras = (texto) => {
                if (/\[[^\]]+\]/.test(texto)) {
                    return true;
                }

                return texto
                    .split(/\r\n|\r|\n/)
                    .some((linha) => linhaContemApenasAcordes(linha));
            };

            const atualizarResumo = () => {
                const valor = campoLetra.value || '';
                const linhas = valor.length === 0 ? 0 : valor.split(/\r\n|\r|\n/).length;
                const caracteres = valor.length;
                const encontrouCifras = possuiCifras(valor);

                contadorLinhas.textContent = linhas;
                contadorCaracteres.textContent = caracteres;

                if (encontrouCifras) {
                    alertaCifras.classList.remove('hidden');
                } else {
                    alertaCifras.classList.add('hidden');
                }
            };

            campoLetra.addEventListener('input', atualizarResumo);

            formulario?.addEventListener('submit', (event) => {
                if (!possuiCifras(campoLetra.value || '')) {
                    return;
                }

                event.preventDefault();
                alertaCifras.classList.remove('hidden');
                campoLetra.focus();
                campoLetra.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });

            atualizarResumo();
        });
    </script>
@endpush

