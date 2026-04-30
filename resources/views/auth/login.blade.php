<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Voz & Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @php
        $loginImage = file_exists(public_path('images/login-liturgia.jpg'))
            ? asset('images/login-liturgia.jpg')
            : asset('images/missa1.jpg');
    @endphp
</head>
<body class="min-h-screen overflow-x-hidden bg-[#160f0f] text-[#fff8ed]">
    <div class="relative min-h-screen">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $loginImage }}');"></div>
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(18,10,10,.52),rgba(18,10,10,.88)),linear-gradient(90deg,rgba(26,13,13,.82),rgba(26,13,13,.72))]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(201,161,95,.14),transparent_28%),radial-gradient(circle_at_right_top,rgba(74,31,36,.28),transparent_24%)]"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md rounded-[28px] border border-[#c9a15f]/20 bg-[#1d1111]/88 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                <div class="text-center">
                    <a href="{{ route('root') }}" class="inline-block">
                        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mx-auto mb-4 w-20 drop-shadow-2xl">
                    </a>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-[#d8b071]">Voz &amp; Cifra</p>
                    <h1 class="mt-3 font-serif text-3xl font-black text-[#fff8ed] sm:text-4xl">Entrar no sistema</h1>
                    <p class="mx-auto mt-3 max-w-sm text-base leading-7 text-[#dbcab5]">
                        Acesso interno ao sistema.
                    </p>
                </div>

                @if (session('status'))
                    <div class="mt-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-5 py-4 text-base leading-7 text-emerald-100">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-8 rounded-2xl border border-rose-500/20 bg-rose-500/10 px-5 py-4 text-base leading-7 text-rose-100">
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.attempt') }}" method="POST" class="mt-8 space-y-6">
                    @csrf

                    <div>
                        <label class="mb-2 block text-base font-bold text-[#f4ddb4]" for="email">E-mail ou CPF</label>
                        <div class="relative">
                            <i class="fa-solid fa-user pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#c9a15f]"></i>
                            <input
                                id="email"
                                type="text"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full rounded-2xl border border-[#e8dcc8]/70 bg-[#f4efe6] py-4 pl-12 pr-4 text-lg text-[#241616] placeholder:text-[#8f7a62] focus:border-[#f4ddb4]/70 focus:outline-none focus:ring-4 focus:ring-[#c9a15f]/10"
                                placeholder="seu@email.com ou 00000000000"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-base font-bold text-[#f4ddb4]" for="password">Senha</label>
                        <div class="relative">
                            <i class="fa-solid fa-lock pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[#c9a15f]"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                class="w-full rounded-2xl border border-[#e8dcc8]/70 bg-[#f4efe6] py-4 pl-12 pr-4 text-lg text-[#241616] placeholder:text-[#8f7a62] focus:border-[#f4ddb4]/70 focus:outline-none focus:ring-4 focus:ring-[#c9a15f]/10"
                                placeholder="Digite sua senha"
                            >
                        </div>
                    </div>

                    @if ($errors->has('email') && str_contains(strtolower((string) $errors->first('email')), 'muitas tentativas'))
                        <div class="rounded-2xl border border-amber-400/20 bg-amber-400/10 px-5 py-4 text-base leading-7 text-amber-100">
                            Por seguranca, apos 5 tentativas invalidas o acesso fica bloqueado temporariamente por 5 minutos.
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-gradient-to-r from-[#d2aa66] to-[#b9884c] px-6 py-4 text-xl font-black text-[#1e130d] shadow-xl transition hover:-translate-y-0.5 hover:brightness-105"
                    >
                        Entrar
                    </button>
                </form>

                <div class="mt-8 border-t border-white/10 pt-5 text-center">
                    <a href="{{ route('root') }}" class="text-sm font-semibold text-[#d8b071] hover:text-[#f4ddb4]">
                        Voltar para a página principal
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
