@extends('member.layouts.app')

@section('title', 'Novo chamado | Voz & Cifra')
@section('mobile_title', 'Novo chamado')
@section('desktop_subtitle', 'Abra um pedido de acorde, acesso ou contestacao direto do seu painel')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900 sm:text-3xl">Abrir novo chamado</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-500">Use esta tela para pedir novos acordes, solicitar ajuda de acesso ou contestar uma inativacao.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.8fr),minmax(300px,1fr)]">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('member.chamados.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="categoria" class="mb-2 block text-sm font-semibold text-slate-700">Tipo do pedido</label>
                    <select id="categoria" name="categoria" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-800">
                        <option value="">Selecione</option>
                        <option value="acorde" @selected(old('categoria') === 'acorde')>Pedido de acorde</option>
                        <option value="contestacao_inativacao" @selected(old('categoria') === 'contestacao_inativacao')>Contestar conta inativada</option>
                        <option value="musica" @selected(old('categoria') === 'musica')>Pedido de musica</option>
                        <option value="acesso" @selected(old('categoria') === 'acesso')>Problema de acesso</option>
                        <option value="outro" @selected(old('categoria') === 'outro')>Outro problema</option>
                    </select>
                </div>

                <div>
                    <label for="descricao" class="mb-2 block text-sm font-semibold text-slate-700">Descreva o que voce precisa</label>
                    <textarea id="descricao" name="descricao" rows="8" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-slate-800" placeholder="Exemplo: Preciso que adicionem o acorde F#9 na musica X, ou quero entender por que minha conta foi inativada.">{{ old('descricao') }}</textarea>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Abrir chamado
                    </button>
                    <a href="{{ route('member.chamados.index') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Voltar
                    </a>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Tipos mais comuns</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p><strong class="text-slate-800">Pedido de acorde:</strong> quando falta um acorde ou diagrama.</p>
                    <p><strong class="text-slate-800">Conta inativada:</strong> quando voce quer contestar uma inativacao ou pedir revisao.</p>
                    <p><strong class="text-slate-800">Problema de acesso:</strong> quando nao consegue entrar ou concluir o primeiro acesso.</p>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-black text-slate-900">Dica</h2>
                <p class="mt-3 text-sm text-slate-600">Quanto mais claro voce escrever, menos o suporte vai precisar pedir detalhes depois. Isso agiliza bastante o atendimento.</p>
            </section>
        </aside>
    </div>
@endsection
