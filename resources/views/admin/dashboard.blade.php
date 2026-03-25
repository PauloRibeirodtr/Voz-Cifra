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

    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="font-bold text-lg text-gray-700 mb-4">Visao geral desta etapa</h2>
        <p class="text-gray-600 leading-7">
            O fluxo inicial do admin master esta ativo. A partir daqui podemos evoluir com seguranca
            as funcionalidades centrais, mantendo o sistema fechado e alinhado com a documentacao.
        </p>
    </div>
@endsection
