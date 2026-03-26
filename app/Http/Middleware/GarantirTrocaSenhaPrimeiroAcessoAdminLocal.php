<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GarantirTrocaSenhaPrimeiroAcessoAdminLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario || !method_exists($usuario, 'ehAdminLocal') || !$usuario->ehAdminLocal()) {
            return $next($request);
        }

        if (!($usuario->primeiro_acesso ?? false)) {
            return $next($request);
        }

        if ($request->routeIs('local-admin.profile') || $request->routeIs('local-admin.profile.update')) {
            return $next($request);
        }

        return redirect()
            ->route('local-admin.profile')
            ->with('status', 'No primeiro acesso, atualize sua senha para continuar usando o painel da igreja com seguranca.');
    }
}
