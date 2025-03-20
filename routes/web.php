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
// ... Dans le bloc "SAISIE_CPC ROUTES" ...
Route::middleware(['auth', 'role:saisie_cpc'])
    ->prefix('saisie_cpc')
    ->name('saisie_cpc.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', function () {
            $search = request('search');
            $dateFrom = request('date_from');
            $dateTo = request('date_to');
        
            $query = \App\Models\GrandProjet::where('type_projet', 'cpc');
        
            // Recherche globale sur plusieurs champs si besoin
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero_dossier', 'LIKE', "%{$search}%")
                      ->orWhere('intitule_projet', 'LIKE', "%{$search}%")
                      ->orWhere('commune_1', 'LIKE', "%{$search}%")
                      ->orWhere('commune_2', 'LIKE', "%{$search}%")
                      ->orWhere('etat', 'LIKE', "%{$search}%")
                      ->orWhere('petitionnaire', 'LIKE', "%{$search}%")
                      ->orWhere('maitre_oeuvre', 'LIKE', "%{$search}%")
                      ->orWhere('categorie_projet', 'LIKE', "%{$search}%")
                      ->orWhere('categorie_petitionnaire', 'LIKE', "%{$search}%")
                      ->orWhere('situation', 'LIKE', "%{$search}%")
                      ->orWhere('observations', 'LIKE', "%{$search}%");
                });
            }
        
            // Intervalle de dates
            if ($dateFrom && $dateTo) {
                // Cherche les projets dont date_arrivee est entre dateFrom et dateTo
                $query->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                // date_arrivee >= dateFrom
                $query->where('date_arrivee', '>=', $dateFrom);
            } elseif ($dateTo) {
                // date_arrivee <= dateTo
                $query->where('date_arrivee', '<=', $dateTo);
            }
        
            // Pagination
            $grandProjets = $query->latest()->paginate(10);
        
            return view('saisie_cpc.dashboard', compact('grandProjets'));
        })->name('dashboard');
        
        
        
        

        // Create + Store
        Route::get('/cpc/create', [GrandProjetCPCController::class, 'create'])->name('cpc.create');
        Route::post('/cpc', [GrandProjetCPCController::class, 'store'])->name('cpc.store');

        // Show
        Route::get('/cpc/{grandProjet}', [GrandProjetCPCController::class, 'show'])->name('cpc.show');

        // *** NOUVELLES ROUTES ***
        // Edit
        Route::get('/cpc/{grandProjet}/edit', [GrandProjetCPCController::class, 'edit'])
            ->name('cpc.edit');

        // Update
        Route::put('/cpc/{grandProjet}', [GrandProjetCPCController::class, 'update'])
            ->name('cpc.update');

        // (Pas de destroy si tu ne veux pas la suppression)
    });

