<?php

use App\Http\Controllers\Admin\AcordeController;
use App\Http\Controllers\Admin\AdminLocalController;
use App\Http\Controllers\Admin\AdminMasterController;
use App\Http\Controllers\Admin\AuditoriaController;
use App\Http\Controllers\Admin\IgrejaController;
use App\Http\Controllers\Admin\MomentoLiturgicoController;
use App\Http\Controllers\Admin\MusicoController as AdminMusicoController;
use App\Http\Controllers\Admin\MusicaController;
use App\Http\Controllers\Admin\PadreController;
use App\Http\Controllers\Admin\TempoLiturgicoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Admin\VersaoMusicalController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\IgrejaAtivaController;
use App\Http\Controllers\Media\PublicStorageController;
use App\Http\Controllers\Coordenador\GestaoIgrejaController as CoordenadorGestaoIgrejaController;
use App\Http\Controllers\LocalAdmin\MusicoController as LocalAdminMusicoController;
use App\Http\Controllers\LocalAdmin\MissaController as LocalAdminMissaController;
use App\Http\Controllers\Member\BibliotecaMusicalController;
use App\Http\Controllers\Member\ColecaoEstudoController;
use App\Http\Controllers\LocalAdmin\PainelAdminLocalController;
use App\Http\Controllers\Member\PainelMembroController;
use App\Http\Controllers\Publico\HomeController;
use App\Http\Controllers\Publico\IgrejaPublicaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('root');
Route::get('/desenvolvedores', [HomeController::class, 'desenvolvedores'])->name('developers');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::post('/contexto/igreja-ativa', [IgrejaAtivaController::class, 'update'])
    ->middleware(['auth', 'verified_custom', 'primeiro_acesso'])
    ->name('contexto.igreja-ativa.update');

Route::middleware(['auth', 'verified_custom', 'super.admin', 'primeiro_acesso'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminMasterController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [AdminMasterController::class, 'profile'])->name('profile');
        Route::put('/perfil', [AdminMasterController::class, 'updateProfile'])->name('profile.update');
        Route::post('/perfil/vinculos', [AdminMasterController::class, 'storeProfileVinculo'])->name('profile.vinculos.store');
        Route::get('/configuracoes', [AdminMasterController::class, 'settings'])->name('settings');

        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::get('/usuarios/criar', [UsuarioController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::get('/usuarios/hierarquia', [UsuarioController::class, 'hierarchy'])->name('usuarios.hierarquia');
        Route::get('/usuarios/{usuario}/editar', [UsuarioController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{usuario}/toggle', [UsuarioController::class, 'toggle'])->name('usuarios.toggle');
        Route::post('/usuarios/{usuario}/resetar-senha', [UsuarioController::class, 'resetPassword'])->name('usuarios.password.reset');
        Route::post('/usuarios/{usuario}/vinculos', [UsuarioController::class, 'storeVinculo'])->name('usuarios.vinculos.store');

        Route::get('/admins-locais', [AdminLocalController::class, 'index'])->name('admins-locais.index');
        Route::post('/admins-locais/{usuario}/toggle', [AdminLocalController::class, 'toggle'])->name('admins-locais.toggle');
        Route::post('/admins-locais/{usuario}/resetar-senha', [AdminLocalController::class, 'resetPassword'])->name('admins-locais.password.reset');

        Route::get('/igrejas', [IgrejaController::class, 'index'])->name('igrejas.index');
        Route::get('/igrejas/criar', [IgrejaController::class, 'create'])->name('igrejas.create');
        Route::post('/igrejas', [IgrejaController::class, 'store'])->name('igrejas.store');
        Route::get('/igrejas/{igreja}/editar', [IgrejaController::class, 'edit'])->name('igrejas.edit');
        Route::put('/igrejas/{igreja}', [IgrejaController::class, 'update'])->name('igrejas.update');
        Route::post('/igrejas/{igreja}/admins-locais', [IgrejaController::class, 'storeAdminLocal'])->name('igrejas.admins-locais.store');
        Route::post('/igrejas/{igreja}/coordenadores', [IgrejaController::class, 'storeCoordenador'])->name('igrejas.coordenadores.store');
        Route::post('/igrejas/{igreja}/admin-local/resetar-senha', [IgrejaController::class, 'resetAdminLocalPassword'])->name('igrejas.admin-local.password.reset');
        Route::post('/igrejas/{igreja}/usuarios/{usuario}/papeis', [IgrejaController::class, 'storePapelUsuarioVinculado'])->name('igrejas.usuarios.papeis.store');
        Route::post('/igrejas/{igreja}/usuarios/{usuario}/papeis/remover', [IgrejaController::class, 'destroyPapelUsuarioVinculado'])->name('igrejas.usuarios.papeis.destroy');

        Route::get('/musicos', [AdminMusicoController::class, 'index'])->name('musicos.index');
        Route::get('/musicos/criar', [AdminMusicoController::class, 'create'])->name('musicos.create');
        Route::post('/musicos', [AdminMusicoController::class, 'store'])->name('musicos.store');
        Route::get('/musicos/{musico}/editar', [AdminMusicoController::class, 'edit'])->name('musicos.edit');
        Route::put('/musicos/{musico}', [AdminMusicoController::class, 'update'])->name('musicos.update');
        Route::post('/musicos/{musico}/toggle', [AdminMusicoController::class, 'toggle'])->name('musicos.toggle');
        Route::post('/musicos/{musico}/resetar-senha', [AdminMusicoController::class, 'resetPassword'])->name('musicos.password.reset');
        Route::delete('/musicos/{musico}', [AdminMusicoController::class, 'destroy'])->name('musicos.destroy');

        Route::get('/padres', [PadreController::class, 'index'])->name('padres.index');
        Route::get('/padres/criar', [PadreController::class, 'create'])->name('padres.create');
        Route::post('/padres', [PadreController::class, 'store'])->name('padres.store');
        Route::get('/padres/{padre}/editar', [PadreController::class, 'edit'])->name('padres.edit');
        Route::put('/padres/{padre}', [PadreController::class, 'update'])->name('padres.update');
        Route::post('/padres/{padre}/toggle', [PadreController::class, 'toggle'])->name('padres.toggle');

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

        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{auditoria}', [AuditoriaController::class, 'show'])->name('auditoria.show');
    });

Route::middleware(['auth', 'verified_custom', 'role:admin_local', 'primeiro_acesso'])
    ->prefix('igreja')
    ->name('local-admin.')
    ->group(function () {
        Route::get('/painel', [PainelAdminLocalController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [PainelAdminLocalController::class, 'profile'])->name('profile');
        Route::put('/perfil', [PainelAdminLocalController::class, 'updateProfile'])->name('profile.update');
        Route::get('/dados', [PainelAdminLocalController::class, 'church'])->name('church');

        Route::get('/musicos', [LocalAdminMusicoController::class, 'index'])->name('musicos.index');
        Route::get('/musicos/criar', [LocalAdminMusicoController::class, 'create'])->name('musicos.create');
        Route::post('/musicos', [LocalAdminMusicoController::class, 'store'])->name('musicos.store');
        Route::post('/musicos/vincular-existente', [LocalAdminMusicoController::class, 'vincularExistente'])->name('musicos.vincular-existente');
        Route::get('/musicos/{musico}/editar', [LocalAdminMusicoController::class, 'edit'])->name('musicos.edit');
        Route::put('/musicos/{musico}', [LocalAdminMusicoController::class, 'update'])->name('musicos.update');
        Route::post('/musicos/{musico}/toggle', [LocalAdminMusicoController::class, 'toggle'])->name('musicos.toggle');
        Route::post('/musicos/{musico}/resetar-senha', [LocalAdminMusicoController::class, 'resetPassword'])->name('musicos.password.reset');
        Route::delete('/musicos/{musico}', [LocalAdminMusicoController::class, 'destroy'])->name('musicos.destroy');

        Route::get('/missas', [LocalAdminMissaController::class, 'index'])->name('missas.index');
        Route::get('/missas/criar', [LocalAdminMissaController::class, 'create'])->name('missas.create');
        Route::post('/missas', [LocalAdminMissaController::class, 'store'])->name('missas.store');
        Route::get('/missas/{missa}/apresentacao', [LocalAdminMissaController::class, 'apresentacao'])->name('missas.apresentacao');
        Route::get('/missas/{missa}', [LocalAdminMissaController::class, 'show'])->name('missas.show');
        Route::get('/missas/{missa}/editar', [LocalAdminMissaController::class, 'edit'])->name('missas.edit');
        Route::put('/missas/{missa}', [LocalAdminMissaController::class, 'update'])->name('missas.update');
        Route::delete('/missas/{missa}', [LocalAdminMissaController::class, 'destroy'])->name('missas.destroy');
        Route::post('/missas/{missa}/toggle', [LocalAdminMissaController::class, 'toggle'])->name('missas.toggle');
        Route::get('/missas/{missa}/pdf', [LocalAdminMissaController::class, 'pdf'])->name('missas.pdf');

        Route::post('/missas/{missa}/repertorio', [LocalAdminMissaController::class, 'storeRepertorio'])->name('repertorio.store');
        Route::put('/missas/{missa}/repertorio/{missaMusica}', [LocalAdminMissaController::class, 'updateRepertorio'])->name('repertorio.update');
        Route::post('/missas/{missa}/repertorio/{missaMusica}/subir', [LocalAdminMissaController::class, 'subirRepertorio'])->name('repertorio.up');
        Route::post('/missas/{missa}/repertorio/{missaMusica}/descer', [LocalAdminMissaController::class, 'descerRepertorio'])->name('repertorio.down');
        Route::delete('/missas/{missa}/repertorio/{missaMusica}', [LocalAdminMissaController::class, 'destroyRepertorio'])->name('repertorio.destroy');
        Route::get('/missas/{missa}/repertorio/{missaMusica}/cifra', [LocalAdminMissaController::class, 'showCifra'])->name('repertorio.cifra');
        Route::get('/missas/{missa}/repertorio/{missaMusica}/impressao', [LocalAdminMissaController::class, 'imprimirCifra'])->name('repertorio.print');
        Route::get('/missas/{missa}/repertorio/{missaMusica}/pdf', [LocalAdminMissaController::class, 'pdfCifra'])->name('repertorio.pdf');
    });

Route::middleware(['auth', 'verified_custom', 'role:coordenador', 'primeiro_acesso'])
    ->prefix('coordenacao')
    ->name('coordenador.')
    ->group(function () {
        Route::get('/painel', [PainelMembroController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [PainelMembroController::class, 'profile'])->name('profile');
        Route::put('/perfil', [PainelMembroController::class, 'updateProfile'])->name('profile.update');

        Route::get('/musicos', [LocalAdminMusicoController::class, 'index'])->name('musicos.index');
        Route::get('/musicos/criar', [LocalAdminMusicoController::class, 'create'])->name('musicos.create');
        Route::post('/musicos', [LocalAdminMusicoController::class, 'store'])->name('musicos.store');
        Route::post('/musicos/vincular-existente', [LocalAdminMusicoController::class, 'vincularExistente'])->name('musicos.vincular-existente');
        Route::get('/musicos/{musico}/editar', [LocalAdminMusicoController::class, 'edit'])->name('musicos.edit');
        Route::put('/musicos/{musico}', [LocalAdminMusicoController::class, 'update'])->name('musicos.update');
        Route::post('/musicos/{musico}/resetar-senha', [LocalAdminMusicoController::class, 'resetPassword'])->name('musicos.password.reset');

        Route::post('/igreja/admins-locais', [CoordenadorGestaoIgrejaController::class, 'storeAdminLocal'])->name('igreja.admins-locais.store');

        Route::get('/musicas', [MusicaController::class, 'index'])->name('musicas.index');
        Route::get('/musicas/criar', [MusicaController::class, 'create'])->name('musicas.create');
        Route::post('/musicas', [MusicaController::class, 'store'])->name('musicas.store');
        Route::get('/musicas/{musica}', [MusicaController::class, 'show'])->name('musicas.show');
        Route::get('/musicas/{musica}/editar', [MusicaController::class, 'edit'])->name('musicas.edit');
        Route::put('/musicas/{musica}', [MusicaController::class, 'update'])->name('musicas.update');

        Route::get('/musicas/{musica}/versoes/criar', [VersaoMusicalController::class, 'create'])->name('versoes-musicais.create');
        Route::post('/musicas/{musica}/versoes', [VersaoMusicalController::class, 'store'])->name('versoes-musicais.store');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}', [VersaoMusicalController::class, 'show'])->name('versoes-musicais.show');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}/editar', [VersaoMusicalController::class, 'edit'])->name('versoes-musicais.edit');
        Route::put('/musicas/{musica}/versoes/{versaoMusical}', [VersaoMusicalController::class, 'update'])->name('versoes-musicais.update');
    });

Route::middleware(['auth', 'verified_custom', 'role:member', 'primeiro_acesso'])
    ->prefix('musico')
    ->name('member.')
    ->group(function () {
        Route::get('/painel', [PainelMembroController::class, 'dashboard'])->name('dashboard');
        Route::get('/perfil', [PainelMembroController::class, 'profile'])->name('profile');
        Route::get('/configuracoes', [PainelMembroController::class, 'profile'])->name('settings');
        Route::put('/perfil', [PainelMembroController::class, 'updateProfile'])->name('profile.update');
        Route::get('/repertorio', [BibliotecaMusicalController::class, 'repertorio'])->name('repertorio');
        Route::get('/musicas', [BibliotecaMusicalController::class, 'musicas'])->name('musicas.index');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}', [BibliotecaMusicalController::class, 'versao'])->name('versoes.show');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}/impressao', [BibliotecaMusicalController::class, 'imprimirVersao'])->name('versoes.print');
        Route::get('/musicas/{musica}/versoes/{versaoMusical}/pdf', [BibliotecaMusicalController::class, 'pdfVersao'])->name('versoes.pdf');
        Route::get('/colecoes', [ColecaoEstudoController::class, 'index'])->name('colecoes.index');
        Route::post('/colecoes', [ColecaoEstudoController::class, 'store'])->name('colecoes.store');
        Route::get('/colecoes/{colecao}', [ColecaoEstudoController::class, 'show'])->name('colecoes.show');
        Route::post('/colecoes/{colecao}/itens', [ColecaoEstudoController::class, 'adicionarItem'])->name('colecoes.itens.store');
        Route::delete('/colecoes/{colecao}/itens/{item}', [ColecaoEstudoController::class, 'removerItem'])->name('colecoes.itens.destroy');
    });


Route::get('/publico/igrejas/{slug}/status', [IgrejaPublicaController::class, 'status'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.status.legacy');

Route::get('/publico/musicos/{slug}/status', [IgrejaPublicaController::class, 'statusMusicos'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.musicos.status.legacy');

Route::get('/publico/musicos/{slug}', [IgrejaPublicaController::class, 'showMusicos'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.musicos.show.legacy');

Route::get('/{slug}/status', [IgrejaPublicaController::class, 'status'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.status');

Route::get('/{slug}/musicos/status', [IgrejaPublicaController::class, 'statusMusicos'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.musicos.status');

Route::get('/{slug}/musicos', [IgrejaPublicaController::class, 'showMusicos'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.musicos.show');

Route::get('/media/public/{path}', [PublicStorageController::class, 'show'])
    ->where('path', '.*')
    ->name('media.public.show');

Route::get('/{slug}', [IgrejaPublicaController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('igrejas.public.show');


Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
