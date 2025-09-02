<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\User;
use App\Models\FluxEtape;
use Illuminate\Http\Request;

class ChefAssignmentController extends Controller
{
    // Liste des dossiers à attribuer (état = enregistrement), par type CPC/CLM
    public function index(Request $request)
    {
        $type = $request->query('type','cpc'); // 'cpc' | 'clm'
        $builder = $type === 'clm' ? GrandProjet::clm() : GrandProjet::cpc();

        $items = $builder
            ->where('etat','enregistrement')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Tous les agents DAJF (tu peux filtrer par actif si tu as une colonne)
        $dajfUsers = User::whereHas('roles', fn($q)=>$q->where('name','dajf'))
                         ->orderBy('name')->get(['id','name','email']);

        return view('chef.assignments.index', compact('items','type','dajfUsers'));
    }

    // Affecter un dossier à un agent DAJF + router 'transmis_dajf'
    public function assignToDajf(Request $request, GrandProjet $grandProjet)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // sécurité : uniquement depuis l'état 'enregistrement'
        abort_unless($grandProjet->etat === 'enregistrement', 403, 'Dossier non éligible.');

        $grandProjet->update([
            'assigned_dajf_id' => $request->user_id,
            'assigned_dajf_at' => now(),
            'etat'             => 'transmis_dajf',
        ]);

        FluxEtape::create([
            'grand_projet_id' => $grandProjet->id,
            'from_etat'       => 'enregistrement',
            'to_etat'         => 'transmis_dajf',
            'happened_at'     => now(),
            'by_user'         => auth()->id(),
            'note'            => 'Affecté à un agent DAJF par le Chef.',
        ]);

        return back()->with('success', 'Dossier affecté et transmis à la DAJF.');
    }
}
