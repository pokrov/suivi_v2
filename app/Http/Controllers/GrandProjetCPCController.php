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

        $grandProjets = $query->latest()->paginate(10);
        return view('grandprojets.cpc.index', compact('grandProjets'));
    }

    public function create()
    {
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get();
        return view('grandprojets.cpc.create', compact('maitresOeuvre'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero_dossier'          => ['required', 'regex:/^\d+\/20\d{2}$/'],
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
        $validated['type_projet'] = 'cpc';
        $validated['user_id']     = Auth::id();

        // Etat initial si non fourni
        if (empty($validated['etat'])) {
            $validated['etat'] = 'enregistrement';
        }

        $gp = GrandProjet::create($validated);

        // Journal initial : entrée dans l'état initial (enregistrement)
        FluxEtape::create([
            'grand_projet_id' => $gp->id,
            'from_etat'       => '—',
            'to_etat'         => $gp->etat,           // enregistrement
            'happened_at'     => $gp->created_at,
            'by_user'         => auth()->id(),
            'note'            => 'Création du dossier',
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
            'numero_dossier'          => ['required', 'regex:/^\d+\/20\d{2}$/'],
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
}
