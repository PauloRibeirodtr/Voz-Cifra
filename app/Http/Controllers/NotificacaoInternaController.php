<?php

namespace App\Http\Controllers;

use App\Models\NotificacaoInterna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificacaoInternaController extends Controller
{
    public function marcarComoLida(Request $request, NotificacaoInterna $notificacao): RedirectResponse
    {
        abort_unless($notificacao->usuario_id === $request->user()->id, 403);

        if ($notificacao->lida_em === null) {
            $notificacao->forceFill(['lida_em' => now()])->save();
        }

        return $notificacao->url
            ? redirect($notificacao->url)
            : back()->with('success', 'Notificacao marcada como lida.');
    }

    public function marcarTodasComoLidas(Request $request): RedirectResponse
    {
        $request->user()
            ->notificacoesInternas()
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);

        return back()->with('success', 'Notificacoes marcadas como lidas.');
    }
}
