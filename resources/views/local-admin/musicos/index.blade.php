@extends('local-admin.layouts.admin')

@php($rotaPrefixo = request()->routeIs('coordenador.*') ? 'coordenador' : 'local-admin')
@php($tituloPagina = $rotaPrefixo === 'coordenador' ? 'Pessoas e ministerio musical' : 'Pessoas da igreja')
@php($subtituloPagina = $rotaPrefixo === 'coordenador'
    ? 'Gerencie musicos da igreja, vincule pessoas ja existentes e acompanhe os papeis ativos desta comunidade.'
    : 'Veja as pessoas vinculadas a ' . $igreja->nome . ', com foco no ministerio musical e nos papeis acumulados da igreja.')

@section('title', $tituloPagina . ' | Voz & Cifra')
@section('mobile_title', $tituloPagina)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $tituloPagina }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $subtituloPagina }}</p>
        </div>

        <a href="{{ route($rotaPrefixo . '.musicos.create') }}" class="inline-flex items-center justify-center rounded-xl bg-green-700 px-4 py-3 font-semibold text-white hover:bg-green-800">
            Cadastrar musico
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
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

    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Vincular pessoa existente</h2>
            <p class="mt-2 text-sm text-gray-500">
                Busque pelo nome de uma pessoa que ja existe no sistema. O sistema reaproveita a conta e adiciona o papel de musico nesta igreja.
            </p>

            <form action="{{ route($rotaPrefixo . '.musicos.vincular-existente') }}" method="POST" class="mt-5 space-y-4" data-vincular-pessoa-form>
                @csrf
                <input type="hidden" name="usuario_id" value="{{ old('usuario_id') }}" data-vincular-usuario-id>

                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700">Nome da pessoa</label>
                    <input
                        type="search"
                        value=""
                        autocomplete="off"
                        class="mt-1 block w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800 shadow-sm focus:border-green-600 focus:ring-2 focus:ring-green-100"
                        placeholder="Digite pelo menos 3 letras"
                        data-vincular-busca
                    >
                    <div class="absolute left-0 right-0 top-full z-20 mt-2 hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl" data-vincular-resultados></div>
                    <p class="mt-2 text-sm text-gray-500" data-vincular-selecionado>
                        Nenhuma pessoa selecionada.
                    </p>
                </div>

                <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-4 text-sm text-blue-800">
                    A busca mostra apenas pessoas ativas que ainda nao aparecem como musicos nesta igreja.
                </div>

                <div>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white hover:bg-slate-800">
                        Vincular pessoa
                    </button>
                </div>
            </form>
        </section>

        <aside class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Regras deste painel</h2>
            <div class="mt-4 space-y-3 text-sm text-gray-600">
                <p>Musicos podem acumular papeis na mesma igreja.</p>
                <p>Padres tambem podem ser vinculados como musicos usando a mesma conta.</p>
                <p>Inativacao de conta continua restrita ao admin master.</p>
                <p>Reset de senha aqui apenas libera novo primeiro acesso.</p>
            </div>
        </aside>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        @if ($usuariosIgreja->isEmpty())
            <div class="p-8 text-center text-gray-500">Nenhuma pessoa vinculada com papel musical nesta igreja.</div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Pessoa</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Papeis na igreja</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Contato</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-500">Acoes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($usuariosIgreja as $usuarioIgreja)
                            @php($papeis = $usuarioIgreja->listarPapeisNaIgreja($igreja))
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-800">{{ $usuarioIgreja->nome }}</div>
                                    <div class="text-sm text-gray-500">{{ $usuarioIgreja->cpfMascarado() }}</div>
                                    @if ($usuarioIgreja->ehPadre())
                                        <div class="mt-2 inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">Padre</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($papeis as $papel)
                                            @php($corBadge = match($papel->value) {
                                                'admin_local' => 'bg-indigo-100 text-indigo-700',
                                                'coordenador' => 'bg-amber-100 text-amber-800',
                                                default => 'bg-green-100 text-green-700',
                                            })
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corBadge }}">
                                                {{ $papel->label() }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div>{{ $usuarioIgreja->email }}</div>
                                    @if ($usuarioIgreja->telefone)
                                        <div class="text-xs text-gray-400">{{ $usuarioIgreja->telefoneMascarado() }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-2">
                                        <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold {{ $usuarioIgreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $usuarioIgreja->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($usuarioIgreja->primeiro_acesso)
                                            <span class="inline-flex w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Primeiro acesso
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route($rotaPrefixo . '.musicos.edit', $usuarioIgreja) }}" class="inline-flex rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Editar</a>
                                        <form action="{{ route($rotaPrefixo . '.musicos.password.reset', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja enviar um novo link de definicao de senha para este usuario?');">
                                            @csrf
                                            <button type="submit" class="inline-flex rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-800 hover:bg-amber-100">Resetar senha</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="space-y-4 p-4 md:hidden">
                @foreach ($usuariosIgreja as $usuarioIgreja)
                    @php($papeis = $usuarioIgreja->listarPapeisNaIgreja($igreja))
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-base font-bold text-gray-800">{{ $usuarioIgreja->nome }}</h2>
                                <p class="mt-1 break-all text-sm text-gray-600">{{ $usuarioIgreja->email }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $usuarioIgreja->cpfMascarado() }}</p>
                            </div>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $usuarioIgreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $usuarioIgreja->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($papeis as $papel)
                                @php($corBadge = match($papel->value) {
                                    'admin_local' => 'bg-indigo-100 text-indigo-700',
                                    'coordenador' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-green-100 text-green-700',
                                })
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $corBadge }}">
                                    {{ $papel->label() }}
                                </span>
                            @endforeach

                            @if ($usuarioIgreja->ehPadre())
                                <span class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">Padre</span>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2">
                            <a href="{{ route($rotaPrefixo . '.musicos.edit', $usuarioIgreja) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Editar</a>
                            <form action="{{ route($rotaPrefixo . '.musicos.password.reset', $usuarioIgreja) }}" method="POST" onsubmit="return confirm('Deseja enviar um novo link de definicao de senha para este usuario?');">
                                @csrf
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">Resetar senha</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const pessoas = @json($usuariosParaVinculo);

            const busca = document.querySelector('[data-vincular-busca]');
            const usuarioId = document.querySelector('[data-vincular-usuario-id]');
            const resultados = document.querySelector('[data-vincular-resultados]');
            const selecionado = document.querySelector('[data-vincular-selecionado]');
            const form = document.querySelector('[data-vincular-pessoa-form]');

            if (!busca || !usuarioId || !resultados || !selecionado || !form) {
                return;
            }

            const normalizar = (texto) => String(texto || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            const fecharResultados = () => {
                resultados.classList.add('hidden');
                resultados.innerHTML = '';
            };

            const escolherPessoa = (pessoa) => {
                usuarioId.value = pessoa.id;
                busca.value = pessoa.nome;
                selecionado.textContent = pessoa.email ? `${pessoa.nome} - ${pessoa.email}` : pessoa.nome;
                selecionado.classList.remove('text-gray-500');
                selecionado.classList.add('text-green-700');
                fecharResultados();
            };

            const renderizarResultados = () => {
                const termo = normalizar(busca.value);
                usuarioId.value = '';
                selecionado.textContent = 'Nenhuma pessoa selecionada.';
                selecionado.classList.add('text-gray-500');
                selecionado.classList.remove('text-green-700');

                if (termo.length < 3) {
                    fecharResultados();
                    return;
                }

                const encontrados = pessoas
                    .filter((pessoa) => normalizar(pessoa.nome).includes(termo))
                    .slice(0, 8);

                if (encontrados.length === 0) {
                    resultados.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Nenhuma pessoa encontrada.</div>';
                    resultados.classList.remove('hidden');
                    return;
                }

                resultados.innerHTML = '';
                encontrados.forEach((pessoa) => {
                    const botao = document.createElement('button');
                    botao.type = 'button';
                    botao.className = 'block w-full px-4 py-3 text-left text-sm hover:bg-green-50';
                    botao.innerHTML = `
                        <span class="block font-semibold text-gray-900"></span>
                        <span class="mt-0.5 block text-xs text-gray-500"></span>
                    `;
                    botao.querySelector('span:first-child').textContent = pessoa.nome;
                    botao.querySelector('span:last-child').textContent = pessoa.email || 'Sem e-mail informado';
                    botao.addEventListener('click', () => escolherPessoa(pessoa));
                    resultados.appendChild(botao);
                });
                resultados.classList.remove('hidden');
            };

            busca.addEventListener('input', renderizarResultados);
            document.addEventListener('click', (event) => {
                if (!resultados.contains(event.target) && event.target !== busca) {
                    fecharResultados();
                }
            });

            form.addEventListener('submit', (event) => {
                if (usuarioId.value !== '') {
                    return;
                }

                event.preventDefault();
                selecionado.textContent = 'Selecione uma pessoa da lista antes de vincular.';
                selecionado.classList.remove('text-gray-500', 'text-green-700');
                selecionado.classList.add('text-red-700');
                busca.focus();
            });
        });
    </script>
@endpush
