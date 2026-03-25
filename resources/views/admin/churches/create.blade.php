@extends('admin.layouts.admin')

@section('title', 'Cadastrar igreja | Voz & Cifra')
@section('mobile_title', 'Nova igreja')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cadastrar igreja</h1>
            <p class="text-sm text-gray-500">Cadastre a igreja e o administrador local responsavel na mesma operacao.</p>
        </div>

        <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Ver igrejas
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

    <form action="{{ route('admin.igrejas.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da igreja</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex.: Paroquia Sao Jose" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Ex.: paroquia-sao-jose" class="{{ $classeInput }}" />
                    <p class="text-xs text-gray-500 mt-1">
                        O slug e o identificador amigavel da igreja na URL. Se ficar em branco, o sistema gera automaticamente a partir do nome.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CNPJ</label>
                    <input type="text" name="cnpj" value="{{ old('cnpj') }}" required placeholder="00.000.000/0000-00" data-cnpj-input class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CEP</label>
                    <input
                        type="text"
                        name="cep"
                        value="{{ old('cep') }}"
                        placeholder="00000-000"
                        maxlength="9"
                        data-cep-input
                        class="{{ $classeInput }}"
                    />
                    <p class="text-xs text-gray-500 mt-1">Ao informar um CEP valido, cidade, estado e endereco serao sugeridos automaticamente.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <input type="text" name="estado" value="{{ old('estado') }}" maxlength="2" required placeholder="UF" data-estado-input class="{{ $classeInput }} uppercase" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Endereco</label>
                    <input type="text" name="endereco" value="{{ old('endereco') }}" placeholder="Rua, numero e complemento" data-endereco-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade') }}" required placeholder="Ex.: Cuiaba" data-cidade-input class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Igreja ativa</label>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Administrador local</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="admin_nome" value="{{ old('admin_nome') }}" required placeholder="Nome completo do administrador local" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="admin_cpf" value="{{ old('admin_cpf') }}" required placeholder="000.000.000-00" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="admin.local@igreja.com" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="admin_telefone" value="{{ old('admin_telefone') }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2 bg-green-50 border border-green-100 rounded-xl p-4 text-sm text-green-800">
                    O administrador local acessara o sistema com o e-mail informado e a senha inicial sera o CPF digitado, usando apenas os numeros.
                    No primeiro acesso, ele devera trocar a senha.
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Salvar igreja
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoCep = document.querySelector('[data-cep-input]');
            const campoEndereco = document.querySelector('[data-endereco-input]');
            const campoCidade = document.querySelector('[data-cidade-input]');
            const campoEstado = document.querySelector('[data-estado-input]');
            const campoCpf = document.querySelector('[name="admin_cpf"]');
            const campoCnpj = document.querySelector('[data-cnpj-input]');
            const campoTelefone = document.querySelector('[data-telefone-input]');

            if (!campoCep || !campoEndereco || !campoCidade || !campoEstado) {
                return;
            }

            const aplicarMascaraCpf = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 11);
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
                valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                return valor;
            };

            const aplicarMascaraCnpj = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 14);
                valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
                valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
                valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                return valor;
            };

            const aplicarMascaraCep = (valor) => {
                valor = valor.replace(/\D/g, '').slice(0, 8);
                valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
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

            const preencherEnderecoPorCep = async () => {
                const cep = campoCep.value.replace(/\D/g, '');

                if (cep.length !== 8) {
                    return;
                }

                try {
                    const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const dados = await resposta.json();

                    if (dados.erro) {
                        return;
                    }

                    const enderecoCompleto = [dados.logradouro, dados.bairro].filter(Boolean).join(' - ');

                    if (!campoEndereco.value.trim()) {
                        campoEndereco.value = enderecoCompleto;
                    }

                    if (!campoCidade.value.trim()) {
                        campoCidade.value = dados.localidade ?? '';
                    }

                    if (!campoEstado.value.trim()) {
                        campoEstado.value = dados.uf ?? '';
                    }
                } catch (erro) {
                    console.warn('Nao foi possivel consultar o CEP agora.', erro);
                }
            };

            campoCep.addEventListener('blur', preencherEnderecoPorCep);
            campoCep.addEventListener('input', () => campoCep.value = aplicarMascaraCep(campoCep.value));
            campoCpf?.addEventListener('input', () => campoCpf.value = aplicarMascaraCpf(campoCpf.value));
            campoCnpj?.addEventListener('input', () => campoCnpj.value = aplicarMascaraCnpj(campoCnpj.value));
            campoTelefone?.addEventListener('input', () => campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value));
        });
    </script>
@endpush
