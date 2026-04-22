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

        $rotaPerfil = method_exists($usuario, 'rotaDestinoPrimeiroAcesso')
            ? $usuario->rotaDestinoPrimeiroAcesso()
            : null;

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
