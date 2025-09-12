<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistroController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registros/arquivados', [RegistroController::class, 'trashed'])->name('registros.trashed');
Route::patch('/registros/{id}/restore', [RegistroController::class, 'restore'])->name('registros.restore');
Route::delete('/registros/{id}/delete', [RegistroController::class, 'forceDelete'])->name('registros.forceDelete');

Route::resource('registros', RegistroController::class);
// Route::get('/registros/{registro}', [RegistroController::class, 'show'])->name('registros.show');
// Route::put('/registros/{registro}', [RegistroController::class, 'update'])->name('registros.update');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
