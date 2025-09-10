<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\MaitreOeuvre;
use App\Models\FluxEtape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrandProjetCPCController extends Controller
{
    public function index()
{
    $search   = request('search');
    $dateFrom = request('date_from');
    $dateTo   = request('date_to');
    $province = request('province');
    $etat     = request('etat'); // ⬅️ new

    $query = GrandProjet::where('type_projet', 'cpc')->with('user');

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('numero_dossier', 'LIKE', "%{$search}%")
              ->orWhere('numero_arrivee', 'LIKE', "%{$search}%")
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

    if ($dateFrom && $dateTo)      $query->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
    elseif ($dateFrom)             $query->where('date_arrivee', '>=', $dateFrom);
    elseif ($dateTo)               $query->where('date_arrivee', '<=', $dateTo);

    if ($province)                 $query->where('province', $province);

    if ($etat)                     $query->where('etat', $etat); // ⬅️ new (ou ->etat($etat))

    $grandProjets = $query->latest()->paginate(10)->appends(request()->query());

    // pour remplir la liste déroulante côté vue
    $etatsOptions = [
        'transmis_dajf'     => 'Vers DAJF',
        'recu_dajf'         => 'DAJF',
        'transmis_dgu'      => 'Vers DGU',
        'recu_dgu'          => 'DGU',
        'vers_comm_interne' => 'Vers Comm. Interne',
        'comm_interne'      => 'Comm. Interne',
        'comm_mixte'        => 'Comm. Mixte',
        'signature_3'       => '3ᵉ signature',
        'retour_dgu'        => 'Retour DGU',
        'retour_bs'         => 'Bureau de suivi',
        'archive'           => 'Archivé',
    ];

    return view('grandprojets.cpc.index', compact('grandProjets','etatsOptions'));
}


    public function create()
    {
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get();
        return view('grandprojets.cpc.create', compact('maitresOeuvre'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
        'numero_dossier'          => ['required', 'regex:/^\d+\/\d{2}$/'], // <= ICI
        'numero_arrivee'          => ['nullable', 'string', 'max:50'],
        'province'                => 'required|string',
        'commune_1'               => 'required|string',
        'date_arrivee'            => 'required|date',
        'petitionnaire'           => 'required|string',
        'categorie_petitionnaire' => 'required|string',
        'intitule_projet'         => 'required|string',
        'categorie_projet'        => 'required|string|in:Commerce,Culte,Equipement de proximité,équipement public,équipement privé,Immeuble,projet agricole,Projet Industriel,Projet touristique,R+1,R+2,RDC,Services,Villa,Autre',
        'contexte_projet'         => 'required|string',
        'maitre_oeuvre'           => 'required|string',
        'situation'               => 'required|string',
        'reference_fonciere'      => 'required|string',
        'reference_envoi'         => 'nullable|string',
        'numero_envoi'            => 'nullable|string',
        'lien_ged'                => 'nullable|url',
        'observations'            => 'nullable|string',
        'proprietaire'            => 'nullable|string',
        'date_commission_mixte'   => 'nullable|date',
    ]);

        $validated['type_envoi']  = $request->has('envoi_papier') ? 'papier' : 'email';
        $validated['type_projet'] = 'cpc';
        $validated['user_id']     = Auth::id();

        // ✅ Etat initial demandé : transmis_dajf
        $validated['etat']        = 'enregistrement';

        $gp = GrandProjet::create($validated);

        // Journal initial
        FluxEtape::create([
            'grand_projet_id' => $gp->id,
            'from_etat'       => '—',
            'to_etat'         => 'transmis_dajf',
            'happened_at'     => $gp->created_at,
            'by_user'         => auth()->id(),
            'note'            => 'Création (transmis à la DAJF)',
        ]);

        if (Auth::user()->hasRole('chef')) {
            return redirect()->route('chef.grandprojets.cpc.index')->with('success', 'Projet CPC enregistré.');
        } elseif (Auth::user()->hasRole('saisie_cpc')) {
            return redirect()->route('saisie_cpc.dashboard')->with('success', 'Projet CPC enregistré.');
        }
        return redirect()->route('login');
    }

    public function show(GrandProjet $grandProjet)
    {
        $grandProjet->load([
            'user',
            'examens.auteur',
            'fluxEtapes.auteur',
        ]);

        $fluxAsc  = $grandProjet->fluxEtapes()->with('auteur')->orderBy('happened_at','asc')->get();
        $fluxDesc = $grandProjet->fluxEtapes;
        $allowedTransitions = $grandProjet->allowedTransitions();

        return view('grandprojets.cpc.show', [
            'cpc'                => $grandProjet,
            'allowedTransitions' => $allowedTransitions,
            'fluxHistory'        => $fluxDesc,
            'fluxAsc'            => $fluxAsc,
        ]);
    }

    public function edit(GrandProjet $grandProjet)
    {
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get();
        return view('grandprojets.cpc.edit', compact('grandProjet','maitresOeuvre'));
    }

    public function update(Request $request, GrandProjet $grandProjet)
    {
        $validated = $request->validate([
            'numero_dossier'          => ['required', 'regex:/^\d+\/\d{2}$/'],
            'numero_arrivee'          => ['nullable', 'string', 'max:50'],
            'province'                => 'required|string',
            'commune_1'               => 'required|string',
            'date_arrivee'            => 'required|date',
            'petitionnaire'           => 'required|string',
            'categorie_petitionnaire' => 'required|string',
            'intitule_projet'         => 'required|string',
            'categorie_projet'        => 'required|string|in:Commerce,Culte,Equipement de proximité,équipement public,équipement privé,Immeuble,projet agricole,Projet Industriel,Projet touristique,R+1,R+2,RDC,Services,Villa,Autre',
            'contexte_projet'         => 'required|string',
            'maitre_oeuvre'           => 'required|string',
            'situation'               => 'required|string',
            'reference_fonciere'      => 'required|string',
            'reference_envoi'         => 'nullable|string',
            'numero_envoi'            => 'nullable|string',
            'lien_ged'                => 'nullable|url',
            'observations'            => 'nullable|string',
            'proprietaire'            => 'nullable|string',
            'date_commission_mixte'   => 'nullable|date',
        ]);

        $validated['type_envoi'] = $request->has('envoi_papier') ? 'papier' : 'email';
        $grandProjet->update($validated);

        if (Auth::user()->hasRole('saisie_cpc')) {
            return redirect()->route('saisie_cpc.dashboard')->with('success', 'Projet mis à jour.');
        }
        return redirect()->route('chef.grandprojets.cpc.index')->with('success', 'Projet mis à jour.');
    }

    public function destroy(GrandProjet $grandProjet)
    {
        $grandProjet->delete();
        return redirect()->route('chef.grandprojets.cpc.index')->with('success', 'Projet supprimé.');
    }

    public function changerEtat(Request $request, GrandProjet $grandProjet)
    {
        $request->validate([
            'etat' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $to      = $request->etat;
        $allowed = $grandProjet->allowedTransitions();
        abort_unless(in_array($to, $allowed, true), 422, 'Transition non autorisée depuis l’état courant.');

        $from = $grandProjet->etat;

        DB::transaction(function () use ($grandProjet, $from, $to, $request) {
            $grandProjet->update(['etat' => $to]);
            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => auth()->id(),
                'note'            => $request->note,
            ]);
        });

        return back()->with('success', "État mis à jour : {$from} → {$to}");
    }
        /** Formulaire de complétion Bureau de suivi (uniquement si retour_bs & favorable) */
    public function completeForm(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->canBeCompletedByBS(), 403, 'Ce dossier n’est pas éligible à la complétion Bureau de suivi.');
        return view('grandprojets.cpc.complete', compact('grandProjet'));
    }

    /** Enregistrement complétion Bureau de suivi */
    // app/Http/Controllers/GrandProjetCPCController.php

public function completeStore(Request $request, GrandProjet $grandProjet)
{
    abort_unless($grandProjet->canBeCompletedByBS(), 403, 'Ce dossier n’est pas éligible à la complétion Bureau de suivi.');

    // 1) Normaliser les décimales (virgule -> point) et nettoyer les espaces fines
    $clean = $request->all();
    $normalizeNumber = function ($v) {
        if ($v === null || $v === '') return null;
        // enlever espaces, espaces insécables, etc.
        $v = preg_replace('/[^\d,.\-]/u', '', (string)$v);
        // convertir virgule en point
        $v = str_replace(',', '.', $v);
        return $v;
    };

    foreach (['superficie_terrain','superficie_couverte','montant_investissement'] as $k) {
        if (array_key_exists($k, $clean)) {
            $clean[$k] = $normalizeNumber($clean[$k]);
        }
    }

    // 2) Valider (après normalisation)
    $validated = \Validator::make($clean, [
        'date_commission_mixte_effective' => ['nullable','date'],
        'superficie_terrain'              => ['nullable','numeric','min:0'],
        'superficie_couverte'             => ['nullable','numeric','min:0'],
        'montant_investissement'          => ['nullable','numeric','min:0'],
        'emplois_prevus'                  => ['nullable','integer','min:0'],
        'nb_logements'                    => ['nullable','integer','min:0'],
    ])->validate();

    // 3) Calcul auto si non fourni
    $TAUX_PAR_M2 = (float) config('app.bs_rate_mad_per_m2', 1200);
    if (
        (empty($validated['montant_investissement']) || (float)$validated['montant_investissement'] === 0.0)
        && !empty($validated['superficie_couverte'])
    ) {
        $validated['montant_investissement'] = round((float)$validated['superficie_couverte'] * $TAUX_PAR_M2, 2);
    }

    // 4) Sauvegarde + journalisation
    \DB::transaction(function () use ($grandProjet, $validated) {
        $grandProjet->fill($validated);
        $grandProjet->bs_completed_at = now();
        $grandProjet->bs_completed_by = auth()->id();
        $grandProjet->save();

        \App\Models\FluxEtape::create([
            'grand_projet_id' => $grandProjet->id,
            'from_etat'       => 'retour_bs',
            'to_etat'         => 'retour_bs',
            'happened_at'     => now(),
            'by_user'         => auth()->id(),
            'note'            => 'Complétion Bureau de suivi (superficies, montants, emplois, logements).',
        ]);
    });

    return redirect()
        ->route('chef.grandprojets.cpc.index')
        ->with('success', 'Complétion enregistrée.');
}


}
