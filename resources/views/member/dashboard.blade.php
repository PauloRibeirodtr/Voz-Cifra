<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Painel do músico | Voz & Cifra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
        <header class="mb-6 flex flex-col gap-4 rounded-3xl bg-green-900 px-6 py-6 text-white shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-200">Voz &amp; Cifra</p>
                <h1 class="mt-2 text-3xl font-black">Painel do músico</h1>
                <p class="mt-2 text-sm text-green-100">Acompanhe sua igreja, confira a próxima missa e mantenha seu acesso atualizado.</p>
            </div>
            <div class="flex flex-col gap-3 sm:items-end">
                <a href="{{ route('member.profile') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">
                    Meu perfil
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">
                        Sair
                    </button>
                </form>
            </div>
        </header>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Olá, {{ $usuario->nome }}</h2>
                <p class="mt-2 text-sm text-gray-500">Seu acesso está vinculado à igreja abaixo. As próximas etapas do módulo do músico vão expandir a leitura prática e o repertório pessoal.</p>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                        <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja?->nome ?: 'Não vinculada' }}</span>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">E-mail</span>
                        <span class="mt-2 block break-all text-base font-bold text-gray-900">{{ $usuario->email }}</span>
                    </div>
                </div>
            </section>

            <aside class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Próximos passos</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li class="rounded-2xl bg-gray-50 px-4 py-3">O painel do músico já está liberado para acesso seguro.</li>
                    <li class="rounded-2xl bg-gray-50 px-4 py-3">O repertório e a leitura musical personalizada entram nas próximas etapas.</li>
                    <li class="rounded-2xl bg-gray-50 px-4 py-3">Quando a administração da igreja preparar a missa, o conteúdo público e interno ficará mais rico.</li>
                </ul>
            </aside>
        </div>
    </div>
</body>
</html>
