@extends('member.layouts.app')

@section('title', 'Meu perfil | Voz & Cifra')
@section('mobile_title', 'Meu perfil')
@section('desktop_subtitle', 'Perfil e configuracoes do musico')

@section('header_actions')
    <a href="{{ route('member.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
        Voltar ao painel
    </a>
@endsection

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100';
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
        <form action="{{ route('member.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-black text-gray-900">Meu perfil</h1>
                        <p class="mt-1 text-sm text-gray-500">Atualize seus dados de acesso e mantenha a conta pronta para uso.</p>
                    </div>
                    @if ($user->primeiro_acesso ?? false)
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Troca de senha pendente</span>
                    @endif
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
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
                        <input type="password" name="password" data-password-strength-input class="{{ $classeInput }}" placeholder="Minimo de 8 caracteres">
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
                <h2 class="text-lg font-bold text-gray-900">Vinculo atual</h2>
                <div class="mt-4 rounded-2xl bg-gray-50 p-4 text-sm text-gray-600">
                    <span class="block text-xs font-black uppercase tracking-wider text-gray-400">Igreja</span>
                    <span class="mt-2 block text-base font-semibold text-gray-900">{{ $igreja?->nome ?: 'Nao vinculada' }}</span>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Preferencias</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p>Use esta tela para manter o acesso atualizado e escolher o tema claro, escuro ou automatico para a sua conta.</p>
                    <a href="{{ route('member.repertorio') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-3 font-semibold text-gray-700 hover:bg-gray-50">
                        Ir para meu repertorio
                    </a>
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    @include('partials.password-strength-script')
@endpush

