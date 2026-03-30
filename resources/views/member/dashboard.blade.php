@extends('member.layouts.app')

@section('title', 'Painel do musico | Voz & Cifra')
@section('mobile_title', 'Painel do musico')
@section('desktop_subtitle', 'Leitura, estudo e repertorio da sua igreja')

@section('header_actions')
    <a href="{{ route('member.repertorio') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Meu repertorio
    </a>
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    <section class="rounded-[2rem] bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-800 px-6 py-7 text-white shadow-sm">
        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-200">Area do musico</p>
        <h1 class="mt-3 text-3xl font-black">Ola, {{ $usuario->nome }}</h1>
        <p class="mt-2 max-w-3xl text-sm text-emerald-100">Seu acesso esta vinculado a {{ $igreja?->nome ?: 'uma igreja nao identificada' }}. Aqui voce acompanha o repertorio, estuda cifras e mantém seus dados de acesso organizados.</p>

        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-100/80">Igreja</span>
                <span class="mt-2 block text-base font-bold text-white">{{ $igreja?->nome ?: 'Nao vinculada' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-100/80">Proxima missa</span>
                <span class="mt-2 block text-base font-bold text-white">{{ $proximaMissa?->titulo ?: 'Ainda nao cadastrada' }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-100/80">Itens no repertorio</span>
                <span class="mt-2 block text-base font-bold text-white">{{ $proximaMissa?->missaMusicas?->count() ?: 0 }}</span>
            </div>
            <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                <span class="block text-xs font-black uppercase tracking-wider text-emerald-100/80">Acesso</span>
                <span class="mt-2 block text-base font-bold text-white">{{ ($usuario->primeiro_acesso ?? false) ? 'Primeiro acesso' : 'Liberado' }}</span>
            </div>
        </div>
    </section>

    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Acesso rapido</h2>
                    <p class="mt-1 text-sm text-gray-500">Entre nas areas mais usadas do modulo do musico.</p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                <a href="{{ route('member.repertorio') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-emerald-200 hover:bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900">Meu repertorio</h3>
                        <i class="fa-solid fa-list-check text-emerald-700"></i>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Veja a missa ativa ou a proxima celebracao preparada para sua igreja.</p>
                </a>

                <a href="{{ route('member.musicas.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-emerald-200 hover:bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900">Biblioteca musical</h3>
                        <i class="fa-solid fa-music text-emerald-700"></i>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Estude musicas e versoes com leitura fora do contexto de uma missa especifica.</p>
                </a>

                <a href="{{ route('member.profile') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-emerald-200 hover:bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900">Meu perfil</h3>
                        <i class="fa-solid fa-user-pen text-emerald-700"></i>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Atualize e-mail, telefone e senha com seguranca.</p>
                </a>

                <a href="{{ route('member.settings') }}" class="rounded-2xl border border-gray-200 bg-gray-50 p-5 transition hover:border-emerald-200 hover:bg-emerald-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-bold text-gray-900">Configuracoes</h3>
                        <i class="fa-solid fa-gear text-emerald-700"></i>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">Centralize suas preferencias de acesso e suas acoes de conta.</p>
                </a>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Resumo da proxima missa</h2>

                @if ($proximaMissa)
                    <div class="mt-4 rounded-2xl bg-gray-50 p-4">
                        <p class="text-base font-bold text-gray-900">{{ $proximaMissa->titulo }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ optional($proximaMissa->data_missa)->format('d/m/Y') }} as {{ substr((string) $proximaMissa->hora_inicio, 0, 5) }}</p>
                        @if($proximaMissa->tempoLiturgico)
                            <p class="mt-1 text-sm text-gray-500">{{ $proximaMissa->tempoLiturgico->nome }}</p>
                        @endif
                        <a href="{{ route('member.repertorio') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800">Abrir repertorio</a>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500">
                        Sua igreja ainda nao tem uma missa futura ou ativa cadastrada para leitura.
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Conta</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="{{ route('member.settings') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-4 font-semibold text-gray-800 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800">
                        <span>Ir para configuracoes</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-red-600 px-4 py-4 font-semibold text-white transition hover:bg-red-700">
                            Sair da conta
                        </button>
                    </form>
                </div>
            </section>
        </aside>
    </div>
@endsection

