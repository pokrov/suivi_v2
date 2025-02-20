<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create'); // Formulaire de création
    Route::post('/users', [UserController::class, 'store'])->name('users.store');         // Traitement du formulaire

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

});

Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    // Gestion des rôles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');      // Liste des rôles
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create'); // Formulaire de création
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');     // Enregistrement
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy'); // Suppression
});