@extends('admin.layouts.admin')

@section('title', 'Painel do Admin Master | Voz & Cifra')
@section('mobile_title', 'Painel')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl font-black text-[#fff8ed] sm:text-3xl">Painel do administrador principal</h1>
        <p class="text-[#d4c2ab]">Visao central da base, com foco nas igrejas, musicas e acessos desta etapa.</p>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 xl:grid-cols-3 sm:mb-8">
        <a href="{{ route('admin.musicos.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Usuarios</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['total_usuarios'] ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.igrejas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Igrejas</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['total_igrejas'] ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.musicas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Musicas</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['total_musicas'] ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.igrejas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Missas</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['total_missas'] ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.igrejas.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Admins locais</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['admins_locais'] ?? 0 }}</div>
        </a>

        <a href="{{ route('admin.musicos.index') }}" class="block rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-6">
            <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-[#d6ad6c]">Membros</h3>
            <div class="text-3xl font-black text-[#fff8ed] sm:text-4xl">{{ $metrics['membros'] ?? 0 }}</div>
        </a>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:mb-8 sm:p-6">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-[#fff8ed]">Acessos rapidos</h2>
            <p class="mt-1 text-sm text-[#d4c2ab]">Atalhos centrais para manter a base organizada sem espalhar o fluxo.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.igrejas.index') }}" class="rounded-2xl border border-white/10 bg-[#2a1b1b] px-4 py-4 transition hover:border-[#c9a15f]/30 hover:bg-[#352121]">
                <span class="block text-xs font-black uppercase tracking-wider text-[#d6ad6c]">Igrejas</span>
                <span class="mt-2 block text-base font-bold text-[#fff8ed]">Gerenciar igrejas</span>
            </a>

            <a href="{{ route('admin.musicas.index') }}" class="rounded-2xl border border-white/10 bg-[#2a1b1b] px-4 py-4 transition hover:border-[#c9a15f]/30 hover:bg-[#352121]">
                <span class="block text-xs font-black uppercase tracking-wider text-[#d6ad6c]">Musicas</span>
                <span class="mt-2 block text-base font-bold text-[#fff8ed]">Biblioteca musical</span>
            </a>

            <a href="{{ route('admin.acordes.index') }}" class="rounded-2xl border border-white/10 bg-[#2a1b1b] px-4 py-4 transition hover:border-[#c9a15f]/30 hover:bg-[#352121]">
                <span class="block text-xs font-black uppercase tracking-wider text-[#d6ad6c]">Acordes</span>
                <span class="mt-2 block text-base font-bold text-[#fff8ed]">Dicionario de acordes</span>
            </a>

            <a href="{{ route('admin.auditoria.index') }}" class="rounded-2xl border border-white/10 bg-[#2a1b1b] px-4 py-4 transition hover:border-[#c9a15f]/30 hover:bg-[#352121]">
                <span class="block text-xs font-black uppercase tracking-wider text-[#d6ad6c]">Auditoria</span>
                <span class="mt-2 block text-base font-bold text-[#fff8ed]">Protocolos e acoes sensiveis</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="rounded-2xl border border-white/10 bg-[#2a1b1b] px-4 py-4 transition hover:border-[#c9a15f]/30 hover:bg-[#352121]">
                <span class="block text-xs font-black uppercase tracking-wider text-[#d6ad6c]">Configuracoes</span>
                <span class="mt-2 block text-base font-bold text-[#fff8ed]">Conta e sistema</span>
            </a>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="mb-4 text-lg font-bold text-[#fff8ed]">Visao geral desta etapa</h2>
        <p class="leading-7 text-[#d4c2ab]">
            O admin master concentra a base principal do sistema. A partir daqui vamos evoluir por blocos, mantendo a experiencia interna mais coerente com a home e com o login.
        </p>
    </div>
@endsection
