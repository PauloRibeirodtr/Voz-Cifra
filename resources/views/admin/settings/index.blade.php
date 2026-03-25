@extends('admin.layouts.admin')

@section('title', 'Configuracoes | Voz & Cifra')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configuracoes</h1>
        <p class="text-sm text-gray-500 mt-1">Organize sua conta, acompanhe o estado atual do sistema e acesse os ajustes disponiveis nesta etapa.</p>
    </div>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Perfil</h2>
                        <p class="text-sm text-gray-500 mt-2">Atualize email, telefone e senha do administrador principal.</p>
                    </div>
                    <div class="h-11 w-11 rounded-full bg-green-100 text-green-700 flex items-center justify-center">
                        <i class="fa-solid fa-user"></i>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('admin.profile') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-800">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>Editar perfil</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Seguranca</h2>
                        <p class="text-sm text-gray-500 mt-2">Use este atalho para alterar sua senha de acesso com seguranca.</p>
                    </div>
                    <div class="h-11 w-11 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('admin.profile') }}#password" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-50">
                        <i class="fa-solid fa-key"></i>
                        <span>Alterar senha</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Configuracoes do sistema</h2>
                    <p class="text-sm text-gray-500 mt-2">Informacoes basicas da instalacao atual. Nesta fase, esses dados sao apenas informativos.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fa-solid fa-sliders"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Nome do sistema</span>
                    <span class="text-sm font-semibold text-gray-800">Voz &amp; Cifra</span>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-4">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Descricao</span>
                    <span class="text-sm text-gray-700">Sistema administrativo para organizacao do ministerio musical e do nucleo liturgico.</span>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Informacoes do sistema</h2>
                    <p class="text-sm text-gray-500 mt-2">Resumo rapido da base atual para acompanhamento da etapa do admin master.</p>
                </div>
                <div class="h-11 w-11 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Igrejas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_igrejas'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Musicas</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_musicas'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Acordes</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_acordes'] }}</span>
                </div>

                <div class="rounded-xl bg-gray-50 border border-gray-200 p-5">
                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Usuarios</span>
                    <span class="text-3xl font-black text-gray-800">{{ $metricasSistema['total_usuarios'] }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
