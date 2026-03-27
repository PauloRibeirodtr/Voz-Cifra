<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Perfil do músico | Voz & Cifra</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    @php
        $classeInput = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-black text-gray-900">Perfil do músico</h1>
                <p class="mt-1 text-sm text-gray-500">Atualize seu acesso para continuar usando a área do músico com segurança.</p>
            </div>
            <div class="flex flex-col gap-3 sm:items-end">
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Voltar ao painel
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-red-50 hover:text-red-700">
                        Sair
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                {{ session('status') }}
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

        <form action="{{ route('member.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Dados de acesso</h2>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" value="{{ $user->nome }}" class="{{ $classeInput }} bg-gray-50" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $classeInput }}" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $user->telefone) }}" class="{{ $classeInput }}" placeholder="(65) 99999-9999">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                        <input type="password" name="password" class="{{ $classeInput }}" placeholder="Mínimo de 8 caracteres">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" class="{{ $classeInput }}" placeholder="Repita a nova senha">
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Vínculo atual</h2>
                <div class="mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-600">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                    <span class="mt-2 block text-base font-semibold text-gray-900">{{ $igreja?->nome ?: 'Não vinculada' }}</span>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">
                    Salvar perfil
                </button>
            </div>
        </form>
    </div>
</body>
</html>
