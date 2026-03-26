@extends('local-admin.layouts.admin')

@section('title', 'Meu perfil | Voz & Cifra')
@section('mobile_title', 'Meu perfil')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Meu perfil</h1>
        <p class="mt-1 text-sm text-gray-500">Atualize os dados de acesso da igreja. A troca de senha libera o uso normal do painel.</p>
    </div>

    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('local-admin.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" value="{{ $user->nome }}" class="{{ $classeInput }} bg-gray-100 text-gray-500" disabled>
                        <p class="mt-1 text-xs text-gray-500">O nome do administrador local permanece vinculado ao cadastro central da igreja.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $classeInput }}" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $user->telefone) }}" data-telefone-input placeholder="(65) 99999-9999" class="{{ $classeInput }}">
                    </div>

                    <div id="password">
                        <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                        <input type="password" name="password" class="{{ $classeInput }}" placeholder="Minimo de 8 caracteres">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" class="{{ $classeInput }}" placeholder="Repita a nova senha">
                    </div>

                    <button class="mt-2 rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
                        Atualizar perfil
                    </button>
                </div>
            </section>

            <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Dados da igreja</h2>
                <div class="mt-4 space-y-4 text-sm text-gray-600">
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                        <span>{{ $igreja->nome }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Cidade</span>
                        <span>{{ $igreja->cidade }} - {{ $igreja->estado }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link publico</span>
                        <a href="{{ $igreja->link_publico }}" target="_blank" class="break-all text-green-700 hover:underline">{{ $igreja->link_publico }}</a>
                    </div>
                </div>

                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h3 class="text-sm font-black uppercase tracking-wider text-gray-500">Sessao</h3>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 px-4 py-3 font-semibold text-white hover:bg-red-700">
                            Sair da conta
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoTelefone = document.querySelector('[data-telefone-input]');

            if (!campoTelefone) {
                return;
            }

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

            campoTelefone.addEventListener('input', () => {
                campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
            });

            campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
        });
    </script>
@endpush
