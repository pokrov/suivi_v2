<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GrandProjetCPCController;
use App\Http\Controllers\GrandProjetCLMController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Redirect root (/) to login
|--------------------------------------------------------------------------
*/
Route::get('/', fn() => redirect()->route('login'));

/*
|--------------------------------------------------------------------------
| Redirect /home based on user role
|--------------------------------------------------------------------------
*/
Route::get('/home', function () {
    if (Auth::check()) {
        return match (true) {
            Auth::user()->hasRole('super_admin') => redirect()->route('superadmin.dashboard'),
            Auth::user()->hasRole('chef')        => redirect()->route('chef.dashboard'),
            Auth::user()->hasRole('saisie_cpc')  => redirect()->route('saisie_cpc.dashboard'),
            default                              => redirect()->route('login'),
        };
    }
    return redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| SUPER ADMIN ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('users', UserController::class);

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| CHEF ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef')
    ->name('chef.')
    ->group(function () {

        // Chef dashboard
        Route::get('/dashboard', fn() => view('chef.dashboard'))->name('dashboard');

        // Statistiques page
        Route::get('/statistiques', fn() => view('chef.statistiques'))->name('statistiques');
    });

/*
|--------------------------------------------------------------------------
| GRAND PROJETS (CHEF) - With Custom Parameter {grandProjet}
|--------------------------------------------------------------------------
|
| This block gives the Chef user full resource routes for CPC and CLM.
| The 'parameters' method ensures the URL uses /cpc/{grandProjet} instead of /cpc/{cpc}.
|
*/
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef/grandprojets')
    ->name('chef.grandprojets.')
    ->group(function () {

        // Full resource for CPC
        Route::resource('cpc', GrandProjetCPCController::class)->parameters([
            'cpc' => 'grandProjet'
        ]);

        // Full resource for CLM
        Route::resource('clm', GrandProjetCLMController::class);
    });

/*
|--------------------------------------------------------------------------
| SAISIE_CPC ROUTES
|--------------------------------------------------------------------------
|
| The role 'saisie_cpc' can see a dashboard listing CPC projects and has partial routes
| to create or show a CPC project. No Edit/Update/Destroy routes are defined here.
|
*/
Route::middleware(['auth', 'role:saisie_cpc'])
    ->prefix('saisie_cpc')
    ->name('saisie_cpc.')
    ->group(function () {

        // Dashboard listing only CPC
        Route::get('/dashboard', function () {
            $grandProjets = \App\Models\GrandProjet::where('categorie_projet', 'CPC')
                ->latest()
                ->paginate(10);

            return view('saisie_cpc.dashboard', compact('grandProjets'));
        })->name('dashboard');

        // Create form
        Route::get('/cpc/create', [GrandProjetCPCController::class, 'create'])
            ->name('cpc.create');

        // Store action
        Route::post('/cpc', [GrandProjetCPCController::class, 'store'])
            ->name('cpc.store');

        // Show detail
        Route::get('/cpc/{grandProjet}', [GrandProjetCPCController::class, 'show'])
            ->name('cpc.show');
    });
