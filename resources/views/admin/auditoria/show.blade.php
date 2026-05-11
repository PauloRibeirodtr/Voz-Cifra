@extends('admin.layouts.admin')

@section('title', 'Detalhe da auditoria | Voz & Cifra')
@section('mobile_title', 'Detalhe da auditoria')
@section('desktop_subtitle', 'Rastreamento completo da acao sensivel')

@section('content')
    @php
        $risco = $contexto['risco'] ?? 'baixo';
        $riscoClasse = match ($risco) {
            'critico' => 'bg-red-100 text-red-700',
            'alto' => 'bg-orange-100 text-orange-700',
            'medio' => 'bg-amber-100 text-amber-700',
            default => 'bg-emerald-100 text-emerald-700',
        };
    @endphp

    <div class="mb-6">
        <a href="{{ route('admin.auditoria.index') }}" class="text-sm font-semibold text-green-700 hover:text-green-800">&larr; Voltar para auditoria</a>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,2fr),minmax(320px,1fr)]">
        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-700">{{ $auditoria->protocolo }}</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($auditoria->categoria) }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $riscoClasse }}">Risco {{ $risco }}</span>
                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">{{ $auditoria->evento }}</span>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $auditoria->resultado === 'email_falhou' ? 'bg-red-100 text-red-700' : ($auditoria->resultado === 'email_enviado' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700') }}">
                        {{ $auditoria->resultado }}
                    </span>
                </div>

                <h1 class="mt-4 text-2xl font-black text-gray-800">Evento auditado</h1>
                <p class="mt-2 text-sm text-gray-600">Este registro mostra quem executou a acao, para quem ela valeu e qual foi o resultado da tentativa de notificacao.</p>

                @if (filled($contexto['resumo'] ?? null))
                    <div class="mt-5 rounded-2xl bg-amber-50 px-4 py-4 text-sm text-amber-900">
                        {{ $contexto['resumo'] }}
                    </div>
                @endif

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Responsavel</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ $auditoria->ator_nome ?: 'Sistema' }}</div>
                        <div class="text-xs text-gray-500">{{ $auditoria->ator_funcao ?: 'Sem funcao registrada' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Usuario alvo</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ $auditoria->alvo_nome ?: 'Nao informado' }}</div>
                        <div class="text-xs text-gray-500">{{ $auditoria->alvo_email ?: 'Sem email' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Igreja</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ $auditoria->igreja_nome ?: 'Nao informada' }}</div>
                        <div class="text-xs text-gray-500">{{ optional($auditoria->igreja)->cidade }}{{ optional($auditoria->igreja)->estado ? ' - ' . optional($auditoria->igreja)->estado : '' }}</div>
                    </div>

                    <div class="rounded-2xl bg-gray-50 px-4 py-4">
                        <div class="text-[11px] font-black uppercase tracking-[0.16em] text-gray-400">Entrega</div>
                        <div class="mt-1 text-sm font-semibold text-gray-700">{{ optional($auditoria->notificacao_enviada_em)->format('d/m/Y H:i') ?: 'Nao enviada' }}</div>
                        <div class="text-xs text-gray-500">{{ $auditoria->erro_envio ?: 'Sem erro registrado' }}</div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Contexto tecnico</h2>
                @if ($contexto !== [])
                    <div class="mt-4 rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100 overflow-x-auto">
                        <pre>{{ json_encode($contexto, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                @else
                    <div class="mt-4 rounded-2xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500">
                        Este evento nao possui contexto adicional registrado.
                    </div>
                @endif
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-gray-800">Metadados</h2>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div><strong class="text-gray-800">Criado em:</strong> {{ $auditoria->created_at->format('d/m/Y H:i') }}</div>
                    <div><strong class="text-gray-800">Atualizado em:</strong> {{ $auditoria->updated_at->format('d/m/Y H:i') }}</div>
                    <div><strong class="text-gray-800">Categoria:</strong> {{ $auditoria->categoria }}</div>
                    <div><strong class="text-gray-800">IP:</strong> {{ $auditoria->ip ?: 'Nao registrado' }}</div>
                    <div><strong class="text-gray-800">User agent:</strong> {{ $auditoria->user_agent ?: 'Nao registrado' }}</div>
                </div>
            </section>
        </div>
    </div>
@endsection
