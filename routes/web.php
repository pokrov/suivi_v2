<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\GrandProjet;
use Illuminate\Http\Request;

use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\GrandProjetCPCController;
use App\Http\Controllers\GrandProjetCLMController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\StatsController;
// (optionnel si tu ajoutes le contrôleur dédié aux transitions)
// use App\Http\Controllers\TransitionController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Auth::routes();

/*
|--------------------------------------------------------------------------
| Root -> Home (pas de boucle)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('home'));

/*
|--------------------------------------------------------------------------
| /home : dispatch par rôle (NE JAMAIS renvoyer un user connecté vers /login)
|--------------------------------------------------------------------------
*/
Route::get('/home', function () {
    if (!Auth::check()) return redirect()->route('login');

    $u = Auth::user();

    if ($u->hasRole('super_admin'))   return redirect()->route('superadmin.dashboard');
    if ($u->hasRole('chef'))          return redirect()->route('chef.dashboard');
    if ($u->hasRole('saisie_cpc'))    return redirect()->route('saisie_cpc.dashboard');
    if ($u->hasRole('dajf'))          return redirect()->route('dajf.dashboard');
    if ($u->hasRole('dgu'))           return redirect()->route('dgu.dashboard');
    if ($u->hasRole('comm_interne'))  return redirect()->route('comm.dashboard');

    // Cas sans rôle reconnu : page STABLE
    return redirect()->route('no.role');
})->name('home');

/*
|--------------------------------------------------------------------------
| Déconnexion forcée (utile en dev pour revenir au login)
|--------------------------------------------------------------------------
*/
Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('force.logout');

/*
|--------------------------------------------------------------------------
| Route STABLE "no.role"
|--------------------------------------------------------------------------
*/
Route::get('/no-role', function () {
    $logoutUrl = route('force.logout');
    return response()->make(
        '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Accès non configuré</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"></head>
        <body style="font-family:Nunito,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Cantarell;">
        <div style="max-width:720px;margin:48px auto;padding:24px;border:1px solid #eee;border-radius:12px">
            <h2>Accès non configuré</h2>
            <p>Votre compte est connecté mais aucun rôle applicatif n’est associé.<br>
            Demandez à un <strong>super administrateur</strong> de vous attribuer un rôle (ex. chef, saisie_cpc, dajf, dgu, comm_interne).</p>
            <p style="margin-top:16px;"><a href="' . $logoutUrl . '">Se déconnecter</a></p>
        </div></body></html>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
})->name('no.role')->middleware('auth');

/*
|--------------------------------------------------------------------------
| SUPER ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', UserController::class);
        Route::resource('maitres-oeuvre', \App\Http\Controllers\MaitreOeuvreController::class);


        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',  [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

/*
|--------------------------------------------------------------------------
| CHEF : dashboard + stats
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef')
    ->name('chef.')
    ->group(function () {
        Route::get('/dashboard', fn () => view('chef.dashboard'))->name('dashboard');
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
    });

/*
|--------------------------------------------------------------------------
| GRAND PROJETS (CHEF) + Examens + Navette
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef/grandprojets')
    ->name('chef.grandprojets.')
    ->group(function () {
        // Ressource CPC (paramètre {grandProjet})
        Route::resource('cpc', GrandProjetCPCController::class)->parameters([
            'cpc' => 'grandProjet',
        ]);

        // Stats (compat)
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

        // Ressource CLM
        Route::resource('clm', GrandProjetCLMController::class);

        // --- EXAMENS (Chef) ---
        Route::get('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])
            ->name('cpc.examens.create');
        Route::post('cpc/{grandProjet}/examens', [ExamenController::class, 'store'])
            ->name('cpc.examens.store');

        // --- NAVETTE / CHANGEMENT D’ÉTAT (Chef) ---
        Route::post('cpc/{grandProjet}/etat', [GrandProjetCPCController::class, 'changerEtat'])
            ->name('cpc.changerEtat');
    });

/*
|--------------------------------------------------------------------------
| SAISIE_CPC
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:saisie_cpc'])
    ->prefix('saisie_cpc')
    ->name('saisie_cpc.')
    ->group(function () {

        // Dashboard (liste CPC + filtres)
        Route::get('/dashboard', function () {
            $search   = request('search');
            $dateFrom = request('date_from');
            $dateTo   = request('date_to');

            $query = \App\Models\GrandProjet::where('type_projet', 'cpc');

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

            if ($dateFrom && $dateTo) {
                $query->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                $query->where('date_arrivee', '>=', $dateFrom);
            } elseif ($dateTo) {
                $query->where('date_arrivee', '<=', $dateTo);
            }

            $grandProjets = $query->latest()->paginate(10);

            return view('saisie_cpc.dashboard', compact('grandProjets'));
        })->name('dashboard');

        // CRUD partiel CPC
        Route::get('/cpc/create', [GrandProjetCPCController::class, 'create'])->name('cpc.create');
        Route::post('/cpc',       [GrandProjetCPCController::class, 'store'])->name('cpc.store');
        Route::get('/cpc/{grandProjet}',        [GrandProjetCPCController::class, 'show'])->name('cpc.show');
        Route::get('/cpc/{grandProjet}/edit',   [GrandProjetCPCController::class, 'edit'])->name('cpc.edit');
        Route::put('/cpc/{grandProjet}',        [GrandProjetCPCController::class, 'update'])->name('cpc.update');

        // Examens (si autorisé pour saisie_cpc)
        Route::get('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])
            ->name('cpc.examens.create');
        Route::post('cpc/{grandProjet}/examens', [ExamenController::class, 'store'])
            ->name('cpc.examens.store');
    });

/*
|--------------------------------------------------------------------------
| DAJF / DGU / COMMISSION INTERNE — dashboards + transitions
|--------------------------------------------------------------------------
|
| Les dashboards ci‑dessous passent une variable $items aux vues.
| Les formulaires d'action dans les vues POSTent vers les routes .../transition
| (tu peux les traiter soit via un TransitionController, soit via une closure).
|
*/
Route::middleware(['auth','role:dajf'])
    ->prefix('dajf')->name('dajf.')
    ->group(function () {
        // Redirige /dajf/dashboard -> inbox
        Route::get('/dashboard', fn() => redirect()->route('dajf.inbox'))->name('dashboard');

        // À TRAITER (voir aussi les dossiers en 'enregistrement')
        Route::get('/inbox', function () {
            $items = GrandProjet::where('type_projet','cpc')
                ->whereIn('etat', ['enregistrement','transmis_dajf','recu_dajf'])
                ->latest()->paginate(12);
            $scope = 'inbox';
            return view('dajf.dashboard', compact('items','scope'));
        })->name('inbox');

        // ENVOYÉS (depuis DAJF)
        Route::get('/outbox', function () {
            $items = GrandProjet::where('type_projet','cpc')
                ->whereIn('etat', ['transmis_dgu']) // dossiers quittent DAJF vers DGU
                ->latest()->paginate(12);
            $scope = 'outbox';
            return view('dajf.dashboard', compact('items','scope'));
        })->name('outbox');

        // Transition DAJF
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            $request->validate(['etat' => 'required|string', 'note' => 'nullable|string']);
            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'enregistrement' => ['recu_dajf'], // prendre en charge
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'], // envoyer DGU
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DAJF.');
            $grandProjet->update(['etat' => $to]);
            return back()->with('success', "DAJF : $from → $to");
        })->name('transition');

        // Compléter (placeholder)
        Route::get('/cpc/{grandProjet}/completer', function (GrandProjet $grandProjet) {
            return view('dajf.completer', compact('grandProjet'));
        })->name('cpc.completer');
    });

/* ===================== DGU ===================== */
Route::middleware(['auth','role:dgu'])
    ->prefix('dgu')->name('dgu.')
    ->group(function () {
        // Redirige /dgu/dashboard -> inbox
        Route::get('/dashboard', fn() => redirect()->route('dgu.inbox'))->name('dashboard');

        // À TRAITER
        Route::get('/inbox', function () {
            $items = GrandProjet::where('type_projet','cpc')
                ->whereIn('etat', ['transmis_dgu','recu_dgu'])
                ->latest()->paginate(12);
            $scope = 'inbox';
            return view('dgu.dashboard', compact('items','scope'));
        })->name('inbox');

        // ENVOYÉS (vers Commission)
        Route::get('/outbox', function () {
            $items = GrandProjet::where('type_projet','cpc')
                ->whereIn('etat', ['comm_interne']) // dossiers partis à la commission
                ->latest()->paginate(12);
            $scope = 'outbox';
            return view('dgu.dashboard', compact('items','scope'));
        })->name('outbox');

        // Transition DGU
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            $request->validate(['etat' => 'required|string', 'note' => 'nullable|string']);
            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'transmis_dgu'  => ['recu_dgu'],
                'recu_dgu'      => ['comm_interne'], // envoyer Commission interne
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DGU.');
            $grandProjet->update(['etat' => $to]);
            return back()->with('success', "DGU : $from → $to");
        })->name('transition');

        // Compléter (placeholder)
        Route::get('/cpc/{grandProjet}/completer', function (GrandProjet $grandProjet) {
            return view('dgu.completer', compact('grandProjet'));
        })->name('cpc.completer');
    });

Route::middleware(['auth','role:comm_interne'])
    ->prefix('comm')->name('comm.')
    ->group(function () {
        // Liste Commission
        Route::get('/dashboard', function () {
            $items = \App\Models\GrandProjet::where('type_projet','cpc')
                ->where('etat', 'comm_interne')
                ->latest()->paginate(12);
            return view('comm.dashboard', compact('items'));
        })->name('dashboard');

        // Commission rend l'avis via ExamenController (crée Examen + choisit redirection)
        Route::get('/cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('examens.create');
        Route::post('/cpc/{grandProjet}/examens',       [ExamenController::class, 'store'])->name('examens.store');
    });


    // Lecture seule de la fiche CPC pour tous les rôles concernés
Route::middleware(['auth','role:chef|saisie_cpc|dajf|dgu|comm_interne'])
    ->get('/cpc/{grandProjet}', [App\Http\Controllers\GrandProjetCPCController::class, 'show'])
    ->name('cpc.show.any');

