@extends('local-admin.layouts.admin')

@section('title', 'Painel da igreja | Voz & Cifra')
@section('mobile_title', 'Painel da igreja')

@section('content')
    @include('local-admin.partials.church-switcher')

    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-green-700">Painel da igreja</p>
        <h1 class="mt-2 text-2xl sm:text-3xl font-black text-gray-900">{{ $igreja->nome }}</h1>
        <p class="mt-2 text-sm text-gray-500">Bem-vindo, {{ $usuario->nome }}. Aqui começa a organização prática da sua igreja.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('local-admin.missas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Missas</span>
            <span class="mt-2 block text-3xl font-black text-gray-900">{{ $metricas['total_missas'] }}</span>
        </a>
        <a href="{{ route('local-admin.missas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Missas ativas</span>
            <span class="mt-2 block text-3xl font-black text-gray-900">{{ $metricas['missas_ativas'] }}</span>
        </a>
        <a href="{{ route('local-admin.musicos.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Membros ativos</span>
            <span class="mt-2 block text-3xl font-black text-gray-900">{{ $metricas['membros_ativos'] }}</span>
        </a>
        <a href="{{ route('local-admin.musicos.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Membros pendentes</span>
            <span class="mt-2 block text-3xl font-black text-gray-900">{{ $metricas['membros_pendentes'] }}</span>
        </a>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Comece por aqui</h2>
                    <p class="mt-1 text-sm text-gray-500">Escolha a tarefa da igreja e siga pelo caminho mais direto.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <a href="{{ route('local-admin.church') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                    <span class="block text-xs font-black uppercase tracking-wider text-green-700">Igreja</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">Dados da igreja, links públicos e QR</span>
                </a>

                <a href="{{ route('local-admin.missas.create') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                    <span class="block text-xs font-black uppercase tracking-wider text-green-700">Missa</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">Cadastrar missa e preparar o repertório</span>
                </a>

                <a href="{{ route('local-admin.missas.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                    <span class="block text-xs font-black uppercase tracking-wider text-green-700">Repertório</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">Organizar músicas da missa</span>
                </a>

                <a href="{{ route('local-admin.profile') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                    <span class="block text-xs font-black uppercase tracking-wider text-green-700">Conta</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">Atualizar e-mail, telefone e senha</span>
                </a>
            </div>
        </section>

        <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Resumo da igreja</h2>
            <div class="mt-4 space-y-4 text-sm text-gray-600">
                <div>
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Administrador local</span>
                    <div class="mt-2 flex items-center gap-3">
                        <div class="h-12 w-12 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                            <img
                                src="{{ $usuario->fotoPerfilUrl() }}"
                                alt="Foto de {{ $usuario->nome }}"
                                class="h-full w-full object-cover"
                                onerror="this.onerror=null;this.src='{{ asset('logo/final.png') }}';"
                            >
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">{{ $usuario->nome }}</p>
                            <p class="break-all text-sm text-gray-500">{{ $usuario->email }}</p>
                        </div>
                    </div>
                </div>
                <div>
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Endereço</span>
                    <span>{{ $igreja->endereco }}{{ $igreja->numero ? ', '.$igreja->numero : '' }}{{ $igreja->bairro ? ' • '.$igreja->bairro : '' }}</span>
                    <p class="mt-1">{{ $igreja->cidade }} - {{ $igreja->estado }}</p>
                </div>
                <div>
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos fiéis</span>
                    <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="break-all text-green-700 hover:underline">{{ $igreja->link_publico }}</a>
                </div>
                <div>
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link dos músicos</span>
                    <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="break-all text-green-700 hover:underline">{{ $igreja->link_publico_musicos }}</a>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <a href="{{ $igreja->qr_code_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Abrir QR dos fiéis
                    </a>
                    <a href="{{ $igreja->qr_code_url_musicos }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Abrir QR dos músicos
                    </a>
                </div>
            </div>
        </aside>
    </div>

    <section class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Missas mais recentes da igreja</h2>
                <p class="mt-1 text-sm text-gray-500">Acompanhe as ultimas celebracoes cadastradas e entre direto para montar o repertorio.</p>
            </div>
            <a href="{{ route('local-admin.missas.index') }}" class="text-sm font-semibold text-green-700 hover:underline">Ver todas</a>
        </div>

        @if ($proximasMissas->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-500">
                Ainda não existe missa cadastrada para esta igreja.
            </div>
        @else
            <div class="space-y-4">
                @foreach ($proximasMissas as $missa)
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">{{ $missa->titulo }}</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    {{ optional($missa->data_missa)->format('d/m/Y') }} • {{ substr($missa->hora_inicio, 0, 5) }} - {{ substr($missa->hora_fim, 0, 5) }}
                                    @if ($missa->tempoLiturgico)
                                        • {{ $missa->tempoLiturgico->nome }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $missa->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $missa->ativo ? 'Ativa' : 'Inativa' }}
                                </span>
                                <a href="{{ route('local-admin.missas.show', $missa) }}" class="rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100">
                                    Abrir
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
