<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // --- AQUI ESTÁ A CORREÇÃO ---
        // Estamos registrando o apelido 'super.admin' para o Laravel encontrar sua classe
        $middleware->alias([
            'super.admin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'verified_custom' => \App\Http\Middleware\EnsureUserIsVerified::class,
            'local_admin.primeiro_acesso' => \App\Http\Middleware\GarantirTrocaSenhaPrimeiroAcessoAdminLocal::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
