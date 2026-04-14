@extends('member.layouts.app')

@section('title', 'Meus chamados | Voz & Cifra')
@section('mobile_title', 'Meus chamados')
@section('desktop_subtitle', 'Acompanhe seu atendimento e continue pelo Telegram quando quiser')

@section('header_actions')
    <a href="{{ route('member.chamados.create') }}" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
        <i class="fa-solid fa-plus"></i>
        <span>Novo chamado</span>
    </a>
    @if ($telegramBaseUrl)
        <a href="{{ $telegramBaseUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-100">
            <i class="fa-brands fa-telegram"></i>
            <span>Abrir suporte no Telegram</span>
        </a>
    @endif
@endsection

@section('content')
    <div class="mb-6 sm:mb-8">
        <h1 class="text-2xl font-black text-slate-900 sm:text-3xl">Meus chamados</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-500">Aqui voce acompanha apenas os seus atendimentos. Quando o suporte responder, o status muda aqui e voce pode continuar tanto pelo painel quanto pelo Telegram.</p>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-emerald-700">Sua central</div>
            <div class="mt-3 text-lg font-black text-slate-900">{{ $usuario->nome }}</div>
            <p class="mt-2 text-sm text-slate-500">Voce ve somente os chamados criados para a sua conta.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-amber-700">Acompanhe status</div>
            <p class="mt-3 text-sm text-slate-600">Veja se o suporte esta analisando, aguardando voce ou se o atendimento ja foi resolvido.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-[11px] font-black uppercase tracking-[0.18em] text-sky-700">Atalho Telegram</div>
            <p class="mt-3 text-sm text-slate-600">Cada protocolo pode ser retomado no bot com um clique, sem precisar digitar comando.</p>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($chamados as $chamado)
            <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $chamado->protocolo }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->statusBadgeClass($chamado->status) }}">{{ $supportService->statusLabel($chamado->status) }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $supportService->prioridadeBadgeClass($chamado->prioridade) }}">{{ $supportService->prioridadeLabel($chamado->prioridade) }}</span>
                        </div>

                        <h2 class="mt-4 text-lg font-black text-slate-900">{{ $chamado->titulo }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ $chamado->descricao }}</p>

                        <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Categoria</div>
                                <div class="mt-1 text-sm font-semibold text-slate-700">{{ $supportService->categoriaLabel($chamado->categoria) }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Ultima interacao</div>
                                <div class="mt-1 text-sm font-semibold text-slate-700">{{ optional($chamado->ultima_interacao_em)->format('d/m/Y H:i') ?: 'Ainda sem resposta' }}</div>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <div class="text-[11px] font-black uppercase tracking-[0.16em] text-slate-400">Mensagens</div>
                                <div class="mt-1 text-sm font-semibold text-slate-700">{{ $chamado->mensagens_publicas_count }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="flex w-full shrink-0 flex-col gap-3 lg:w-56">
                        <a href="{{ route('member.chamados.show', $chamado) }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                            Ver detalhes
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">
                Voce ainda nao tem chamados por aqui. Quando abrir um atendimento pelo suporte, ele aparecera nesta tela.
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $chamados->links() }}
    </div>
@endsection
