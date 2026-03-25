@extends('admin.layouts.admin')

@section('title', 'Perfil | Voz & Cifra')
@section('mobile_title', 'Perfil')

@section('content')
    @php
        $classeInput = 'mt-1 block w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-800 placeholder-gray-400 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100';
    @endphp

    <h1 class="text-2xl font-bold mb-4">Meu perfil</h1>

    @if (session('status'))
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-700 p-4 mb-6 text-sm rounded">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6 text-sm rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 text-sm rounded">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.profile.update') }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white p-6 rounded shadow-sm grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input type="text" value="{{ $user->nome }}" class="{{ $classeInput }} bg-gray-100 text-gray-500" disabled />
                <p class="text-xs text-gray-500 mt-1">O nome do admin master permanece fixo nesta etapa.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="admin@ministeriomusical.com" class="{{ $classeInput }}" required />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Telefone</label>
                <input type="text" name="telefone" value="{{ old('telefone', $user->telefone) }}" placeholder="(65) 99999-9999" data-telefone-input class="{{ $classeInput }}" />
            </div>

            <div id="password">
                <label class="block text-sm font-medium text-gray-700">Nova senha</label>
                <input type="password" name="password" placeholder="Minimo de 8 caracteres" class="{{ $classeInput }}" />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Confirmar nova senha</label>
                <input type="password" name="password_confirmation" placeholder="Repita a nova senha" class="{{ $classeInput }}" />
            </div>

            <button class="px-4 py-2 bg-green-600 text-white rounded mt-2">Atualizar perfil</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const campoTelefone = document.querySelector('[data-telefone-input]');

            if (!campoTelefone) {
                return;
            }

            campoTelefone.addEventListener('input', () => {
                let valor = campoTelefone.value.replace(/\D/g, '').slice(0, 11);

                if (valor.length <= 10) {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
                } else {
                    valor = valor.replace(/^(\d{2})(\d)/, '($1) $2');
                    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
                }

                campoTelefone.value = valor;
            });
        });
    </script>
@endpush
