<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Ministerio Musical</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center relative overflow-hidden">

    <div class="absolute top-0 left-0 w-full h-64 bg-green-900 rounded-b-[50%] scale-110 -translate-y-20 z-0"></div>
    <div class="absolute bottom-10 right-10 w-64 h-64 bg-green-200 rounded-full blur-3xl opacity-30 z-0"></div>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden relative z-10 p-8 m-4">
        <div class="text-center mb-8">
            <a href="{{ route('root') }}" class="inline-block">
                <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="w-24 mx-auto mb-4 drop-shadow-lg">
            </a>
            <h2 class="text-2xl font-bold text-gray-800">Bem-vindo de volta!</h2>
            <p class="text-gray-400 text-sm mt-1">Acesse sua area do sistema com seguranca.</p>
        </div>

        @if (session('status'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1" for="email">E-mail</label>
                <div class="relative">
                    <i class="fa-solid fa-envelope absolute left-4 top-3.5 text-gray-400"></i>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition"
                        placeholder="seu@email.com"
                    >
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1" for="password">Senha</label>
                <div class="relative">
                    <i class="fa-solid fa-lock absolute left-4 top-3.5 text-gray-400"></i>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full pl-11 pr-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-100 transition"
                        placeholder="Digite sua senha"
                    >
                </div>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                Por seguranca, apos 5 tentativas de login invalidas o acesso fica temporariamente bloqueado por 5 minutos.
            </div>

            <button
                type="submit"
                class="w-full bg-green-900 text-white font-bold py-3 rounded-lg hover:bg-green-800 transition transform hover:-translate-y-0.5 shadow-lg"
            >
                Entrar
            </button>
        </form>

        <div class="text-center mt-8 pt-6 border-t border-gray-100">
            <p class="text-sm text-gray-500">Acesso restrito aos perfis liberados no sistema.</p>
        </div>
    </div>

</body>
</html>
