<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->web(append: [
            \App\Http\Middleware\ResolverIgrejaAtiva::class,
        ]);

        $middleware->alias([
            'super.admin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'verified_custom' => \App\Http\Middleware\EnsureUserIsVerified::class,
            'primeiro_acesso' => \App\Http\Middleware\GarantirTrocaSenhaPrimeiroAcesso::class,
            'unique.submission' => \App\Http\Middleware\PreventDuplicateSubmission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sua sessao expirou. Atualize a pagina e tente novamente.',
                ], 419);
            }

            if ($request->routeIs('logout') || ! $request->user()) {
                return redirect()
                    ->route('login')
                    ->with('status', 'Sua sessao expirou. Entre novamente para continuar.');
            }

            return redirect()
                ->back()
                ->with('warning', 'Sua sessao expirou. Atualize a pagina e tente novamente.');
        });
    })->create();
