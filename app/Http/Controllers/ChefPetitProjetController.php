<?php

namespace App\Http\Controllers;

use App\Models\PetitProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChefPetitProjetController extends Controller
{
    // Afficher tous les petits projets
    public function index(Request $request)
    {
        $petitsProjets = PetitProjet::orderBy('created_at', 'desc')->paginate(10);
        return view('petitprojets.index', compact('petitsProjets'));
    }

    // Afficher le formulaire de création
    public function create()
    {
        return view('chefpetitprojets.create');
    }

    // Traiter la création d'un petit projet
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_projet'         => 'required|string|unique:petits_projets,numero_projet',
            'titre_projet'          => 'required|string|max:255',
            'province'              => 'required|string',
            'commune'               => 'required|string',
            // Ajoute ici la validation pour les autres champs comme commission, avis, pétitionnaire, etc.
        ]);

        $validated['user_id'] = Auth::id(); // Le chef peut créer un projet pour tout le monde

        PetitProjet::create($validated);

        return redirect()->route('chef.petitprojets.index')->with('success', 'Petit projet créé avec succès.');
    }
}
