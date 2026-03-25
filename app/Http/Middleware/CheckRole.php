<?php

namespace App\Http\Middleware;

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

        if (method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster()) {
            return $next($request);
        }

        if (($usuario->perfil_global ?? null) === $papel) {
            return $next($request);
        }

        return match ($usuario->perfil_global ?? null) {
            'admin_local' => redirect()->route('local-admin.dashboard'),
            'member' => redirect()->route('member.dashboard'),
            default => redirect()->route('root'),
        };
    }
}
