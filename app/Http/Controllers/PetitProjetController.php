<?php

namespace App\Http\Controllers;

use App\Models\PetitProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetitProjetController extends Controller
{
    // Affiche la liste des petits projets selon le rôle
    public function index(Request $request)
    {
        // Si l'utilisateur a le rôle 'chef', afficher tous les projets
        if (Auth::user()->hasRole('chef')) {
            $petitsProjets = PetitProjet::orderBy('created_at', 'desc')->paginate(10);
        } else {
            // Sinon, s'il a le rôle 'saisie_petit', afficher uniquement ses propres projets
            $petitsProjets = PetitProjet::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }
        
        return view('petitprojets.index', compact('petitsProjets'));
    }

    // Affiche le formulaire de création d'un petit projet
    public function create()
    {
        return view('petitprojets.create');
    }

    // Stocke un nouveau petit projet
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_projet'         => 'required|string|unique:petits_projets,numero_projet',
            'titre_projet'          => 'required|string|max:255',
            'province'              => 'required|string',
            'commune'               => 'required|string',
            'petitionnaire' => 'required|string',
            // Ajoutez ici la validation des autres champs nécessaires...
        ]);

        $validated['user_id'] = Auth::id();

        PetitProjet::create($validated);

        return redirect()->route('petitprojets.index')->with('success', 'Petit projet créé avec succès.');
    }

    // Affiche le formulaire d'édition d'un petit projet
    public function edit(PetitProjet $petitProjet)
    {
        return view('petitprojets.edit', compact('petitProjet'));
    }

    // Met à jour le petit projet
    public function update(Request $request, PetitProjet $petitProjet)
    {
        $validated = $request->validate([
            'numero_projet'         => 'required|string|unique:petits_projets,numero_projet,' . $petitProjet->id,
            'titre_projet'          => 'required|string|max:255',
            'province'              => 'required|string',
            'commune'               => 'required|string',
            'petitionnaire' => 'required|string',
            // Ajoutez ici la validation des autres champs...
        ]);

        $petitProjet->update($validated);

        return redirect()->route('petitprojets.index')->with('success', 'Petit projet mis à jour avec succès.');
    }

    // Supprime un petit projet
    public function destroy(PetitProjet $petitProjet)
    {
        $petitProjet->delete();
        return redirect()->route('petitprojets.index')->with('success', 'Petit projet supprimé avec succès.');
    }
}
