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

        if ($papel === 'admin_local' && method_exists($usuario, 'possuiPapel')) {
            if ($usuario->possuiPapel(PapelIgreja::ADMIN_LOCAL->value, $igrejaAtivaId)) {
                return $next($request);
            }
        }

        if ($papel === 'member' && method_exists($usuario, 'possuiPapel')) {
            foreach ([PapelIgreja::MUSICO->value, PapelIgreja::COORDENADOR->value, PapelIgreja::ADMIN_LOCAL->value] as $papelLocal) {
                if ($usuario->possuiPapel($papelLocal, $igrejaAtivaId)) {
                    return $next($request);
                }
            }
        }

        if (method_exists($usuario, 'ehAdminLocal') && $usuario->ehAdminLocal()) {
            return redirect()->route('local-admin.dashboard');
        }

        if (method_exists($usuario, 'ehMembro') && $usuario->ehMembro()) {
            return redirect()->route('member.dashboard');
        }

        return redirect()->route('root');
    }
}
