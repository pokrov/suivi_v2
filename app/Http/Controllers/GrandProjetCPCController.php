<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MaitreOeuvre; 

class GrandProjetCPCController extends Controller
{
    /**
     * Index: Chef only (full resource route).
     */
    public function index()
    {
        $search     = request('search');
        $dateFrom   = request('date_from');
        $dateTo     = request('date_to');
        $province   = request('province');  // nouveau paramètre
    
        $query = GrandProjet::where('type_projet', 'cpc')->with('user');
    
        // Recherche globale
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
            $query->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
        } elseif ($dateFrom) {
            $query->where('date_arrivee', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('date_arrivee', '<=', $dateTo);
        }
    
        // Filtre sur la province
        if ($province) {
            $query->where('province', $province);
        }
    
        $grandProjets = $query->latest()->paginate(10);
    
        return view('grandprojets.cpc.index', compact('grandProjets'));
    }
    

    /**
     * Create form: Accessed by both Chef & saisie_cpc (partial route for saisie_cpc).
     */
    public function create()
    {   $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get(); // Récupérer tous les maîtres d'œuvre
        return view('grandprojets.cpc.create', compact('maitresOeuvre'));
        
    }

    /**
     * Store: Shared by Chef & saisie_cpc.
     */
    public function store(Request $request)
    {
        // Enhanced validation with regex for numero_dossier
        $validated = $request->validate([
            'numero_dossier'          => ['required','regex:/^\d+\/20\d{2}$/'],
            'province'                => 'required|string',
            'commune_1'               => 'required|string',
            'date_arrivee'            => 'required|date',
            'petitionnaire'           => 'required|string',
            'categorie_petitionnaire' => 'required|string',
            'intitule_projet'         => 'required|string',
            'categorie_projet' => 'required|string|in:Commerce,Culte,Equipement de proximité,équipement public,équipement privé,Immeuble,projet agricole,Projet Industriel,Projet touristique,R+1,R+2,RDC,Services,Villa,Autre',
            'contexte_projet'         => 'required|string',
            'maitre_oeuvre'           => 'required|string',
            'situation'               => 'required|string',
            'reference_fonciere'      => 'required|string',

            // Optionals
            'reference_envoi'         => 'nullable|string',
            'numero_envoi'            => 'nullable|string',
            'date_commission_interne' => 'nullable|date',
            'lien_ged'                => 'nullable|url',
            'observations'            => 'nullable|string',
            'proprietaire'            => 'nullable|string',
            // 'a_proprietaire' => 'nullable|boolean' (already covered by existence of 'proprietaire')
        ]);

        // Determine if envoi_papier is checked => set type_envoi
        if ($request->has('envoi_papier')) {
            $validated['type_envoi'] = 'papier';
        } else {
            $validated['type_envoi'] = 'email';  // or null if you prefer
        }

        // Force the type_projet to "cpc"
        $validated['type_projet'] = 'cpc';
        // Associate the currently logged in user
        $validated['user_id'] = Auth::id();

        GrandProjet::create($validated);

        // Redirect based on user role
        if (Auth::user()->hasRole('chef')) {
            return redirect()
                ->route('chef.grandprojets.cpc.index')
                ->with('success', 'Projet CPC enregistré avec succès (Chef).');
        } elseif (Auth::user()->hasRole('saisie_cpc')) {
            return redirect()
                ->route('saisie_cpc.dashboard')
                ->with('success', 'Projet CPC enregistré avec succès (Saisie CPC).');
        }

        // Fallback (should not happen with your role-based routes)
        return redirect()->route('login');
    }

    /**
     * Show: Both Chef & saisie_cpc can see details of a CPC.
     */
    public function show(GrandProjet $grandProjet)
{
    $grandProjet->load([
        'user',
        'examens.auteur',
        'fluxEtapes.auteur',
    ]);

    $allowedTransitions = $grandProjet->allowedTransitions();

    return view('grandprojets.cpc.show', [
        'cpc' => $grandProjet,
        'allowedTransitions' => $allowedTransitions,
        'fluxHistory' => $grandProjet->fluxEtapes, // déjà trié desc
    ]);
}


    /**
     * Edit: Chef only.
     */
    public function edit(GrandProjet $grandProjet)
    {
        // Only Chef route calls this
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get();
        return view('grandprojets.cpc.edit', compact('grandProjet', 'maitresOeuvre'));
    }

    /**
     * Update: Chef only.
     */
    public function update(Request $request, GrandProjet $grandProjet)
{
    // Validation des données
    $validated = $request->validate([
        'numero_dossier'          => ['required','regex:/^\d+\/20\d{2}$/'],
        'province'                => 'required|string',
        'commune_1'               => 'required|string',
        'date_arrivee'            => 'required|date',
        'petitionnaire'           => 'required|string',
        'categorie_petitionnaire' => 'required|string',
        'intitule_projet'         => 'required|string',
        'categorie_projet' => 'required|string|in:Commerce,Culte,Equipement de proximité,équipement public,équipement privé,Immeuble,projet agricole,Projet Industriel,Projet touristique,R+1,R+2,RDC,Services,Villa,Autre',
        'contexte_projet'         => 'required|string',
        'maitre_oeuvre'           => 'required|string',
        'situation'               => 'required|string',
        'reference_fonciere'      => 'required|string',

        // Optionnels
        'reference_envoi'         => 'nullable|string',
        'numero_envoi'            => 'nullable|string',
        'date_commission_interne' => 'nullable|date',
        'lien_ged'                => 'nullable|url',
        'observations'            => 'nullable|string',
        'proprietaire'            => 'nullable|string',
    ]);

    // Déterminer le type d'envoi
    $validated['type_envoi'] = $request->has('envoi_papier') ? 'papier' : 'email';

    // Mettre à jour le projet
    $grandProjet->update($validated);

    // Redirection selon le rôle de l'utilisateur
    if (Auth::user()->hasRole('saisie_cpc')) {
        return redirect()
            ->route('saisie_cpc.dashboard')
            ->with('success', 'Projet CPC mis à jour avec succès.');
    } else {
        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC mis à jour avec succès.');
    }
}


    /**
     * Destroy: Chef only.
     */
    public function destroy(GrandProjet $grandProjet)
    {
        // Chef only
        $grandProjet->delete();

        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC supprimé avec succès.');
    }

    public function changerEtat(Request $request, GrandProjet $grandProjet)
{
    $request->validate([
        'etat' => 'required|string',
        'note' => 'nullable|string',
    ]);

    $to = $request->etat;
    $allowed = $grandProjet->allowedTransitions();
    abort_unless(in_array($to, $allowed, true), 422, 'Transition non autorisée depuis l’état courant.');

    $from = $grandProjet->etat;

    \DB::transaction(function () use ($grandProjet, $from, $to, $request) {
        // 1) Update état
        $grandProjet->update(['etat' => $to]);

        // 2) Journal (navette)
        \App\Models\FluxEtape::create([
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

}
