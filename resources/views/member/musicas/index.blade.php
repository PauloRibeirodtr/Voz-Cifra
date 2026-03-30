<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Biblioteca musical | Voz & Cifra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50 text-gray-900">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.22em] text-green-700">Voz &amp; Cifra</p>
                <h1 class="mt-2 text-3xl font-black text-gray-900">Biblioteca musical</h1>
                <p class="mt-2 text-sm text-gray-500">Estude musicas e versoes ativas fora do contexto de uma missa especifica.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:flex">
                <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Painel</a>
                <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Repertorio da igreja</a>
            </div>
        </div>

        <form action="{{ route('member.musicas.index') }}" method="GET" class="mb-6 rounded-3xl border border-gray-100 bg-white p-4 shadow-sm">
            <label class="block text-sm font-medium text-gray-700">Buscar musica</label>
            <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                <input type="text" name="busca" value="{{ $busca }}" class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Titulo, artista ou trecho da letra">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">Buscar</button>
            </div>
        </form>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            @forelse ($musicas as $musica)
                <article class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $musica->titulo }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $musica->artista ?: 'Artista nao informado' }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-semibold">
                            @if ($musica->tempoLiturgico)
                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">{{ $musica->tempoLiturgico->nome }}</span>
                            @endif
                            @if ($musica->momentoLiturgico)
                                <span class="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-indigo-700">{{ $musica->momentoLiturgico->nome }}</span>
                            @endif
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-600">{{ \\Illuminate\\Support\\Str::limit(preg_replace('/\s+/', ' ', $musica->letra), 180) }}</p>

                    <div class="mt-5 space-y-3">
                        @foreach ($musica->versoesMusicais as $versao)
                            <div class="rounded-2xl bg-gray-50 px-4 py-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm font-bold text-gray-900">{{ $versao->titulo ?: 'Versao principal' }}</p>
                                        <div class="mt-1 flex flex-wrap gap-2 text-xs font-semibold">
                                            @if ($versao->tom_musical)
                                                <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-amber-700">Tom {{ $versao->tom_musical }}</span>
                                            @endif
                                            @if ($versao->bpm)
                                                <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-blue-700">BPM {{ $versao->bpm }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ route('member.versoes.show', [$musica, $versao]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-100">Estudar cifra</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500 shadow-sm xl:col-span-2">Nenhuma musica encontrada com esse filtro.</div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $musicas->links() }}
        </div>
    </div>
</body>
</html>
