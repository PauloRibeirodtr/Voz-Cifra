@extends('local-admin.layouts.admin')

@section('title', 'Minha igreja | Voz & Cifra')
@section('mobile_title', 'Minha igreja')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dados da igreja</h1>
        <p class="mt-1 text-sm text-gray-500">Visualizacao da igreja vinculada ao seu acesso local.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <section class="xl:col-span-2 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Informacoes principais</h2>

            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Nome da igreja</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja->nome }}</span>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Slug</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja->slug }}</span>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Cidade / Estado</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja->cidade }} - {{ $igreja->estado }}</span>
                </div>
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Administrador local</span>
                    <span class="mt-2 block text-base font-bold text-gray-900">{{ $usuario->nome }}</span>
                </div>
            </div>

            <div class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 p-4">
                <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Endereco</span>
                <span class="mt-2 block text-base font-bold text-gray-900">{{ $igreja->endereco ?: 'Endereco nao informado.' }}</span>
            </div>
        </section>

        <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Acesso publico</h2>
            <p class="mt-2 text-sm text-gray-500">Este link e o QR fixo da igreja. O conteudo publico futuro sera atualizado nele.</p>

            <div class="mt-5 space-y-4">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Link publico</span>
                    <a href="{{ $igreja->link_publico }}" target="_blank" class="mt-2 block break-all text-sm font-semibold text-green-700 hover:underline">
                        {{ $igreja->link_publico }}
                    </a>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">QR fixo</span>
                    <a href="{{ $igreja->qr_code_url }}" target="_blank" class="mt-2 inline-flex items-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800">
                        Abrir QR da igreja
                    </a>
                    <div class="mt-4 rounded-2xl border border-gray-200 bg-white p-3">
                        <img src="{{ $igreja->qr_code_url }}" alt="QR da igreja {{ $igreja->nome }}" class="mx-auto h-auto w-full max-w-[220px] rounded-xl">
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
