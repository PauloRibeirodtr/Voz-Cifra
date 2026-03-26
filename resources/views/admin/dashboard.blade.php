@extends('admin.layouts.admin')

@section('title', 'Painel do Admin Master | Voz & Cifra')
@section('mobile_title', 'Painel')

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl sm:text-3xl font-black text-gray-800">Painel do administrador principal</h1>
        <p class="text-gray-500">Resumo geral da base central do sistema.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Usuarios</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['total_usuarios'] ?? 0 }}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-green-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Igrejas</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['total_igrejas'] ?? 0 }}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-orange-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Musicas</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['total_musicas'] ?? 0 }}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-purple-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Missas</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['total_missas'] ?? 0 }}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Admins locais</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['admins_locais'] ?? 0 }}</div>
        </div>

        <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100 border-l-4 border-l-emerald-500">
            <h3 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-1">Membros</h3>
            <div class="text-3xl sm:text-4xl font-black text-gray-800">{{ $metrics['membros'] ?? 0 }}</div>
        </div>
    </div>

    <div class="mb-6 sm:mb-8 rounded-2xl border border-gray-100 bg-white p-5 sm:p-6 shadow-sm">
        <div class="mb-4">
            <h2 class="text-lg font-bold text-gray-800">Acessos rapidos</h2>
            <p class="mt-1 text-sm text-gray-500">Atalhos para os modulos centrais mais usados nesta etapa.</p>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.igrejas.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                <span class="block text-xs font-black uppercase tracking-wider text-green-700">Igrejas</span>
                <span class="mt-2 block text-base font-bold text-gray-800">Gerenciar igrejas</span>
            </a>

            <a href="{{ route('admin.musicas.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                <span class="block text-xs font-black uppercase tracking-wider text-green-700">Musicas</span>
                <span class="mt-2 block text-base font-bold text-gray-800">Biblioteca musical</span>
            </a>

            <a href="{{ route('admin.acordes.index') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                <span class="block text-xs font-black uppercase tracking-wider text-green-700">Acordes</span>
                <span class="mt-2 block text-base font-bold text-gray-800">Dicionario de acordes</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="rounded-2xl border border-gray-200 bg-gray-50 px-4 py-4 transition hover:border-green-200 hover:bg-green-50">
                <span class="block text-xs font-black uppercase tracking-wider text-green-700">Configuracoes</span>
                <span class="mt-2 block text-base font-bold text-gray-800">Conta e sistema</span>
            </a>
        </div>
    </div>

    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="font-bold text-lg text-gray-700 mb-4">Visao geral desta etapa</h2>
        <p class="text-gray-600 leading-7">
            O fluxo inicial do admin master esta ativo. A partir daqui podemos evoluir com seguranca
            as funcionalidades centrais, mantendo o sistema fechado e alinhado com a documentacao.
        </p>
    </div>
@endsection
