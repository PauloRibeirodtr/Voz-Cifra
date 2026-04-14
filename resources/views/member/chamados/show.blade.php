@extends('member.layouts.app')

@section('title', 'Chamado | Voz & Cifra')
@section('mobile_title', 'Chamado')
@section('desktop_subtitle', 'Detalhes do atendimento e historico da sua conversa com o suporte')

@section('header_actions')
    @if ($telegramUrl)
        <a href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
            <i class="fa-brands fa-telegram"></i>
            <span>Continuar no Telegram</span>
        </a>
    @endif
@endsection

@section('content')
    <div class="mb-6">
        <a href="{{ route('member.chamados.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">&larr; Voltar para meus chamados</a>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr),minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $chamado->protocolo }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->statusBadgeClass($chamado->status) }}">{{ $supportService->statusLabel($chamado->status) }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->prioridadeBadgeClass($chamado->prioridade) }}">{{ $supportService->prioridadeLabel($chamado->prioridade) }}</span>
                </div>

                <h1 class="mt-4 text-2xl font-black text-slate-900">{{ $chamado->titulo }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ $chamado->descricao }}</p>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Igreja</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">{{ $chamado->igreja_nome ?: 'Nao informada' }}</div>
                        <div class="text-xs text-slate-500">Categoria: {{ $supportService->categoriaLabel($chamado->categoria) }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Responsavel</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">{{ $chamado->responsavel?->nome ?: 'Equipe de suporte' }}</div>
                        <div class="text-xs text-slate-500">Ultima interacao: {{ optional($chamado->ultima_interacao_em)->format('d/m/Y H:i') ?: 'Ainda sem resposta' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black text-slate-900">Historico do atendimento</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $chamado->mensagens->count() }} mensagens</span>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse ($chamado->mensagens as $mensagem)
                        <article class="rounded-2xl border px-4 py-4 {{ $mensagem->origem === 'suporte' ? 'border-sky-100 bg-sky-50/50' : 'border-slate-200 bg-slate-50' }}">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-bold text-slate-800">{{ $mensagem->autor_nome ?: 'Suporte' }}</span>
                                <span class="rounded-full bg-white px-2 py-1 text-[11px] font-semibold text-slate-600">
                                    {{ $mensagem->origem === 'suporte' ? 'Suporte' : 'Voce' }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $mensagem->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $mensagem->mensagem }}</p>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-300 px-5 py-8 text-sm text-slate-500">
                            Ainda nao existem respostas visiveis neste atendimento.
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Resumo rapido</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div><strong class="text-slate-800">Criado em:</strong> {{ $chamado->created_at->format('d/m/Y H:i') }}</div>
                    <div><strong class="text-slate-800">Status atual:</strong> {{ $supportService->statusLabel($chamado->status) }}</div>
                    <div><strong class="text-slate-800">Prioridade:</strong> {{ $supportService->prioridadeLabel($chamado->prioridade) }}</div>
                    <div><strong class="text-slate-800">Canal:</strong> {{ $chamado->canal_origem }}</div>
                    @if ($chamado->resolucao_resumo)
                        <div><strong class="text-slate-800">Solucao registrada:</strong> {{ $chamado->resolucao_resumo }}</div>
                    @endif
                </div>
            </section>

            @if (in_array($chamado->status, ['resolvido', 'fechado'], true))
                <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black text-slate-900">Avaliar atendimento</h2>
                    <p class="mt-2 text-sm text-slate-500">Sua avaliacao vale para este chamado inteiro. Se voce avaliar aqui, nao precisa avaliar de novo no Telegram.</p>

                    <form action="{{ route('member.chamados.avaliar', $chamado) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <select name="avaliacao_nota" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800">
                            <option value="">Escolha uma nota</option>
                            @for ($nota = 5; $nota >= 1; $nota--)
                                <option value="{{ $nota }}" @selected(old('avaliacao_nota', $chamado->avaliacao_nota) == $nota)>{{ $nota }} {{ $nota === 1 ? 'estrela' : 'estrelas' }}</option>
                            @endfor
                        </select>

                        <textarea name="avaliacao_comentario" rows="4" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-800" placeholder="Conte como foi sua experiencia com o atendimento">{{ old('avaliacao_comentario', $chamado->avaliacao_comentario) }}</textarea>

                        <button type="submit" class="w-full rounded-xl bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Salvar avaliacao
                        </button>
                    </form>
                </section>
            @endif
        </div>
    </div>
@endsection
