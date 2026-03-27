@php
    $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
@endphp

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
    <h2 class="text-lg font-bold text-gray-800 mb-4">Dados do padre</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nome</label>
            <input type="text" name="nome" value="{{ old('nome', $padre->nome) }}" required placeholder="Nome completo do padre" class="{{ $classeInput }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">CPF</label>
            <input type="text" name="cpf" value="{{ old('cpf', $padre->cpf) }}" required data-cpf-input placeholder="000.000.000-00" class="{{ $classeInput }}">
            <p class="mt-1 text-xs text-gray-500">Mantido para organizacao interna e para evitar cadastros duplicados.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Igreja</label>
            <select name="igreja_id" class="{{ $classeInput }}">
                <option value="">Sem vinculo especifico</option>
                @foreach ($igrejas as $igreja)
                    <option value="{{ $igreja->id }}" @selected((string) old('igreja_id', $padre->igreja_id) === (string) $igreja->id)>
                        {{ $igreja->nome }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">Opcional. O padre pode ficar livre ou vinculado a uma igreja especifica.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <input type="hidden" name="ativo" value="0">
            <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $padre->exists ? $padre->ativo : true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500">
            <label for="ativo" class="text-sm font-medium text-gray-700">Padre ativo</label>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoCpf = document.querySelector('[data-cpf-input]');

            const aplicarMascaraCpf = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 11);
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                return valor;
            };

            campoCpf?.addEventListener('input', () => {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            });

            if (campoCpf) {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            }
        });
    </script>
@endpush
