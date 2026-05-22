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

        .church-combobox {
            position: relative;
        }

        .church-combobox__results {
            position: absolute;
            z-index: 30;
            right: 0;
            left: 0;
            top: calc(100% + 0.35rem);
            max-height: 17rem;
            overflow-y: auto;
            border: 1px solid rgba(115, 85, 52, 0.18);
            border-radius: 1rem;
            background: #fffdf9;
            box-shadow: 0 18px 42px rgba(61, 39, 18, 0.16);
            padding: 0.4rem;
        }

        .church-combobox__results[hidden] {
            display: none;
        }

        .church-combobox__option,
        .church-combobox__empty {
            display: flex;
            width: 100%;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 0;
            border-radius: 0.8rem;
            background: transparent;
            padding: 0.82rem 0.9rem;
            color: #2f2419;
            font: inherit;
            font-size: 0.94rem;
            text-align: left;
        }

        .church-combobox__option {
            cursor: pointer;
        }

        .church-combobox__option:hover,
        .church-combobox__option:focus {
            background: #f5efe6;
            outline: none;
        }

        .church-combobox__option span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .church-combobox__option small,
        .church-combobox__empty {
            color: #7a6755;
            font-weight: 700;
        }

        body.theme-dark .church-combobox__results {
            background: var(--admin-surface);
            border-color: var(--admin-border-strong);
        }

        body.theme-dark .church-combobox__option,
        body.theme-dark .church-combobox__empty {
            color: var(--admin-text);
        }

        body.theme-dark .church-combobox__option:hover,
        body.theme-dark .church-combobox__option:focus {
            background: var(--admin-surface-muted);
        }

        body.theme-dark .church-combobox__option small,
        body.theme-dark .church-combobox__empty {
            color: var(--admin-text-soft);
        }
    </style>
@endpush

@section('content')
    @php
        $igrejaSelecionadaId = (string) old('igreja_id', request('igreja_id'));
        $igrejaSelecionadaNome = $igrejaSelecionadaId !== ''
            ? optional($igrejas->firstWhere('id', (int) $igrejaSelecionadaId))->nome
            : '';
    @endphp

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Cadastro central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Cadastrar usuário</h1>
                <p class="admin-page-description mt-4 max-w-xl text-sm text-gray-500">
                    Este é o cadastro central de usuários do sistema. Ele vale para admin master, coordenador, admin local, músico e padre.
                    O tipo inicial define regras de obrigatoriedade e papeis iniciais, mas depois é possível ajustar tudo individualmente.
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
                        
                    </div>
                </div>

                <div class="admin-panel-body">
                    <form action="{{ route('admin.usuarios.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="admin-form-grid xl:grid-cols-2">
                            <div data-guide-target="usuario-tipo">
                                <label class="admin-label">Tipo inicial</label>
                                <select name="tipo_cadastro" class="admin-select" data-tipo-cadastro>
                                    <option value="admin_master" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'admin_master')>Admin master</option>
                                    <option value="coordenador" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'coordenador')>Coordenador</option>
                                    <option value="admin_local" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'admin_local')>Admin local</option>
                                    <option value="musico" @selected(old('tipo_cadastro', request('tipo_cadastro', 'musico')) === 'musico')>Músico</option>
                                    <option value="padre" @selected(old('tipo_cadastro', request('tipo_cadastro')) === 'padre')>Padre</option>
                                </select>
                            </div>

                            <div data-guide-target="usuario-igreja">
                                <label class="admin-label">Igreja inicial</label>
                                <div class="church-combobox" data-igreja-combobox>
                                    <input type="hidden" name="igreja_id" value="{{ $igrejaSelecionadaId }}" data-igreja-inicial>
                                    <input
                                        type="search"
                                        class="admin-input"
                                        value="{{ $igrejaSelecionadaNome }}"
                                        placeholder="Digite e escolha a igreja"
                                        autocomplete="off"
                                        role="combobox"
                                        aria-autocomplete="list"
                                        aria-expanded="false"
                                        data-igreja-filtro
                                    >
                                    <div class="church-combobox__results" data-igreja-resultados hidden></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500" data-igreja-ajuda>
                                    Digite pelo menos 2 letras e escolha uma sugestao.
                                </p>
                            </div>

                            <div data-guide-target="usuario-dados">
                                <label class="admin-label">Nome</label>
                                <input type="text" name="nome" value="{{ old('nome') }}" required class="admin-input">
                            </div>

                            <div data-guide-target="usuario-cpf">
                                <label class="admin-label">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="admin-input" placeholder="000.000.000-00">
                            </div>

                            <div data-guide-target="usuario-email">
                                <label class="admin-label">E-mail</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="admin-input" placeholder="Padre sem login pode ficar em branco">
                            </div>

                            <div data-guide-target="usuario-telefone">
                                <label class="admin-label">Telefone</label>
                                <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="admin-input">
                            </div>

                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-4 text-sm font-semibold text-emerald-900">
                                O link seguro pode ser enviado agora ou depois pela acao de redefinir senha. Ele expira em 60 minutos.
                            </div>

                            <div class="xl:col-span-2">
                                
                            </div>
                        </div>

                        <div class="admin-checkbox-row" data-guide-target="usuario-acesso">
                            <label class="admin-checkbox">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }}>
                                <span>Conta ativa</span>
                            </label>
                            <label class="admin-checkbox">
                                <input type="hidden" name="enviar_convite" value="0">
                                <input type="checkbox" name="enviar_convite" value="1" {{ old('enviar_convite', false) ? 'checked' : '' }}>
                                <span>Enviar convite de acesso agora</span>
                            </label>
                        </div>

                        <div class="admin-actions" data-guide-target="usuario-salvar">
                            <button type="submit" class="admin-btn admin-btn-primary">Cadastrar usuário</button>
                            <a href="{{ route('admin.usuarios.index') }}" class="admin-btn admin-btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </section>

            
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
            const igrejaResultados = document.querySelector('[data-igreja-resultados]');
            const igrejaAjuda = document.querySelector('[data-igreja-ajuda]');
            const tiposComIgrejaObrigatoria = ['coordenador', 'admin_local', 'musico'];
            const igrejas = @json($igrejas->map(fn ($igreja) => ['id' => $igreja->id, 'nome' => $igreja->nome])->values(), JSON_UNESCAPED_UNICODE);
            let validarIgrejaSelecionada = () => true;

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

                if (igrejaAjuda) {
                    igrejaAjuda.textContent = exigeIgreja
                        ? 'Obrigatoria para este tipo de cadastro. Digite e escolha uma sugestao.'
                        : 'Opcional para admin master e padre.';
                }

                validarIgrejaSelecionada();
            };

            atualizarObrigatoriedadeIgreja();
            tipoCadastro?.addEventListener('change', atualizarObrigatoriedadeIgreja);

            if (igrejaInicial && igrejaFiltro && igrejaResultados) {
                const normalizar = (valor) => valor
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim();

                const fecharSugestoes = () => {
                    igrejaResultados.hidden = true;
                    igrejaFiltro.setAttribute('aria-expanded', 'false');
                };

                const abrirSugestoes = () => {
                    igrejaResultados.hidden = false;
                    igrejaFiltro.setAttribute('aria-expanded', 'true');
                };

                const selecionarIgreja = (igreja) => {
                    igrejaInicial.value = String(igreja.id);
                    igrejaFiltro.value = igreja.nome;
                    igrejaFiltro.setCustomValidity('');
                    fecharSugestoes();
                };

                validarIgrejaSelecionada = () => {
                    const exigeIgreja = tiposComIgrejaObrigatoria.includes(tipoCadastro?.value || '');
                    const termo = normalizar(igrejaFiltro.value);

                    if (termo === '') {
                        igrejaInicial.value = '';
                    }

                    if (!exigeIgreja) {
                        igrejaFiltro.setCustomValidity('');
                        return true;
                    }

                    if (igrejaInicial.value !== '') {
                        igrejaFiltro.setCustomValidity('');
                        return true;
                    }

                    igrejaFiltro.setCustomValidity('Digite e escolha uma igreja da lista.');
                    return false;
                };

                const renderizarSugestoes = (limparSelecao = false) => {
                    const termo = normalizar(igrejaFiltro.value);
                    if (limparSelecao) {
                        igrejaInicial.value = '';
                    }
                    igrejaResultados.replaceChildren();

                    if (termo.length < 2) {
                        fecharSugestoes();
                        validarIgrejaSelecionada();
                        return;
                    }

                    const encontradas = igrejas
                        .filter((igreja) => normalizar(igreja.nome).includes(termo))
                        .slice(0, 8);

                    if (encontradas.length === 0) {
                        const vazio = document.createElement('div');
                        vazio.className = 'church-combobox__empty';
                        vazio.textContent = 'Nenhuma igreja encontrada.';
                        igrejaResultados.appendChild(vazio);
                        abrirSugestoes();
                        validarIgrejaSelecionada();
                        return;
                    }

                    encontradas.forEach((igreja) => {
                        const botao = document.createElement('button');
                        const nome = document.createElement('span');
                        const dica = document.createElement('small');
                        botao.type = 'button';
                        botao.className = 'church-combobox__option';
                        nome.textContent = igreja.nome;
                        dica.textContent = 'Escolher';
                        botao.append(nome, dica);
                        botao.addEventListener('click', () => selecionarIgreja(igreja));
                        igrejaResultados.appendChild(botao);
                    });

                    abrirSugestoes();
                    validarIgrejaSelecionada();
                };

                igrejaFiltro.addEventListener('input', () => renderizarSugestoes(true));
                igrejaFiltro.addEventListener('focus', () => renderizarSugestoes(false));
                igrejaFiltro.addEventListener('blur', () => {
                    window.setTimeout(() => {
                        const igrejaExata = igrejas.find((igreja) => normalizar(igreja.nome) === normalizar(igrejaFiltro.value));

                        if (igrejaExata) {
                            selecionarIgreja(igrejaExata);
                        } else {
                            validarIgrejaSelecionada();
                            fecharSugestoes();
                        }
                    }, 160);
                });

                igrejaFiltro.form?.addEventListener('submit', (event) => {
                    if (!validarIgrejaSelecionada()) {
                        event.preventDefault();
                        igrejaFiltro.reportValidity();
                    }
                });

                atualizarObrigatoriedadeIgreja();
            }
        });
    </script>
    @include('partials.password-strength-script')
@endpush
