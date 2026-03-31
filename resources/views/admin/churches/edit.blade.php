@extends('admin.layouts.admin')

@section('title', 'Editar igreja | Voz & Cifra')
@section('mobile_title', 'Editar igreja')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Editar igreja</h1>
            <p class="text-sm text-gray-500">Atualize os dados da igreja e gerencie ate 2 admins locais por igreja.</p>
        </div>

        <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.igrejas.update', $igreja) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da igreja</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $igreja->nome) }}" required placeholder="Ex.: Paroquia Sao Jose" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $igreja->slug) }}" placeholder="Ex.: paroquia-sao-jose" class="{{ $classeInput }}" />
                    <p class="text-xs text-gray-500 mt-1">
                        O slug e o nome amigavel usado na URL da igreja. Se voce deixar vazio no cadastro, ele e gerado automaticamente.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CNPJ</label>
                    <input type="text" name="cnpj" value="{{ old('cnpj', $igreja->cnpj) }}" required placeholder="00.000.000/0000-00" data-cnpj-input class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CEP</label>
                    <input
                        type="text"
                        name="cep"
                        value="{{ old('cep', $igreja->cep) }}"
                        placeholder="00000-000"
                        maxlength="9"
                        data-cep-input
                        class="{{ $classeInput }}"
                    />
                    <p class="text-xs text-gray-500 mt-1">Ao informar um CEP valido, cidade, estado e endereco podem ser sugeridos automaticamente.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <input type="text" name="estado" value="{{ old('estado', $igreja->estado) }}" maxlength="2" required placeholder="UF" data-estado-input class="{{ $classeInput }} uppercase" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Endereco</label>
                    <input type="text" name="endereco" value="{{ old('endereco', $igreja->endereco) }}" placeholder="Rua, numero e complemento" data-endereco-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade', $igreja->cidade) }}" required placeholder="Ex.: Cuiaba" data-cidade-input class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', $igreja->ativo) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Igreja ativa</label>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Admin local principal</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="admin_nome" value="{{ old('admin_nome', $adminLocal?->nome) }}" required placeholder="Nome completo do admin local principal" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="admin_cpf" value="{{ old('admin_cpf', $adminLocal?->cpf) }}" required placeholder="000.000.000-00" data-cpf-input class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email', $adminLocal?->email) }}" required placeholder="admin.local@igreja.com" class="{{ $classeInput }}" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="admin_telefone" value="{{ old('admin_telefone', $adminLocal?->telefone) }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
                    Os dados do admin local principal sao mantidos aqui. Nas secoes abaixo voce escolhe exatamente qual admin local tera a senha resetada e, se houver vaga, pode adicionar um segundo admin local.
                </div>
            </div>
        </div>

        @if (($adminsLocais ?? collect())->isNotEmpty())
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <h2 class="text-lg font-bold text-gray-800">Admins locais da igreja</h2>
                        <p class="mt-2 text-sm text-gray-500">
                            Cada igreja pode ter ate 2 admins locais. Escolha exatamente qual admin tera a senha resetada.
                        </p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ ($adminsLocais ?? collect())->count() }}/2 admins locais
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach (($adminsLocais ?? collect()) as $admin)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-bold text-gray-900">{{ $admin->nome }}</h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ $admin->email }}</p>
                                    <p class="mt-1 text-xs text-gray-400">{{ $admin->cpf }} @if($admin->telefone) • {{ $admin->telefone }} @endif</p>
                                </div>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $admin->primeiro_acesso ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $admin->primeiro_acesso ? 'Troca pendente no proximo login' : 'Acesso liberado' }}
                                </span>
                            </div>

                            <form action="{{ route('admin.igrejas.admin-local.password.reset', $igreja) }}" method="POST" class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-2" onsubmit="return confirm('Confirma a redefinicao da senha de {{ $admin->nome }}?');">
                                @csrf
                                <input type="hidden" name="origem" value="edit">
                                <input type="hidden" name="admin_local_id" value="{{ $admin->id }}">

                                <div data-password-strength-container>
                                    <label class="block text-sm font-medium text-gray-700">Nova senha manual para {{ $admin->nome }}</label>
                                    <input type="password" name="password" data-password-strength-input class="{{ $classeInput }}" placeholder="Opcional">
                                    @include('partials.password-strength-meter')
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                                    <input type="password" name="password_confirmation" data-password-confirmation-input class="{{ $classeInput }}" placeholder="Repita a nova senha">
                                </div>

                                <div class="lg:col-span-2 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                                    Se a nova senha ficar em branco, o sistema vai usar o CPF deste admin local como senha padrao e obrigar a troca no proximo acesso.
                                </div>

                                <div class="lg:col-span-2">
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">
                                        Resetar senha de {{ $admin->nome }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if (($adminsLocais ?? collect())->count() < 2)
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800">Adicionar segundo admin local</h2>
                <p class="mt-2 text-sm text-gray-500">
                    O admin master pode cadastrar mais um admin local para esta igreja. O limite e de 2 admins locais por igreja.
                </p>

                <form action="{{ route('admin.igrejas.admins-locais.store', $igreja) }}" method="POST" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome') }}" required class="{{ $classeInput }}" placeholder="Nome completo do segundo admin local" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="{{ $classeInput }}" placeholder="000.000.000-00" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="{{ $classeInput }}" placeholder="admin.local.2@igreja.com" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="{{ $classeInput }}" placeholder="(65) 99999-9999" />
                    </div>

                    <div class="md:col-span-2 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-800">
                        A senha inicial sera o CPF informado, com troca obrigatoria no primeiro acesso.
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                            Adicionar admin local
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-lg font-bold text-gray-800">Link publico e QR fixo</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Este link e permanente para a igreja e servira como base da area publica e do QR Code fixo.
                        No futuro, o conteudo desta URL mudara conforme a missa ativa organizada pelo admin local.
                    </p>

                    <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Link publico da igreja</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-semibold text-green-700 hover:underline">
                            {{ $igreja->link_publico }}
                        </a>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Abrir pagina publica
                        </a>
                        <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Abrir QR fixo
                        </a>
                    </div>
                </div>

                <div class="w-full max-w-xs rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Previa do QR Code fixo</span>
                    <img src="{{ $igreja->qr_code_url }}" alt="QR Code da igreja {{ $igreja->nome }}" class="mt-4 w-full rounded-xl border border-gray-200 bg-white p-3" />
                    <p class="mt-3 text-xs leading-5 text-gray-500">
                        O QR aponta sempre para a mesma URL da igreja. O que vai mudar depois e o conteudo exibido nessa pagina.
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Atualizar igreja
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
            const camposCpf = document.querySelectorAll('[data-cpf-input]');
            const campoCnpj = document.querySelector('[data-cnpj-input]');
            const camposTelefone = document.querySelectorAll('[data-telefone-input]');

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
            camposCpf.forEach((campo) => campo.addEventListener('input', () => campo.value = aplicarMascaraCpf(campo.value)));
            campoCnpj?.addEventListener('input', () => campoCnpj.value = aplicarMascaraCnpj(campoCnpj.value));
            camposTelefone.forEach((campo) => campo.addEventListener('input', () => campo.value = aplicarMascaraTelefone(campo.value)));
        });
    </script>
    @include('partials.password-strength-script')
@endpush
