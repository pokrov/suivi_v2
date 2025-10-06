<?php

namespace App\Http\Controllers;

use App\Models\PetitProjet;
use Illuminate\Http\Request;

class PetitProjetController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->string('search')->toString();
        $dateFrom = $request->date('date_from');
        $dateTo   = $request->date('date_to');

        $q = PetitProjet::query();

        if ($search) {
            $q->where(function($qq) use ($search){
                $qq->where('numero_dossier','like',"%$search%")
                   ->orWhere('intitule_projet','like',"%$search%")
                   ->orWhere('petitionnaire','like',"%$search%")
                   ->orWhere('maitre_oeuvre','like',"%$search%")
                   ->orWhere('commune_1','like',"%$search%")
                   ->orWhere('commune_2','like',"%$search%")
                   ->orWhere('reference_fonciere','like',"%$search%")
                   ->orWhere('rokhas_numero','like',"%$search%")
                   ->orWhere('rokhas_avis','like',"%$search%");
            });
        }

        if ($dateFrom && $dateTo)      $q->whereBetween('date_arrivee', [$dateFrom, $dateTo]);
        elseif ($dateFrom)             $q->where('date_arrivee', '>=', $dateFrom);
        elseif ($dateTo)               $q->where('date_arrivee', '<=', $dateTo);

        $items = $q->latest()->paginate(12)->withQueryString();

        return view('petitprojets.index', compact('items','search','dateFrom','dateTo'));
    }

    public function create()
    {
        return view('petitprojets.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['user_id'] = auth()->id();
        $data['etat']    = 'enregistrement';

        PetitProjet::create($data);

        return redirect()->route('chef.petitprojets.index')->with('success','Petit projet créé.');
    }

    public function edit(PetitProjet $petitprojet)
    {
        return view('petitprojets.edit', compact('petitprojet'));
    }

    public function update(Request $request, PetitProjet $petitprojet)
    {
        $data = $this->validateData($request);
        $petitprojet->update($data);

        return redirect()->route('chef.petitprojets.index')->with('success','Petit projet mis à jour.');
    }

    public function destroy(PetitProjet $petitprojet)
    {
        $petitprojet->delete();
        return back()->with('success','Petit projet supprimé.');
    }
    public function show(PetitProjet $petitprojet)
{
    return view('petitprojets.show', compact('petitprojet'));
}


    protected function validateData(Request $request): array
    {
        return $request->validate([
            // Identification
            'numero_dossier'          => ['required','string','max:255'],
            'province'                => ['nullable','string','max:255'],
            'commune_1'               => ['nullable','string','max:255'],
            'commune_2'               => ['nullable','string','max:255'],

            // Arrivée
            'date_arrivee'            => ['nullable','date'],
            'numero_arrivee'          => ['nullable','string','max:255'],

            // Acteurs
            'petitionnaire'           => ['nullable','string','max:255'],
            'a_proprietaire'          => ['nullable','boolean'],
            'proprietaire'            => ['nullable','string','max:255'],
            'categorie_petitionnaire' => ['nullable','string','max:255'],
            'maitre_oeuvre'           => ['nullable','string','max:255'],

            // Projet
            'intitule_projet'         => ['nullable','string','max:255'],
            'categorie_projet'        => ['nullable'],
            'categorie_projet.*'      => ['nullable','string','max:255'],
            'contexte_projet'         => ['nullable','string','max:255'],
            'situation'               => ['nullable','string','max:255'],
            'reference_fonciere'      => ['nullable','string','max:255'],
            'lien_ged'                => ['nullable','url','max:1024'],
            'observations'            => ['nullable','string','max:10000'],

            // Indicateurs
            'superficie_terrain'      => ['nullable','numeric'],
            'superficie_couverte'     => ['nullable','numeric'],
            'montant_investissement'  => ['nullable','numeric'],
            'emplois_prevus'          => ['nullable','integer','min:0'],
            'nb_logements'            => ['nullable','integer','min:0'],

            // Rokhas
            'rokhas_numero'           => ['nullable','string','max:255'],
            'rokhas_lien'             => ['nullable','url','max:1024'],
            'rokhas_avis'             => ['nullable','in:favorable,defavorable,sous_reserve,sans_objet'],
            'rokhas_avis_date'        => ['nullable','date'],
            'rokhas_avis_commentaire' => ['nullable','string','max:10000'],
            'rokhas_piece_url'        => ['nullable','url','max:1024'],

            // Etat (optionnel via formulaire d’édition)
            'etat'                    => ['nullable','in:enregistrement,archive'],
        ]);
    }
}
