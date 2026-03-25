<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Painel da Igreja — Ministério Musical</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto p-6">
        <header class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-green-900">Painel — {{ $church->name }}</h1>
                <p class="text-sm text-green-700">Gerencie membros e repertório da sua igreja</p>
            </div>
            <div>
                <a href="{{ route('home') }}" class="text-sm text-green-700 hover:underline"><i class="fa-solid fa-arrow-left mr-2"></i>Voltar</a>
            </div>
        </header>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 bg-red-50 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <section class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Solicitações Pendentes</h2>
            @if($pendingMembers->isEmpty())
                <div class="text-gray-500">Nenhuma solicitação pendente no momento.</div>
            @else
                <div class="space-y-4">
                    @foreach($pendingMembers as $member)
                        <div class="flex items-center justify-between border p-3 rounded">
                            <div>
                                <div class="font-bold text-gray-800">{{ $member->name }}</div>
                                <div class="text-sm text-gray-500">{{ $member->email }} • {{ $member->phone }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <form action="{{ route('local-admin.approve', $member->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-green-900 text-white rounded hover:bg-green-800">Aprovar</button>
                                </form>

                                <form action="{{ route('local-admin.reject', $member->id) }}" method="POST" onsubmit="return confirm('Remover solicitação?');">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Rejeitar</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Repertório (em breve)</h2>
            <p class="text-gray-500">Nesta área você poderá gerenciar as músicas da igreja.</p>
        </section>
    </div>
</body>
</html>
