@extends('admin.layouts.admin')

@section('title', 'Configuracoes | Voz & Cifra')
@section('mobile_title', 'Configuracoes')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configuracoes</h1>
        <p class="text-sm text-gray-500 mt-1">Organize sua conta, acompanhe o estado atual do sistema e acesse os ajustes disponiveis nesta etapa.</p>
    </div>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
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

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
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

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
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

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
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
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Igrejas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_igrejas'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Musicas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_musicas'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Acordes</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_acordes'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Usuarios</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_usuarios'] }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Administradores principais</h2>
                    <p class="text-sm text-gray-500 mt-2">Cadastre outros usuarios com perfil de admin master para compartilhar a administracao central do sistema.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                    <h3 class="text-base font-bold text-gray-800">Novo admin master</h3>
                    <p class="mt-1 text-sm text-gray-500">Se a senha ficar em branco, o sistema usa o CPF sem pontuacao como senha inicial e marca primeiro acesso.</p>

                    <form action="{{ route('admin.admins-master.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Nome</label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Administrador Master">
                            @error('nome')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf') }}" required data-cpf-input class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="000.000.000-00">
                                @error('cpf')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Telefone</label>
                                <input type="text" name="telefone" value="{{ old('telefone') }}" data-telefone-input class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="(65) 99999-9999">
                                @error('telefone')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="admin@vozecifra.com">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div data-password-strength-container>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Senha inicial</label>
                                <input type="password" name="password" data-password-strength-input class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Opcional">
                                @include('partials.password-strength-meter')
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Confirmar senha</label>
                                <input type="password" name="password_confirmation" data-password-confirmation-input class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Repita a senha">
                            </div>
                        </div>

                        <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                            <input type="hidden" name="ativo" value="0">
                            <input type="checkbox" name="ativo" value="1" {{ old('ativo', '1') ? 'checked' : '' }} class="rounded border-gray-300 text-green-700 focus:ring-green-500">
                            <span>Admin master ativo</span>
                        </label>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                            Cadastrar admin master
                        </button>
                    </form>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Admins master cadastrados</h3>
                            <p class="text-sm text-gray-500">Usuarios com acesso total ao painel central.</p>
                        </div>
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                            {{ $adminsMaster->count() }} total
                        </span>
                    </div>

                    <div class="space-y-3">
                        @foreach ($adminsMaster as $adminMaster)
                            <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="font-semibold text-gray-800">{{ $adminMaster->nome }}</p>
                                        <p class="mt-1 break-all text-sm text-gray-600">{{ $adminMaster->email }}</p>
                                        <p class="mt-1 text-xs text-gray-400">
                                            CPF:
                                            {{
                                                preg_replace(
                                                    '/(\d{3})(\d{3})(\d{3})(\d{2})/',
                                                    '$1.$2.$3-$4',
                                                    preg_replace('/\D+/', '', (string) $adminMaster->cpf)
                                                ) ?: $adminMaster->cpf
                                            }}
                                        </p>
                                        @if ($adminMaster->telefone)
                                            @php
                                                $telefoneNumerico = preg_replace('/\D+/', '', (string) $adminMaster->telefone);
                                                $telefoneFormatado = strlen($telefoneNumerico) === 11
                                                    ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefoneNumerico)
                                                    : preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefoneNumerico);
                                            @endphp
                                            <p class="mt-1 text-xs text-gray-400">Telefone: {{ $telefoneFormatado ?: $adminMaster->telefone }}</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $adminMaster->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $adminMaster->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($adminMaster->primeiro_acesso)
                                            <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-[11px] font-semibold text-amber-700">
                                                Primeiro acesso
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
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
