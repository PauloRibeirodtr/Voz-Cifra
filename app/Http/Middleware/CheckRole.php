<?php

namespace App\Http\Middleware;

use App\Enums\PapelIgreja;
use App\Services\IgrejaAtivaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $papel): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return redirect()->route('login');
        }

        if (!($usuario->ativo ?? false)) {
            abort(403, 'Conta inativa.');
        }

        if (method_exists($usuario, 'nivelGlobal') && $usuario->nivelGlobal() >= 6) {
            return $next($request);
        }

        if (method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster()) {
            return $next($request);
        }

        if (($usuario->perfil_global ?? null) === $papel) {
            return $next($request);
        }

        $igrejaAtivaId = app(IgrejaAtivaService::class)->getId();

        if (method_exists($usuario, 'possuiPapel') && $this->usuarioTemPapelSolicitado($usuario, $papel, $igrejaAtivaId)) {
            return $next($request);
        }

        if (method_exists($usuario, 'rotaDestinoAposLogin')) {
            $rotaDestino = $usuario->rotaDestinoAposLogin();

            if ($rotaDestino !== null) {
                return redirect()->route($rotaDestino);
            }
        }

        return redirect()->route('root');
    }

    private function usuarioTemPapelSolicitado(mixed $usuario, string $papel, ?int $igrejaAtivaId): bool
    {
        return match ($papel) {
            'admin_local' => $usuario->possuiPapel(PapelIgreja::ADMIN_LOCAL->value, $igrejaAtivaId),
            'coordenador' => $usuario->possuiPapel(PapelIgreja::COORDENADOR->value, $igrejaAtivaId),
            'member', 'musico' => collect([
                PapelIgreja::MUSICO->value,
                PapelIgreja::COORDENADOR->value,
                PapelIgreja::ADMIN_LOCAL->value,
            ])->contains(fn (string $papelLocal) => $usuario->possuiPapel($papelLocal, $igrejaAtivaId)),
            default => false,
        };
    }
}
