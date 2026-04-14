@extends('admin.layouts.admin')

@section('title', 'Atendimento | Voz & Cifra')
@section('mobile_title', 'Atendimento')
@section('desktop_subtitle', 'Detalhe do chamado e historico de suporte')

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.chamados.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">&larr; Voltar para chamados</a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr),minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $chamado->protocolo }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->statusBadgeClass($chamado->status) }}">{{ $supportService->statusLabel($chamado->status) }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->prioridadeBadgeClass($chamado->prioridade) }}">{{ $supportService->prioridadeLabel($chamado->prioridade) }}</span>
                </div>

                <h1 class="mt-4 text-2xl font-black text-gray-800">{{ $chamado->titulo }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $chamado->descricao }}</p>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Solicitante</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->solicitante_nome ?: 'Nao informado' }}</div>
                        <div class="text-xs text-gray-500">{{ $chamado->solicitante_email ?: 'Sem email' }}</div>
                        <div class="text-xs text-gray-500">{{ $chamado->solicitante_telegram_chat_id ?: 'Sem chat Telegram' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ $chamado->igreja_nome ?: 'Nao informada' }}</div>
                        <div class="text-xs text-gray-500">Categoria: {{ $supportService->categoriaLabel($chamado->categoria) }}</div>
                        <div class="text-xs text-gray-500">Canal: {{ $chamado->canal_origem }}</div>
                    </div>
                </div>

                @if ($musicoAlvoPedidoAcesso)
                    <div class="mt-5 rounded-2xl border border-sky-100 bg-sky-50 p-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-sky-700">Musico alvo do pedido</div>
                        <div class="mt-2 text-sm font-semibold text-slate-800">{{ $musicoAlvoPedidoAcesso->nome }}</div>
                        <div class="text-xs text-slate-500">{{ $musicoAlvoPedidoAcesso->email ?: 'Sem email' }}</div>
                        <div class="mt-2 text-xs text-slate-600">
                            Status da conta:
                            {{ $musicoAlvoPedidoAcesso->ativo ? 'Ativa' : 'Inativa' }}
                            ·
                            {{ $musicoAlvoPedidoAcesso->primeiro_acesso ? 'Primeiro acesso pendente' : 'Ja acessou' }}
                        </div>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-gray-800">Historico da conversa</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $chamado->mensagens->count() }} mensagens</span>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($chamado->mensagens as $mensagem)
                        <article class="rounded-2xl border px-4 py-4 {{ $mensagem->origem === 'suporte' ? 'border-blue-100 bg-blue-50/50' : 'border-gray-200 bg-gray-50' }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-gray-800">{{ $mensagem->autor_nome ?: 'Sistema' }}</span>
                                <span class="rounded-full bg-white px-2 py-1 text-[11px] font-semibold text-gray-600">{{ $mensagem->origem }}</span>
                                @if ($mensagem->interno)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-[11px] font-semibold text-amber-700">interno</span>
                                @endif
                                <span class="text-xs text-gray-500">{{ $mensagem->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm text-gray-700">{{ $mensagem->mensagem }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500">
                            Ainda nao existem mensagens registradas neste atendimento.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Acoes rapidas</h2>
                <div class="mt-4 space-y-3">
                    <form action="{{ route('admin.chamados.assumir', $chamado) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-800 hover:bg-slate-50">
                            Assumir atendimento
                        </button>
                    </form>

                    @if ($supportService->podeAprovarPedidoAcesso($chamado))
                        <form action="{{ route('admin.chamados.aprovar-pedido-acesso', $chamado) }}" method="POST" onsubmit="return confirm('Deseja aprovar este pedido e liberar o acesso do musico?');">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-sky-700 px-4 py-3 text-sm font-semibold text-white hover:bg-sky-800">
                                Aprovar pedido de acesso
                            </button>
                        </form>
                    @endif

                    <form action="{{ route('admin.chamados.pedir-mais-dados', $chamado) }}" method="POST" class="space-y-3">
                        @csrf
                        <textarea name="mensagem" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800" placeholder="Escreva o que ainda falta para o usuario responder">{{ old('mensagem') }}</textarea>
                        <button type="submit" class="w-full rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800 hover:bg-amber-100">
                            Pedir mais dados
                        </button>
                    </form>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Atualizar status</h2>
                <form action="{{ route('admin.chamados.status.update', $chamado) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800">
                        @foreach ($statusOptions as $valor => $label)
                            <option value="{{ $valor }}" @selected($chamado->status === $valor)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <textarea name="resolucao_resumo" rows="4" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800" placeholder="Resumo da resolucao, se houver">{{ old('resolucao_resumo', $chamado->resolucao_resumo) }}</textarea>

                    <button type="submit" class="w-full rounded-xl bg-green-700 px-4 py-3 text-sm font-semibold text-white hover:bg-green-800">
                        Salvar status
                    </button>
                </form>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Responder atendimento</h2>
                <form action="{{ route('admin.chamados.mensagens.store', $chamado) }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <textarea name="mensagem" rows="6" required class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-800" placeholder="Escreva a resposta para o atendimento">{{ old('mensagem') }}</textarea>

                    <label class="inline-flex items-center gap-3 text-sm font-medium text-gray-700">
                        <input type="hidden" name="interno" value="0">
                        <input type="checkbox" name="interno" value="1" class="rounded border-gray-300 text-green-700 focus:ring-green-500">
                        <span>Mensagem interna da equipe</span>
                    </label>

                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-800">
                        Registrar resposta
                    </button>
                </form>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Metadados</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div><strong class="text-gray-800">Criado em:</strong> {{ $chamado->created_at->format('d/m/Y H:i') }}</div>
                    <div><strong class="text-gray-800">Ultima interacao:</strong> {{ optional($chamado->ultima_interacao_em)->format('d/m/Y H:i') ?: 'Nao registrada' }}</div>
                    <div><strong class="text-gray-800">Responsavel atual:</strong> {{ $chamado->responsavel?->nome ?: 'Nao atribuido' }}</div>
                    <div><strong class="text-gray-800">Origem:</strong> {{ $chamado->origem_tipo ?: 'Nao informada' }}</div>
                    @if ($chamado->resolucao_resumo)
                        <div><strong class="text-gray-800">Resumo atual:</strong> {{ $chamado->resolucao_resumo }}</div>
                    @endif
                    @if ($chamado->auditoriaEvento)
                        <div><strong class="text-gray-800">Evento de auditoria:</strong> {{ $chamado->auditoriaEvento->evento }}</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
