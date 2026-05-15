@extends('admin.layouts.admin')

@section('title', 'Cadastrar Usuário | Voz & Cifra')
@section('mobile_title', 'Cadastrar usuário')

@push('styles')
    <style>
        .user-help-details {
            border-radius: 1.25rem;
            border: 1px solid rgba(148, 163, 184, 0.24);
            background: rgba(255, 255, 255, 0.76);
            overflow: hidden;
        }

        .user-help-details summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 3.5rem;
            padding: 0 1rem;
            cursor: pointer;
            color: #111827;
            font-weight: 800;
            list-style: none;
        }

        .user-help-details summary::-webkit-details-marker {
            display: none;
        }

        .user-help-details summary::after {
            content: "+";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 999px;
            background: #f3f4f6;
            color: #166534;
            font-weight: 900;
        }

        .user-help-details[open] summary::after {
            content: "-";
        }

        .user-help-details__body {
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            padding: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Cadastro central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Cadastrar usuário</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Cadastre a conta base primeiro e aplique o primeiro papel por igreja no mesmo fluxo.
                    Musico, admin local e coordenador precisam de uma igreja inicial.
                </p>
            </div>

            <div class="admin-page-actions">
                <a href="{{ route('admin.usuarios.index') }}" class="admin-btn admin-btn-secondary">Voltar</a>
            </div>
        </section>

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
            <section class="admin-panel">
                <div class="admin-panel-header">
                    <div>
                        <p class="admin-page-kicker">Conta base</p>
                        <h2 class="text-lg font-bold text-gray-800">Dados principais do usuário</h2>
                        <p class="mt-2 text-sm text-gray-500">
                            O cadastro central vale para admin master, coordenador, admin local, músico e padre.
                        </p>
                    </div>
                </div>

                <div class="admin-panel-body">
                    <form action="{{ route('admin.usuarios.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="admin-form-grid xl:grid-cols-2">
                            <div>
                                <label class="admin-label">Tipo inicial</label>
                                <select name="tipo_cadastro" class="admin-select" data-tipo-cadastro>
                                    <option value="admin_master" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'admin_master')>Admin master</option>
                                    <option value="coordenador" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'coordenador')>Coordenador</option>
                                    <option value="admin_local" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'admin_local')>Admin local</option>
                                    <option value="musico" @selected(old('tipo_cadastro', request('tipo_cadastro', 'musico')) === 'musico')>Músico</option>
                                    <option value="padre" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'padre')>Padre</option>
                                </select>
                            </div>

                            <div>
                                <label class="admin-label">Igreja inicial</label>
                                <input
                                    type="search"
                                    class="admin-input mb-2"
                                    placeholder="Digite parte do nome da igreja"
                                    autocomplete="off"
                                    data-igreja-filtro
                                >
                                <select name="igreja_id" class="admin-select" data-igreja-inicial>
                                    <option value="">Selecionar igreja</option>
                                    @foreach ($igrejas as $igreja)
                                        <option value="{{ $igreja->id }}" @selected((string) old('igreja_id', request('igreja_id')) === (string) $igreja->id)>{{ $igreja->nome }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-xs text-gray-500" data-igreja-ajuda>
                                    Obrigatoria para musico, admin local e coordenador. Admin master nao depende de igreja.
                                </p>
                            </div>

                            <div>
                                <label class="admin-label">Nome</label>
                                <input type="text" name="nome" value="{{ old('nome') }}" required class="admin-input">
                            </div>

                            <div>
                                <label class="admin-label">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="admin-input" placeholder="000.000.000-00">
                            </div>

                            <div>
                                <label class="admin-label">E-mail</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="admin-input" placeholder="Padre sem login pode ficar em branco">
                            </div>

                            <div>
                                <label class="admin-label">Telefone</label>
                                <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="admin-input">
                            </div>

                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-900">
                                Ao salvar, o sistema enviara um link seguro para a pessoa definir a propria senha. O link expira em 60 minutos.
                            </div>

                            <div class="xl:col-span-2">
                                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-900">
                                    Quando o tipo inicial for <strong>admin master</strong>, a conta já nasce com acesso global do sistema.
                                    Não existe mais nível separado nesta etapa. Coordenador continua sendo papel por igreja e pode acumular várias igrejas.
                                </div>
                            </div>
                        </div>

                        <div class="admin-checkbox-row">
                            <label class="admin-checkbox">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }}>
                                <span>Conta ativa</span>
                            </label>
                        </div>

                        <div class="admin-actions">
                            <button type="submit" class="admin-btn admin-btn-primary">Cadastrar usuário</button>
                            <a href="{{ route('admin.usuarios.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="space-y-6">
                <details class="user-help-details admin-highlight-surface 2xl:sticky 2xl:top-6">
                    <summary>Como funciona este cadastro</summary>
                    <div class="user-help-details__body space-y-3 text-sm leading-7 text-gray-600">
                        <p><strong>Admin master:</strong> acesso global, sem igreja obrigatoria.</p>
                        <p><strong>Coordenador, admin local e musico:</strong> exigem igreja inicial e ja recebem o papel ao salvar.</p>
                        <p><strong>Padre:</strong> pode ficar sem e-mail. O sistema cria um e-mail tecnico interno se precisar.</p>
                        <p><strong>Sem duplicacao:</strong> CPF e e-mail reaproveitam a mesma pessoa.</p>
                    </div>
                </details>

                <details class="user-help-details admin-muted-surface">
                    <summary>Fluxo recomendado</summary>
                    <div class="user-help-details__body text-sm leading-7 text-gray-600">
                        Primeiro cadastre a conta. Depois aplique papel por igreja somente quando alguem for operar missa, repertorio ou rotina daquela comunidade.
                    </div>
                </details>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoCpf = document.querySelector('[data-cpf-input]');
            const campoTelefone = document.querySelector('[data-telefone-input]');
            const tipoCadastro = document.querySelector('[data-tipo-cadastro]');
            const igrejaInicial = document.querySelector('[data-igreja-inicial]');
            const igrejaFiltro = document.querySelector('[data-igreja-filtro]');
            const igrejaAjuda = document.querySelector('[data-igreja-ajuda]');
            const tiposComIgrejaObrigatoria = ['coordenador', 'admin_local', 'musico'];

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

            const atualizarObrigatoriedadeIgreja = () => {
                if (!tipoCadastro || !igrejaInicial) {
                    return;
                }

                const exigeIgreja = tiposComIgrejaObrigatoria.includes(tipoCadastro.value);
                igrejaInicial.required = exigeIgreja;

                if (igrejaAjuda) {
                    igrejaAjuda.textContent = exigeIgreja
                        ? 'Obrigatoria para este tipo de cadastro.'
                        : 'Opcional para admin master e padre.';
                }
            };

            atualizarObrigatoriedadeIgreja();
            tipoCadastro?.addEventListener('change', atualizarObrigatoriedadeIgreja);

            if (igrejaInicial && igrejaFiltro) {
                const opcoesIgreja = Array.from(igrejaInicial.options).map((option) => ({
                    value: option.value,
                    text: option.textContent || '',
                    selected: option.selected,
                }));

                const normalizar = (valor) => valor
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();

                const renderizarOpcoesIgreja = () => {
                    const termo = normalizar(igrejaFiltro.value);
                    const valorAtual = igrejaInicial.value;
                    const opcoesFiltradas = opcoesIgreja.filter((option, index) => {
                        return index === 0 || termo === '' || normalizar(option.text).includes(termo);
                    });

                    igrejaInicial.replaceChildren();

                    opcoesFiltradas.forEach((optionData, index) => {
                        const option = new Option(
                            index === 0 && termo !== '' ? `Selecionar igreja (${Math.max(opcoesFiltradas.length - 1, 0)} encontrada(s))` : optionData.text,
                            optionData.value,
                            false,
                            optionData.value === valorAtual
                        );
                        igrejaInicial.appendChild(option);
                    });

                    if (opcoesFiltradas.length === 1 && termo !== '') {
                        const option = new Option('Nenhuma igreja encontrada', '', false, false);
                        option.disabled = true;
                        igrejaInicial.appendChild(option);
                    }
                };

                igrejaFiltro.addEventListener('input', renderizarOpcoesIgreja);
            }
        });
    </script>
    @include('partials.password-strength-script')
@endpush
