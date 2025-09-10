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
use App\Http\Controllers\ChefAssignmentController;
Auth::routes();

/* ===== REDIRECT RACINE / HOME ===== */
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

/* =========================================================
   SUPER ADMIN
========================================================= */
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

/* =========================================================
   CHEF (DASHBOARD + GRAND PROJETS CPC & CLM + assignments)
========================================================= */
Route::middleware(['auth', 'role:chef'])
    ->prefix('chef')->name('chef.')->group(function () {

        // Tableau de bord & stats
        Route::get('/dashboard', fn () => view('chef.dashboard'))->name('dashboard');
        Route::get('/stats', [StatsController::class, 'index'])->name('stats.index');

        /* ----- assignments ----- */
        // Alias pour compatibilité avec l’ancienne navbar
Route::get('/assignments', [ChefAssignmentController::class, 'index'])
    ->name('assignments.index');

        Route::post('/assignments/{grandProjet}/dajf', [ChefAssignmentController::class, 'assignToDajf'])
            ->name('assignments.assign.dajf');
        Route::post('/assignments/{grandProjet}/dgu', [ChefAssignmentController::class, 'assignToDgu'])
            ->name('assignments.assign.dgu');

        /* ----- GRAND PROJETS ----- */
        Route::prefix('grandprojets')->name('grandprojets.')->group(function () {

            // === CPC ===
            Route::resource('cpc', GrandProjetCPCController::class)
                ->parameters(['cpc' => 'grandProjet']);

            // Examens CPC côté chef
            Route::get ('cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
            Route::post('cpc/{grandProjet}/examens',        [ExamenController::class, 'store'])->name('cpc.examens.store');

            // Changement d'état CPC (optionnel)
            Route::post('cpc/{grandProjet}/etat', [GrandProjetCPCController::class, 'changerEtat'])->name('cpc.changerEtat');

            // Complétion Bureau de Suivi (CPC)
            Route::get ('cpc/{grandProjet}/complete', [GrandProjetCPCController::class, 'completeForm'])->name('cpc.complete.form');
            Route::put ('cpc/{grandProjet}/complete', [GrandProjetCPCController::class, 'completeStore'])->name('cpc.complete.store');

            // === CLM ===
            Route::resource('clm', GrandProjetCLMController::class)
                ->parameters(['clm' => 'grandProjet']);

            // Complétion Bureau de Suivi (CLM)
            Route::get ('clm/{grandProjet}/complete', [GrandProjetCLMController::class, 'completeForm'])->name('clm.complete.form');
            Route::put ('clm/{grandProjet}/complete', [GrandProjetCLMController::class, 'completeStore'])->name('clm.complete.store');
        });
    });


/* =========================================================
   SAISIE CPC (DASHBOARD + CPC + CLM)
========================================================= */
Route::middleware(['auth', 'role:saisie_cpc'])
    ->prefix('saisie_cpc')->name('saisie_cpc.')->group(function () {

        /* Dashboard (liste CPC) */
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

        /* ----- CPC côté saisie ----- */
        Route::get('/cpc/create', [GrandProjetCPCController::class, 'create'])->name('cpc.create');
        Route::post('/cpc',       [GrandProjetCPCController::class, 'store'])->name('cpc.store');
        Route::get('/cpc/{grandProjet}',      [GrandProjetCPCController::class, 'show'])->name('cpc.show');
        Route::get('/cpc/{grandProjet}/edit', [GrandProjetCPCController::class, 'edit'])->name('cpc.edit');
        Route::put('/cpc/{grandProjet}',      [GrandProjetCPCController::class, 'update'])->name('cpc.update');

        Route::get ('/cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
        Route::post('/cpc/{grandProjet}/examens',        [ExamenController::class, 'store'])->name('cpc.examens.store');

        /* ----- CLM côté saisie (si besoin de saisie CLM) ----- */
        Route::get ('/clm/create',            [GrandProjetCLMController::class, 'create'])->name('clm.create');
        Route::post('/clm',                   [GrandProjetCLMController::class, 'store'])->name('clm.store');
        Route::get ('/clm/{grandProjet}',     [GrandProjetCLMController::class, 'show'])->name('clm.show');
        Route::get ('/clm/{grandProjet}/edit',[GrandProjetCLMController::class, 'edit'])->name('clm.edit');
        Route::put ('/clm/{grandProjet}',     [GrandProjetCLMController::class, 'update'])->name('clm.update');
    });

/* =========================================================
   DAJF — CPC + CLM (avec assignation DGU à l’envoi)
========================================================= */
Route::middleware(['auth','role:dajf'])
    ->prefix('dajf')->name('dajf.')->group(function () {

        // Par défaut → CPC à traiter (mes dossiers)
        Route::get('/dashboard', fn() => redirect()->route('dajf.inbox', ['type'=>'cpc','mine'=>1]))->name('dashboard');

        // ===== À TRAITER =====
        Route::get('/inbox', function () {
            $scope   = 'inbox';
            $type    = request('type','cpc');              // 'cpc' | 'clm'
            $mine    = request('mine','1') === '1';        // 1 = mes dossiers
            $authId  = auth()->id();

            $builder = $type === 'clm' ? GrandProjet::clm() : GrandProjet::cpc();
            $q = $builder->whereIn('etat', ['enregistrement','transmis_dajf','recu_dajf']);

            if ($mine) {
                $q->where(function($qq) use ($authId){
                    $qq->whereNull('assigned_dajf_id')
                       ->orWhere('assigned_dajf_id', $authId);
                });
            }

            $items = $q->latest()->paginate(12)->withQueryString();

            $counts = [
                'cpc_inbox'  => GrandProjet::cpc()->whereIn('etat',['enregistrement','transmis_dajf','recu_dajf'])->count(),
                'cpc_outbox' => GrandProjet::cpc()->whereIn('etat',['transmis_dgu'])->count(),
                'clm_inbox'  => GrandProjet::clm()->whereIn('etat',['enregistrement','transmis_dajf','recu_dajf'])->count(),
                'clm_outbox' => GrandProjet::clm()->whereIn('etat',['transmis_dgu'])->count(),
            ];

            // liste des agents DGU pour l’assignation
            $dguUsers = \App\Models\User::role('dgu')->orderBy('name')->get(['id','name']);

            return view('dajf.dashboard', compact('items','scope','type','counts','dguUsers'));
        })->name('inbox');

        // ===== ENVOYÉS =====
        Route::get('/outbox', function () {
            $scope   = 'outbox';
            $type    = request('type','cpc');
            $mine    = request('mine','1') === '1';
            $authId  = auth()->id();

            $builder = $type === 'clm' ? GrandProjet::clm() : GrandProjet::cpc();
            $q = $builder->whereIn('etat', ['transmis_dgu']);

            if ($mine) $q->where('assigned_dajf_id', $authId);

            $items = $q->latest()->paginate(12)->withQueryString();

            $counts = [
                'cpc_inbox'  => GrandProjet::cpc()->whereIn('etat',['enregistrement','transmis_dajf','recu_dajf'])->count(),
                'cpc_outbox' => GrandProjet::cpc()->whereIn('etat',['transmis_dgu'])->count(),
                'clm_inbox'  => GrandProjet::clm()->whereIn('etat',['enregistrement','transmis_dajf','recu_dajf'])->count(),
                'clm_outbox' => GrandProjet::clm()->whereIn('etat',['transmis_dgu'])->count(),
            ];

            $dguUsers = \App\Models\User::role('dgu')->orderBy('name')->get(['id','name']);

            return view('dajf.dashboard', compact('items','scope','type','counts','dguUsers'));
        })->name('outbox');

        /* ===== Transitions CPC ===== */
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            abort_unless($grandProjet->type_projet === 'cpc', 404);

            $request->validate([
                'etat'            => 'required|string|in:recu_dajf,transmis_dgu',
                'note'            => 'nullable|string',
                'assigned_dgu_id' => 'nullable|integer|exists:users,id',
            ]);

            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'enregistrement' => ['recu_dajf'],
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'],
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée.');

            // si transmission vers DGU => assignation obligatoire + rôle DGU
            if ($to === 'transmis_dgu') {
                $assigneeId = $request->integer('assigned_dgu_id');
                abort_unless($assigneeId, 422, 'Veuillez choisir un agent DGU.');
                $assignee = \App\Models\User::where('id', $assigneeId)->role('dgu')->first();
                abort_unless($assignee, 422, 'L’utilisateur choisi n’a pas le rôle DGU.');
            }

            \DB::transaction(function () use ($grandProjet, $from, $to, $request) {
                $payload = ['etat' => $to];

                if ($to === 'transmis_dgu') {
                    $payload['assigned_dgu_id'] = $request->integer('assigned_dgu_id');
                    $payload['assigned_dgu_at'] = now();
                }

                $grandProjet->update(array_merge($payload, [
                    'assigned_dajf_id' => auth()->id(),              // on garde la traçabilité DAJF
                    'assigned_dajf_at' => $grandProjet->assigned_dajf_at ?? now(),
                ]));

                \App\Models\FluxEtape::create([
                    'grand_projet_id' => $grandProjet->id,
                    'from_etat'       => $from,
                    'to_etat'         => $to,
                    'happened_at'     => now(),
                    'by_user'         => auth()->id(),
                    'note'            => $request->note ?? ($to === 'transmis_dgu' ? 'Transmission vers DGU avec assignation.' : null),
                ]);
            });

            return back()->with('success', "DAJF (CPC) : $from → $to");
        })->name('cpc.transition');

        /* ===== Transitions CLM ===== */
        Route::post('/clm/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            abort_unless($grandProjet->type_projet === 'clm', 404);

            $request->validate([
                'etat'            => 'required|string|in:recu_dajf,transmis_dgu',
                'note'            => 'nullable|string',
                'assigned_dgu_id' => 'nullable|integer|exists:users,id',
            ]);

            $from = $grandProjet->etat;
            $to   = $request->etat;

            $allowed = [
                'enregistrement' => ['recu_dajf'],
                'transmis_dajf'  => ['recu_dajf'],
                'recu_dajf'      => ['transmis_dgu'],
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée.');

            if ($to === 'transmis_dgu') {
                $assigneeId = $request->integer('assigned_dgu_id');
                abort_unless($assigneeId, 422, 'Veuillez choisir un agent DGU.');
                $assignee = \App\Models\User::where('id', $assigneeId)->role('dgu')->first();
                abort_unless($assignee, 422, 'L’utilisateur choisi n’a pas le rôle DGU.');
            }

            \DB::transaction(function () use ($grandProjet, $from, $to, $request) {
                $payload = ['etat' => $to];

                if ($to === 'transmis_dgu') {
                    $payload['assigned_dgu_id'] = $request->integer('assigned_dgu_id');
                    $payload['assigned_dgu_at'] = now();
                }

                $grandProjet->update(array_merge($payload, [
                    'assigned_dajf_id' => auth()->id(),
                    'assigned_dajf_at' => $grandProjet->assigned_dajf_at ?? now(),
                ]));

                \App\Models\FluxEtape::create([
                    'grand_projet_id' => $grandProjet->id,
                    'from_etat'       => $from,
                    'to_etat'         => $to,
                    'happened_at'     => now(),
                    'by_user'         => auth()->id(),
                    'note'            => $request->note ?? ($to === 'transmis_dgu' ? 'Transmission vers DGU avec assignation.' : null),
                ]);
            });

            return back()->with('success', "DAJF (CLM) : $from → $to");
        })->name('clm.transition');

        // Formulaire “Compléter” (même vue pour CPC/CLM)
        Route::get('/cpc/{grandProjet}/completer', fn(GrandProjet $grandProjet)
            => view('dajf.completer', compact('grandProjet')))->name('cpc.completer');
        Route::get('/clm/{grandProjet}/completer', fn(GrandProjet $grandProjet)
            => view('dajf.completer', compact('grandProjet')))->name('clm.completer');
    });

/* =========================================================
   DGU — CPC + CLM (seul l’assigné peut traiter)
========================================================= */
Route::middleware(['auth','role:dgu'])
    ->prefix('dgu')->name('dgu.')->group(function () {

        Route::get('/dashboard', fn () => redirect()->route('dgu.inbox', ['type' => 'cpc']))->name('dashboard');

        // À traiter
        // À traiter
Route::get('/inbox', function () {
    $scope   = 'inbox';
    $type    = request('type','cpc');
    $builder = $type === 'clm' ? GrandProjet::clm() : GrandProjet::cpc();

    $uid = auth()->id();
    $isPrivileged = auth()->user()->hasAnyRole(['chef','super_admin']);

    // Etats visibles en inbox DGU
    $q = $builder->whereIn('etat', ['transmis_dgu','recu_dgu']);

    // 🔒 Filtre: si pas "chef/super_admin", ne montrer que les dossiers assignés à CET agent DGU
    if (!$isPrivileged) {
        $q->where('assigned_dgu_id', $uid);
    }

    $items = $q->latest()->paginate(12)->withQueryString();

    $counts = [
        'cpc_inbox'  => GrandProjet::cpc()->whereIn('etat',['transmis_dgu','recu_dgu'])->count(),
        'cpc_outbox' => GrandProjet::cpc()->whereIn('etat',['vers_comm_interne'])->count(),
        'clm_inbox'  => GrandProjet::clm()->whereIn('etat',['transmis_dgu','recu_dgu'])->count(),
        'clm_outbox' => GrandProjet::clm()->whereIn('etat',['vers_comm_interne'])->count(),
    ];

    return view('dgu.dashboard', compact('items','scope','type','counts'));
})->name('inbox');

        // Envoyés
        // Envoyés
Route::get('/outbox', function () {
    $scope   = 'outbox';
    $type    = request('type','cpc');
    $builder = $type === 'clm' ? GrandProjet::clm() : GrandProjet::cpc();

    $uid = auth()->id();
    $isPrivileged = auth()->user()->hasAnyRole(['chef','super_admin']);

    // Etats "envoyés" côté DGU
    $q = $builder->whereIn('etat', ['vers_comm_interne']);

    // 🔒 Même principe: si pas "chef/super_admin", ne montrer que ce qui lui était assigné
    if (!$isPrivileged) {
        $q->where('assigned_dgu_id', $uid);
    }

    $items = $q->latest()->paginate(12)->withQueryString();

    $counts = [
        'cpc_inbox'  => GrandProjet::cpc()->whereIn('etat',['transmis_dgu','recu_dgu'])->count(),
        'cpc_outbox' => GrandProjet::cpc()->whereIn('etat',['vers_comm_interne'])->count(),
        'clm_inbox'  => GrandProjet::clm()->whereIn('etat',['transmis_dgu','recu_dgu'])->count(),
        'clm_outbox' => GrandProjet::clm()->whereIn('etat',['vers_comm_interne'])->count(),
    ];

    return view('dgu.dashboard', compact('items','scope','type','counts'));
})->name('outbox');


        /* ----- Transitions CPC (DGU) ----- */
        Route::post('/cpc/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            abort_unless($grandProjet->type_projet === 'cpc', 404);

            $request->validate([
                'etat' => 'required|string|in:recu_dgu,vers_comm_interne',
                'note' => 'nullable|string',
            ]);

            $from = $grandProjet->etat; 
            $to   = $request->etat;

            $allowed = [
                'transmis_dgu' => ['recu_dgu'],
                'recu_dgu'     => ['vers_comm_interne'],
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DGU (CPC).');

            // 🔒 seul l’agent assigné (ou Chef / Super Admin)
            $uid = auth()->id();
            $isPrivileged = auth()->user()->hasAnyRole(['chef','super_admin']);
            abort_unless($isPrivileged || $grandProjet->assigned_dgu_id === $uid, 403, 'Ce dossier est assigné à un autre agent DGU.');

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

            return back()->with('success', "DGU (CPC) : $from → $to");
        })->name('cpc.transition');

        /* ----- Transitions CLM (DGU) ----- */
        Route::post('/clm/{grandProjet}/transition', function (Request $request, GrandProjet $grandProjet) {
            abort_unless($grandProjet->type_projet === 'clm', 404);

            $request->validate([
                'etat' => 'required|string|in:recu_dgu,vers_comm_interne',
                'note' => 'nullable|string',
            ]);

            $from = $grandProjet->etat; 
            $to   = $request->etat;

            $allowed = [
                'transmis_dgu' => ['recu_dgu'],
                'recu_dgu'     => ['vers_comm_interne'],
            ];
            abort_unless(isset($allowed[$from]) && in_array($to, $allowed[$from], true), 403, 'Transition non autorisée pour DGU (CLM).');

            $uid = auth()->id();
            $isPrivileged = auth()->user()->hasAnyRole(['chef','super_admin']);
            abort_unless($isPrivileged || $grandProjet->assigned_dgu_id === $uid, 403, 'Ce dossier est assigné à un autre agent DGU.');

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

            return back()->with('success', "DGU (CLM) : $from → $to");
        })->name('clm.transition');

        // Formulaire "Compléter" (même vue pour CPC/CLM)
        Route::get('/cpc/{grandProjet}/completer', fn(GrandProjet $grandProjet)
            => view('dgu.completer', compact('grandProjet')))->name('cpc.completer');
        Route::get('/clm/{grandProjet}/completer', fn(GrandProjet $grandProjet)
            => view('dgu.completer', compact('grandProjet')))->name('clm.completer');
    });


/* =========================================================
   COMMISSION INTERNE — CPC + CLM (UX simplifiée)
========================================================= */
Route::middleware(['auth','role:comm_interne'])
    ->prefix('comm')->name('comm.')->group(function () {

        // Dashboard : type=cpc|clm, scope=recevoir|interne|mixte|signature|suivi|tous
        Route::get('/dashboard', function () {
            $type  = request('type','cpc');           // 'cpc' par défaut
            $scope = request('scope','interne');      // 'interne' par défaut

            $map = [
                'recevoir'  => ['vers_comm_interne'],
                'interne'   => ['comm_interne'],
                'mixte'     => ['comm_mixte'],
                'signature' => ['signature_3'],
                'suivi'     => ['retour_bs'],
                'tous'      => ['vers_comm_interne','comm_interne','comm_mixte','signature_3','retour_bs'],
            ];
            $states  = $map[$scope] ?? $map['interne'];
            $builder = $type === 'clm' ? \App\Models\GrandProjet::clm() : \App\Models\GrandProjet::cpc();

            // Query principale
            $q = $builder
                ->with(['examens' => fn($qq) => $qq->orderBy('numero_examen','desc')])
                ->whereIn('etat', $states)
                ->latest();

            // Cas spécial : onglet "mixte" avec sous-filtre facultatif ?mixte=to_sig|to_bs|all
            $mixte = request('mixte', 'all');
            if ($scope === 'mixte' && in_array($mixte, ['to_sig','to_bs'], true)) {
                $items = $q->get()->filter(function($gp) use ($mixte){
                    $lastInterne = $gp->examens->firstWhere('type_examen','interne');
                    $avis = $lastInterne->avis ?? null;     // favorable | defavorable | null
                    return $mixte === 'to_sig' ? $avis === 'favorable'
                         : ($mixte === 'to_bs'  ? $avis === 'defavorable' : true);
                });
                $items = new \Illuminate\Pagination\LengthAwarePaginator(
                    $items->values(), $items->count(), 12, request('page',1),
                    ['path'=>request()->url(), 'query'=>request()->query()]
                );
            } else {
                $items = $q->paginate(12)->withQueryString();
            }

            // Compteurs pour les gros boutons et les onglets
            $counts = [
                'cpc' => [
                    'recevoir'  => \App\Models\GrandProjet::cpc()->whereIn('etat',$map['recevoir'])->count(),
                    'interne'   => \App\Models\GrandProjet::cpc()->whereIn('etat',$map['interne'])->count(),
                    'mixte'     => \App\Models\GrandProjet::cpc()->whereIn('etat',$map['mixte'])->count(),
                    'signature' => \App\Models\GrandProjet::cpc()->whereIn('etat',$map['signature'])->count(),
                    'suivi'     => \App\Models\GrandProjet::cpc()->whereIn('etat',$map['suivi'])->count(),
                ],
                'clm' => [
                    'recevoir'  => \App\Models\GrandProjet::clm()->whereIn('etat',$map['recevoir'])->count(),
                    'interne'   => \App\Models\GrandProjet::clm()->whereIn('etat',$map['interne'])->count(),
                    'mixte'     => \App\Models\GrandProjet::clm()->whereIn('etat',$map['mixte'])->count(),
                    'signature' => \App\Models\GrandProjet::clm()->whereIn('etat',$map['signature'])->count(),
                    'suivi'     => \App\Models\GrandProjet::clm()->whereIn('etat',$map['suivi'])->count(),
                ],
            ];

            return view('comm.dashboard', compact('items','type','scope','counts','mixte'));
        })->name('dashboard');

        /* ===== Examens (avis interne / mixte) ===== */
        // CPC
        Route::get ('/cpc/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('cpc.examens.create');
        Route::post('/cpc/{grandProjet}/examens',        [ExamenController::class, 'store'])->name('cpc.examens.store');
        // CLM
        Route::get ('/clm/{grandProjet}/examens/create', [ExamenController::class, 'create'])->name('clm.examens.create');
        Route::post('/clm/{grandProjet}/examens',        [ExamenController::class, 'store'])->name('clm.examens.store');

        // >>> ALIAS GÉNÉRIQUES (pour les vues existantes) <<<
        // /comm/{type}/{grandProjet}/examens/create  => name: comm.examens.create
        Route::get('/{type}/{grandProjet}/examens/create', function (string $type, GrandProjet $grandProjet) {
            abort_unless(in_array($type, ['cpc','clm'], true), 404);
            // optionnel : vérifier la cohérence type <-> modèle
            abort_unless($grandProjet->type_projet === $type, 404);
            return app(ExamenController::class)->create($grandProjet);
        })->whereIn('type', ['cpc','clm'])->name('examens.create');

        // /comm/{type}/{grandProjet}/examens        => name: comm.examens.store
        Route::post('/{type}/{grandProjet}/examens', function (Request $request, string $type, GrandProjet $grandProjet) {
            abort_unless(in_array($type, ['cpc','clm'], true), 404);
            abort_unless($grandProjet->type_projet === $type, 404);
            return app(ExamenController::class)->store($request, $grandProjet);
        })->whereIn('type', ['cpc','clm'])->name('examens.store');

        /* ===== Transitions / actions ===== */
        // CPC
        Route::post('/cpc/{grandProjet}/recevoir',           [CommissionActionsController::class, 'receive'])->name('cpc.recevoir');
        Route::post('/cpc/{grandProjet}/mixte-to-signature', [CommissionActionsController::class, 'mixteToSignature'])->name('cpc.mixte.toSignature');
        Route::post('/cpc/{grandProjet}/mixte-to-bs',        [CommissionActionsController::class, 'mixteToBs'])->name('cpc.mixte.toBs');
        Route::post('/cpc/{grandProjet}/marquer-signe',      [CommissionActionsController::class, 'markSigned'])->name('cpc.markSigned');
        Route::post('/cpc/{grandProjet}/archiver',           [CommissionActionsController::class, 'archive'])->name('cpc.archive');

        // CLM (mêmes méthodes, penser à vérifier $gp->type_projet === 'clm' dans le contrôleur)
        Route::post('/clm/{grandProjet}/recevoir',           [CommissionActionsController::class, 'receive'])->name('clm.recevoir');
        Route::post('/clm/{grandProjet}/mixte-to-signature', [CommissionActionsController::class, 'mixteToSignature'])->name('clm.mixte.toSignature');
        Route::post('/clm/{grandProjet}/mixte-to-bs',        [CommissionActionsController::class, 'mixteToBs'])->name('clm.mixte.toBs');
        Route::post('/clm/{grandProjet}/marquer-signe',      [CommissionActionsController::class, 'markSigned'])->name('clm.markSigned');
        Route::post('/clm/{grandProjet}/archiver',           [CommissionActionsController::class, 'archive'])->name('clm.archive');
    });

/* =========================================================
   FICHES PARTAGÉES (Lecture seule multi-rôles)
========================================================= */
Route::middleware(['auth','role:chef|saisie_cpc|dajf|dgu|comm_interne|super_admin'])
    ->group(function () {
        Route::get('/projets/cpc/{grandProjet}', [GrandProjetCPCController::class, 'show'])->name('cpc.show.shared');
        Route::get('/projets/clm/{grandProjet}', [GrandProjetCLMController::class, 'show'])->name('clm.show.shared');
    });
