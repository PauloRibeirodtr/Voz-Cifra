@extends('member.layouts.app')

@php($isCoordenadorArea = request()->routeIs('coordenador.*'))
@php($routePrefix = $isCoordenadorArea ? 'coordenador' : 'member')
@php($tituloPerfil = $isCoordenadorArea ? 'Perfil do coordenador' : 'Meu perfil')

@section('title', $tituloPerfil . ' | Voz & Cifra')
@section('mobile_title', $tituloPerfil)
@section('desktop_subtitle', $isCoordenadorArea ? 'Perfil e acesso operacional da igreja' : 'Perfil e configurações do músico')

@section('header_actions')
    @php($routePrefix = request()->routeIs('coordenador.*') ? 'coordenador' : 'member')
    <a href="{{ route($routePrefix . '.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Voltar ao painel
    </a>
@endsection

@section('content')
    @php
        $isCoordenadorArea = request()->routeIs('coordenador.*');
        $routePrefix = $isCoordenadorArea ? 'coordenador' : 'member';
        $tituloPerfil = $isCoordenadorArea ? 'Perfil do coordenador' : 'Meu perfil';
        $classeInput = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100';
        $papeisAtivos = $igreja ? $user->listarPapeisNaIgreja($igreja) : collect();
    @endphp

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

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <form action="{{ route($routePrefix . '.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">{{ $tituloPerfil }}</h1>
                        <p class="mt-1 text-sm text-gray-500">Atualize seus dados de acesso e mantenha a conta pronta para uso.</p>
                    </div>
                    @if ($user->primeiro_acesso ?? false)
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Troca de senha pendente</span>
                    @endif
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2 rounded-3xl border border-gray-200 bg-gray-50 p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <img
                                id="member-profile-preview"
                                src="{{ $user->fotoPerfilUrl() }}"
                                alt="Foto de perfil de {{ $user->nome }}"
                                class="h-24 w-24 rounded-3xl border border-gray-200 object-cover bg-white shadow-sm"
                            >
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700">Foto de perfil</label>
                                <input
                                    type="file"
                                    name="foto_perfil"
                                    accept="image/*"
                                    class="{{ $classeInput }}"
                                    data-image-preview-input
                                    data-image-preview-target="#member-profile-preview"
                                    data-default-src="{{ $user->fotoPerfilUrl() }}"
                                >
                                <p class="mt-2 text-xs text-gray-500">Use JPG, PNG ou WebP com até 2 MB. A foto aparece na navegação lateral e ajuda a identificar a conta ativa.</p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                        <input type="text" value="{{ $user->nome }}" class="{{ $classeInput }} bg-gray-50" disabled>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $classeInput }}" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input type="text" name="telefone" value="{{ old('telefone', $user->telefone) }}" class="{{ $classeInput }}" placeholder="(65) 99999-9999">
                    </div>

                    <div data-password-strength-container>
                        <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                        <input type="password" name="password" data-password-strength-input class="{{ $classeInput }}" placeholder="Mínimo de 8 caracteres">
                        @include('partials.password-strength-meter', ['required' => (bool) ($user->primeiro_acesso ?? false)])
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                        <input type="password" name="password_confirmation" data-password-confirmation-input class="{{ $classeInput }}" placeholder="Repita a nova senha">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Tema da interface</label>
                        <select name="theme_preference" class="{{ $classeInput }}">
                            <option value="system" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'system')>Seguir o dispositivo</option>
                            <option value="light" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'light')>Modo claro</option>
                            <option value="dark" @selected(old('theme_preference', $user->theme_preference ?? 'system') === 'dark')>Modo escuro</option>
                        </select>
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 font-semibold text-white hover:bg-emerald-800">
                    Salvar perfil
                </button>
            </div>
        </form>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Vínculo atual</h2>
                <div class="mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-600">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                    <span class="mt-2 block text-base font-semibold text-gray-900">{{ $igreja?->nome ?: 'Não vinculada' }}</span>
                </div>

                @if ($papeisAtivos->isNotEmpty())
                    <div class="mt-4">
                        <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Papéis ativos</span>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($papeisAtivos as $papel)
                                @php($corBadge = match($papel->value) {
                                    'admin_local' => 'bg-indigo-100 text-indigo-700',
                                    'coordenador' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-green-100 text-green-700',
                                })
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corBadge }}">{{ $papel->label() }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Preferências</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p>Use esta tela para manter o acesso atualizado e escolher o tema claro, escuro ou automático para a sua conta.</p>
                    <a href="{{ route($isCoordenadorArea ? 'coordenador.musicas.index' : 'member.repertorio') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                        {{ $isCoordenadorArea ? 'Ir para músicas da igreja' : 'Ir para meu repertório' }}
                    </a>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    @include('partials.image-preview-script')
    @include('partials.password-strength-script')
@endpush
