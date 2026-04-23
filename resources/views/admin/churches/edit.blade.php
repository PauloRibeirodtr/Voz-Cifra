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
            <p class="text-sm text-gray-500">Atualize os dados da igreja e gerencie coordenadores, admins locais e links publicos sem depender do modelo legado.</p>
        </div>

        <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Voltar
        </a>
    </div>

    <div class="admin-inline-note mb-6 px-5 py-4 text-sm leading-7">
        Esta igreja pode continuar ativa mesmo sem admin local vinculado. O papel local so se torna necessario quando alguem vai operar missas, repertorios e a rotina da comunidade.
    </div>

    <div class="mb-6">
        <span class="inline-flex rounded-full px-4 py-2 text-sm font-semibold {{ $igreja->estaOperacional() ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
            Status operacional: {{ $igreja->statusOperacionalLabel() }}
        </span>
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

    @if (session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 text-sm rounded">
            {{ session('info') }}
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

    <form action="{{ route('admin.igrejas.update', $igreja) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="admin-section-card p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da igreja</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2 rounded-3xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <img
                            id="church-image-edit-preview"
                            src="{{ $igreja->imagemUrl() }}"
                            alt="Imagem da igreja {{ $igreja->nome }}"
                            class="h-24 w-24 rounded-3xl border border-gray-200 object-cover bg-white shadow-sm"
                        />
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Imagem ou logo da igreja</label>
                            <input
                                type="file"
                                name="imagem"
                                accept="image/*"
                                class="{{ $classeInput }}"
                                data-image-preview-input
                                data-image-preview-target="#church-image-edit-preview"
                                data-default-src="{{ $igreja->imagemUrl() }}"
                            />
                            <p class="mt-2 text-xs text-gray-500">Troque a imagem quando precisar atualizar a identidade visual usada no painel e nos links publicos.</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome', $igreja->nome) }}" required placeholder="Ex.: Paroquia Sao Jose" class="{{ $classeInput }}" />
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

        <div class="admin-section-card p-6">
            @php($criarAdminLocalAgora = (bool) old('criar_admin_local_agora', (bool) $adminLocal))

            <h2 class="text-lg font-bold text-gray-800 mb-4">Administrador local</h2>
            <p class="mb-4 text-sm text-gray-500">Use a mesma regra do cadastro: a igreja pode existir sem admin local, mas a operacao local depende desse vinculo ativo.</p>

            <div class="space-y-4">
                <div>
                    <input type="hidden" name="criar_admin_local_agora" value="0">
                    <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="criar_admin_local_agora"
                            value="1"
                            {{ $criarAdminLocalAgora ? 'checked' : '' }}
                            data-admin-local-toggle
                            class="mt-1 rounded border-gray-300 text-green-700 focus:ring-green-500"
                        >
                        <span>
                            <strong class="block text-gray-900">Cadastrar ou atualizar admin local agora</strong>
                            Se marcado, o bloco abaixo fica disponivel para revisar o admin local principal. Se desmarcado, nada sera alterado nessa parte.
                        </span>
                    </label>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-900">
                    Se a caixa ficar desmarcada, o bloco abaixo fica oculto e nenhum admin local sera alterado neste formulario. Os admins locais ja vinculados continuam preservados.
                </div>

                <div data-admin-local-panel class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" name="admin_nome" value="{{ old('admin_nome', $adminLocal?->nome) }}" placeholder="Nome completo do admin local principal" class="{{ $classeInput }}" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">CPF</label>
                        <input type="text" name="admin_cpf" value="{{ old('admin_cpf', $adminLocal?->cpf) }}" placeholder="000.000.000-00" data-cpf-input class="{{ $classeInput }}" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="admin_email" value="{{ old('admin_email', $adminLocal?->email) }}" placeholder="admin.local@igreja.com" class="{{ $classeInput }}" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" name="admin_telefone" value="{{ old('admin_telefone', $adminLocal?->telefone) }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                    </div>

                    <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-4 text-sm text-blue-800">
                        Os dados do admin local principal ficam aqui. Nas secoes abaixo voce pode adicionar outros admins locais e coordenadores para a mesma igreja, reaproveitando usuarios ja existentes quando os dados coincidirem.
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                Salvar dados da igreja
            </button>
        </div>
    </form>

        @if (($adminsLocais ?? collect())->isNotEmpty())
            <div class="admin-section-card p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-2xl">
                        <h2 class="text-lg font-bold text-gray-800">Admins locais da igreja</h2>
                        <p class="mt-2 text-sm text-gray-500">
                            Escolha exatamente qual admin local tera a senha resetada. A igreja pode acumular mais de um admin local.
                        </p>
                    </div>
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                        {{ ($adminsLocais ?? collect())->count() }} admins locais
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

        <div class="admin-section-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <h2 class="text-lg font-bold text-gray-800">Usuarios vinculados a esta igreja</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Esta e a forma mais segura de promover ou revogar `admin local`, `coordenador` e `musico`: escolha apenas usuarios ja vinculados a esta igreja e aplique a acao desejada.
                    </p>
                </div>
                <div class="w-full max-w-md">
                    <label class="block text-xs font-black uppercase tracking-[0.16em] text-gray-400">Buscar vinculado</label>
                    <input
                        type="text"
                        class="{{ $classeInput }}"
                        placeholder="Pesquisar por nome, CPF ou e-mail"
                        data-vinculos-search-input
                    />
                </div>
            </div>

            <div class="mt-6 space-y-4" data-vinculos-search-list>
                @forelse (($usuariosVinculados ?? collect()) as $vinculo)
                    @php($usuarioVinculado = $vinculo->usuario)
                    @php($papeisAtivos = $vinculo->listarPapeisAtivos())
                    @php($termoBusca = mb_strtolower(trim(($usuarioVinculado->nome ?? '') . ' ' . ($usuarioVinculado->cpf ?? '') . ' ' . ($usuarioVinculado->email ?? ''))))

                    <article
                        class="rounded-2xl border border-gray-200 bg-gray-50 p-4"
                        data-vinculo-item
                        data-vinculo-search="{{ $termoBusca }}"
                    >
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-bold text-gray-900">{{ $usuarioVinculado->nome }}</h3>
                                    @if ($vinculo->responsavel_principal)
                                        <span class="admin-badge admin-badge-info">Vinculo principal</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $usuarioVinculado->email }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $usuarioVinculado->cpf }} @if($usuarioVinculado->telefone) • {{ $usuarioVinculado->telefone }} @endif</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse ($papeisAtivos as $papelAtivo)
                                        <span class="admin-badge {{ $papelAtivo->value === 'admin_local' ? 'admin-badge-success' : ($papelAtivo->value === 'coordenador' ? 'admin-badge-warning' : ($papelAtivo->value === 'musico' ? 'admin-badge-info' : 'admin-badge-neutral')) }}">
                                            {{ $papelAtivo->label() }}
                                        </span>
                                    @empty
                                        <span class="admin-badge admin-badge-neutral">Sem papel operacional ativo</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2 xl:w-[38rem]">
                                <form action="{{ route('admin.igrejas.usuarios.papeis.store', [$igreja, $usuarioVinculado]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="papel" value="admin_local">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'admin_local') ? 'bg-green-100 text-green-800' : 'bg-green-700 text-white hover:bg-green-800' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'admin_local') ? 'disabled' : '' }}
                                    >
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'admin_local') ? 'Ja e admin local' : 'Tornar admin local' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.igrejas.usuarios.papeis.destroy', [$igreja, $usuarioVinculado]) }}" method="POST" onsubmit="return confirm('Remover o papel de admin local de {{ $usuarioVinculado->nome }}?');">
                                    @csrf
                                    <input type="hidden" name="papel" value="admin_local">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'admin_local') ? 'bg-amber-600 text-white hover:bg-amber-700' : 'bg-gray-100 text-gray-400' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'admin_local') ? '' : 'disabled' }}
                                    >
                                        Remover admin local
                                    </button>
                                </form>

                                <form action="{{ route('admin.igrejas.usuarios.papeis.store', [$igreja, $usuarioVinculado]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="papel" value="coordenador">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'coordenador') ? 'bg-slate-200 text-slate-700' : 'bg-slate-900 text-white hover:bg-slate-800' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'coordenador') ? 'disabled' : '' }}
                                    >
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'coordenador') ? 'Ja e coordenador' : 'Tornar coordenador' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.igrejas.usuarios.papeis.destroy', [$igreja, $usuarioVinculado]) }}" method="POST" onsubmit="return confirm('Remover o papel de coordenador de {{ $usuarioVinculado->nome }}?');">
                                    @csrf
                                    <input type="hidden" name="papel" value="coordenador">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'coordenador') ? 'bg-orange-600 text-white hover:bg-orange-700' : 'bg-gray-100 text-gray-400' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'coordenador') ? '' : 'disabled' }}
                                    >
                                        Remover coordenador
                                    </button>
                                </form>

                                <form action="{{ route('admin.igrejas.usuarios.papeis.store', [$igreja, $usuarioVinculado]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="papel" value="musico">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'musico') ? 'bg-indigo-100 text-indigo-800' : 'bg-indigo-700 text-white hover:bg-indigo-800' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'musico') ? 'disabled' : '' }}
                                    >
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'musico') ? 'Ja e musico' : 'Tornar musico' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.igrejas.usuarios.papeis.destroy', [$igreja, $usuarioVinculado]) }}" method="POST" onsubmit="return confirm('Remover o papel de musico de {{ $usuarioVinculado->nome }}?');">
                                    @csrf
                                    <input type="hidden" name="papel" value="musico">
                                    <button
                                        type="submit"
                                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'musico') ? 'bg-violet-600 text-white hover:bg-violet-700' : 'bg-gray-100 text-gray-400' }}"
                                        {{ $papeisAtivos->contains(fn ($papel) => $papel->value === 'musico') ? '' : 'disabled' }}
                                    >
                                        Remover musico
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                        Esta igreja ainda nao possui usuarios vinculados para promover por busca.
                    </div>
                @endforelse
            </div>
        </div>

            <div class="admin-section-card p-6">
                <h2 class="text-lg font-bold text-gray-800">Adicionar ou promover admin local</h2>
                <p class="mt-2 text-sm text-gray-500">
                    Use este formulario apenas quando a pessoa ainda nao estiver vinculada a esta igreja ou quando voce realmente precisar cadastrar manualmente.
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
                        A senha inicial sera o CPF informado para contas novas. Quando a pessoa ja existir, o sistema reaproveita a conta e concede o novo papel nesta igreja.
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                            Adicionar admin local
                        </button>
                    </div>
                </form>
            </div>

        <div class="admin-section-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-lg font-bold text-gray-800">Coordenadores da igreja</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Coordenadores podem acumular papeis com musico ou admin local. Prefira usar a busca acima quando o usuario ja estiver vinculado a esta igreja.
                    </p>
                    <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                        O comportamento regional por cidade ou CEP ainda nao foi implementado. Hoje o coordenador continua sendo um papel por igreja, mesmo quando a mesma pessoa acumula varias igrejas.
                    </div>
                </div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                    {{ ($coordenadores ?? collect())->count() }} coordenadores
                </span>
            </div>

            @if (($coordenadores ?? collect())->isNotEmpty())
                <div class="mt-6 space-y-4">
                    @foreach (($coordenadores ?? collect()) as $coordenador)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                            <h3 class="text-base font-bold text-gray-900">{{ $coordenador->nome }}</h3>
                            <p class="mt-1 text-sm text-gray-600">{{ $coordenador->email }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $coordenador->cpf }} @if($coordenador->telefone) • {{ $coordenador->telefone }} @endif</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.igrejas.coordenadores.store', $igreja) }}" method="POST" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required class="{{ $classeInput }}" placeholder="Nome completo do coordenador" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CPF</label>
                    <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="{{ $classeInput }}" placeholder="000.000.000-00" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="{{ $classeInput }}" placeholder="coordenador@igreja.com" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Telefone</label>
                    <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="{{ $classeInput }}" placeholder="(65) 99999-9999" />
                </div>

                <div class="md:col-span-2 rounded-2xl border border-amber-100 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    Use os mesmos dados da conta ja existente quando quiser apenas adicionar o papel de coordenador a uma pessoa que ja participa do sistema.
                </div>

                <div class="md:col-span-2">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-800">
                        Adicionar coordenador
                    </button>
                </div>
            </form>
        </div>

        <div class="admin-section-card p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-2xl">
                    <h2 class="text-lg font-bold text-gray-800">Links publicos e QR fixos</h2>
                    <p class="mt-2 text-sm text-gray-500">
                        Cada igreja possui um link publico para fieis e outro link publico especifico para musicos. Os dois podem ser compartilhados separadamente conforme a missa for publicada.
                    </p>

                    <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Link publico dos fieis</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-semibold text-green-700 hover:underline">
                            {{ $igreja->link_publico }}
                        </a>
                    </div>

                    <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Link publico dos musicos</span>
                        <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="mt-2 block break-all text-sm font-semibold text-slate-900 hover:underline">
                            {{ $igreja->link_publico_musicos }}
                        </a>
                    </div>

                    <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Abrir pagina dos fieis
                        </a>
                        <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Abrir QR dos fieis
                        </a>
                        <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            Abrir pagina dos musicos
                        </a>
                    </div>
                </div>

                <div class="w-full max-w-xs rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Previa do QR dos fieis</span>
                    <img src="{{ $igreja->qr_code_url }}" alt="QR Code da igreja {{ $igreja->nome }}" class="mt-4 w-full rounded-xl border border-gray-200 bg-white p-3" />
                    <div class="mt-3 rounded-xl border border-green-100 bg-green-50 px-3 py-3 text-sm text-green-800">
                        Aponte a camera para o QR Code e acompanhe a pagina publica dos fieis.
                    </div>
                    <p class="mt-3 text-xs leading-5 text-gray-500">
                        O QR aponta sempre para o link publico dos fieis. O link dos musicos pode ser compartilhado separadamente para leitura com cifras.
                    </p>
                </div>
            </div>
        </div>

@endsection

@push('scripts')
    @include('partials.image-preview-script')
    <script src="{{ asset('js/admin/church-form.js') }}"></script>
    @include('partials.password-strength-script')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoBusca = document.querySelector('[data-vinculos-search-input]');
            const itens = document.querySelectorAll('[data-vinculo-item]');

            if (!campoBusca || itens.length === 0) {
                return;
            }

            campoBusca.addEventListener('input', () => {
                const termo = campoBusca.value.trim().toLowerCase();

                itens.forEach((item) => {
                    const base = item.dataset.vinculoSearch || '';
                    const exibir = termo === '' || base.includes(termo);

                    item.classList.toggle('hidden', !exibir);
                });
            });
        });
    </script>
@endpush
