<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GarantirTrocaSenhaPrimeiroAcesso
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if (!$usuario || !($usuario->primeiro_acesso ?? false)) {
            return $next($request);
        }

        $rotaPerfil = match (true) {
            method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster() => 'admin.profile',
            method_exists($usuario, 'ehAdminLocal') && $usuario->ehAdminLocal() => 'local-admin.profile',
            method_exists($usuario, 'ehMembro') && $usuario->ehMembro() => 'member.profile',
            default => null,
        };

        if ($rotaPerfil === null) {
            return $next($request);
        }

        if ($request->routeIs($rotaPerfil) || $request->routeIs($rotaPerfil . '.update')) {
            return $next($request);
        }

        return redirect()
            ->route($rotaPerfil)
            ->with('status', 'No primeiro acesso, atualize sua senha para continuar usando o sistema com seguranca.');
    }
}
