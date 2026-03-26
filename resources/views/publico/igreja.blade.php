<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $igreja->nome }} | Voz & Cifra</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-green-950 via-green-900 to-emerald-800 text-white">
    <main class="mx-auto flex min-h-screen max-w-5xl items-center px-6 py-12">
        <section class="w-full rounded-[2rem] border border-white/10 bg-white/10 p-8 shadow-2xl backdrop-blur md:p-12">
            <div class="flex flex-col gap-8 md:flex-row md:items-start md:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-black uppercase tracking-[0.35em] text-emerald-200">Voz & Cifra</p>
                    <h1 class="mt-4 text-4xl font-black tracking-tight text-white md:text-5xl">{{ $igreja->nome }}</h1>
                    <p class="mt-4 text-base leading-7 text-emerald-50/90">
                        Este e o link publico fixo da igreja. Nesta etapa, a pagina publica ainda esta em preparacao.
                        No fluxo final, o fiel vera aqui a missa ativa da igreja sem cifras, enquanto musicos e administradores terao seus acessos proprios.
                    </p>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <span class="block text-xs font-bold uppercase tracking-wider text-emerald-200">Cidade</span>
                            <span class="mt-2 block text-lg font-semibold text-white">{{ $igreja->cidade }} - {{ $igreja->estado }}</span>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-black/10 p-4">
                            <span class="block text-xs font-bold uppercase tracking-wider text-emerald-200">Slug fixo</span>
                            <span class="mt-2 block break-all text-lg font-semibold text-white">{{ $igreja->slug }}</span>
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-sm rounded-[1.75rem] border border-white/10 bg-black/10 p-6">
                    <span class="block text-xs font-bold uppercase tracking-wider text-emerald-200">Status desta etapa</span>
                    <ul class="mt-4 space-y-3 text-sm text-emerald-50/90">
                        <li>Link publico fixo da igreja preparado.</li>
                        <li>Slug unico pronto para uso em QR Code fixo.</li>
                        <li>Missa publica completa sera conectada na proxima fase.</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
