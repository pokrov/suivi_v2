<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrandProjetCPCController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupère uniquement les projets de catégorie 'CPC'
        $grandProjets = GrandProjet::where('categorie_projet', 'CPC')
            ->latest()
            ->paginate(10);

        return view('grandprojets.cpc.index', compact('grandProjets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('grandprojets.cpc.create');
    }

    /**
     * Store a newly created resource in storage.
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

        // Forcer le type_projet = 'cpc'
        $validated['type_projet'] = 'cpc';
        $validated['user_id'] = Auth::id();

        GrandProjet::create($validated);

        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC enregistré avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GrandProjet $grandProjet)
{
    // Provide $cpc to the view
    return view('grandprojets.cpc.show', [
        'cpc' => $grandProjet
    ]);
}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GrandProjet $grandProjet)
    {
        // Blade will receive the variable $grandProjet
        return view('grandprojets.cpc.edit', compact('grandProjet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GrandProjet $grandProjet)
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

        $grandProjet->update($validated);

        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GrandProjet $grandProjet)
    {
        $grandProjet->delete();

        return redirect()
            ->route('chef.grandprojets.cpc.index')
            ->with('success', 'Projet CPC supprimé avec succès.');
    }
}
