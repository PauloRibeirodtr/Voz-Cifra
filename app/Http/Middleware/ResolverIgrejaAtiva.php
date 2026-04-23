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
            // Resolve a igreja ativa priorizando vinculos reais e sem depender do campo legado.
            $this->igrejaAtivaService->getId();
        }

        return $next($request);
    }
}
