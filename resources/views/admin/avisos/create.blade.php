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
                        @php
                            $usuarioSelecionado = $usuarios->firstWhere('id', (int) old('usuario_id'));
                        @endphp
                        <label for="usuario_busca" class="block text-sm font-semibold text-gray-700">Usuario</label>
                        <input type="hidden" id="usuario_id" name="usuario_id" value="{{ old('usuario_id') }}" data-aviso-usuario-id>
                        <div class="relative mt-1" data-aviso-usuario-combobox>
                            <input
                                id="usuario_busca"
                                type="text"
                                value="{{ $usuarioSelecionado ? $usuarioSelecionado->nome . ' - ' . $usuarioSelecionado->email : '' }}"
                                class="block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 focus:border-green-600 focus:ring-2 focus:ring-green-100"
                                placeholder="Digite ao menos 3 letras do nome ou e-mail"
                                autocomplete="off"
                                data-aviso-usuario-busca
                            >
                            <div class="absolute left-0 right-0 top-[calc(100%+0.35rem)] z-20 hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl" data-aviso-usuario-resultados></div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500" data-aviso-usuario-ajuda>Digite 3 letras para ver sugestoes e clique no usuario correto.</p>
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
            const usuarios = @json($usuarios->map(fn ($usuario) => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'busca' => \Illuminate\Support\Str::lower(trim($usuario->nome . ' ' . $usuario->email)),
            ])->values(), JSON_UNESCAPED_UNICODE);
            const usuarioId = document.querySelector('[data-aviso-usuario-id]');
            const usuarioBusca = document.querySelector('[data-aviso-usuario-busca]');
            const usuarioResultados = document.querySelector('[data-aviso-usuario-resultados]');

            const escaparHtml = (valor) => String(valor || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const atualizarCampos = () => {
                const valor = escopo?.value || 'todos';
                campos.forEach((campo) => {
                    campo.classList.toggle('hidden', campo.dataset.avisoCampo !== valor);
                });
            };

            const esconderResultados = () => {
                if (!usuarioResultados) {
                    return;
                }

                usuarioResultados.classList.add('hidden');
                usuarioResultados.innerHTML = '';
            };

            const selecionarUsuario = (usuario) => {
                if (usuarioId) {
                    usuarioId.value = usuario.id;
                }

                if (usuarioBusca) {
                    usuarioBusca.value = `${usuario.nome} - ${usuario.email || 'sem e-mail'}`;
                }

                esconderResultados();
            };

            const renderizarUsuarios = () => {
                if (!usuarioBusca || !usuarioResultados) {
                    return;
                }

                const termo = usuarioBusca.value.trim().toLowerCase();

                if (usuarioId && termo !== '') {
                    usuarioId.value = '';
                }

                if (termo.length < 3) {
                    esconderResultados();

                    return;
                }

                const encontrados = usuarios
                    .filter((usuario) => usuario.busca.includes(termo))
                    .slice(0, 8);

                if (encontrados.length === 0) {
                    usuarioResultados.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Nenhum usuario encontrado.</div>';
                    usuarioResultados.classList.remove('hidden');

                    return;
                }

                usuarioResultados.innerHTML = encontrados.map((usuario) => `
                    <button type="button" class="block w-full px-4 py-3 text-left hover:bg-emerald-50" data-aviso-usuario-opcao="${usuario.id}">
                        <span class="block text-sm font-bold text-gray-900">${escaparHtml(usuario.nome)}</span>
                        <span class="block text-xs text-gray-500">${escaparHtml(usuario.email || 'sem e-mail')}</span>
                    </button>
                `).join('');
                usuarioResultados.classList.remove('hidden');
            };

            escopo?.addEventListener('change', atualizarCampos);
            usuarioBusca?.addEventListener('input', renderizarUsuarios);
            usuarioBusca?.addEventListener('focus', renderizarUsuarios);

            usuarioResultados?.addEventListener('click', (event) => {
                const botao = event.target.closest('[data-aviso-usuario-opcao]');

                if (!botao) {
                    return;
                }

                const usuario = usuarios.find((item) => String(item.id) === String(botao.dataset.avisoUsuarioOpcao));

                if (usuario) {
                    selecionarUsuario(usuario);
                }
            });

            document.addEventListener('click', (event) => {
                if (!event.target.closest('[data-aviso-usuario-combobox]')) {
                    esconderResultados();
                }
            });

            atualizarCampos();
        });
    </script>
@endpush
