@extends('admin.layouts.admin')

@section('title', 'Cadastrar igreja | Voz & Cifra')
@section('mobile_title', 'Nova igreja')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-[#6c4a21] focus:ring-2 focus:ring-[#d6ad6c]/30';
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Cadastrar igreja</h1>
            <p class="text-sm text-gray-500">Cadastre a igreja primeiro. O administrador local pode ser vinculado agora ou depois, sem impedir o registro da comunidade.</p>
        </div>

        <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-gray-700 font-medium hover:bg-gray-50 sm:w-auto">
            Ver igrejas
        </a>
    </div>

    <div class="admin-inline-note mb-6 px-5 py-4 text-sm leading-7">
        Igreja sem administrador local continua válida no cadastro. O vínculo pode ser feito depois, quando a comunidade estiver pronta para operar missas, repertórios e publicações.
    </div>

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session('info'))
        <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 text-sm rounded">
            {{ session('info') }}
        </div>
    @endif

    @if (session('duplicidade_igreja'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-bold">{{ session('duplicidade_igreja.mensagem') }}</p>
            <div class="mt-3 space-y-2">
                @foreach (session('duplicidade_igreja.igrejas', []) as $igrejaParecida)
                    <div class="rounded-xl bg-white/80 px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $igrejaParecida['nome'] }}</p>
                        <p class="text-xs text-gray-600">
                            {{ $igrejaParecida['cidade'] }} - {{ $igrejaParecida['estado'] }}
                            @if (!empty($igrejaParecida['endereco']))
                                • {{ $igrejaParecida['endereco'] }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-xs font-semibold">Se for outra comunidade, clique em Salvar igreja novamente para continuar.</p>
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

    <form action="{{ route('admin.igrejas.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @if (session('duplicidade_igreja'))
            <input type="hidden" name="confirmar_duplicidade" value="1">
        @endif

        <div class="admin-section-card p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Dados da igreja</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2 rounded-3xl border border-gray-200 bg-gray-50 p-5" data-guide-target="igreja-imagem">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        <div class="h-28 w-28 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
                            <img
                                id="church-image-create-preview"
                                src="{{ asset('logo/final.png') }}"
                                alt="Imagem padrão da igreja"
                                class="h-full w-full object-cover"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Imagem ou logo da igreja</label>
                            <input
                                type="file"
                                name="imagem"
                                accept="image/*"
                                class="{{ $classeInput }}"
                                data-image-preview-input
                                data-image-preview-target="#church-image-create-preview"
                                data-default-src="{{ asset('logo/final.png') }}"
                            />
                            <p class="mt-2 text-xs text-gray-500">Use JPG, PNG ou WebP com até 2 MB. Prefira imagem quadrada ou em 4:3 para manter boa leitura nos cards, no painel e nos links públicos.</p>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2" data-guide-target="igreja-nome">
                    <label class="block text-sm font-medium text-gray-700">Nome</label>
                    <input type="text" name="nome" value="{{ old('nome') }}" required placeholder="Ex.: Paroquia Sao Jose" class="{{ $classeInput }}" />
                </div>

                <div data-guide-target="igreja-cnpj">
                    <label class="block text-sm font-medium text-gray-700">CNPJ</label>
                    <input type="text" name="cnpj" value="{{ old('cnpj') }}" required placeholder="00.000.000/0000-00" data-cnpj-input class="{{ $classeInput }}" />
                </div>

                <div data-guide-target="igreja-telefone">
                    <label class="block text-sm font-medium text-gray-700">Telefone da secretaria</label>
                    <input type="text" name="telefone_secretaria" value="{{ old('telefone_secretaria') }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
                    <p class="text-xs text-gray-500 mt-1">Opcional. Use o telefone oficial da secretaria ou atendimento da igreja.</p>
                </div>

                <div data-guide-target="igreja-cep">
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
                    <p class="text-xs text-gray-500 mt-1">Ao informar um CEP válido, cidade, estado e endereço poderão ser sugeridos automaticamente.</p>
                </div>

                <div data-guide-target="igreja-estado">
                    <label class="block text-sm font-medium text-gray-700">Estado</label>
                    <input type="text" name="estado" value="{{ old('estado') }}" maxlength="2" required placeholder="UF" data-estado-input class="{{ $classeInput }} uppercase" />
                </div>

                <div class="md:col-span-2" data-guide-target="igreja-endereco">
                    <label class="block text-sm font-medium text-gray-700">Endereço</label>
                    <input type="text" name="endereco" value="{{ old('endereco') }}" placeholder="Rua, número e complemento" data-endereco-input class="{{ $classeInput }}" />
                </div>

                <div class="md:col-span-2" data-guide-target="igreja-cidade">
                    <label class="block text-sm font-medium text-gray-700">Cidade</label>
                    <input type="text" name="cidade" value="{{ old('cidade') }}" required placeholder="Ex.: Cuiabá" data-cidade-input class="{{ $classeInput }}" />
                </div>

                <div class="flex items-center gap-3 pt-2" data-guide-target="igreja-ativo">
                    <input type="hidden" name="ativo" value="0" />
                    <input id="ativo" type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-[#6c4a21] focus:ring-[#6c4a21]" />
                    <label for="ativo" class="text-sm font-medium text-gray-700">Igreja ativa</label>
                </div>
            </div>
        </div>

        <div class="admin-section-card p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Administrador local</h2>
            <p class="mb-4 text-sm text-gray-500">Defina se a unidade já sai pronta para operação local ou se ficará aguardando administrador local.</p>

            <div class="space-y-4">
                <div data-guide-target="igreja-admin-toggle">
                    @php($criarAdminLocalAgora = (bool) old('criar_admin_local_agora', false))
                    <input type="hidden" name="criar_admin_local_agora" value="0">
                    <label class="flex items-start gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 text-sm text-gray-700">
                        <input
                            type="checkbox"
                            name="criar_admin_local_agora"
                            value="1"
                            {{ $criarAdminLocalAgora ? 'checked' : '' }}
                            data-admin-local-toggle
                            class="mt-1 rounded border-gray-300 text-[#6c4a21] focus:ring-[#6c4a21]"
                        >
                        <span>
                            <strong class="block text-gray-900">Cadastrar administrador local agora</strong>
                            Se marcado, a igreja já sai operacional com administrador local ativo. Se desmarcado, ela será criada com o status <strong>aguardando administrador local</strong>.
                        </span>
                    </label>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-900">
                    Se a caixa ficar desmarcada, a igreja será cadastrada normalmente, continuará editável pelo admin master e aparecerá com o status <strong>aguardando administrador local</strong>.
                </div>

                <div data-admin-local-panel data-guide-target="igreja-admin-dados" class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                    <div class="admin-inline-note-warm md:col-span-2 p-4 text-sm">
                        Se voce preencher os dados acima, o administrador local recebera um link seguro por e-mail para definir a propria senha. O link expira em 60 minutos.
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end" data-guide-target="igreja-salvar">
            <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-gray-700 font-medium hover:bg-gray-50">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#6c4a21] px-5 py-3 font-semibold text-white hover:bg-[#7a5528]">
                Salvar igreja
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    @include('partials.image-preview-script')
    <script src="{{ asset('js/admin/church-form.js') }}"></script>
@endpush
