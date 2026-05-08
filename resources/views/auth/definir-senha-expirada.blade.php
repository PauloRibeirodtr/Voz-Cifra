<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link expirado | Voz & Cifra</title>
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
        <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(18,10,10,.62),rgba(18,10,10,.92)),linear-gradient(90deg,rgba(26,13,13,.88),rgba(26,13,13,.74))]"></div>

        <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8">
            <section class="w-full max-w-md rounded-[28px] border border-[#c9a15f]/20 bg-[#1d1111]/90 p-6 text-center shadow-2xl backdrop-blur-xl sm:p-8">
                <a href="{{ route('root') }}" class="inline-block">
                    <img src="{{ asset('logo/final.png') }}" alt="Logo Voz e Cifra" class="mx-auto mb-4 w-20 drop-shadow-2xl">
                </a>
                <p class="text-xs font-black uppercase tracking-[0.28em] text-[#d8b071]">Voz &amp; Cifra</p>
                <h1 class="mt-3 font-serif text-3xl font-black text-[#fff8ed] sm:text-4xl">Link expirado</h1>
                <p class="mx-auto mt-4 max-w-sm text-base leading-7 text-[#dbcab5]">
                    Este link de definicao de senha expirou ou ja foi utilizado.
                </p>

                <div class="mt-7 rounded-2xl border border-[#c9a15f]/20 bg-[#c9a15f]/10 px-5 py-4 text-left text-sm leading-6 text-[#f4ddb4]">
                    <strong class="block text-[#fff8ed]">Como continuar</strong>
                    <span>
                        Peça ao administrador para reenviar o convite de acesso. O novo link tambem tera validade de {{ $expiraEmMinutos }} minutos.
                    </span>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-2">
                    <a
                        href="{{ route('login') }}"
                        class="rounded-2xl border border-[#c9a15f]/25 bg-[#fff8ed] px-5 py-4 font-black text-[#1e130d] transition hover:-translate-y-0.5 hover:brightness-105"
                    >
                        Ir para login
                    </a>
                    <a
                        href="{{ route('root') }}"
                        class="rounded-2xl border border-[#c9a15f]/25 px-5 py-4 font-black text-[#f4ddb4] transition hover:-translate-y-0.5 hover:bg-[#c9a15f]/10"
                    >
                        Pagina inicial
                    </a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
