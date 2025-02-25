<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GrandProjetCPCController;
use App\Http\Controllers\GrandProjetCLMController;

Auth::routes();

// 🌐 Redirection de la racine vers la page de connexion
Route::get('/', fn() => redirect()->route('login'));

// 🏠 Redirection après connexion selon le rôle de l'utilisateur
Route::get('/home', function () {
    if (Auth::check()) {
        return match(true) {
            Auth::user()->hasRole('super_admin') => redirect()->route('superadmin.dashboard'),
            Auth::user()->hasRole('chef') => redirect()->route('chef.dashboard'),
            Auth::user()->hasRole('saisie_petit') => redirect()->route('saisie.dashboard'),
            default => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
})->name('home');

/* -----------------------------------
  🛡️ Routes Super Administrateur
----------------------------------- */
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

/* -----------------------------------
  📊 Routes pour le Chef
----------------------------------- */
/* -----------------------------------
  📊 Routes pour le Chef
----------------------------------- */
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef')
    ->name('chef.')
    ->group(function () {
        // Dashboard du chef
        Route::get('/dashboard', fn() => view('chef.dashboard'))->name('dashboard');

        // Suppress or remove this block if you want custom parameters:
        // Route::prefix('grandprojets')->name('grandprojets.')->group(function () {
        //     Route::resource('cpc', GrandProjetCPCController::class);
        //     Route::resource('clm', GrandProjetCLMController::class);
        // });

        Route::get('/statistiques', fn() => view('chef.statistiques'))->name('statistiques');
    });


/* -----------------------------------
  ✅ Keep only this block for custom parameter {grandProjet}
----------------------------------- */
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef/grandprojets')
    ->name('chef.grandprojets.')
    ->group(function () {
        Route::resource('cpc', GrandProjetCPCController::class)->parameters([
            'cpc' => 'grandProjet'
        ]);
        Route::resource('clm', GrandProjetCLMController::class);
    });
