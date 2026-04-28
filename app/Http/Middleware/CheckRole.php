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
        $igrejaAtivaService = app(IgrejaAtivaService::class);

        if (!$usuario) {
            return redirect()->route('login');
        }

        $usuario = method_exists($usuario, 'fresh') ? ($usuario->fresh() ?? $usuario) : $usuario;

        if (!($usuario->ativo ?? false)) {
            abort(403, 'Conta inativa.');
        }

        if (method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster()) {
            return $next($request);
        }

        if (($usuario->perfil_global ?? null) === 'admin_master' || (int) ($usuario->nivel_global ?? 0) >= 6) {
            return $next($request);
        }

        $igrejaAtivaId = $igrejaAtivaService->getId();

        if (method_exists($usuario, 'possuiPapel') && $this->usuarioTemPapelSolicitado($usuario, $papel, $igrejaAtivaId)) {
            return $next($request);
        }

        $igrejaCompativel = $this->resolverIgrejaCompativelPorPapel($usuario, $papel);

        if ($igrejaCompativel !== null) {
            $igrejaAtivaService->set($igrejaCompativel);

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

    private function resolverIgrejaCompativelPorPapel(mixed $usuario, string $papel): ?int
    {
        if (!method_exists($usuario, 'igrejasDisponiveisPorPapel')) {
            return null;
        }

        $igreja = match ($papel) {
            'admin_local' => $usuario->igrejasDisponiveisPorPapel(PapelIgreja::ADMIN_LOCAL)->first(),
            'coordenador' => $usuario->igrejasDisponiveisPorPapel(PapelIgreja::COORDENADOR)->first(),
            'member', 'musico' => $usuario->igrejasDisponiveisPorPapel(PapelIgreja::MUSICO)->first()
                ?? $usuario->igrejasDisponiveisPorPapel(PapelIgreja::COORDENADOR)->first()
                ?? $usuario->igrejasDisponiveisPorPapel(PapelIgreja::ADMIN_LOCAL)->first(),
            default => null,
        };

        return is_object($igreja) && isset($igreja->id) ? (int) $igreja->id : null;
    }
}
