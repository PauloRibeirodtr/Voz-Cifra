<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Painel do Músico — Ministério Musical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto p-6">
        <header class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-green-900">Olá, {{ $user->name }}</h1>
                <p class="text-sm text-green-700">Bem-vindo ao painel do músico — acesse suas cifras e repertório.</p>
            </div>
            <div>
                <a href="{{ route('home') }}" class="text-sm text-green-700 hover:underline"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar</a>
            </div>
        </header>

        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Minha Biblioteca</h2>
            <p class="text-gray-500">Aqui estarão suas cifras salvas e playlists.</p>
        </section>
    </div>
</body>
</html>
