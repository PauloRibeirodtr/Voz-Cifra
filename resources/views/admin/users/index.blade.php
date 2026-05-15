@extends('admin.layouts.admin')

@section('title', 'Usuários | Voz & Cifra')
@section('mobile_title', 'Usuários')

@push('styles')
    <style>
        .user-metric-link {
            position: relative;
            overflow: hidden;
            text-decoration: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .user-metric-link::after {
            content: '\f054';
            position: absolute;
            right: 1.1rem;
            top: 1.1rem;
            font-family: "Font Awesome 6 Free";
            font-size: 0.75rem;
            font-weight: 900;
            color: #94a3b8;
            opacity: 0;
            transform: translateX(-4px);
            transition: opacity 0.18s ease, transform 0.18s ease;
        }

        .user-metric-link:hover {
            border-color: rgba(22, 101, 52, 0.22);
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.1);
            transform: translateY(-1px);
        }

        .user-metric-link:hover::after {
            opacity: 1;
            transform: translateX(0);
        }

        .user-row {
            border-radius: 1.25rem;
            transition: background-color 0.18s ease, box-shadow 0.18s ease;
        }

        .user-row:hover {
            background: #f8fafc;
            box-shadow: inset 4px 0 0 #16a34a;
        }

        .user-row-main-link {
            color: inherit;
            text-decoration: none;
        }

        .user-row-main-link:hover h3 {
            color: #166534;
        }
    </style>
@endpush

@section('content')
    @php
        $badgeClasse = static fn (string $cor): string => match ($cor) {
            'verde' => 'bg-emerald-100 text-emerald-700',
            'azul' => 'bg-sky-100 text-sky-700',
            'ambar' => 'bg-amber-100 text-amber-700',
            'roxo' => 'bg-violet-100 text-violet-700',
            'cinza' => 'bg-slate-100 text-slate-700',
            default => 'bg-slate-100 text-slate-700',
        };
    @endphp

    <div class="admin-page-shell">
        <section class="admin-page-header">
            <div class="admin-page-intro">
                <p class="admin-page-kicker">Gestão central</p>
                <h1 class="admin-page-title mt-2 text-2xl font-black sm:text-3xl">Usuários</h1>
                <p class="admin-page-copy mt-3 max-w-3xl text-sm sm:text-base">
                    Gestão central das contas do sistema. Aqui o admin master cadastra usuários, acompanha quem já está vinculado a igrejas
                    e identifica quem ainda precisa receber papel por igreja.
                </p>
            </div>

            <div class="admin-page-actions">
                <a href="{{ route('admin.usuarios.create') }}" class="admin-btn admin-btn-primary">Cadastrar usuário</a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-7">
            <a href="{{ route('admin.usuarios.index') }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Total</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['total'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['presenca' => 'online']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-emerald-500">Online agora</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['online'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'admin_master']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-indigo-500">Admins master</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['admins_master'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'coordenador']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-violet-500">Coordenadores</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['coordenadores'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'admin_local']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-emerald-500">Admins locais</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['admins_locais'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'musico']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-amber-500">Músicos</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['musicos'] ?? 0 }}</div>
            </a>

            <a href="{{ route('admin.usuarios.index', ['tipo' => 'sem_vinculo']) }}" class="admin-stat-card user-metric-link block p-5">
                <div class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Sem vínculo</div>
                <div class="mt-3 text-3xl font-black text-slate-900">{{ $metricas['sem_vinculo'] ?? 0 }}</div>
            </a>
        </section>

        <section class="admin-filter-surface p-5">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="admin-form-grid xl:grid-cols-5">
                <div class="xl:col-span-2">
                    <label class="admin-label">Busca</label>
                    <input type="text" name="q" value="{{ $filtros['q'] ?? '' }}" class="admin-input" placeholder="Nome, e-mail, CPF, igreja ou cidade" minlength="3">
                    <p class="mt-2 text-xs text-gray-500">
                        Digite ao menos 3 caracteres. A busca ignora maiúsculas/minúsculas e aceita partes do texto.
                    </p>

                    @if (!($filtros['busca_minima_atingida'] ?? true))
                        <p class="mt-2 rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900">
                            Informe pelo menos 3 caracteres para aplicar a busca.
                        </p>
                    @endif
                </div>

                <div>
                    <label class="admin-label">Tipo</label>
                    <select name="tipo" class="admin-select">
                        <option value="">Todos</option>
                        <option value="admin_master" @selected(($filtros['tipo'] ?? '') === 'admin_master')>Admin master</option>
                        <option value="coordenador" @selected(($filtros['tipo'] ?? '') === 'coordenador')>Coordenador</option>
                        <option value="admin_local" @selected(($filtros['tipo'] ?? '') === 'admin_local')>Admin local</option>
                        <option value="musico" @selected(($filtros['tipo'] ?? '') === 'musico')>Músico</option>
                        <option value="padre" @selected(($filtros['tipo'] ?? '') === 'padre')>Padre</option>
                        <option value="sem_vinculo" @selected(($filtros['tipo'] ?? '') === 'sem_vinculo')>Sem vínculo</option>
                    </select>
                </div>

                <div>
                    <label class="admin-label">Presença</label>
                    <select name="presenca" class="admin-select">
                        <option value="">Todos</option>
                        <option value="online" @selected(($filtros['presenca'] ?? '') === 'online')>Online agora</option>
                        <option value="offline" @selected(($filtros['presenca'] ?? '') === 'offline')>Offline</option>
                    </select>
                </div>

                <div>
                    <label class="admin-label">Status</label>
                    <select name="status" class="admin-select">
                        <option value="">Todos</option>
                        <option value="ativo" @selected(($filtros['status'] ?? '') === 'ativo')>Ativo</option>
                        <option value="inativo" @selected(($filtros['status'] ?? '') === 'inativo')>Inativo</option>
                    </select>
                </div>

                <div class="xl:col-span-5 admin-actions">
                    <button type="submit" class="admin-btn admin-btn-warm">Filtrar</button>
                    <a href="{{ route('admin.usuarios.index') }}" class="admin-btn admin-btn-secondary">Limpar</a>
                </div>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-header">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Base de usuários</h2>
                </div>
            </div>

            <div class="admin-panel-body pt-0">
                <div class="divide-y divide-gray-100">
                    @forelse ($usuarios as $usuario)
                        @php
                            $vinculosAtivosUsuario = $usuario->vinculosIgreja?->where('ativo', true) ?? collect();
                            $ultimaAtividade = $usuario->getAttribute('ultima_atividade_em');
                            $presencaOnline = (bool) $usuario->getAttribute('presenca_online');
                        @endphp

                        <article class="user-row px-1 py-5 sm:px-2">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <a
                                    href="{{ $usuario->ehAdminMaster() && auth()->id() === $usuario->id
                                        ? route('admin.profile')
                                        : route('admin.usuarios.edit', $usuario) }}"
                                    class="user-row-main-link min-w-0 flex flex-1 gap-4"
                                    aria-label="Abrir {{ $usuario->nome }}"
                                >
                                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                                        <img
                                            src="{{ $usuario->fotoPerfilUrl() }}"
                                            alt="Foto de {{ $usuario->nome }}"
                                            class="h-full w-full object-cover"
                                            onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';"
                                        >
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="text-base font-bold text-gray-800">{{ $usuario->nome }}</h3>

                                            <span class="admin-badge {{ $usuario->ativo ? 'admin-badge-success' : 'admin-badge-danger' }}">
                                                {{ $usuario->ativo ? 'Ativo' : 'Inativo' }}
                                            </span>

                                            @if ($usuario->primeiro_acesso)
                                                <span class="admin-badge {{ $badgeClasse('ambar') }}">
                                                    Primeiro acesso
                                                </span>
                                            @endif

                                            @if ($presencaOnline)
                                                <span class="admin-badge admin-badge-success">
                                                    Online agora
                                                </span>
                                            @else
                                                <span class="admin-badge {{ $badgeClasse('cinza') }}">
                                                    Offline
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex flex-col gap-1 text-sm text-gray-500">
                                            <span class="break-all">{{ $usuario->email }}</span>
                                            <span>CPF: {{ $usuario->cpfMascarado() }}</span>
                                            <span>
                                                @if ($presencaOnline)
                                                    Ativo agora, visto {{ $ultimaAtividade?->diffForHumans() }}
                                                @elseif ($ultimaAtividade)
                                                    Última atividade {{ $ultimaAtividade->diffForHumans() }}
                                                @else
                                                    Sem atividade registrada nesta sessão
                                                @endif
                                            </span>
                                        </div>

                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @if ($usuario->ehAdminMaster())
                                                <span class="admin-badge {{ $badgeClasse('azul') }}">
                                                    Admin master
                                                </span>
                                            @endif

                                            @if ($usuario->ehPadre())
                                                <span class="admin-badge {{ $badgeClasse('roxo') }}">
                                                    Padre
                                                </span>
                                            @endif

                                            @forelse ($vinculosAtivosUsuario as $vinculo)
                                                @foreach ($vinculo->listarPapeisAtivos() as $papel)
                                                    <span class="admin-badge {{ $badgeClasse('verde') }}">
                                                        {{ $papel->label() }} em {{ $vinculo->igreja?->nome }}
                                                    </span>
                                                @endforeach
                                            @empty
                                                @if (!$usuario->ehAdminMaster())
                                                    <span class="admin-badge {{ $badgeClasse('cinza') }}">
                                                        Sem papel por igreja
                                                    </span>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                </a>

                                <div class="admin-actions xl:justify-end">
                                    <a
                                        href="{{ $usuario->ehAdminMaster() && auth()->id() === $usuario->id
                                            ? route('admin.profile')
                                            : route('admin.usuarios.edit', $usuario) }}"
                                        class="admin-btn admin-btn-secondary"
                                    >
                                        {{ $usuario->ehAdminMaster() && auth()->id() === $usuario->id ? 'Meu perfil' : 'Editar' }}
                                    </a>

                                    @if ($usuario->ehAdminMaster())
                                        <span class="admin-badge {{ $badgeClasse('ambar') }}">
                                            Senha e status do master são geridos apenas pelo próprio titular
                                        </span>
                                    @else
                                        <form action="{{ route('admin.usuarios.password.reset', $usuario) }}" method="POST" onsubmit="return confirm('Deseja enviar um novo link de definição de senha para este usuário?');">
                                            @csrf
                                            <button type="submit" class="admin-btn admin-btn-warm">
                                                {{ $usuario->primeiro_acesso ? 'Reenviar convite' : 'Redefinir senha' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.usuarios.toggle', $usuario) }}" method="POST" onsubmit="return confirm('Confirma alterar o status desta conta?');">
                                            @csrf
                                            <button type="submit" class="admin-btn {{ $usuario->ativo ? 'admin-btn-danger' : 'admin-btn-primary' }}">
                                                {{ $usuario->ativo ? 'Inativar' : 'Reativar' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="admin-empty-state text-sm">
                            Nenhum usuário encontrado com os filtros atuais.
                        </div>
                    @endforelse
                </div>

                @if ($usuarios->hasPages())
                    <div class="border-t border-gray-100 px-5 py-4">
                        {{ $usuarios->links() }}
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection
