@extends('admin.layouts.admin')

@section('title', 'Novo Usuario | Voz & Cifra')
@section('mobile_title', 'Novo usuario')

@section('content')
    <div class="admin-page-intro flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="admin-page-kicker">Cadastro central</p>
            <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Novo usuario</h1>
            <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                Cadastre uma conta central e, se quiser, ja aplique o primeiro papel por igreja. Se a igreja ficar em branco,
                a conta sera criada e podera ser vinculada depois.
            </p>
        </div>

        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <form action="{{ route('admin.usuarios.store') }}" method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Tipo inicial</label>
                        <select name="tipo_cadastro" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                            <option value="admin_master" @selected(old('tipo_cadastro') === 'admin_master')>Admin master</option>
                            <option value="coordenador" @selected(old('tipo_cadastro') === 'coordenador')>Coordenador</option>
                            <option value="admin_local" @selected(old('tipo_cadastro') === 'admin_local')>Admin local</option>
                            <option value="musico" @selected(old('tipo_cadastro', 'musico') === 'musico')>Musico</option>
                            <option value="padre" @selected(old('tipo_cadastro') === 'padre')>Padre</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Igreja inicial</label>
                        <select name="igreja_id" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                            <option value="">Criar sem vinculo inicial</option>
                            @foreach ($igrejas as $igreja)
                                <option value="{{ $igreja->id }}" @selected((string) old('igreja_id') === (string) $igreja->id)>{{ $igreja->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nome</label>
                        <input type="text" name="nome" value="{{ old('nome') }}" required class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">CPF</label>
                        <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" placeholder="000.000.000-00">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" placeholder="Padre sem login pode ficar em branco">
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div data-password-strength-container>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Senha inicial</label>
                        <input type="password" name="password" data-password-strength-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" placeholder="Se ficar em branco, usa CPF">
                        @include('partials.password-strength-meter')
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Confirmar senha</label>
                        <input type="password" name="password_confirmation" data-password-confirmation-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nivel global</label>
                    <select name="nivel_global" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                        @foreach ($niveisGlobais as $nivel)
                            <option value="{{ $nivel }}" @selected((string) old('nivel_global', '6') === (string) $nivel)>
                                Nivel {{ $nivel }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">So vale para admin master. Os demais usuarios ficam no modelo novo de papéis por igreja.</p>
                </div>

                <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-green-700">
                    <span>Conta ativa</span>
                </label>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit" class="inline-flex items-center rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Salvar usuario
                    </button>
                    <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        <aside class="admin-highlight-surface rounded-3xl p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-800">Como este cadastro funciona</h2>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <p><strong>Admin master:</strong> ja nasce com acesso global.</p>
                <p><strong>Coordenador, admin local e musico:</strong> se houver igreja escolhida, o papel ja e aplicado. Sem igreja, a conta fica criada aguardando vinculo.</p>
                <p><strong>Padre:</strong> pode existir sem login. Se o e-mail ficar vazio, o sistema cria um e-mail tecnico interno e reaproveita essa mesma conta depois.</p>
                <p><strong>Sem duplicacao:</strong> CPF e e-mail sao reaproveitados para promover a mesma pessoa.</p>
            </div>
        </aside>
    </div>
@endsection

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

            if (campoCpf) {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
                campoCpf.addEventListener('input', () => {
                    campoCpf.value = aplicarMascaraCpf(campoCpf.value);
                });
            }

            if (campoTelefone) {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
                campoTelefone.addEventListener('input', () => {
                    campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
                });
            }
        });
    </script>
    @include('partials.password-strength-script')
@endpush
