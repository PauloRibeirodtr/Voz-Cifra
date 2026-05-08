@extends('admin.layouts.admin')

@section('title', 'Editar Usuario | Voz & Cifra')
@section('mobile_title', 'Editar usuario')

@section('content')
    @php
        $emailTecnico = str_ends_with(mb_strtolower(trim((string) $usuario->email)), '@sem-login.local');
        $papeisPorIgreja = $usuario->vinculosIgreja
            ->where('ativo', true)
            ->mapWithKeys(fn ($vinculo) => [
                (string) $vinculo->igreja_id => $vinculo->listarPapeisAtivos()
                    ->map(fn ($papel) => $papel->value)
                    ->values()
                    ->all(),
            ]);
    @endphp

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Conta central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">{{ $usuario->nome }}</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Atualize a conta base, acompanhe os vinculos atuais e adicione novos papeis por igreja sem duplicar usuario.
                </p>
            </div>

            <div class="admin-page-actions">
                <a href="{{ route('admin.usuarios.index') }}" class="admin-btn admin-btn-secondary">Voltar</a>
            </div>
        </section>

        @if (session('success'))
            <section class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </section>
        @endif

        @if ($errors->any())
            <section class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1.7fr)_minmax(21rem,0.9fr)]">
            <div class="space-y-6">
                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Conta base</p>
                            <h2 class="text-lg font-bold text-gray-800">Dados da pessoa</h2>
                            <p class="mt-2 text-sm text-gray-500">Os papeis por igreja ficam no bloco seguinte. Aqui voce ajusta a conta principal.</p>
                        </div>
                    </div>

                    <div class="admin-panel-body">
                        <form action="{{ route('admin.usuarios.update', $usuario) }}" method="POST" class="space-y-6">
                            @csrf
                            @method('PUT')

                            <div class="admin-form-grid xl:grid-cols-2">
                                <div>
                                    <label class="admin-label">Perfil global</label>
                                    <select name="perfil_global" class="admin-select">
                                        <option value="usuario" @selected(old('perfil_global', $usuario->perfil_global) === 'usuario')>Usuario operacional</option>
                                        <option value="admin_master" @selected(old('perfil_global', $usuario->perfil_global) === 'admin_master')>Admin master</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="admin-label">Acesso global</label>
                                    <div class="admin-input flex items-center bg-gray-50 text-sm text-gray-600">
                                        {{ $usuario->ehAdminMaster() ? 'Admin master' : 'Usuario operacional' }}
                                    </div>
                                </div>

                                <div>
                                    <label class="admin-label">Nome</label>
                                    <input type="text" name="nome" value="{{ old('nome', $usuario->nome) }}" required class="admin-input">
                                </div>

                                <div>
                                    <label class="admin-label">CPF</label>
                                    <input type="text" name="cpf" value="{{ old('cpf', $usuario->cpf) }}" required data-cpf-input class="admin-input">
                                </div>

                                <div>
                                    <label class="admin-label">E-mail</label>
                                    <input type="email" name="email" value="{{ old('email', $emailTecnico ? '' : $usuario->email) }}" class="admin-input" placeholder="{{ $emailTecnico ? 'Padre sem login tecnico' : '' }}">
                                </div>

                                <div>
                                    <label class="admin-label">Telefone</label>
                                    <input type="text" name="telefone" value="{{ old('telefone', $usuario->telefone) }}" data-telefone-input class="admin-input">
                                </div>

                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-900">
                                    Para liberar ou redefinir o acesso, envie um link seguro. O link expira e deve ser reenviado se o usuario perder o prazo.
                                </div>
                            </div>

                            <div class="admin-checkbox-row">
                                <label class="admin-checkbox">
                                    <input type="hidden" name="eh_padre" value="0">
                                    <input type="checkbox" name="eh_padre" value="1" {{ old('eh_padre', $usuario->eh_padre) ? 'checked' : '' }}>
                                    <span>Marcar como padre</span>
                                </label>

                                <label class="admin-checkbox">
                                    <input type="hidden" name="ativo" value="0">
                                    <input type="checkbox" name="ativo" value="1" {{ old('ativo', $usuario->ativo) ? 'checked' : '' }}>
                                    <span>Conta ativa</span>
                                </label>
                            </div>

                            <div class="admin-actions">
                                <button type="submit" class="admin-btn admin-btn-primary">Salvar dados</button>
                                @unless ($usuario->ehAdminMaster())
                                    <button type="submit" form="reset-senha-usuario" class="admin-btn admin-btn-warm">
                                        {{ $usuario->primeiro_acesso ? 'Reenviar convite' : 'Resetar senha' }}
                                    </button>
                                @endunless
                            </div>
                        </form>

                        @unless ($usuario->ehAdminMaster())
                            <form id="reset-senha-usuario" action="{{ route('admin.usuarios.password.reset', $usuario) }}" method="POST" onsubmit="return confirm('Deseja enviar um novo link de definicao de senha para este usuario?');">
                                @csrf
                            </form>
                        @endunless
                    </div>
                </section>

                <section class="admin-highlight-surface p-5 sm:p-6">
                    <div class="mb-5">
                        <p class="admin-page-kicker">Acumulo de papeis</p>
                        <h2 class="text-lg font-bold text-gray-800">Ajustar papeis por igreja</h2>
                        <p class="mt-2 text-sm text-gray-500">Selecione a igreja e marque apenas os papeis que esta pessoa deve manter. Desmarcar um papel remove o acesso naquela igreja.</p>
                        @if ($emailTecnico)
                            <p class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                Esta conta ainda esta em modo tecnico sem login publico. Se o padre for operar com login, primeiro salve um e-mail real na conta base.
                            </p>
                        @endif
                    </div>

                    <form action="{{ route('admin.usuarios.vinculos.store', $usuario) }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label class="admin-label">Igreja</label>
                            <select name="igreja_id" class="admin-select" required data-igreja-papeis-select>
                                <option value="">Selecione a igreja</option>
                                @foreach ($igrejas as $igreja)
                                    <option value="{{ $igreja->id }}" @selected((string) old('igreja_id') === (string) $igreja->id)>{{ $igreja->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <span class="admin-label mb-3 block">Papeis nesta igreja</span>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                @foreach (\App\Enums\PapelIgreja::cases() as $papel)
                                    <label class="admin-checkbox rounded-2xl border border-gray-200 bg-white/70 px-4 py-3">
                                        <input type="checkbox" name="papeis[]" value="{{ $papel->value }}" data-papel-checkbox>
                                        <span>{{ $papel->label() }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <p class="mt-3 text-xs text-gray-500" data-papeis-helper>
                                Escolha uma igreja para carregar os papeis atuais desta conta.
                            </p>
                        </div>

                        <div class="admin-actions">
                            <button type="submit" class="admin-btn admin-btn-secondary">Salvar papeis</button>
                        </div>
                    </form>
                </section>
            </div>

            <aside class="space-y-6">
                <section class="admin-panel 2xl:sticky 2xl:top-6">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Resumo atual</p>
                            <h2 class="text-lg font-bold text-gray-800">Leitura rapida da conta</h2>
                        </div>
                    </div>

                    <div class="admin-panel-body space-y-3 text-sm leading-7 text-gray-600">
                        <p><strong>Status:</strong> {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}</p>
                        <p><strong>Perfil global:</strong> {{ $usuario->ehAdminMaster() ? 'Admin master' : 'Usuario operacional' }}</p>
                        <p><strong>Primeiro acesso:</strong> {{ $usuario->primeiro_acesso ? 'Pendente' : 'Liberado' }}</p>
                        @if ($usuario->primeiro_acesso && !$emailTecnico)
                            <p class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                                Se o convite venceu, use Reenviar convite para gerar um novo link.
                            </p>
                        @endif
                        <p><strong>Padre:</strong> {{ $usuario->ehPadre() ? 'Sim' : 'Nao' }}</p>
                        @if ($emailTecnico)
                            <p><strong>Login:</strong> Conta tecnica sem login publico</p>
                        @endif
                    </div>
                </section>

                <section class="admin-panel">
                    <div class="admin-panel-header">
                        <div>
                            <p class="admin-page-kicker">Vinculos atuais</p>
                            <h2 class="text-lg font-bold text-gray-800">Igrejas e papeis ativos</h2>
                        </div>
                    </div>

                    <div class="admin-panel-body space-y-4">
                        @forelse ($usuario->vinculosIgreja->where('ativo', true) as $vinculo)
                            <article class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4">
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
                                        <span class="admin-badge admin-badge-success">{{ $papel->label() }}</span>
                                    @empty
                                        <span class="admin-badge admin-badge-neutral">Sem papel ativo</span>
                                    @endforelse
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                                Nenhum vinculo por igreja ativo nesta conta.
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="admin-muted-surface p-5 sm:p-6">
                    <p class="admin-page-kicker">Acoes rapidas</p>
                    <h2 class="mt-2 text-lg font-bold text-gray-800">Status da conta</h2>

                    @if ($usuario->ehAdminMaster())
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                            Contas admin master nao podem ser resetadas ou inativadas por este fluxo. Para atualizar sua propria senha, use a tela de Perfil.
                        </div>
                    @else
                        <div class="mt-4 admin-actions">
                            <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" onsubmit="return confirm('Confirma alterar o status desta conta?');" class="w-full">
                                @csrf
                                <button type="submit" class="admin-btn {{ $usuario->ativo ? 'admin-btn-danger' : 'admin-btn-primary' }} w-full">
                                    {{ $usuario->ativo ? 'Inativar conta' : 'Reativar conta' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </section>
            </aside>
        </div>
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

            const papeisPorIgreja = @json($papeisPorIgreja);
            const selectIgreja = document.querySelector('[data-igreja-papeis-select]');
            const checkboxesPapeis = Array.from(document.querySelectorAll('[data-papel-checkbox]'));
            const helperPapeis = document.querySelector('[data-papeis-helper]');

            const atualizarPapeisDaIgreja = () => {
                if (!selectIgreja) {
                    return;
                }

                const igrejaId = selectIgreja.value;
                const papeisAtuais = new Set(papeisPorIgreja[igrejaId] || []);

                checkboxesPapeis.forEach((checkbox) => {
                    const papelAtual = papeisAtuais.has(checkbox.value);
                    const label = checkbox.closest('label');

                    checkbox.checked = papelAtual;
                    label?.classList.toggle('bg-emerald-50', papelAtual);
                    label?.classList.toggle('border-emerald-200', papelAtual);
                    label?.classList.toggle('text-emerald-800', papelAtual);
                });

                if (!helperPapeis) {
                    return;
                }

                if (!igrejaId) {
                    helperPapeis.textContent = 'Escolha uma igreja para carregar os papeis atuais desta conta.';
                    return;
                }

                if (papeisAtuais.size > 0) {
                    helperPapeis.textContent = 'Os papeis atuais aparecem marcados. Desmarque para remover ou marque novos papeis para conceder.';
                    return;
                }

                helperPapeis.textContent = 'Esta conta ainda nao tem papel ativo nesta igreja. Marque os papeis que deseja conceder.';
            };

            selectIgreja?.addEventListener('change', atualizarPapeisDaIgreja);
            atualizarPapeisDaIgreja();
        });
    </script>
    @include('partials.password-strength-script')
@endpush
