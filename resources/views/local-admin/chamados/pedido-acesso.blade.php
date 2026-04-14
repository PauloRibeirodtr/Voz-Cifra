@extends('local-admin.layouts.admin')

@section('title', 'Pedir acesso | Voz & Cifra')
@section('mobile_title', 'Pedir acesso')
@section('desktop_subtitle', 'Solicitacoes para liberar o acesso de musicos da sua igreja')

@section('content')
    <div class="mb-6 flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Solicitar acesso para musico</h1>
            <p class="mt-1 max-w-3xl text-sm text-gray-500">Somente admins locais podem abrir esse pedido. Escolha um musico da igreja, explique o motivo e o suporte nivel 7 recebe a solicitacao ja organizada.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.8fr),minmax(320px,1fr)]">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
            <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-gray-50 px-4 py-4">
                    <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                    <div class="mt-2 text-sm font-semibold text-gray-800">{{ $igreja->nome }}</div>
                </div>
                <div class="rounded-2xl bg-gray-50 px-4 py-4">
                    <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Solicitante</div>
                    <div class="mt-2 text-sm font-semibold text-gray-800">{{ $adminLocal->nome }}</div>
                </div>
                <div class="rounded-2xl bg-gray-50 px-4 py-4">
                    <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Regra</div>
                    <div class="mt-2 text-sm font-semibold text-gray-800">Apenas musicos da sua igreja</div>
                </div>
            </div>

            <form method="POST" action="{{ route('local-admin.chamados.acesso.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="filtro-musico" class="mb-2 block text-sm font-semibold text-gray-700">Pesquisar musico</label>
                    <input
                        type="text"
                        id="filtro-musico"
                        placeholder="Digite nome, email ou CPF para localizar"
                        class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-800"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Escolha o musico</label>
                    <div id="lista-musicos" class="grid max-h-[28rem] grid-cols-1 gap-3 overflow-y-auto pr-1">
                        @forelse ($musicos as $musico)
                            @php
                                $selecionado = (int) old('musico_id', $musicoSelecionadoId) === (int) $musico->id;
                                $jaTemAcesso = $musico->ativo && !$musico->primeiro_acesso;
                            @endphp
                            <label
                                class="musico-card flex cursor-pointer items-start gap-4 rounded-2xl border px-4 py-4 transition {{ $selecionado ? 'border-green-400 bg-green-50' : 'border-gray-200 bg-white hover:border-green-200 hover:bg-green-50/40' }}"
                                data-search="{{ mb_strtolower($musico->nome . ' ' . ($musico->email ?? '') . ' ' . ($musico->cpf ?? '')) }}"
                            >
                                <input type="radio" name="musico_id" value="{{ $musico->id }}" class="mt-1 h-4 w-4 border-gray-300 text-green-700 focus:ring-green-500" @checked($selecionado)>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-gray-800">{{ $musico->nome }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $musico->ativo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $musico->ativo ? 'Ativo' : 'Inativo' }}
                                        </span>
                                        @if ($musico->primeiro_acesso)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[11px] font-semibold text-amber-700">
                                                Primeiro acesso
                                            </span>
                                        @endif
                                        @if ($jaTemAcesso)
                                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">
                                                Ja liberado
                                            </span>
                                        @endif
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">
                                        <div>{{ $musico->email ?: 'Sem email cadastrado' }}</div>
                                        <div>{{ $musico->cpf ?: 'Sem CPF cadastrado' }}</div>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500">
                                Nenhum musico encontrado para esta igreja.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div>
                    <label for="motivo" class="mb-2 block text-sm font-semibold text-gray-700">Motivo do pedido</label>
                    <textarea id="motivo" name="motivo" rows="6" class="w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-gray-800" placeholder="Explique por que esse musico precisa receber acesso. Exemplo: mudou de aparelho, nao concluiu o primeiro acesso ou precisa voltar a acompanhar as missas.">{{ old('motivo') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Enviar pedido ao suporte
                    </button>
                    <a href="{{ route('local-admin.musicos.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Voltar para musicos
                    </a>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Como funciona</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <p>1. Voce escolhe um musico da sua igreja.</p>
                    <p>2. Explica o motivo do acesso.</p>
                    <p>3. O pedido entra como chamado para o nivel 7.</p>
                    <p>4. O suporte analisa e responde com o protocolo.</p>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Pedidos recentes</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($recentes as $chamado)
                        <article class="rounded-2xl bg-gray-50 px-4 py-4">
                            <div class="text-xs font-black uppercase tracking-[0.16em] text-gray-400">{{ $chamado->protocolo }}</div>
                            <div class="mt-2 text-sm font-semibold text-gray-800">{{ $chamado->titulo }}</div>
                            <div class="mt-1 text-xs text-gray-500">{{ $supportService->statusLabel($chamado->status) }} · {{ $chamado->created_at->format('d/m/Y H:i') }}</div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500">
                            Voce ainda nao enviou pedidos de acesso por aqui.
                        </div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('filtro-musico');
            const cards = Array.from(document.querySelectorAll('.musico-card'));

            if (!input || cards.length === 0) {
                return;
            }

            input.addEventListener('input', () => {
                const termo = input.value.trim().toLowerCase();

                cards.forEach((card) => {
                    const search = card.dataset.search || '';
                    card.classList.toggle('hidden', termo !== '' && !search.includes(termo));
                });
            });
        });
    </script>
@endpush
