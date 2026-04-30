@extends('admin.layouts.admin')

@section('title', 'Enviar aviso | Voz & Cifra')
@section('mobile_title', 'Enviar aviso')

@section('content')
    <div class="admin-page-intro">
        <p class="admin-page-kicker">Comunicacao</p>
        <h1 class="admin-page-title mt-2 text-2xl font-bold">Enviar aviso</h1>
        <p class="admin-page-copy mt-3 text-sm sm:text-base">Envie uma mensagem simples para todos, uma igreja, um papel operacional ou um usuario especifico.</p>
    </div>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.avisos.store') }}" class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
            @csrf

            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label for="escopo" class="block text-sm font-semibold text-gray-700">Destino</label>
                    <select id="escopo" name="escopo" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" data-aviso-escopo>
                        <option value="todos" @selected(old('escopo', 'todos') === 'todos')>Todos os usuarios ativos</option>
                        <option value="igreja" @selected(old('escopo') === 'igreja')>Uma igreja</option>
                        <option value="papel" @selected(old('escopo') === 'papel')>Um papel</option>
                        <option value="usuario" @selected(old('escopo') === 'usuario')>Um usuario especifico</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div data-aviso-campo="igreja">
                        <label for="igreja_id" class="block text-sm font-semibold text-gray-700">Igreja</label>
                        <select id="igreja_id" name="igreja_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Selecione</option>
                            @foreach ($igrejas as $igreja)
                                <option value="{{ $igreja->id }}" @selected((string) old('igreja_id') === (string) $igreja->id)>
                                    {{ $igreja->nome }} @if($igreja->cidade) - {{ $igreja->cidade }} @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div data-aviso-campo="papel">
                        <label for="papel" class="block text-sm font-semibold text-gray-700">Papel</label>
                        <select id="papel" name="papel" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Selecione</option>
                            @foreach ($papeis as $papel)
                                <option value="{{ $papel->value }}" @selected(old('papel') === $papel->value)>{{ $papel->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div data-aviso-campo="usuario">
                        <label for="usuario_id" class="block text-sm font-semibold text-gray-700">Usuario</label>
                        <select id="usuario_id" name="usuario_id" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100">
                            <option value="">Selecione</option>
                            @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}" @selected((string) old('usuario_id') === (string) $usuario->id)>
                                    {{ $usuario->nome }} - {{ $usuario->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="titulo" class="block text-sm font-semibold text-gray-700">Titulo</label>
                    <input id="titulo" name="titulo" type="text" value="{{ old('titulo') }}" maxlength="120" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Ex.: Aviso sobre ensaio geral">
                </div>

                <div>
                    <label for="mensagem" class="block text-sm font-semibold text-gray-700">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="7" maxlength="3000" class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100" placeholder="Escreva o aviso de forma objetiva.">{{ old('mensagem') }}</textarea>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.settings') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 font-semibold text-gray-700 hover:bg-gray-50">Cancelar</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-5 py-3 font-semibold text-white hover:bg-green-800">Enviar aviso</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const escopo = document.querySelector('[data-aviso-escopo]');
            const campos = document.querySelectorAll('[data-aviso-campo]');

            const atualizarCampos = () => {
                const valor = escopo?.value || 'todos';
                campos.forEach((campo) => {
                    campo.classList.toggle('hidden', campo.dataset.avisoCampo !== valor);
                });
            };

            escopo?.addEventListener('change', atualizarCampos);
            atualizarCampos();
        });
    </script>
@endpush
