@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    $musicaAtual = $musica ?? null;
    $tituloPagina = $modo === 'create' ? 'Cadastrar musica' : 'Editar musica';
    $descricaoPagina = $modo === 'create'
        ? 'Cadastre aqui apenas a musica base. A cifra entra depois, na versao musical.'
        : 'Atualize apenas a musica base. A cifra continua sendo tratada nas versoes musicais.';
@endphp

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $tituloPagina }}</h1>
        <p class="text-sm text-gray-500">{{ $descricaoPagina }}</p>
    </div>

    <a href="{{ route('admin.musicas.index') }}" class="rounded-lg border border-gray-200 bg-white px-4 py-2 font-medium text-gray-700 hover:bg-gray-50">
        Ver musicas
    </a>
</div>

@if ($errors->any())
    <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if (session('duplicidade_musica'))
    <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <p class="font-bold">{{ session('duplicidade_musica.mensagem') }}</p>
        <div class="mt-3 space-y-2">
            @foreach (session('duplicidade_musica.musicas', []) as $musicaParecida)
                <div class="rounded-xl bg-white/80 px-4 py-3">
                    <p class="font-semibold text-gray-900">{{ $musicaParecida['titulo'] }}</p>
                    <p class="text-xs text-gray-600">
                        {{ $musicaParecida['artista'] }} • {{ $musicaParecida['ativo'] ? 'Ativa' : 'Inativa' }}
                    </p>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs font-semibold">Se for outra musica, clique em salvar novamente para continuar.</p>
    </div>

    <input type="hidden" name="confirmar_duplicidade" value="1">
@endif

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <div class="space-y-6 xl:col-span-2">
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
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
                    <p class="mt-1 text-xs text-gray-500">Use o nome principal pelo qual a musica e conhecida no ministerio.</p>
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
                    <p class="mt-1 text-xs text-gray-500">Campo opcional. Informe apenas se ajudar a identificar melhor a musica.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
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
                        <p class="mt-1 text-xs text-gray-500">Escolha quando a musica tiver uso liturgico predominante.</p>
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
                        <p class="mt-1 text-xs text-gray-500">Escolha quando a musica for claramente usada em um momento especifico.</p>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-sm font-medium text-gray-700">Letra base</label>
                        <span class="text-xs text-gray-500"><span id="contador_linhas">0</span> linhas • <span id="contador_caracteres">0</span> caracteres</span>
                    </div>
                    <textarea
                        id="letra"
                        name="letra"
                        rows="16"
                        required
                        placeholder="Digite apenas a letra da musica, sem cifras."
                        class="{{ $classeInput }}"
                    >{{ old('letra', $musicaAtual->letra ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Use a letra limpa. Se houver cifra, ela deve ser cadastrada depois na versao musical.</p>
                    <p class="mt-1 text-xs font-semibold text-amber-700">Separe as estrofes com uma linha em branco. Use "Refrão:" para destacar o refrão.</p>
                    <div id="alerta_cifras" class="hidden mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        Voce inseriu cifra na letra base. Salve apenas a letra limpa e deixe a cifra para a versao musical.
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
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-lg font-bold text-gray-800">Fluxo correto</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <p>1. Cadastre a musica base com titulo, classificacao e letra limpa.</p>
                <p>2. Salve a musica.</p>
                <p>3. Depois crie a versao musical com tom, bpm e letra com cifras.</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/admin/music-form.js') }}"></script>
@endpush
