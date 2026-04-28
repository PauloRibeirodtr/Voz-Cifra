<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

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

        abort(403, 'Acesso restrito ao administrador master.');
    }
}
