<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => to_route('registros.index'))
    ->middleware('auth');

Route::fallback(function () {
    return redirect('/registros');
});

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('users', [UserManagementController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');

    Route::get('users/create', [UserManagementController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    Route::post('users', [UserManagementController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');

    Route::get('users/{user}/roles-perms', [UserManagementController::class, 'editRolesPermissions'])
        ->middleware('permission:users.assign-roles|users.assign-permissions')
        ->name('users.roles-perms');
    Route::put('users/{user}/roles-perms', [UserManagementController::class, 'updateRolesPermissions'])
        ->middleware('permission:users.assign-roles|users.assign-permissions')
        ->name('users.roles-perms.update');

    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    Route::put('users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');

    Route::patch('users/{user}/restore', [UserManagementController::class, 'restore'])->name('users.restore');
    Route::delete('users/{user}/force', [UserManagementController::class, 'forceDelete'])->name('users.forceDelete');

    Route::get('logs', [LogController::class, 'index'])
        ->middleware('permission:logs.view')   // 'auth' já está no grupo
        ->name('logs.index');
});

Route::middleware('auth')->group(function () {
    // MARCAS
    Route::get('/marcas',[MarcaController::class, 'index'])->name('marcas.index');

    Route::get('/marcas/create', [MarcaController::class, 'create'])->name('marcas.create');

    Route::post('/marcas/store', [MarcaController::class, 'store'])->name('marcas.store');

    Route::get('/marcas/{marcas_id}', [MarcaController::class, 'show'])->name('marcas.show');

    Route::get('/marcas/{marcas_id}/edit', [MarcaController::class, 'edit'])->name('marcas.edit');

    Route::put('/marcas/{marcas_id}',[MarcaController::class, 'update'])->name('marcas.update');

    Route::delete('/marcas/{marcas_id}', [MarcaController::class, 'destroy'])->name('marcas.destroy');

    // Itens

    Route::get ('/itens', [ItemController::class, 'index'])->name('itens.index');

    Route::get('/itens/create', [ItemController::class, 'create'])->name('itens.create');

    Route::post('/itens/store', [ItemController::class, 'store'])->name('itens.store');

    Route::get('/itens/{item_id}', [ItemController::class, 'show'])->name('itens.show');

    Route::get('/itens/{item_id}/edit', [ItemController::class, 'edit'])->name('itens.edit');

    Route::put('/itens/{item_id}', [ItemController::class, 'update'])->name('itens.update');

    Route::delete('/itens/{item_id}', [ItemController::class, 'destroy'])->name('itens.destroy');

    // === REGISTROS ===

    // Lista + arquivados (GETs "fixos" primeiro)
    Route::get('/registros', [RegistroController::class, 'index'])
        ->middleware(['permission:registros.view'])
        ->name('registros.index');

    Route::get('/registros/arquivados', [RegistroController::class, 'trashed'])
        ->middleware(['permission:registros.restore|registros.force-delete'])
        ->name('registros.trashed');

    // Formulários (GETs específicos)
    Route::get('/registros/create', [RegistroController::class, 'create'])
        ->middleware(['permission:registros.create'])
        ->name('registros.create');

    Route::get('/registros/{registro}/edit', [RegistroController::class, 'edit'])
        ->middleware(['permission:registros.update'])
        ->name('registros.edit');

    // Ações (POST/PUT/PATCH/DELETE)
    Route::post('/registros', [RegistroController::class, 'store'])
        ->middleware(['permission:registros.create'])
        ->name('registros.store');

    Route::put('/registros/{registro}', [RegistroController::class, 'update'])
        ->middleware(['permission:registros.update'])
        ->name('registros.update');

    Route::patch('/registros/{id}/restore', [RegistroController::class, 'restore'])
        ->middleware(['permission:registros.restore'])
        ->name('registros.restore');

    Route::patch('/registros/{id}/toggle-patio', [RegistroController::class, 'togglePatio'])
        ->middleware(['permission:registros.view'])
        ->name('registros.togglePatio');

    Route::delete('/registros/{id}/delete', [RegistroController::class, 'forceDelete'])
        ->middleware(['permission:registros.force-delete'])
        ->name('registros.forceDelete');

    Route::delete('/registros/{registro}', [RegistroController::class, 'destroy'])
        ->middleware(['permission:registros.delete'])
        ->name('registros.destroy');

    // Por último: a rota curinga SHOW
    Route::get('/registros/{registro}', [RegistroController::class, 'show'])
        ->middleware(['permission:registros.view'])
        ->name('registros.show');


    // Route::get('/registros/arquivados', [RegistroController::class, 'trashed'])->name('registros.trashed');
    // Route::patch('/registros/{id}/restore', [RegistroController::class, 'restore'])->name('registros.restore');
    // Route::delete('/registros/{id}/delete', [RegistroController::class, 'forceDelete'])->name('registros.forceDelete');
});

require __DIR__ . '/auth.php';



//Route::patch('/registros/{id}/toggle-patio', [RegistroController::class, 'togglePatio']) ->name('registros.togglePatio'); // usa o metodo patch porque é uma atualização parcial
// da um nome pra rota (registros.togglePatio) -> assim voce chama no blade com route('registros.togglePatio', $registros->$id).