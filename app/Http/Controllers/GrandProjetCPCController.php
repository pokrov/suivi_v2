<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrandProjetCPCController extends Controller
{
    /**
     * Index: Chef only (full resource route).
     */
    public function index()
    {
        // Chef can list all CPC. Also eager-load the user who created each project.
        $grandProjets = GrandProjet::where('categorie_projet', 'CPC')
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('grandprojets.cpc.index', compact('grandProjets'));
    }

    /**
     * Create form: Accessed by both Chef & saisie_cpc (partial route for saisie_cpc).
     */
    public function create()
    {
        return view('grandprojets.cpc.create');
    }

    /**
     * Store: Shared by Chef & saisie_cpc.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_dossier'          => 'required|string',
            'province'                => 'required|string',
            'commune_1'               => 'required|string',
            'date_arrivee'            => 'required|date',
            'petitionnaire'           => 'required|string',
            'categorie_petitionnaire' => 'required|string',
            'intitule_projet'         => 'required|string',
            'categorie_projet'        => 'required|string|in:CPC',
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
            'a_proprietaire'          => 'nullable|boolean',
            'proprietaire'            => 'nullable|string',
        ]);

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
        return view('grandprojets.cpc.show', [
            'cpc' => $grandProjet
        ]);
    }

    /**
     * Edit: Chef only.
     */
    public function edit(GrandProjet $grandProjet)
    {
        // Only Chef route calls this
        return view('grandprojets.cpc.edit', compact('grandProjet'));
    }

    /**
     * Update: Chef only.
     */
    public function update(Request $request, GrandProjet $grandProjet)
    {
        // Chef only
        $validated = $request->validate([
            'numero_dossier'          => 'required|string',
            'province'                => 'required|string',
            'commune_1'               => 'required|string',
            'date_arrivee'            => 'required|date',
            'petitionnaire'           => 'required|string',
            'categorie_petitionnaire' => 'required|string',
            'intitule_projet'         => 'required|string',
            'categorie_projet'        => 'required|string|in:CPC',
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
            'a_proprietaire'          => 'nullable|boolean',
            'proprietaire'            => 'nullable|string',
        ]);

        $grandProjet->update($validated);

        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC mis à jour avec succès.');
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
}
