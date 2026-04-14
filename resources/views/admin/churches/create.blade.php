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
            <p class="text-sm text-gray-500">Cadastre a igreja primeiro. O admin local pode ser vinculado agora ou depois, sem travar a unidade.</p>
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
            <p class="mb-4 text-sm text-gray-500">Este bloco e opcional. Se preferir, a igreja pode ser criada agora e o admin local ser definido depois.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="admin_nome" value="{{ old('admin_nome') }}" placeholder="Nome completo do administrador local" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="admin_cpf" value="{{ old('admin_cpf') }}" placeholder="000.000.000-00" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="admin.local@igreja.com" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="admin_telefone" value="{{ old('admin_telefone') }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2 bg-green-50 border border-green-100 rounded-xl p-4 text-sm text-green-800">
                    Se voce preencher os dados acima, o admin local acessara o sistema com o e-mail informado e a senha inicial sera o CPF digitado, usando apenas os numeros.
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
    <script src="{{ asset('js/admin/church-form.js') }}"></script>
@endpush
