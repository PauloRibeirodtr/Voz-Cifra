<?php

use App\Http\Controllers\Admin\AcordeController;
use App\Http\Controllers\Admin\AdminMasterController;
use App\Http\Controllers\Admin\IgrejaController;
use App\Http\Controllers\Admin\MomentoLiturgicoController;
use App\Http\Controllers\Admin\MusicaController;
use App\Http\Controllers\Admin\TempoLiturgicoController;
use App\Http\Controllers\Admin\VersaoMusicalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\LocalAdmin\MissaController as LocalAdminMissaController;
use App\Http\Controllers\LocalAdmin\PainelAdminLocalController;
use App\Http\Controllers\Publico\IgrejaPublicaController;
use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    /** @var Usuario|null $usuario */
    $usuario = Auth::user();

    if (method_exists($usuario, 'ehAdminMaster') && $usuario->ehAdminMaster()) {
        return redirect()->route('admin.dashboard');
    }

    if (method_exists($usuario, 'ehAdminLocal') && $usuario->ehAdminLocal()) {
        return redirect()->route('local-admin.dashboard');
    }

    return redirect()->route('login');
})->name('root');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'verified_custom', 'super.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminMasterController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [AdminMasterController::class, 'profile'])->name('profile');
        Route::put('/perfil', [AdminMasterController::class, 'updateProfile'])->name('profile.update');
        Route::get('/configuracoes', [AdminMasterController::class, 'settings'])->name('settings');
        Route::post('/configuracoes/admins-master', [AdminMasterController::class, 'storeAdminMaster'])->name('admins-master.store');

        Route::get('/igrejas', [IgrejaController::class, 'index'])->name('igrejas.index');
        Route::get('/igrejas/criar', [IgrejaController::class, 'create'])->name('igrejas.create');
        Route::post('/igrejas', [IgrejaController::class, 'store'])->name('igrejas.store');
        Route::get('/igrejas/{igreja}/editar', [IgrejaController::class, 'edit'])->name('igrejas.edit');
        Route::put('/igrejas/{igreja}', [IgrejaController::class, 'update'])->name('igrejas.update');

        Route::get('/acordes', [AcordeController::class, 'index'])->name('acordes.index');
        Route::get('/acordes/criar', [AcordeController::class, 'create'])->name('acordes.create');
        Route::post('/acordes', [AcordeController::class, 'store'])->name('acordes.store');
        Route::get('/acordes/{id}', [AcordeController::class, 'show'])->name('acordes.show');
        Route::get('/acordes/{id}/editar', [AcordeController::class, 'edit'])->name('acordes.edit');
        Route::put('/acordes/{id}', [AcordeController::class, 'update'])->name('acordes.update');
        Route::delete('/acordes/{id}', [AcordeController::class, 'destroy'])->name('acordes.destroy');

        Route::get('/tempos-liturgicos', [TempoLiturgicoController::class, 'index'])->name('tempos-liturgicos.index');
        Route::get('/tempos-liturgicos/criar', [TempoLiturgicoController::class, 'create'])->name('tempos-liturgicos.create');
        Route::post('/tempos-liturgicos', [TempoLiturgicoController::class, 'store'])->name('tempos-liturgicos.store');
        Route::get('/tempos-liturgicos/{tempoLiturgico}/editar', [TempoLiturgicoController::class, 'edit'])->name('tempos-liturgicos.edit');
        Route::put('/tempos-liturgicos/{tempoLiturgico}', [TempoLiturgicoController::class, 'update'])->name('tempos-liturgicos.update');
        Route::delete('/tempos-liturgicos/{tempoLiturgico}', [TempoLiturgicoController::class, 'destroy'])->name('tempos-liturgicos.destroy');

        Route::get('/momentos-liturgicos', [MomentoLiturgicoController::class, 'index'])->name('momentos-liturgicos.index');
        Route::get('/momentos-liturgicos/criar', [MomentoLiturgicoController::class, 'create'])->name('momentos-liturgicos.create');
        Route::post('/momentos-liturgicos', [MomentoLiturgicoController::class, 'store'])->name('momentos-liturgicos.store');
        Route::get('/momentos-liturgicos/{momentoLiturgico}/editar', [MomentoLiturgicoController::class, 'edit'])->name('momentos-liturgicos.edit');
        Route::put('/momentos-liturgicos/{momentoLiturgico}', [MomentoLiturgicoController::class, 'update'])->name('momentos-liturgicos.update');
        Route::delete('/momentos-liturgicos/{momentoLiturgico}', [MomentoLiturgicoController::class, 'destroy'])->name('momentos-liturgicos.destroy');

        Route::get('/musicas', [MusicaController::class, 'index'])->name('musicas.index');
        Route::get('/musicas/criar', [MusicaController::class, 'create'])->name('musicas.create');
        Route::post('/musicas', [MusicaController::class, 'store'])->name('musicas.store');
        Route::get('/musicas/{musica}', [MusicaController::class, 'show'])->name('musicas.show');
        Route::get('/musicas/{musica}/editar', [MusicaController::class, 'edit'])->name('musicas.edit');
        Route::put('/musicas/{musica}', [MusicaController::class, 'update'])->name('musicas.update');
        Route::delete('/musicas/{musica}', [MusicaController::class, 'destroy'])->name('musicas.destroy');

        Route::get('/musicas/{musica}/versoes/criar', [VersaoMusicalController::class, 'create'])->name('versoes-musicais.create');
        Route::post('/musicas/{musica}/versoes', [VersaoMusicalController::class, 'store'])->name('versoes-musicais.store');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}', [VersaoMusicalController::class, 'show'])->name('versoes-musicais.show');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}/editar', [VersaoMusicalController::class, 'edit'])->name('versoes-musicais.edit');
        Route::put('/musicas/{musica}/versoes/{versaoMusical}', [VersaoMusicalController::class, 'update'])->name('versoes-musicais.update');
        Route::delete('/musicas/{musica}/versoes/{versaoMusical}', [VersaoMusicalController::class, 'destroy'])->name('versoes-musicais.destroy');
    });

Route::middleware(['auth', 'verified_custom', 'role:admin_local', 'local_admin.primeiro_acesso'])
    ->prefix('igreja')
    ->name('local-admin.')
    ->group(function () {
        Route::get('/painel', [PainelAdminLocalController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [PainelAdminLocalController::class, 'profile'])->name('profile');
        Route::put('/perfil', [PainelAdminLocalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/dados', [PainelAdminLocalController::class, 'church'])->name('church');

        Route::get('/missas', [LocalAdminMissaController::class, 'index'])->name('missas.index');
        Route::get('/missas/criar', [LocalAdminMissaController::class, 'create'])->name('missas.create');
        Route::post('/missas', [LocalAdminMissaController::class, 'store'])->name('missas.store');
        Route::get('/missas/{missa}/apresentacao', [LocalAdminMissaController::class, 'apresentacao'])->name('missas.apresentacao');
        Route::get('/missas/{missa}', [LocalAdminMissaController::class, 'show'])->name('missas.show');
        Route::get('/missas/{missa}/editar', [LocalAdminMissaController::class, 'edit'])->name('missas.edit');
        Route::put('/missas/{missa}', [LocalAdminMissaController::class, 'update'])->name('missas.update');
        Route::post('/missas/{missa}/toggle', [LocalAdminMissaController::class, 'toggle'])->name('missas.toggle');
        Route::get('/missas/{missa}/pdf', [LocalAdminMissaController::class, 'pdf'])->name('missas.pdf');

        Route::post('/missas/{missa}/repertorio', [LocalAdminMissaController::class, 'storeRepertorio'])->name('repertorio.store');
        Route::put('/missas/{missa}/repertorio/{missaMusica}', [LocalAdminMissaController::class, 'updateRepertorio'])->name('repertorio.update');
        Route::post('/missas/{missa}/repertorio/{missaMusica}/subir', [LocalAdminMissaController::class, 'subirRepertorio'])->name('repertorio.up');
        Route::post('/missas/{missa}/repertorio/{missaMusica}/descer', [LocalAdminMissaController::class, 'descerRepertorio'])->name('repertorio.down');
        Route::delete('/missas/{missa}/repertorio/{missaMusica}', [LocalAdminMissaController::class, 'destroyRepertorio'])->name('repertorio.destroy');
        Route::get('/missas/{missa}/repertorio/{missaMusica}/cifra', [LocalAdminMissaController::class, 'showCifra'])->name('repertorio.cifra');
    });

Route::get('/publico/igrejas/{slug}/status', [IgrejaPublicaController::class, 'status'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.status');

Route::get('/{slug}', [IgrejaPublicaController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.show');
