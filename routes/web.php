<?php

use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

    Route::get('logs', [LogController::class, 'index'])
        ->middleware('permission:users.view')   // 'auth' já está no grupo
        ->name('logs.index');
});
// Route::resource('registros', RegistroController::class);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // === REGISTROS ===

    // Lista + arquivados (GETs "fixos" primeiro)
    Route::get('/registros', [RegistroController::class, 'index'])
        ->middleware(['auth', 'permission:registros.view'])
        ->name('registros.index');

    Route::get('/registros/arquivados', [RegistroController::class, 'trashed'])
        ->middleware(['auth', 'permission:registros.view'])
        ->name('registros.trashed');

    // Formulários (GETs específicos)
    Route::get('/registros/create', [RegistroController::class, 'create'])
        ->middleware(['auth', 'permission:registros.create'])
        ->name('registros.create');

    Route::get('/registros/{registro}/edit', [RegistroController::class, 'edit'])
        ->middleware(['auth', 'permission:registros.update'])
        ->name('registros.edit');

    // Ações (POST/PUT/PATCH/DELETE)
    Route::post('/registros', [RegistroController::class, 'store'])
        ->middleware(['auth', 'permission:registros.create'])
        ->name('registros.store');

    Route::put('/registros/{registro}', [RegistroController::class, 'update'])
        ->middleware(['auth', 'permission:registros.update'])
        ->name('registros.update');

    Route::patch('/registros/{id}/restore', [RegistroController::class, 'restore'])
        ->middleware(['auth', 'permission:registros.update'])
        ->name('registros.restore');

    Route::patch('/registros/{id}/toggle-patio', [RegistroController::class, 'togglePatio'])
        ->middleware(['auth', 'permission:registros.update'])
        ->name('registros.togglePatio');

    Route::delete('/registros/{id}/delete', [RegistroController::class, 'forceDelete'])
        ->middleware(['auth', 'permission:registros.delete'])
        ->name('registros.forceDelete');

    Route::delete('/registros/{registro}', [RegistroController::class, 'destroy'])
        ->middleware(['auth', 'permission:registros.delete'])
        ->name('registros.destroy');

    // Por último: a rota curinga SHOW
    Route::get('/registros/{registro}', [RegistroController::class, 'show'])
        ->middleware(['auth', 'permission:registros.view'])
        ->name('registros.show');


    // Route::get('/registros/arquivados', [RegistroController::class, 'trashed'])->name('registros.trashed');
    // Route::patch('/registros/{id}/restore', [RegistroController::class, 'restore'])->name('registros.restore');
    // Route::delete('/registros/{id}/delete', [RegistroController::class, 'forceDelete'])->name('registros.forceDelete');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';



//Route::patch('/registros/{id}/toggle-patio', [RegistroController::class, 'togglePatio']) ->name('registros.togglePatio'); // usa o metodo patch porque é uma atualização parcial
// da um nome pra rota (registros.togglePatio) -> assim voce chama no blade com route('registros.togglePatio', $registros->$id).