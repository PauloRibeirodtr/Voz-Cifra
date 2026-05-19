@extends('admin.layouts.admin')

@section('title', 'Igrejas | Voz & Cifra')
@section('mobile_title', 'Igrejas')

@section('content')
    @php
        $filtroStatus = $filtroStatus ?? 'todas';
        $buscaAtual = $busca ?? '';
        $urlFiltro = fn (string $status) => route('admin.igrejas.index', array_filter([
            'busca' => $buscaAtual,
            'status' => $status === 'todas' ? null : $status,
        ], fn ($valor) => filled($valor)));
        $classeFiltroAtivo = 'ring-4 ring-[#6c4a21]/15 border-[#6c4a21]/30';
        $sugestoesIgrejasBase64 = base64_encode(json_encode($sugestoesIgrejas ?? []));
    @endphp

    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-3xl">
            <h1 class="text-2xl font-bold text-gray-800">Igrejas cadastradas</h1>
            <p class="text-sm text-gray-500">Encontre a igreja, confira o status e acesse as acoes principais sem precisar rolar tanto.</p>
        </div>

        <a href="{{ route('admin.igrejas.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 sm:self-start">
            Cadastrar igreja
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded border-l-4 border-green-500 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded border-l-4 border-red-500 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <a href="{{ $urlFiltro('todas') }}" class="admin-section-card border p-5 transition hover:-translate-y-0.5 hover:shadow-lg {{ $filtroStatus === 'todas' ? $classeFiltroAtivo : 'border-transparent' }}">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Total</p>
            <p class="mt-3 text-3xl font-black text-gray-900">{{ $totalIgrejas ?? $igrejas->count() }}</p>
            <p class="mt-2 text-sm text-gray-500">Mostrar todas as igrejas{{ $buscaAtual !== '' ? ' desta busca' : '' }}.</p>
        </a>

        <a href="{{ $urlFiltro('operacionais') }}" class="admin-section-card border p-5 transition hover:-translate-y-0.5 hover:shadow-lg {{ $filtroStatus === 'operacionais' ? $classeFiltroAtivo : 'border-transparent' }}">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Operacionais</p>
            <p class="mt-3 text-3xl font-black text-blue-700">{{ $igrejasOperacionais ?? 0 }}</p>
            <p class="mt-2 text-sm text-gray-500">Igrejas liberadas para a rotina local.</p>
        </a>

        <a href="{{ $urlFiltro('aguardando') }}" class="admin-section-card border p-5 transition hover:-translate-y-0.5 hover:shadow-lg {{ $filtroStatus === 'aguardando' ? $classeFiltroAtivo : 'border-transparent' }}">
            <p class="text-xs font-black uppercase tracking-[0.18em] text-gray-400">Aguardando vinculo</p>
            <p class="mt-3 text-3xl font-black text-amber-700">{{ $igrejasAguardando ?? 0 }}</p>
            <p class="mt-2 text-sm text-gray-500">Igrejas que ainda precisam de admin local.</p>
        </a>
    </div>

    <form method="GET" action="{{ route('admin.igrejas.index') }}" class="admin-section-card mb-6 p-5">
        @if ($filtroStatus !== 'todas')
            <input type="hidden" name="status" value="{{ $filtroStatus }}">
        @endif

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end">
            <div class="relative">
                <label for="busca" class="block text-xs font-black uppercase tracking-[0.18em] text-gray-400">Pesquisar igreja</label>
                <input
                    id="busca"
                    name="busca"
                    type="search"
                    value="{{ $buscaAtual }}"
                    placeholder="Digite nome, cidade, endereco ou parte do nome"
                    autocomplete="off"
                    class="mt-2 block w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-800 shadow-sm outline-none transition focus:border-[#6c4a21] focus:ring-4 focus:ring-[#6c4a21]/10"
                    data-church-search-input
                >
                <div class="absolute z-20 mt-2 hidden w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl" data-church-suggestions></div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-[#6c4a21] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5a3d1b]">
                    Buscar
                </button>
                @if ($buscaAtual !== '' || $filtroStatus !== 'todas')
                    <a href="{{ route('admin.igrejas.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Limpar
                    </a>
                @endif
            </div>
        </div>

        @if ($buscaAtual !== '' || $filtroStatus !== 'todas')
            <p class="mt-3 text-sm text-gray-500">
                Exibindo resultados para
                @if ($buscaAtual !== '')
                    <span class="font-semibold text-gray-800">{{ $buscaAtual }}</span>
                @else
                    <span class="font-semibold text-gray-800">todas as igrejas</span>
                @endif
                @if ($filtroStatus === 'operacionais')
                    em <span class="font-semibold text-blue-700">operacionais</span>.
                @elseif ($filtroStatus === 'aguardando')
                    em <span class="font-semibold text-amber-700">aguardando vinculo</span>.
                @else
                    .
                @endif
            </p>
        @endif
    </form>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        @forelse ($igrejas as $igreja)
            @php($adminsLocais = $igreja->adminsLocais)
            @php($coordenadores = $igreja->coordenadores)

            <article class="admin-section-card p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                    <a href="{{ route('admin.igrejas.edit', $igreja) }}" class="h-20 w-20 shrink-0 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm transition hover:ring-4 hover:ring-[#6c4a21]/15" aria-label="Editar igreja {{ $igreja->nome }}">
                        <img src="{{ $igreja->imagemUrl() }}" alt="Imagem da igreja {{ $igreja->nome }}" class="h-full w-full object-cover">
                    </a>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-black leading-tight text-gray-900">{{ $igreja->nome }}</h2>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $igreja->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $igreja->ativo ? 'Ativa' : 'Inativa' }}
                            </span>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $igreja->estaOperacional() ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $igreja->statusOperacionalLabel() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-gray-600">{{ $igreja->cidade }} - {{ $igreja->estado }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $igreja->endereco ?: 'Endereco nao informado' }}</p>
                        @if (filled($igreja->telefone_secretaria))
                            <p class="mt-1 text-xs font-semibold text-gray-600">Secretaria: {{ $igreja->telefone_secretaria }}</p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-semibold">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{{ $adminsLocais->count() }} {{ $adminsLocais->count() === 1 ? 'admin' : 'admins' }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-amber-800">{{ $coordenadores->count() }} {{ $coordenadores->count() === 1 ? 'coordenador' : 'coordenadores' }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <a href="{{ route('admin.igrejas.edit', $igreja) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        Editar
                    </a>
                    <a href="{{ $igreja->link_publico }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-[#6c4a21]/20 bg-[#f8f1e7] px-3 py-3 text-sm font-semibold text-[#6c4a21] transition hover:bg-[#efe2cf]">
                        Fieis
                    </a>
                    <a href="{{ $igreja->link_publico_musicos }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-3 py-3 text-sm font-semibold text-slate-800 transition hover:bg-slate-200">
                        Musicos
                    </a>
                    <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50" data-copy-url="{{ $igreja->link_publico_musicos }}">
                        Copiar
                    </button>
                </div>
            </article>
        @empty
            <div class="admin-section-card p-10 text-center text-gray-500 xl:col-span-2">
                {{ $buscaAtual !== '' || $filtroStatus !== 'todas' ? 'Nenhuma igreja encontrada para os filtros informados.' : 'Nenhuma igreja cadastrada ate o momento.' }}
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const copiarBotoes = document.querySelectorAll('[data-copy-url]');
            const campoBusca = document.querySelector('[data-church-search-input]');
            const sugestoesContainer = document.querySelector('[data-church-suggestions]');
            const sugestoes = JSON.parse(atob(@json($sugestoesIgrejasBase64)));

            const normalizar = (valor) => (valor || '')
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();

            copiarBotoes.forEach((botao) => {
                botao.addEventListener('click', async () => {
                    const url = botao.getAttribute('data-copy-url');

                    if (!url) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(url);
                        const textoOriginal = botao.textContent;
                        botao.textContent = 'Copiado';

                        window.setTimeout(() => {
                            botao.textContent = textoOriginal;
                        }, 1800);
                    } catch (error) {
                        console.debug('Nao foi possivel copiar o link.', error);
                    }
                });
            });

            if (!campoBusca || !sugestoesContainer) {
                return;
            }

            const esconderSugestoes = () => {
                sugestoesContainer.classList.add('hidden');
                sugestoesContainer.innerHTML = '';
            };

            campoBusca.addEventListener('input', () => {
                const termo = normalizar(campoBusca.value.trim());

                if (termo.length < 3) {
                    esconderSugestoes();
                    return;
                }

                const resultados = sugestoes
                    .filter((igreja) => normalizar(`${igreja.nome} ${igreja.cidade} ${igreja.estado}`).includes(termo))
                    .slice(0, 6);

                if (resultados.length === 0) {
                    esconderSugestoes();
                    return;
                }

                sugestoesContainer.innerHTML = '';

                resultados.forEach((igreja) => {
                    const botao = document.createElement('button');
                    const nome = document.createElement('span');
                    const local = document.createElement('span');

                    botao.type = 'button';
                    botao.className = 'block w-full px-4 py-3 text-left hover:bg-[#f8f1e7]';
                    botao.setAttribute('data-suggestion-value', igreja.nome);

                    nome.className = 'block text-sm font-semibold text-gray-900';
                    nome.textContent = igreja.nome;

                    local.className = 'block text-xs text-gray-500';
                    local.textContent = `${igreja.cidade} - ${igreja.estado}`;

                    botao.append(nome, local);
                    sugestoesContainer.appendChild(botao);
                });

                sugestoesContainer.classList.remove('hidden');
            });

            sugestoesContainer.addEventListener('click', (evento) => {
                const botao = evento.target.closest('[data-suggestion-value]');

                if (!botao) {
                    return;
                }

                campoBusca.value = botao.getAttribute('data-suggestion-value');
                esconderSugestoes();
                campoBusca.form?.submit();
            });

            document.addEventListener('click', (evento) => {
                if (!sugestoesContainer.contains(evento.target) && evento.target !== campoBusca) {
                    esconderSugestoes();
                }
            });
        });
    </script>
@endpush
