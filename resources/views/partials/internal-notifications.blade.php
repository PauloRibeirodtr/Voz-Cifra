@auth
    @php
        $tone = $tone ?? 'light';
        static $internalNotificationState = null;

        if ($internalNotificationState === null) {
            $canShowInternalNotifications = \Illuminate\Support\Facades\Schema::hasTable('notificacoes_internas');
            $notificacoesHeader = collect();
            $notificacoesNaoLidas = 0;

            if ($canShowInternalNotifications) {
                $notificacoesHeader = auth()->user()
                    ->notificacoesInternas()
                    ->latest()
                    ->limit(6)
                    ->get();
                $notificacoesNaoLidas = auth()->user()
                    ->notificacoesInternas()
                    ->whereNull('lida_em')
                    ->count();
            }

            $internalNotificationState = [
                'canShowInternalNotifications' => $canShowInternalNotifications,
                'notificacoesHeader' => $notificacoesHeader,
                'notificacoesNaoLidas' => $notificacoesNaoLidas,
            ];
        }

        $canShowInternalNotifications = $internalNotificationState['canShowInternalNotifications'];
        $notificacoesHeader = $internalNotificationState['notificacoesHeader'];
        $notificacoesNaoLidas = $internalNotificationState['notificacoesNaoLidas'];
        $buttonClasses = $tone === 'dark'
            ? 'border-white/10 bg-[#2a1b1b] text-[#f3dfbd] hover:border-[#c9a15f]/40 hover:bg-[#352121] hover:text-[#fff8ed]'
            : 'border-[#e7d8c6] bg-white text-[#4b3426] hover:border-[#c9a15f]/60 hover:text-[#8a5a26]';
    @endphp

    @if ($canShowInternalNotifications)
        <details class="relative">
            <summary
                class="relative inline-flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-2xl border shadow-sm transition {{ $buttonClasses }}"
                style="list-style: none;"
                aria-label="Abrir notificacoes"
            >
                <i class="fa-solid fa-bell text-sm"></i>
                @if ($notificacoesNaoLidas > 0)
                    <span class="absolute -right-1 -top-1 min-w-5 rounded-full bg-emerald-600 px-1.5 py-0.5 text-center text-[10px] font-black leading-none text-white shadow">
                        {{ $notificacoesNaoLidas > 9 ? '9+' : $notificacoesNaoLidas }}
                    </span>
                @endif
            </summary>

            <div class="fixed right-3 top-20 z-[90] w-[min(24rem,calc(100vw-1.5rem))] overflow-hidden rounded-[1.35rem] border border-[#e7d8c6] bg-white text-[#261a14] shadow-2xl shadow-slate-950/20 lg:right-8 lg:top-24">
                <div class="flex items-start justify-between gap-3 border-b border-[#efe4d6] px-4 py-3">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-[#8a5a26]">Notificacoes</p>
                        <h2 class="mt-1 text-sm font-black">Central de avisos</h2>
                    </div>

                    @if ($notificacoesNaoLidas > 0)
                        <form method="POST" action="{{ route('notificacoes.ler-todas') }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-[#e7d8c6] px-3 py-1.5 text-xs font-bold text-[#6f4a2c] transition hover:bg-[#f8f1e8]">
                                Ler todas
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-96 overflow-y-auto p-2">
                    @forelse ($notificacoesHeader as $notificacao)
                        @php
                            $dadosNotificacao = $notificacao->dados ?? [];
                            $solicitacaoTom = null;

                            if (($notificacao->tipo ?? null) === 'pedido_mudanca_tom' && filled($dadosNotificacao['solicitacao_id'] ?? null)) {
                                $solicitacaoTom = \App\Models\SolicitacaoMudancaTom::query()
                                    ->whereKey($dadosNotificacao['solicitacao_id'])
                                    ->where('status', \App\Models\SolicitacaoMudancaTom::STATUS_PENDENTE)
                                    ->first();
                            }
                        @endphp
                        <form method="POST" action="{{ route('notificacoes.ler', $notificacao) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl px-3 py-3 text-left transition hover:bg-[#f8f1e8] {{ $notificacao->lida_em ? 'opacity-70' : 'bg-emerald-50/70' }}">
                                <span class="flex items-start justify-between gap-3">
                                    <span class="min-w-0">
                                        <span class="block text-sm font-black text-[#1f1712]">{{ $notificacao->titulo }}</span>
                                        @if (filled($notificacao->mensagem))
                                            <span class="mt-1 block text-xs leading-relaxed text-[#6f5b4d]">{{ $notificacao->mensagem }}</span>
                                        @endif
                                        <span class="mt-2 block text-[11px] font-bold text-[#9a7a55]">{{ $notificacao->created_at?->diffForHumans() }}</span>
                                    </span>

                                    @if (!$notificacao->lida_em)
                                        <span class="mt-1 h-2.5 w-2.5 flex-none rounded-full bg-emerald-600"></span>
                                    @endif
                                </span>
                            </button>
                        </form>
                        @if ($solicitacaoTom)
                            <div class="-mt-1 mb-2 rounded-2xl border border-emerald-100 bg-white px-3 pb-3 pt-2">
                                <div class="grid grid-cols-2 gap-2">
                                    <form method="POST" action="{{ route('notificacoes.repertorio.tom.aprovar', $solicitacaoTom) }}">
                                        @csrf
                                        <input type="hidden" name="voltar_para" value="back">
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-3 py-2 text-xs font-black text-white transition hover:bg-emerald-800">
                                            Aprovar tom
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('notificacoes.repertorio.tom.recusar', $solicitacaoTom) }}">
                                        @csrf
                                        <input type="hidden" name="voltar_para" value="back">
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl border border-[#e7d8c6] bg-[#fff8ed] px-3 py-2 text-xs font-black text-[#6f4a2c] transition hover:bg-[#f8f1e8]">
                                            Recusar
                                        </button>
                                    </form>
                                </div>
                                @if (filled($notificacao->url))
                                    <a href="{{ $notificacao->url }}" class="mt-2 inline-flex w-full items-center justify-center rounded-xl border border-gray-200 px-3 py-2 text-xs font-bold text-gray-600 transition hover:bg-gray-50">
                                        Abrir repertorio
                                    </a>
                                @endif
                            </div>
                        @endif
                    @empty
                        <div class="px-4 py-8 text-center">
                            <p class="text-sm font-bold text-[#4b3426]">Tudo limpo por aqui.</p>
                            <p class="mt-1 text-xs text-[#8b7565]">Quando um papel, acesso ou pedido de tom mudar, aparece neste sininho.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </details>
    @endif
@endauth
