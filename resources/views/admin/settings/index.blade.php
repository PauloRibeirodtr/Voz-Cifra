@extends('admin.layouts.admin')

@section('title', 'Configuracoes | Voz & Cifra')
@section('mobile_title', 'Configuracoes')

@section('content')
    <div class="admin-page-intro">
        <p class="admin-page-kicker">Conta e sistema</p>
        <h1 class="admin-page-title mt-2 text-2xl font-bold">Configuracoes</h1>
        <p class="admin-page-copy mt-3 text-sm sm:text-base">Organize sua conta, acompanhe o estado atual do sistema e acesse os ajustes disponiveis nesta etapa.</p>
    </div>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="admin-stat-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Perfil</h2>
                        <p class="text-sm text-gray-500 mt-2">Atualize email, telefone e senha do administrador principal em um unico lugar.</p>
                    </div>
                    <div class="h-11 w-11 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('admin.profile') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-800">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Editar perfil</span>
                    </a>
                </div>
            </div>

            <div class="admin-stat-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Avisos</h2>
                        <p class="mt-2 text-sm text-gray-500">Envie uma mensagem para todos, uma igreja, um papel ou uma pessoa especifica.</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('admin.avisos.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white hover:bg-amber-700">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Enviar aviso</span>
                    </a>
                </div>
            </div>

            <div class="admin-stat-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Sessao</h2>
                        <p class="text-sm text-gray-500 mt-2">Encerre o acesso atual com seguranca quando terminar de usar o painel.</p>
                    </div>
                    <div class="h-11 w-11 rounded-full bg-red-100 text-red-700 flex items-center justify-center">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                </div>

                <div class="mt-6">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 font-semibold text-white hover:bg-red-700">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Sair da conta</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Configuracoes do sistema</h2>
                    <p class="text-sm text-gray-500 mt-2">Informacoes basicas da instalacao atual. Nesta fase, esses dados sao apenas informativos.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fa-solid fa-sliders"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Nome do sistema</span>
                    <span class="text-sm font-semibold text-gray-800">Voz &amp; Cifra</span>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Descricao</span>
                    <span class="text-sm text-gray-700">Sistema administrativo para organizacao do ministerio musical e do nucleo liturgico.</span>
                </div>
            </div>
        </div>

        <div class="admin-highlight-surface rounded-3xl p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Informacoes do sistema</h2>
                    <p class="text-sm text-gray-500 mt-2">Resumo rapido da base atual para acompanhamento da etapa do admin master.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="admin-muted-surface rounded-xl p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Igrejas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_igrejas'] }}</span>
                </div>

                <div class="admin-muted-surface rounded-xl p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Musicas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_musicas'] }}</span>
                </div>

                <div class="admin-muted-surface rounded-xl p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Acordes</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_acordes'] }}</span>
                </div>

                <div class="admin-muted-surface rounded-xl p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Usuarios</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_usuarios'] }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Gestao de admins master</h2>
                    <p class="text-sm text-gray-500 mt-2">A criacao de novos admins master agora fica centralizada em Usuarios para evitar fluxo duplicado.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-users-gear"></i>
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
                Use o modulo <strong>Usuarios</strong> para cadastrar novas contas admin master. A edicao da propria senha, e-mail e telefone continua disponivel aqui em <strong>Perfil</strong>.
            </div>

            <div class="mt-5">
                <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Ir para Usuarios</span>
                </a>
            </div>
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

            campoCpf?.addEventListener('input', () => {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            });

            campoTelefone?.addEventListener('input', () => {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
            });

            if (campoCpf) {
                campoCpf.value = aplicarMascaraCpf(campoCpf.value);
            }

            if (campoTelefone) {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
            }
        });
    </script>
    @include('partials.password-strength-script')
@endpush
