<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\GrandProjet;

use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MaitreOeuvreController;
use App\Http\Controllers\GrandProjetCPCController;
use App\Http\Controllers\GrandProjetCLMController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\CommissionActionsController;

use App\Http\Controllers\StatsController;

Auth::routes();

Route::get('/', fn () => redirect()->route('home'));

Route::get('/home', function () {
    if (!Auth::check()) return redirect()->route('login');
    $u = Auth::user();
    if     ($u->hasRole('super_admin'))   return redirect()->route('superadmin.dashboard');
    elseif ($u->hasRole('chef'))          return redirect()->route('chef.dashboard');
    elseif ($u->hasRole('saisie_cpc'))    return redirect()->route('saisie_cpc.dashboard');
    elseif ($u->hasRole('dajf'))          return redirect()->route('dajf.dashboard');
    elseif ($u->hasRole('dgu'))           return redirect()->route('dgu.dashboard');
    elseif ($u->hasRole('comm_interne'))  return redirect()->route('comm.dashboard');
    return redirect()->route('no.role');
})->name('home');

Route::get('/force-logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('force.logout');

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
            Demandez à un <strong>super administrateur</strong> de vous attribuer un rôle (chef, saisie_cpc, dajf, dgu, comm_interne).</p>
            <p style="margin-top:16px;"><a href="' . $logoutUrl . '">Se déconnecter</a></p>
        </div></body></html>',
        200,
        ['Content-Type' => 'text/html; charset=UTF-8']
    );
})->name('no.role')->middleware('auth');

/* ===== SUPER ADMIN ===== */
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::resource('maitres-oeuvre', MaitreOeuvreController::class);
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/',  [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });
    });

/* ===== CHEF ===== */
/* ===== GRAND PROJETS (CHEF) ===== */
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef')->name('chef.')->group(function () {
        Route::get('/dashboard', fn () => view('chef.dashboard'))->name('dashboard');
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

        Route::prefix('grandprojets')->name('grandprojets.')->group(function () {
            Route::resource('cpc', GrandProjetCPCController::class)->parameters(['cpc' => 'grandProjet']);
            Route::get('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
            Route::post('cpc/{grandProjet}/examens', [ExamenController::class, 'store'])->name('cpc.examens.store');
            Route::post('cpc/{grandProjet}/etat', [GrandProjetCPCController::class, 'changerEtat'])->name('cpc.changerEtat');

            // === Complétion Bureau de suivi (côté CHEF) ===
            Route::get ('cpc/{grandProjet}/complete', [GrandProjetCPCController::class, 'completeForm'])->name('cpc.complete.form');
            Route::put ('cpc/{grandProjet}/complete', [GrandProjetCPCController::class, 'completeStore'])->name('cpc.complete.store');
        });
    });


/* ===== GRAND PROJETS (CHEF) ===== */
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef/grandprojets')->name('chef.grandprojets.')->group(function () {
        Route::resource('cpc', GrandProjetCPCController::class)->parameters(['cpc' => 'grandProjet']);
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');
        Route::resource('clm', GrandProjetCLMController::class);
        Route::get('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
        Route::post('cpc/{grandProjet}/examens', [ExamenController::class, 'store'])->name('cpc.examens.store');
        Route::post('cpc/{grandProjet}/etat', [GrandProjetCPCController::class, 'changerEtat'])->name('cpc.changerEtat');
    });

/* ===== SAISIE CPC ===== */
Route::middleware(['auth', 'role:saisie_cpc'])
    ->prefix('saisie_cpc')->name('saisie_cpc.')->group(function () {
        Route::get('/dashboard', function () {
            $search   = request('search');
            $dateFrom = request('date_from');
            $dateTo   = request('date_to');

            $q = GrandProjet::where('type_projet','cpc');
            if ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('numero_dossier','like',"%$search%")
                       ->orWhere('intitule_projet','like',"%$search%")
                       ->orWhere('commune_1','like',"%$search%")
                       ->orWhere('commune_2','like',"%$search%")
                       ->orWhere('etat','like',"%$search%")
                       ->orWhere('petitionnaire','like',"%$search%")
                       ->orWhere('maitre_oeuvre','like',"%$search%")
                       ->orWhere('categorie_projet','like',"%$search%")
                       ->orWhere('categorie_petitionnaire','like',"%$search%")
                       ->orWhere('situation','like',"%$search%")
                       ->orWhere('observations','like',"%$search%");
                });
            }
            if ($dateFrom && $dateTo)      $q->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
            elseif ($dateFrom)             $q->where('date_arrivee', '>=', $dateFrom);
            elseif ($dateTo)               $q->where('date_arrivee', '<=', $dateTo);

            $grandProjets = $q->latest()->paginate(10);
            return view('saisie_cpc.dashboard', compact('grandProjets'));
        })->name('dashboard');

        Route::get('/cpc/create', [GrandProjetCPCController::class, 'create'])->name('cpc.create');
        Route::post('/cpc',       [GrandProjetCPCController::class, 'store'])->name('cpc.store');
        Route::get('/cpc/{grandProjet}',      [GrandProjetCPCController::class, 'show'])->name('cpc.show');
        Route::get('/cpc/{grandProjet}/edit', [GrandProjetCPCController::class, 'edit'])->name('cpc.edit');
        Route::put('/cpc/{grandProjet}',      [GrandProjetCPCController::class, 'update'])->name('cpc.update');

        Route::get('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
        Route::post('cpc/{grandProjet}/examens',       [ExamenController::class, 'store'])->name('cpc.examens.store');
    });

/* ===== DAJF ===== */
Route::middleware(['auth','role:dajf'])
    ->prefix('dajf')->name('dajf.')->group(function () {

        Route::get('/dashboard', fn() => redirect()->route('dajf.inbox'))->name('dashboard');

        Route::get('/inbox', function () {
            $items = GrandProjet::cpc()
                ->whereIn('etat', ['enregistrement','transmis_dajf','recu_dajf'])
                ->latest()->paginate(12);
            $scope = 'inbox';
            return view('dajf.dashboard', compact('items','scope'));
        })->name('inbox');

        Route::get('/outbox', function () {
            $items = GrandProjet::cpc()
                ->whereIn('etat', ['transmis_dgu'])
                ->latest()->paginate(12);
            $scope = 'outbox';
            return view('dajf.dashboard', compact('items','scope'));
        })->name('outbox');

        // >>> Journalisation ICI <<<
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            $request->validate(['etat' => 'required|string', 'note' => 'nullable|string']);
            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'enregistrement' => ['recu_dajf'],
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'],
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DAJF.');

            \DB::transaction(function () use ($grandProjet, $from, $to, $request) {
                $grandProjet->update(['etat' => $to]);
                \App\Models\FluxEtape::create([
                    'grand_projet_id' => $grandProjet->id,
                    'from_etat'       => $from,
                    'to_etat'         => $to,
                    'happened_at'     => now(),
                    'by_user'         => auth()->id(),
                    'note'            => $request->note,
                ]);
            });

            return back()->with('success', "DAJF : $from → $to");
        })->name('transition');

        Route::get('/cpc/{grandProjet}/completer', function (GrandProjet $grandProjet) {
            return view('dajf.completer', compact('grandProjet'));
        })->name('cpc.completer');
    });

/* ===== DGU ===== */
Route::middleware(['auth','role:dgu'])
    ->prefix('dgu')->name('dgu.')->group(function () {

        Route::get('/dashboard', fn() => redirect()->route('dgu.inbox'))->name('dashboard');

        Route::get('/inbox', function () {
            $items = GrandProjet::cpc()
                ->whereIn('etat', ['transmis_dgu','recu_dgu'])
                ->latest()->paginate(12);
            $scope = 'inbox';
            return view('dgu.dashboard', compact('items','scope'));
        })->name('inbox');

        Route::get('/outbox', function () {
            $items = GrandProjet::cpc()
                ->whereIn('etat', ['vers_comm_interne']) // <-- modifié (suivi : ce qui a été transmis vers la commission)
                ->latest()->paginate(12);
            $scope = 'outbox';
            return view('dgu.dashboard', compact('items','scope'));
        })->name('outbox');

        // >>> Journalisation ICI <<<
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            $request->validate(['etat' => 'required|string', 'note' => 'nullable|string']);
            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'transmis_dgu'      => ['recu_dgu'],
                'recu_dgu'          => ['vers_comm_interne'], // <-- modifié
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DGU.');

            \DB::transaction(function () use ($grandProjet, $from, $to, $request) {
                $grandProjet->update(['etat' => $to]);
                \App\Models\FluxEtape::create([
                    'grand_projet_id' => $grandProjet->id,
                    'from_etat'       => $from,
                    'to_etat'         => $to,
                    'happened_at'     => now(),
                    'by_user'         => auth()->id(),
                    'note'            => $request->note,
                ]);
            });

            return back()->with('success', "DGU : $from → $to");
        })->name('transition');

        Route::get('/cpc/{grandProjet}/completer', function (GrandProjet $grandProjet) {
            return view('dgu.completer', compact('grandProjet'));
        })->name('cpc.completer');
    });

/* ===== COMMISSION INTERNE ===== */
Route::middleware(['auth','role:comm_interne'])
    ->prefix('comm')->name('comm.')->group(function () {

        // Dashboard avec scopes (onglets) + sous-filtre pour la mixte
        Route::get('/dashboard', function () {
            $scope = request('scope', 'interne');

            // Mapping onglet -> états
            $map = [
                'recevoir'  => ['vers_comm_interne'],                                 // À recevoir
                'interne'   => ['comm_interne'],                                      // Commission interne
                'mixte'     => ['comm_mixte'],                                        // Commission mixte (avec sous-filtre interne favorable/défavorable)
                'signature' => ['signature_3'],                                       // 3ème signature
                'suivi'     => ['retour_bs'],                                         // Bureau de suivi
                'tous'      => ['vers_comm_interne','comm_interne','comm_mixte','signature_3','retour_bs'],
            ];
            $states = $map[$scope] ?? $map['interne'];

            // Charger examens pour lire l'avis interne
            $q = \App\Models\GrandProjet::cpc()
                ->with(['examens' => function($qq){ $qq->orderBy('numero_examen','desc'); }])
                ->whereIn('etat', $states)
                ->latest();

            // Sous-filtre spécifique à "mixte" : ?mixte=to_sig | to_bs | all
            $mixte = request('mixte', 'all');
            if ($scope === 'mixte' && in_array($mixte, ['to_sig','to_bs'], true)) {
                $items = $q->get()->filter(function($gp) use ($mixte){
                    $lastInterne = $gp->examens->firstWhere('type_examen','interne');
                    $avis = $lastInterne->avis ?? null; // 'favorable' | 'defavorable' | null
                    if ($mixte === 'to_sig') return $avis === 'favorable';
                    if ($mixte === 'to_bs')  return $avis === 'defavorable';
                    return true;
                });
                // Paginer "manuellement" après filtre (simple)
                $items = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items->values(),
                    $items->count(),
                    12,
                    request('page',1),
                    ['path' => request()->url(), 'query' => request()->query()]
                );
            } else {
                $items = $q->paginate(12);
            }

            return view('comm.dashboard', compact('items','scope','mixte'));
        })->name('dashboard');

        // Examens (avis interne / mixte)
        Route::get('/cpc/{grandProjet}/examens/create', [\App\Http\Controllers\ExamenController::class, 'create'])->name('examens.create');
        Route::post('/cpc/{grandProjet}/examens',       [\App\Http\Controllers\ExamenController::class, 'store'])->name('examens.store');

        // Recevoir (vers_comm_interne -> comm_interne)
        Route::post('/cpc/{grandProjet}/recevoir', [\App\Http\Controllers\CommissionActionsController::class, 'receive'])->name('recevoir');

        // MIXTE : envoyer selon l'avis interne
        Route::post('/cpc/{grandProjet}/mixte-to-signature', [\App\Http\Controllers\CommissionActionsController::class, 'mixteToSignature'])->name('mixte.toSignature'); // comm_mixte -> signature_3
        Route::post('/cpc/{grandProjet}/mixte-to-bs',        [\App\Http\Controllers\CommissionActionsController::class, 'mixteToBs'])->name('mixte.toBs');           // comm_mixte -> retour_bs

        // 3e signature -> retour_bs
        Route::post('/cpc/{grandProjet}/marquer-signe', [\App\Http\Controllers\CommissionActionsController::class, 'markSigned'])->name('markSigned');

        // Bureau de suivi -> archive
        Route::post('/cpc/{grandProjet}/archiver', [\App\Http\Controllers\CommissionActionsController::class, 'archive'])->name('archive');
    });

/* ===== FICHE PARTAGÉE (lecture) ===== */
Route::middleware(['auth','role:chef|saisie_cpc|dajf|dgu|comm_interne|super_admin'])
    ->get('/projets/cpc/{grandProjet}', [GrandProjetCPCController::class, 'show'])
    ->name('cpc.show.shared');
