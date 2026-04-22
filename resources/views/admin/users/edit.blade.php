@extends('admin.layouts.admin')

@section('title', 'Editar Usuario | Voz & Cifra')
@section('mobile_title', 'Editar usuario')

@section('content')
    @php
        $emailTecnico = str_ends_with(mb_strtolower(trim((string) $usuario->email)), '@sem-login.local');
    @endphp

    <div class="admin-page-intro flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="admin-page-kicker">Conta central</p>
            <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">{{ $usuario->nome }}</h1>
            <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                Atualize a conta base, acompanhe os vinculos atuais e adicione novos papéis por igreja sem duplicar usuario.
            </p>
        </div>

        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.7fr)_minmax(20rem,0.95fr)]">
        <div class="space-y-6">
            <section class="admin-section-card p-5 sm:p-6 lg:p-7">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-gray-800">Conta base</h2>
                    <p class="mt-1 text-sm text-gray-500">Aqui ficam os dados da pessoa. Os papéis por igreja sao gerenciados no bloco abaixo.</p>
                </div>

                <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Perfil global</label>
                            <select name="perfil_global" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                                <option value="usuario" @selected(old('perfil_global', $usuario->perfil_global) === 'usuario')>Usuario operacional</option>
                                <option value="admin_master" @selected(old('perfil_global', $usuario->perfil_global) === 'admin_master')>Admin master</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nivel global</label>
                            <select name="nivel_global" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                                @foreach ($niveisGlobais as $nivel)
                                    <option value="{{ $nivel }}" @selected((string) old('nivel_global', (string) $usuario->nivelGlobal()) === (string) $nivel)>
                                        Nivel {{ $nivel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nome</label>
                            <input type="text" name="nome" value="{{ old('nome', $usuario->nome) }}" required class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">CPF</label>
                            <input type="text" name="cpf" value="{{ old('cpf', $usuario->cpf) }}" required data-cpf-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">E-mail</label>
                            <input type="email" name="email" value="{{ old('email', $emailTecnico ? '' : $usuario->email) }}" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" placeholder="{{ $emailTecnico ? 'Padre sem login tecnico' : '' }}">
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Telefone</label>
                            <input type="text" name="telefone" value="{{ old('telefone', $usuario->telefone) }}" data-telefone-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                        <div data-password-strength-container>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Nova senha provisoria</label>
                            <input type="password" name="password" data-password-strength-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" placeholder="Opcional">
                            @include('partials.password-strength-meter')
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Confirmar senha</label>
                            <input type="password" name="password_confirmation" data-password-confirmation-input class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800">
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-5">
                        <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input type="hidden" name="eh_padre" value="0">
                            <input type="checkbox" name="eh_padre" value="1" {{ old('eh_padre', $usuario->eh_padre) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700">
                            <span>Marcar como padre</span>
                        </label>

                        <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input type="hidden" name="ativo" value="0">
                            <input type="checkbox" name="ativo" value="1" {{ old('ativo', $usuario->ativo) ? 'checked' : '' }} class="rounded border-gray-300 text-green-700">
                            <span>Conta ativa</span>
                        </label>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="inline-flex items-center rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">
                            Salvar dados
                        </button>
                    </div>
                </form>

                <form action="{{ route('admin.usuarios.password.reset', $usuario) }}" method="POST" class="mt-3" onsubmit="return confirm('Deseja resetar a senha provisoria deste usuario para o CPF sem pontuacao?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white hover:bg-amber-700">
                        Resetar senha
                    </button>
                </form>
            </section>

            <section class="admin-highlight-surface rounded-3xl p-5 shadow-sm sm:p-6">
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-gray-800">Adicionar papel por igreja</h2>
                    <p class="mt-1 text-sm text-gray-500">Use este fluxo para promover a mesma conta em outra igreja ou acumular funcoes.</p>
                    @if ($emailTecnico)
                        <p class="mt-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                            Esta conta ainda esta em modo tecnico sem login publico. Se o padre for operar com login, primeiro salve um e-mail real na conta base.
                        </p>
                    @endif
                </div>

                <form action="{{ route('admin.usuarios.vinculos.store', $usuario) }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-gray-400">Igreja</label>
                        <select name="igreja_id" class="w-full rounded-xl border border-gray-300 px-4 py-3 text-gray-800" required>
                            <option value="">Selecione a igreja</option>
                            @foreach ($igrejas as $igreja)
                                <option value="{{ $igreja->id }}">{{ $igreja->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-400">Papéis a conceder</span>
                        <div class="flex flex-wrap gap-4">
                            @foreach (\App\Enums\PapelIgreja::cases() as $papel)
                                <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700">
                                    <input type="checkbox" name="papeis[]" value="{{ $papel->value }}" class="rounded border-gray-300 text-green-700">
                                    <span>{{ $papel->label() }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="inline-flex items-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Aplicar vinculo
                    </button>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="admin-section-card p-5 shadow-sm sm:p-6 2xl:sticky 2xl:top-6">
                <h2 class="text-lg font-bold text-gray-800">Resumo atual</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p><strong>Status:</strong> {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}</p>
                    <p><strong>Perfil global:</strong> {{ $usuario->ehAdminMaster() ? 'Admin master' : 'Usuario operacional' }}</p>
                    <p><strong>Primeiro acesso:</strong> {{ $usuario->primeiro_acesso ? 'Pendente' : 'Liberado' }}</p>
                    <p><strong>Padre:</strong> {{ $usuario->ehPadre() ? 'Sim' : 'Nao' }}</p>
                    @if ($emailTecnico)
                        <p><strong>Login:</strong> Conta tecnica sem login publico</p>
                    @endif
                </div>
            </section>

            <section class="admin-section-card p-5 shadow-sm sm:p-6">
                <h2 class="text-lg font-bold text-gray-800">Vinculos atuais</h2>
                <div class="mt-4 space-y-4">
                    @forelse ($usuario->vinculosIgreja->where('ativo', true) as $vinculo)
                        <article class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $vinculo->igreja?->nome }}</div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        {{ $vinculo->responsavel_principal ? 'Vinculo principal' : 'Vinculo ativo' }}
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @forelse ($vinculo->listarPapeisAtivos() as $papel)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ $papel->label() }}
                                    </span>
                                @empty
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                        Sem papel ativo
                                    </span>
                                @endforelse
                            </div>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                            Nenhum vinculo por igreja ativo nesta conta.
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="admin-muted-surface rounded-3xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-800">Acoes rapidas</h2>
                <div class="mt-4 space-y-3">
                    <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" onsubmit="return confirm('Confirma alterar o status desta conta?');">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white {{ $usuario->ativo ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                            {{ $usuario->ativo ? 'Inativar conta' : 'Reativar conta' }}
                        </button>
                    </form>
                </div>
            </section>
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
