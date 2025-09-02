<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\MaitreOeuvre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GrandProjetCLMController extends Controller
{
    /**
     * Liste des CLM avec filtres.
     */
    public function index(Request $request)
    {
        $q = GrandProjet::query()->clm();

        if ($s = trim($request->get('search', ''))) {
            $q->where(function ($qq) use ($s) {
                $qq->where('numero_dossier', 'like', "%{$s}%")
                   ->orWhere('intitule_projet', 'like', "%{$s}%")
                   ->orWhere('petitionnaire', 'like', "%{$s}%");
            });
        }
        if ($from = $request->date_from) $q->whereDate('date_arrivee', '>=', $from);
        if ($to   = $request->date_to)   $q->whereDate('date_arrivee', '<=', $to);
        if ($prov = $request->province)  $q->where('province', $prov);
        if ($etat = $request->etat)      $q->where('etat', $etat);

        $grandProjets = $q->latest()->paginate(12)->withQueryString();

        $etatsOptions = [
            'transmis_dajf'     => 'Saisie',
            'recu_dajf'         => 'Vers DAJF',
            'transmis_dgu'      => 'DAJF',
            'recu_dgu'          => 'Vers DGU',
            'vers_comm_interne' => 'DGU',
            'comm_interne'      => 'Comm. Interne',
            'comm_mixte'        => 'Comm. Mixte',
            'signature_3'       => '3ᵉ signature',
            'retour_bs'         => 'Bureau de suivi',
            'archive'           => 'Archivé',
        ];

        return view('grandprojets.clm.index', compact('grandProjets', 'etatsOptions'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get(['nom']);
        return view('grandprojets.clm.create', compact('maitresOeuvre'));
    }

    /**
     * Enregistrement d’un nouveau CLM.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            // Requis
            'numero_dossier'           => ['required','string','max:50', Rule::unique('grand_projets','numero_dossier')],
            'province'                 => ['required','string','max:120'],
            'commune_1'                => ['required','string','max:120'],
            'date_arrivee'             => ['required','date'],
            'petitionnaire'            => ['required','string','max:180'],
            'categorie_petitionnaire'  => ['required','string','max:120'],
            'intitule_projet'          => ['required','string','max:255'],
            'maitre_oeuvre'            => ['required','string','max:180'],
            'situation'                => ['required','string','max:500'],

            // Catégories CLM (au moins 1)
            'categorie_projet'         => ['required','array','min:1'],
            'categorie_projet.*'       => ['string'],

            // Facultatifs
            'commune_2'                => ['nullable','string','max:120'],
            'reference_envoi'          => ['nullable','string','max:255'],
            'numero_envoi'             => ['nullable','string','max:255'],
            'numero_arrivee'           => ['nullable','string','max:255'],
            'date_commission_mixte'    => ['nullable','date'],
            'lien_ged'                 => ['nullable','url','max:255'],
            'reference_fonciere'       => ['nullable','string','max:255'],
            'observations'             => ['nullable','string'],
            'proprietaire'             => ['nullable','string','max:180'],
            'contexte_projet'          => ['nullable','string','max:120'],
        ]);

        // Normalisation catégories : array -> chaîne lisible
        $data['categorie_projet'] = implode(' | ', $data['categorie_projet']);

        // Valeurs systèmes
        $data['type_projet']             = 'clm';
        $data['etat']                    = 'transmis_dajf';
        $data['user_id']                 = auth()->id();
        $data['categorie_petitionnaire'] = $data['categorie_petitionnaire'] ?? 'Particulier';
        $data['envoi_papier']            = $request->boolean('envoi_papier');

        // Sécuriser les facultatifs (si colonnes NOT NULL en base)
        $data['contexte_projet']        = $data['contexte_projet']        ?? null;
        $data['proprietaire']           = $data['proprietaire']           ?? null;
        $data['commune_2']              = $data['commune_2']              ?? null;
        $data['reference_envoi']        = $data['reference_envoi']        ?? null;
        $data['numero_envoi']           = $data['numero_envoi']           ?? null;
        $data['numero_arrivee']         = $data['numero_arrivee']         ?? null;
        $data['date_commission_mixte']  = $data['date_commission_mixte']  ?? null;
        $data['lien_ged']               = $data['lien_ged']               ?? null;
        $data['reference_fonciere']     = $data['reference_fonciere']     ?? null;
        $data['observations']           = $data['observations']           ?? null;

        $grandProjet = GrandProjet::create($data);

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Dossier CLM créé.');
    }

    /**
     * Affichage d’un CLM.
     * Paramètre {grandProjet} lié par route model binding.
     */
    public function show(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $fluxAsc = method_exists($grandProjet, 'fluxEtapes')
            ? $grandProjet->fluxEtapes()->orderBy('happened_at','asc')->get()
            : collect();

        return view('grandprojets.clm.show', compact('grandProjet','fluxAsc'));
    }

    /**
     * Formulaire d’édition.
     */
    public function edit(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get(['nom']);
        return view('grandprojets.clm.edit', compact('grandProjet','maitresOeuvre'));
    }

    /**
     * Mise à jour d’un CLM.
     */
    public function update(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $data = $request->validate([
            // Requis
            'numero_dossier'           => ['required','string','max:50', Rule::unique('grand_projets','numero_dossier')->ignore($grandProjet->id)],
            'province'                 => ['required','string','max:120'],
            'commune_1'                => ['required','string','max:120'],
            'date_arrivee'             => ['required','date'],
            'petitionnaire'            => ['required','string','max:180'],
            'categorie_petitionnaire'  => ['required','string','max:120'],
            'intitule_projet'          => ['required','string','max:255'],
            'maitre_oeuvre'            => ['required','string','max:180'],
            'situation'                => ['required','string','max:500'],

            // Catégories CLM
            'categorie_projet'         => ['required','array','min:1'],
            'categorie_projet.*'       => ['string'],

            // Facultatifs
            'commune_2'                => ['nullable','string','max:120'],
            'reference_envoi'          => ['nullable','string','max:255'],
            'numero_envoi'             => ['nullable','string','max:255'],
            'numero_arrivee'           => ['nullable','string','max:255'],
            'date_commission_mixte'    => ['nullable','date'],
            'lien_ged'                 => ['nullable','url','max:255'],
            'reference_fonciere'       => ['nullable','string','max:255'],
            'observations'             => ['nullable','string'],
            'proprietaire'             => ['nullable','string','max:180'],
            'contexte_projet'          => ['nullable','string','max:120'],
        ]);

        $data['categorie_projet']        = implode(' | ', $data['categorie_projet']);
        $data['categorie_petitionnaire'] = $data['categorie_petitionnaire'] ?? 'Particulier';
        $data['envoi_papier']            = $request->boolean('envoi_papier');

        // Sécuriser facultatifs
        $data['contexte_projet']        = $data['contexte_projet']        ?? null;
        $data['proprietaire']           = $data['proprietaire']           ?? null;
        $data['commune_2']              = $data['commune_2']              ?? null;
        $data['reference_envoi']        = $data['reference_envoi']        ?? null;
        $data['numero_envoi']           = $data['numero_envoi']           ?? null;
        $data['numero_arrivee']         = $data['numero_arrivee']         ?? null;
        $data['date_commission_mixte']  = $data['date_commission_mixte']  ?? null;
        $data['lien_ged']               = $data['lien_ged']               ?? null;
        $data['reference_fonciere']     = $data['reference_fonciere']     ?? null;
        $data['observations']           = $data['observations']           ?? null;

        $grandProjet->update($data);

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Dossier mis à jour.');
    }

    /**
     * Suppression.
     */
    public function destroy(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $grandProjet->delete();

        return redirect()
            ->route('chef.grandprojets.clm.index')
            ->with('success','Dossier supprimé.');
    }

    /**
     * Formulaire de complétion (retour Bureau de Suivi).
     */
    public function completeForm(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        abort_unless($grandProjet->etat === 'retour_bs', 403);

        // IMPORTANT : on passe bien la variable sous le nom $grandProjet pour la vue
        return view('grandprojets.clm.complete', compact('grandProjet'));
    }

    /**
     * Enregistrement de la complétion (BS).
     */
    public function completeStore(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        abort_unless($grandProjet->etat === 'retour_bs', 403);

        $request->validate([
            'date_commission_mixte_effective' => ['nullable','date'],
            'superficie_terrain'              => ['nullable','numeric','min:0'],
            'superficie_couverte'             => ['nullable','numeric','min:0'],
            'montant_investissement'          => ['nullable','numeric','min:0'],
            'emplois_prevus'                  => ['nullable','integer','min:0'],
            'nb_logements'                    => ['nullable','integer','min:0'],
        ]);

        $grandProjet->update(array_merge(
            $request->only([
                'date_commission_mixte_effective',
                'superficie_terrain',
                'superficie_couverte',
                'montant_investissement',
                'emplois_prevus',
                'nb_logements',
            ]),
            [
                'bs_completed_at' => now(),
                'bs_completed_by' => auth()->id(),
            ]
        ));

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Complétion enregistrée.');
    }
}
