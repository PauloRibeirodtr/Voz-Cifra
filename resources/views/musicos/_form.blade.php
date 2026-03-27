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

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" name="nome" value="{{ old('nome', $musico->nome) }}" class="{{ $classeInput }}" placeholder="Nome completo do músico" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">CPF</label>
                <input type="text" name="cpf" value="{{ old('cpf', $musico->cpf) }}" class="{{ $classeInput }}" placeholder="000.000.000-00" data-cpf-input required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                <input type="text" name="telefone" value="{{ old('telefone', $musico->telefone) }}" class="{{ $classeInput }}" placeholder="(65) 99999-9999" data-telefone-input>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $musico->email) }}" class="{{ $classeInput }}" placeholder="musico@igreja.com" required>
            </div>

            @if (!empty($mostrarCampoIgreja))
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Igreja</label>
                    <select name="igreja_id" class="{{ $classeInput }}" required>
                        <option value="">Selecione a igreja</option>
                        @foreach ($igrejas as $igrejaOption)
                            <option value="{{ $igrejaOption->id }}" @selected((string) old('igreja_id', $musico->igreja_id) === (string) $igrejaOption->id)>
                                {{ $igrejaOption->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="md:col-span-2 rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 text-sm text-gray-600">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja vinculada</span>
                    <span class="mt-2 block text-base font-semibold text-gray-900">{{ $igreja->nome }}</span>
                </div>
            @endif
        </div>
    </section>

    <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-gray-900">Acesso do músico</h2>
        <p class="mt-2 text-sm text-gray-500">Se a senha inicial ficar em branco, o sistema usa o CPF como senha padrão e obriga a troca no primeiro acesso.</p>

        @unless ($musico->exists)
            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-700">Senha inicial</label>
                <input type="password" name="password" class="{{ $classeInput }}" placeholder="Opcional">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Confirmar senha inicial</label>
                <input type="password" name="password_confirmation" class="{{ $classeInput }}" placeholder="Repita a senha">
            </div>
        @endunless

        <label class="mt-5 inline-flex items-start gap-3 text-sm font-medium text-gray-700">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $musico->exists ? (int) $musico->ativo : 1) ? 'checked' : '' }} class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-500">
            <span>Deixar este músico ativo</span>
        </label>

        <div class="mt-6 space-y-3">
            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                {{ $musico->exists ? 'Salvar alterações' : 'Cadastrar músico' }}
            </button>

            <a href="{{ $rotaVoltar }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                Voltar
            </a>
        </div>
    </aside>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoCpf = document.querySelector('[data-cpf-input]');
            const campoTelefone = document.querySelector('[data-telefone-input]');

            const aplicarMascaraCpf = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 11);
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                return valor;
            };

            const aplicarMascaraTelefone = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 11);

                if (valor.length <= 10) {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }

                return valor;
            };

            campoCpf?.addEventListener('input', () => {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            });

            campoTelefone?.addEventListener('input', () => {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
            });

            if (campoCpf) {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            }

            if (campoTelefone) {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
            }
        });
    </script>
@endpush
