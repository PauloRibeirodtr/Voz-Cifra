<?php

namespace App\Http\Middleware;

use App\Services\IgrejaAtivaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolverIgrejaAtiva
{
    public function __construct(
        private readonly IgrejaAtivaService $igrejaAtivaService
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user();

        if ($usuario !== null && !$request->session()->has('igreja_ativa_id')) {
            if (!empty($usuario->igreja_id)) {
                $this->igrejaAtivaService->set((int) $usuario->igreja_id);
            } else {
                // Resolve fallback sem bloquear acesso, mesmo sem igreja legada.
                $this->igrejaAtivaService->getId();
            }
        }

        return $next($request);
    }
}

