<?php

namespace App\Http\Controllers;

use App\Models\Missa;
use App\Models\MissaMusica;
use App\Models\NotificacaoInterna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class NotificacaoInternaController extends Controller
{
    public function marcarComoLida(Request $request, NotificacaoInterna $notificacao): RedirectResponse
    {
        abort_unless($notificacao->usuario_id === $request->user()->id, 403);

        if ($notificacao->lida_em === null) {
            $notificacao->forceFill(['lida_em' => now()])->save();
        }

        if (! filled($notificacao->url)) {
            return back()->with('success', 'Notificacao marcada como lida.');
        }

        if (! $this->urlInternaPermitida($notificacao->url) || ! $this->destinoDisponivel($notificacao->url)) {
            return redirect()
                ->route($this->rotaSeguraPara($request))
                ->with('warning', 'Essa notificacao ja foi resolvida ou aponta para um item que nao esta mais disponivel.');
        }

        return redirect($notificacao->url);
    }

    public function marcarTodasComoLidas(Request $request): RedirectResponse
    {
        $request->user()
            ->notificacoesInternas()
            ->whereNull('lida_em')
            ->update(['lida_em' => now()]);

        return back()->with('success', 'Notificacoes marcadas como lidas.');
    }

    private function urlInternaPermitida(string $url): bool
    {
        $partes = parse_url($url);

        if ($partes === false) {
            return false;
        }

        $host = $partes['host'] ?? null;

        if ($host === null) {
            return str_starts_with($url, '/');
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

        return $appHost !== null && strcasecmp((string) $host, (string) $appHost) === 0;
    }

    private function destinoDisponivel(string $url): bool
    {
        $partes = parse_url($url);

        if ($partes === false) {
            return false;
        }

        $path = '/' . ltrim((string) ($partes['path'] ?? ''), '/');
        $fragmento = (string) ($partes['fragment'] ?? '');

        if (preg_match('#^/igreja/missas/(\d+)$#', $path, $matches) === 1) {
            $missaId = (int) $matches[1];

            if (! Missa::query()->whereKey($missaId)->exists()) {
                return false;
            }

            return $this->fragmentoRepertorioDisponivel($fragmento, $missaId);
        }

        if ($path === '/musico/repertorio' || $path === '/coordenacao/repertorio') {
            return $this->fragmentoRepertorioDisponivel($fragmento);
        }

        return true;
    }

    private function fragmentoRepertorioDisponivel(string $fragmento, ?int $missaId = null): bool
    {
        if (preg_match('/^repertorio-item-(\d+)$/', $fragmento, $matches) !== 1) {
            return true;
        }

        return MissaMusica::query()
            ->whereKey((int) $matches[1])
            ->when($missaId !== null, fn ($query) => $query->where('missa_id', $missaId))
            ->exists();
    }

    private function rotaSeguraPara(Request $request): string
    {
        $usuario = $request->user();
        $rotaPreferida = $usuario?->rotaDestinoAposLogin();

        if (is_string($rotaPreferida) && Route::has($rotaPreferida)) {
            return $rotaPreferida;
        }

        return Route::has('root') ? 'root' : 'login';
    }
}
