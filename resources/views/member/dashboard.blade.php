@extends('member.layouts.app')

@php($isCoordenadorArea = request()->routeIs('coordenador.*'))
@php($routePrefix = $isCoordenadorArea ? 'coordenador' : 'member')
@php($tituloArea = $isCoordenadorArea ? 'Painel do coordenador' : 'Painel do musico')
@php($subtituloArea = $isCoordenadorArea ? 'Gestao musical e operacional da sua igreja' : 'Leitura, estudo e repertorio da sua igreja')

@section('title', $tituloArea . ' | Voz & Cifra')
@section('mobile_title', $tituloArea)
@section('desktop_subtitle', $subtituloArea)

@section('header_actions')
    @if ($isCoordenadorArea)
        <a href="{{ route('coordenador.musicas.index') }}" class="music-btn">
            Musicas e versoes
        </a>
    @else
        <a href="{{ route('member.repertorio') }}" class="music-btn">
            Meu repertorio
        </a>
    @endif
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
            {{ session('status') }}
        </div>
    @endif

    @include('member.partials.church-switcher', ['igrejaAtual' => $igreja])

    <section class="rounded-[2rem] bg-gradient-to-r from-emerald-900 via-emerald-800 to-teal-800 px-6 py-7 text-white shadow-sm">
        <p class="text-[11px] font-black uppercase tracking-[0.22em] text-emerald-200">{{ $isCoordenadorArea ? 'Area do coordenador' : 'Area do musico' }}</p>
        <h1 class="mt-3 text-3xl font-black">Ola, {{ $usuario->nome }}</h1>
        <p class="mt-2 max-w-3xl text-sm text-emerald-100">
            Seu acesso principal esta vinculado a {{ $igreja?->nome ?: 'uma igreja nao identificada' }}.
            @if ($isCoordenadorArea)
                Aqui voce gerencia musicos, organiza musicas e acompanha a operacao musical da comunidade.
            @else
                Aqui voce acompanha o repertorio, estuda cifras e mantem seus dados de acesso organizados.
            @endif
        </p>

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
        <section class="music-card rounded-3xl p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Comece por aqui</h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $isCoordenadorArea ? 'Escolha uma tarefa de gestao musical sem precisar procurar no menu.' : 'Escolha o que voce quer fazer agora no modulo do musico.' }}
                    </p>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                @if ($isCoordenadorArea)
                    <a href="{{ route('coordenador.musicos.index') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Musicos da igreja</h3>
                            <i class="fa-solid fa-users text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Cadastre, vincule e acompanhe pessoas com papel musical nesta igreja.</p>
                    </a>

                    <a href="{{ route('coordenador.musicas.index') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Biblioteca musical</h3>
                            <i class="fa-solid fa-music text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Crie musicas, organize versoes e mantenha o acervo preparado para as igrejas.</p>
                    </a>

                    <a href="#admin-local-form" class="music-card music-card-action rounded-2xl p-5" data-guide-target="atalho-admin-local">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Admin local</h3>
                            <i class="fa-solid fa-user-shield text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Atribua uma pessoa para administrar a rotina desta igreja.</p>
                    </a>
                @else
                    <a href="{{ route('member.repertorio') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Meu repertorio</h3>
                            <i class="fa-solid fa-list-check text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Veja a missa ativa ou a proxima celebracao preparada para sua igreja.</p>
                    </a>

                    <a href="{{ route('member.musicas.index') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Biblioteca musical</h3>
                            <i class="fa-solid fa-music text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Estude musicas e versoes com leitura fora do contexto de uma missa especifica.</p>
                    </a>

                    <a href="{{ route('member.profile') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Meu perfil</h3>
                            <i class="fa-solid fa-user-pen text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Atualize e-mail, telefone e senha com seguranca.</p>
                    </a>

                    <a href="{{ route('member.settings') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Configuracoes</h3>
                            <i class="fa-solid fa-gear text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Centralize suas preferencias de acesso e suas acoes de conta.</p>
                    </a>
                @endif

                @if ($isCoordenadorArea)
                    <a href="{{ route('coordenador.profile') }}" class="music-card music-card-action rounded-2xl p-5">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">Meu perfil</h3>
                            <i class="fa-solid fa-user-pen text-emerald-700"></i>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">Atualize seus dados de acesso sem sair do contexto operacional da igreja.</p>
                    </a>
                @endif
            </div>
        </section>

        <aside class="space-y-6">
            @if ($isCoordenadorArea)
                <section id="admin-local-form" class="music-card rounded-3xl p-6" data-guide-target="admin-local-form">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Cadastrar admin local</h2>
                            <p class="mt-2 text-sm text-gray-500">
                                O cadastro vale somente para {{ $igreja?->nome ?: 'a igreja ativa' }}.
                            </p>
                        </div>
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                            <i class="fa-solid fa-user-shield"></i>
                        </span>
                    </div>

                    <form action="{{ route('coordenador.igreja.admins-locais.store') }}" method="POST" class="mt-5 space-y-4">
                        @csrf

                        <div data-guide-target="admin-local-nome">
                            <label class="block text-sm font-semibold text-gray-700">Nome completo</label>
                            <input type="text" name="nome" value="{{ old('nome') }}" required maxlength="255" autocomplete="name" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100">
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div data-guide-target="admin-local-cpf">
                                <label class="block text-sm font-semibold text-gray-700">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf') }}" required maxlength="14" inputmode="numeric" autocomplete="off" data-cpf-input class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="000.000.000-00">
                            </div>

                            <div data-guide-target="admin-local-telefone">
                                <label class="block text-sm font-semibold text-gray-700">Telefone</label>
                                <input type="text" name="telefone" value="{{ old('telefone') }}" maxlength="20" inputmode="tel" autocomplete="tel" data-telefone-input class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="(65) 99999-9999">
                            </div>
                        </div>

                        <div data-guide-target="admin-local-email">
                            <label class="block text-sm font-semibold text-gray-700">E-mail</label>
                            <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100" placeholder="admin.local@igreja.com">
                        </div>

                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                            A pessoa recebera o papel de admin local apenas nesta igreja. O convite de acesso pode ser reenviado depois.
                        </div>

                        <button type="submit" class="music-btn music-btn-primary w-full gap-2" data-guide-target="admin-local-salvar">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Cadastrar admin local</span>
                        </button>
                    </form>
                </section>
            @endif

            <section class="music-card rounded-3xl p-6">
                <h2 class="text-lg font-bold text-gray-900">{{ $isCoordenadorArea ? 'Resumo da operacao' : 'Resumo da proxima missa' }}</h2>

                @if ($proximaMissa)
                    <div class="mt-4 rounded-2xl bg-gray-50 p-4">
                        <p class="text-base font-bold text-gray-900">{{ $proximaMissa->titulo }}</p>
                        <p class="mt-1 text-sm text-gray-500">{{ optional($proximaMissa->data_missa)->format('d/m/Y') }} as {{ substr((string) $proximaMissa->hora_inicio, 0, 5) }}</p>
                        @if($proximaMissa->tempoLiturgico)
                            <p class="mt-1 text-sm text-gray-500">{{ $proximaMissa->tempoLiturgico->nome }}</p>
                        @endif

                        @if ($isCoordenadorArea)
                            <a href="{{ route('coordenador.musicas.index') }}" class="music-btn music-btn-primary mt-4">Abrir biblioteca musical</a>
                        @else
                            <a href="{{ route('member.repertorio') }}" class="music-btn music-btn-primary mt-4">Abrir repertorio</a>
                        @endif
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500">
                        Sua igreja ainda nao tem uma missa futura ou ativa cadastrada para leitura.
                    </div>
                @endif
            </section>

            <section class="music-card rounded-3xl p-6">
                <h2 class="text-lg font-bold text-gray-900">Conta</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <a href="{{ route($routePrefix . '.profile') }}" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white px-4 py-4 font-semibold text-gray-800 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800">
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

@push('scripts')
    @if ($isCoordenadorArea)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const aplicarMascaraCpf = (valor) => valor
                    .replace(/\D/g, '')
                    .slice(0, 11)
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})$/, '$1-$2');

                const aplicarMascaraTelefone = (valor) => {
                    valor = valor.replace(/\D/g, '').slice(0, 11);

                    if (valor.length <= 10) {
                        return valor
                            .replace(/^(\d{2})(\d)/, '($1) $2')
                            .replace(/(\d{4})(\d)/, '$1-$2');
                    }

                    return valor
                        .replace(/^(\d{2})(\d)/, '($1) $2')
                        .replace(/(\d{5})(\d)/, '$1-$2');
                };

                document.querySelectorAll('[data-cpf-input]').forEach((campo) => {
                    campo.value = aplicarMascaraCpf(campo.value);
                    campo.addEventListener('input', () => {
                        campo.value = aplicarMascaraCpf(campo.value);
                    });
                });

                document.querySelectorAll('[data-telefone-input]').forEach((campo) => {
                    campo.value = aplicarMascaraTelefone(campo.value);
                    campo.addEventListener('input', () => {
                        campo.value = aplicarMascaraTelefone(campo.value);
                    });
                });
            });
        </script>
    @endif
@endpush
