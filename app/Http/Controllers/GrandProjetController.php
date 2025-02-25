<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GrandProjetController extends Controller
{
    /**
     * Affiche la liste des grands projets de type CPC.
     */
    public function index()
    {
        $grandProjets = GrandProjet::where('type_projet', 'cpc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('grandprojets.cpc.index', compact('grandProjets'));
    }

    /**
     * Affiche le formulaire de création pour un projet CPC.
     */
    public function create()
    {
        return view('grandprojets.cpc.create');
    }

    /**
     * Enregistre un nouveau grand projet CPC dans la base de données.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_dossier'         => 'required|string',
            'province'               => 'required|string',
            'commune_1'              => 'required|string',
            'date_arrivee'           => 'required|date',
            'petitionnaire'          => 'required|string',
            'categorie_petitionnaire'=> 'required|string',
            'intitule_projet'        => 'required|string',
            'categorie_projet'       => 'required|string|in:CPC',
            'contexte_projet'        => 'required|string',
            'maitre_oeuvre'          => 'required|string',
            'situation'              => 'required|string',
            'reference_fonciere'     => 'required|string',
        ]);

        $validated['type_projet'] = 'cpc';
        $validated['user_id'] = Auth::id();

        GrandProjet::create($validated);

        return redirect()->route('chef.grandprojets.index')
                         ->with('success', 'Grand projet CPC enregistré avec succès.');
    }

    /**
     * Affiche les détails d’un grand projet spécifique.
     */
    public function show(GrandProjet $grandprojet)
    {
        return view('grandprojets.cpc.show', compact('grandprojet'));
    }

    /**
     * Affiche le formulaire d’édition d’un projet CPC existant.
     */
    public function edit(GrandProjet $grandprojet)
    {
        return view('grandprojets.cpc.edit', compact('grandprojet'));
    }

    /**
     * Met à jour un projet CPC existant dans la base de données.
     */
    public function update(Request $request, GrandProjet $grandprojet)
    {
        $validated = $request->validate([
            'numero_dossier'         => 'required|string',
            'province'               => 'required|string',
            'commune_1'              => 'required|string',
            'date_arrivee'           => 'required|date',
            'petitionnaire'          => 'required|string',
            'intitule_projet'        => 'required|string',
            'categorie_petitionnaire'=> 'required|string',
            'categorie_projet'       => 'required|string',
            'contexte_projet'        => 'required|string',
            'maitre_oeuvre'          => 'required|string',
            'situation'              => 'required|string',
            'reference_fonciere'     => 'required|string',
        ]);

        $grandprojet->update($validated);

        return redirect()->route('chef.grandprojets.index')
                         ->with('success', 'Grand projet mis à jour avec succès.');
    }

    /**
     * Supprime un projet CPC de la base de données.
     */
    public function destroy(GrandProjet $grandprojet)
    {
        $grandprojet->delete();
        return redirect()->route('chef.grandprojets.index')
                         ->with('success', 'Grand projet supprimé avec succès.');
    }
}
