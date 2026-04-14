@php
    $classeInput = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
@endphp

<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
    <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
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
            @if (!empty($modoCriacao))
                <div class="rounded-2xl border border-green-100 bg-green-50 p-4">
                    <span class="block text-sm font-semibold text-green-900">Deseja reaproveitar o repertório de uma missa anterior?</span>
                    <div class="mt-4 flex flex-wrap gap-4">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="radio" name="reaproveitar_repertorio" value="0" {{ old('reaproveitar_repertorio', '0') !== '1' ? 'checked' : '' }} class="border-gray-300 text-green-700 focus:ring-green-500">
                            <span>Não</span>
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="radio" name="reaproveitar_repertorio" value="1" {{ old('reaproveitar_repertorio') === '1' ? 'checked' : '' }} class="border-gray-300 text-green-700 focus:ring-green-500">
                            <span>Sim</span>
                        </label>
                    </div>

                    <div class="mt-4" id="bloco_reaproveitar_repertorio">
                        <label class="block text-sm font-medium text-gray-700">Missa anterior</label>
                        <select name="missa_origem_id" class="{{ $classeInput }}">
                            <option value="">Selecione uma missa da mesma igreja</option>
                            @foreach (($missasAnteriores ?? collect()) as $missaAnterior)
                                <option value="{{ $missaAnterior->id }}" @selected((string) old('missa_origem_id') === (string) $missaAnterior->id)>
                                    {{ $missaAnterior->titulo }} • {{ optional($missaAnterior->data_missa)->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-gray-500">O sistema copia músicas, ordem, momento litúrgico e versão musical. Depois você pode ajustar tudo livremente.</p>
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700">Titulo da missa</label>
                <input type="text" name="titulo" value="{{ old('titulo', $missa->titulo) }}" class="{{ $classeInput }}" placeholder="Ex.: Missa dominical da noite" required>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Data</label>
                    <input type="date" name="data_missa" value="{{ old('data_missa', optional($missa->data_missa)->format('Y-m-d')) }}" class="{{ $classeInput }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tempo liturgico</label>
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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora de inicio</label>
                    <input type="time" name="hora_inicio" value="{{ old('hora_inicio', $missa->hora_inicio ? substr((string) $missa->hora_inicio, 0, 5) : '') }}" class="{{ $classeInput }}" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Hora de termino</label>
                    <input type="time" name="hora_fim" value="{{ old('hora_fim', $missa->hora_fim ? substr((string) $missa->hora_fim, 0, 5) : '') }}" class="{{ $classeInput }}" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Padre</label>
                <select name="padre_id" class="{{ $classeInput }}">
                    <option value="">Nao vincular agora</option>
                    @foreach ($padres as $padre)
                        <option value="{{ $padre->id }}" @selected((string) old('padre_id', $missa->celebrante_usuario_id ?? null) === (string) $padre->id)>
                            {{ $padre->nome }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">Selecione um padre cadastrado. O sistema evita conflito do mesmo padre em duas missas no mesmo horario.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Observacoes</label>
                <textarea name="observacoes" rows="5" class="{{ $classeInput }}" placeholder="Observacoes gerais da missa, orientacoes ou combinados internos.">{{ old('observacoes', $missa->observacoes) }}</textarea>
            </div>
        </div>
    </section>

    <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900">Status da missa</h2>
        <p class="mt-2 text-sm text-gray-500">Se marcar como ativa, esta missa passa a ser a principal da igreja para o fluxo futuro.</p>

        <label class="mt-5 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $missa->exists ? (int) $missa->ativo : 1) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-500">
            <span>Deixar esta missa ativa para a igreja</span>
        </label>

        <label class="mt-5 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="publica_para_fieis" value="0">
            <input type="checkbox" name="publica_para_fieis" value="1" {{ old('publica_para_fieis', $missa->publica_para_fieis ?? false) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-500">
            <span>Publicar esta missa para os fieis no link publico</span>
        </label>

        <label class="mt-4 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="publica_para_musicos" value="0">
            <input type="checkbox" name="publica_para_musicos" value="1" {{ old('publica_para_musicos', $missa->publica_para_musicos ?? false) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-500">
            <span>Publicar esta missa para os musicos com cifras e estudo</span>
        </label>

        <div class="mt-6 space-y-3">
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                {{ $missa->exists ? 'Salvar alteracoes' : 'Criar missa' }}
            </button>

            <a href="{{ route('local-admin.missas.index') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                Voltar
            </a>
        </div>
    </aside>
</div>

@if (!empty($modoCriacao))
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const radios = document.querySelectorAll('[name="reaproveitar_repertorio"]');
                const bloco = document.getElementById('bloco_reaproveitar_repertorio');
                const select = document.querySelector('[name="missa_origem_id"]');

                if (!bloco || !select || radios.length === 0) {
                    return;
                }

                const atualizarBloco = () => {
                    const desejaReaproveitar = document.querySelector('[name="reaproveitar_repertorio"]:checked')?.value === '1';
                    bloco.style.display = desejaReaproveitar ? 'block' : 'none';

                    if (!desejaReaproveitar) {
                        select.value = '';
                    }
                };

                radios.forEach((radio) => {
                    radio.addEventListener('change', atualizarBloco);
                });

                atualizarBloco();
            });
        </script>
    @endpush
@endif
