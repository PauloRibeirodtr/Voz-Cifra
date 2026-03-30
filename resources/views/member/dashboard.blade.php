<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Painel do musico | Voz & Cifra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <header class="mb-6 flex flex-col gap-4 rounded-3xl bg-green-900 px-6 py-6 text-white shadow-sm lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-200">Voz &amp; Cifra</p>
                <h1 class="mt-2 text-3xl font-black">Painel do musico</h1>
                <p class="mt-2 text-sm text-green-100">Leitura, estudo e repertorio da sua igreja em um lugar so.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Meu repertorio</a>
                <a href="{{ route('member.musicas.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Biblioteca musical</a>
                <a href="{{ route('member.profile') }}" class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Meu perfil</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm font-semibold text-white hover:bg-white/20">Sair</button>
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
                <h2 class="text-lg font-bold text-gray-900">Ola, {{ $usuario->nome }}</h2>
                <p class="mt-2 text-sm text-gray-500">Seu acesso esta vinculado a {{ $igreja?->nome ?: 'uma igreja nao identificada' }}. A partir de agora voce ja pode estudar as versoes musicais e acompanhar o repertorio preparado pela igreja.</p>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                        <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja?->nome ?: 'Nao vinculada' }}</span>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Proxima missa</span>
                        <span class="mt-2 block text-base font-bold text-gray-900">{{ $proximaMissa?->titulo ?: 'Ainda nao cadastrada' }}</span>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Itens no repertorio</span>
                        <span class="mt-2 block text-base font-bold text-gray-900">{{ $proximaMissa?->missaMusicas?->count() ?: 0 }}</span>
                    </div>
                </div>
            </section>

            <aside class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Acesso rapido</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="{{ route('member.repertorio') }}" class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 font-semibold text-gray-800 hover:bg-green-50 hover:text-green-800">
                        <span>Ver repertorio da igreja</span>
                        <i class="fa-solid fa-list-check"></i>
                    </a>
                    <a href="{{ route('member.musicas.index') }}" class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 font-semibold text-gray-800 hover:bg-green-50 hover:text-green-800">
                        <span>Estudar musicas</span>
                        <i class="fa-solid fa-music"></i>
                    </a>
                    <a href="{{ route('member.profile') }}" class="flex items-center justify-between rounded-2xl bg-gray-50 px-4 py-4 font-semibold text-gray-800 hover:bg-green-50 hover:text-green-800">
                        <span>Configuracoes do acesso</span>
                        <i class="fa-solid fa-user-gear"></i>
                    </a>
                </div>
            </aside>
        </div>

        @if ($proximaMissa)
            <section class="mt-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Resumo da proxima missa</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ optional($proximaMissa->data_missa)->format('d/m/Y') }} as {{ substr((string) $proximaMissa->hora_inicio, 0, 5) }} @if($proximaMissa->tempoLiturgico) • {{ $proximaMissa->tempoLiturgico->nome }} @endif</p>
                    </div>
                    <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Abrir repertorio</a>
                </div>
            </section>
        @endif
    </div>
</body>
</html>
