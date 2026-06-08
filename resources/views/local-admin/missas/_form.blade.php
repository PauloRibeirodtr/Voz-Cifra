@php
    $classeInput = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-[#8c6933] focus:ring-2 focus:ring-[#ead6b3]';
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm xl:col-span-2" data-guide-target="missa-dados">
        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4">
            @if (!empty($modoCriacao) && ($missasAnteriores ?? collect())->isNotEmpty())
                <div class="rounded-2xl border border-[#ead6b3] bg-[#fff8ed] p-4" data-guide-target="missa-reaproveitar">
                    <span class="block text-sm font-semibold text-[#5b3d1a]">Deseja reaproveitar o repert&oacute;rio de uma missa anterior?</span>
                    <div class="mt-4 flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="radio" name="reaproveitar_repertorio" value="0" {{ old('reaproveitar_repertorio', '0') !== '1' ? 'checked' : '' }} class="border-gray-300 text-[#6c4a21] focus:ring-[#d6ad6c]">
                            <span>N&atilde;o</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="radio" name="reaproveitar_repertorio" value="1" {{ old('reaproveitar_repertorio') === '1' ? 'checked' : '' }} class="border-gray-300 text-[#6c4a21] focus:ring-[#d6ad6c]">
                            <span>Sim</span>
                        </label>
                    </div>

                    <div class="mt-4" id="bloco_reaproveitar_repertorio">
                        <label class="block text-sm font-medium text-gray-700">Missa anterior</label>
                        <select name="missa_origem_id" class="{{ $classeInput }}">
                            <option value="">Selecione uma missa disponivel</option>
                            @foreach (($missasAnteriores ?? collect()) as $missaAnterior)
                                <option value="{{ $missaAnterior->id }}" @selected((string) old('missa_origem_id') === (string) $missaAnterior->id)>
                                    {{ $missaAnterior->titulo }} &bull; {{ $missaAnterior->igreja?->nome }} &bull; {{ optional($missaAnterior->data_missa)->format('d/m/Y') }} &bull; {{ substr((string) $missaAnterior->hora_inicio, 0, 5) }} - {{ substr((string) $missaAnterior->hora_fim, 0, 5) }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Ao selecionar, o formulario usa titulo, data, horarios, tempo liturgico, celebrante, observacoes e repertorio como ponto de partida.</p>
                        <div id="resumo_missa_origem" class="mt-3 hidden whitespace-pre-line rounded-xl border border-[#ead6b3] bg-white px-4 py-3 text-xs leading-relaxed text-[#5b3d1a]"></div>
                    </div>
                </div>
            @endif

            <div data-guide-target="missa-titulo">
                <label class="block text-sm font-medium text-gray-700">T&iacute;tulo da missa</label>
                <input type="text" name="titulo" value="{{ old('titulo', $missa->titulo) }}" class="{{ $classeInput }}" placeholder="Ex.: Missa dominical da noite" required>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-guide-target="missa-data-tempo">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data</label>
                    <input type="date" name="data_missa" value="{{ old('data_missa', optional($missa->data_missa)->format('Y-m-d')) }}" class="{{ $classeInput }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tempo lit&uacute;rgico</label>
                    <select name="tempo_liturgico_id" class="{{ $classeInput }}">
                        <option value="">Selecionar depois</option>
                        @foreach ($temposLiturgicos as $tempoLiturgico)
                            <option value="{{ $tempoLiturgico->id }}" @selected((string) old('tempo_liturgico_id', $missa->tempo_liturgico_id) === (string) $tempoLiturgico->id)>
                                {{ $tempoLiturgico->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-guide-target="missa-horarios">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora de in&iacute;cio</label>
                    <input type="time" name="hora_inicio" value="{{ old('hora_inicio', $missa->hora_inicio ? substr((string) $missa->hora_inicio, 0, 5) : '') }}" class="{{ $classeInput }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora de t&eacute;rmino</label>
                    <input type="time" name="hora_fim" value="{{ old('hora_fim', $missa->hora_fim ? substr((string) $missa->hora_fim, 0, 5) : '') }}" class="{{ $classeInput }}" required>
                </div>
            </div>

            <div data-guide-target="missa-celebrante">
                <label class="block text-sm font-medium text-gray-700">Celebrante</label>
                <select name="padre_id" class="{{ $classeInput }}">
                    <option value="">N&atilde;o vincular agora</option>
                    @foreach ($padres as $padre)
                        <option value="{{ $padre->id }}" @selected((string) old('padre_id', $missa->celebrante_usuario_id ?? null) === (string) $padre->id)>
                            {{ $padre->nome }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Selecione um celebrante cadastrado. O sistema evita conflito do mesmo padre em duas missas no mesmo hor&aacute;rio.</p>
            </div>

            <div data-guide-target="missa-observacoes">
                <label class="block text-sm font-medium text-gray-700">Observa&ccedil;&otilde;es</label>
                <textarea name="observacoes" rows="5" class="{{ $classeInput }}" placeholder="Observa&ccedil;&otilde;es gerais da missa, orienta&ccedil;&otilde;es ou combinados internos.">{{ old('observacoes', $missa->observacoes) }}</textarea>
            </div>
        </div>
    </section>

    <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm" data-guide-target="missa-publicacao">
        <h2 class="text-lg font-bold text-gray-900">Status da missa</h2>
        <p class="mt-2 text-sm text-gray-500">Se marcar como ativa, esta missa passa a ser a principal da igreja para o fluxo operacional.</p>

        <label class="mt-5 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $missa->exists ? (int) $missa->ativo : 1) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-[#6c4a21] focus:ring-[#d6ad6c]">
            <span>Deixar esta missa ativa para a igreja</span>
        </label>

        <label class="mt-5 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="publica_para_fieis" value="0">
            <input type="checkbox" name="publica_para_fieis" value="1" {{ old('publica_para_fieis', $missa->exists ? (int) $missa->publica_para_fieis : 1) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-[#6c4a21] focus:ring-[#d6ad6c]">
            <span>Publicar esta missa para os fi&eacute;is no link p&uacute;blico</span>
        </label>

        <label class="mt-4 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="publica_para_musicos" value="0">
            <input type="checkbox" name="publica_para_musicos" value="1" {{ old('publica_para_musicos', $missa->exists ? (int) $missa->publica_para_musicos : 1) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-[#6c4a21] focus:ring-[#d6ad6c]">
            <span>Publicar esta missa para os m&uacute;sicos com cifras e estudo</span>
        </label>

        <div class="mt-6 space-y-3" data-guide-target="missa-salvar">
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-[#6c4a21] px-5 py-3 font-semibold text-white transition hover:bg-[#5b3d1a]">
                {{ $missa->exists ? 'Salvar alterações' : 'Cadastrar missa e abrir repertório' }}
            </button>

            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                Voltar
            </a>
        </div>
    </aside>
</div>

@if (!empty($modoCriacao))
    @push('scripts')
        @php
            $missasAnterioresParaReaproveitar = collect($missasAnteriores ?? [])->map(function ($missaAnterior) {
                return [
                    'id' => (string) $missaAnterior->id,
                    'titulo' => (string) $missaAnterior->titulo,
                    'igreja_nome' => (string) ($missaAnterior->igreja?->nome ?? ''),
                    'data_missa' => optional($missaAnterior->data_missa)->format('Y-m-d'),
                    'tempo_liturgico_id' => $missaAnterior->tempo_liturgico_id ? (string) $missaAnterior->tempo_liturgico_id : '',
                    'padre_id' => $missaAnterior->celebrante_usuario_id ? (string) $missaAnterior->celebrante_usuario_id : '',
                    'hora_inicio' => $missaAnterior->hora_inicio ? substr((string) $missaAnterior->hora_inicio, 0, 5) : '',
                    'hora_fim' => $missaAnterior->hora_fim ? substr((string) $missaAnterior->hora_fim, 0, 5) : '',
                    'observacoes' => (string) ($missaAnterior->observacoes ?? ''),
                    'tempo_liturgico_nome' => (string) ($missaAnterior->tempoLiturgico?->nome ?? 'Sem tempo liturgico'),
                    'celebrante_nome' => (string) ($missaAnterior->celebrante?->nome ?? 'Sem celebrante'),
                    'musicas' => $missaAnterior->missaMusicas
                        ->sortBy('ordem')
                        ->map(fn ($item) => (string) ($item->musica?->titulo ?? 'Musica sem titulo'))
                        ->values()
                        ->all(),
                ];
            })->values();
        @endphp
        <script type="application/json" id="missa-form-reaproveitar-dados">{!! json_encode($missasAnterioresParaReaproveitar, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
        <script type="application/json" id="missa-form-preencher-ao-carregar">{!! json_encode(!session()->hasOldInput(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}</script>
        <script src="{{ asset('js/local-admin/missa-form.js') }}"></script>
    @endpush
@endif
