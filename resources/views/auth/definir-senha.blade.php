<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Definir senha | Voz & Cifra</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/final.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#160f0f] text-[#fff8ed]">
    @php
        $loginImage = file_exists(public_path('images/login-liturgia.jpg'))
            ? asset('images/login-liturgia.jpg')
            : asset('images/missa1.jpg');
    @endphp

    <div class="relative min-h-screen">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $loginImage }}');"></div>
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(18,10,10,.58),rgba(18,10,10,.9)),linear-gradient(90deg,rgba(26,13,13,.86),rgba(26,13,13,.72))]"></div>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8">
            <section class="w-full max-w-md rounded-[28px] border border-[#c9a15f]/20 bg-[#1d1111]/90 p-6 shadow-2xl backdrop-blur-xl sm:p-8">
                <div class="text-center">
                    <a href="{{ route('root') }}" class="inline-block">
                        <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mx-auto mb-4 w-20 drop-shadow-2xl">
                    </a>
                    <p class="text-xs font-black uppercase tracking-[0.28em] text-[#d8b071]">Voz &amp; Cifra</p>
                    <h1 class="mt-3 font-serif text-3xl font-black text-[#fff8ed] sm:text-4xl">Definir senha</h1>
                    <p class="mx-auto mt-3 max-w-sm text-base leading-7 text-[#dbcab5]">
                        Crie uma senha forte para liberar seu acesso ao painel.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="mt-8 rounded-2xl border border-rose-500/20 bg-rose-500/10 px-5 py-4 text-base leading-7 text-rose-100">
                        <ul class="space-y-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('password.setup.store') }}" method="POST" class="mt-8 space-y-6">
                    @csrf
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label class="mb-2 block text-base font-bold text-[#f4ddb4]" for="password">Nova senha</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autofocus
                            class="w-full rounded-2xl border border-[#e8dcc8]/70 bg-[#f4efe6] px-4 py-4 text-lg text-[#241616] placeholder:text-[#8f7a62] focus:border-[#f4ddb4]/70 focus:outline-none focus:ring-4 focus:ring-[#c9a15f]/10"
                            placeholder="Minimo de 8 caracteres"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-base font-bold text-[#f4ddb4]" for="password_confirmation">Confirmar senha</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            class="w-full rounded-2xl border border-[#e8dcc8]/70 bg-[#f4efe6] px-4 py-4 text-lg text-[#241616] placeholder:text-[#8f7a62] focus:border-[#f4ddb4]/70 focus:outline-none focus:ring-4 focus:ring-[#c9a15f]/10"
                            placeholder="Repita a nova senha"
                        >
                    </div>

                    <div class="rounded-2xl border border-[#c9a15f]/20 bg-[#c9a15f]/10 px-5 py-4 text-sm leading-6 text-[#f4ddb4]">
                        Use letra maiuscula, letra minuscula, numero e caractere especial.
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-gradient-to-r from-[#d2aa66] to-[#b9884c] px-6 py-4 text-xl font-black text-[#1e130d] shadow-xl transition hover:-translate-y-0.5 hover:brightness-105"
                    >
                        Salvar e entrar
                    </button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
