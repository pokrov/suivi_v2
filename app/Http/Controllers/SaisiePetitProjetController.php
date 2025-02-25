<?php

namespace App\Http\Controllers;

use App\Models\PetitProjet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SaisiePetitProjetController extends Controller
{
    // Afficher uniquement les projets créés par l'utilisateur connecté
    public function index(Request $request)
    {
        $petitsProjets = PetitProjet::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('saisiepetitprojets.index', compact('petitsProjets'));
    }

    // Afficher le formulaire de création pour "saisie_petit"
    public function create()
    {
        return view('saisiepetitprojets.create');
    }

    // Traiter la création d'un petit projet par un utilisateur de type "saisie_petit"
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_projet'         => 'required|string|unique:petits_projets,numero_projet',
            'titre_projet'          => 'required|string|max:255',
            'province'              => 'required|string',
            'commune'               => 'required|string',
            // Ajoute ici la validation pour les autres champs...
        ]);

        $validated['user_id'] = Auth::id();

        PetitProjet::create($validated);

        return redirect()->route('saisie.petitprojets.index')->with('success', 'Petit projet créé avec succès.');
    }
}
