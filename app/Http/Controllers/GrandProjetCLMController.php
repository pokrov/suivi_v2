<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\MaitreOeuvre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GrandProjetCLMController extends Controller
{
    /** Liste des CLM avec filtres. */
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

    /** Formulaire de création. */
    public function create()
    {
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get(['nom']);
        return view('grandprojets.clm.create', compact('maitresOeuvre'));
    }

    /** Enregistrement d’un nouveau CLM. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_dossier'           => ['required','string','max:50','regex:/^\d+\/\d{2}$/', Rule::unique('grand_projets','numero_dossier')],
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

        // Normalisation catégories
        $data['categorie_projet'] = implode(' | ', $data['categorie_projet']);

        // Valeurs systèmes
        $data['type_projet']             = 'clm';
        $data['etat']                    = 'enregistrement';
        $data['user_id']                 = auth()->id();
        $data['categorie_petitionnaire'] = $data['categorie_petitionnaire'] ?? 'Particulier';
        $data['envoi_papier']            = $request->boolean('envoi_papier');

        // Sécuriser les facultatifs
        foreach ([
            'contexte_projet','proprietaire','commune_2','reference_envoi','numero_envoi',
            'numero_arrivee','date_commission_mixte','lien_ged','reference_fonciere','observations'
        ] as $f) { $data[$f] = $data[$f] ?? null; }

        $grandProjet = GrandProjet::create($data);

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Dossier CLM créé.');
    }

    /** Affichage d’un CLM. */
    public function show(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $fluxAsc = method_exists($grandProjet, 'fluxEtapes')
            ? $grandProjet->fluxEtapes()->orderBy('happened_at','asc')->get()
            : collect();

        return view('grandprojets.clm.show', compact('grandProjet','fluxAsc'));
    }

    /** Formulaire d’édition. */
    public function edit(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        $maitresOeuvre = MaitreOeuvre::orderBy('nom')->get(['nom']);
        return view('grandprojets.clm.edit', compact('grandProjet','maitresOeuvre'));
    }

    /** Mise à jour d’un CLM. */
    public function update(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);

        $data = $request->validate([
            'numero_dossier'           => ['required','string','max:50','regex:/^\d+\/\d{2}$/', Rule::unique('grand_projets','numero_dossier')->ignore($grandProjet->id)],
            'province'                 => ['required','string','max:120'],
            'commune_1'                => ['required','string','max:120'],
            'date_arrivee'             => ['required','date'],
            'petitionnaire'            => ['required','string','max:180'],
            'categorie_petitionnaire'  => ['required','string','max:120'],
            'intitule_projet'          => ['required','string','max:255'],
            'maitre_oeuvre'            => ['required','string','max:180'],
            'situation'                => ['required','string','max:500'],

            'categorie_projet'         => ['required','array','min:1'],
            'categorie_projet.*'       => ['string'],

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

        foreach ([
            'contexte_projet','proprietaire','commune_2','reference_envoi','numero_envoi',
            'numero_arrivee','date_commission_mixte','lien_ged','reference_fonciere','observations'
        ] as $f) { $data[$f] = $data[$f] ?? null; }

        $grandProjet->update($data);

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Dossier mis à jour.');
    }

    /** Suppression. */
    public function destroy(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        $grandProjet->delete();

        return redirect()
            ->route('chef.grandprojets.clm.index')
            ->with('success','Dossier supprimé.');
    }

    /** Formulaire de complétion (retour BS). */
    public function completeForm(GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        abort_unless($grandProjet->etat === 'retour_bs', 403);

        $clmType = $this->inferClmType($grandProjet);
        return view('grandprojets.clm.complete', compact('grandProjet','clmType'));
    }

    /** Enregistrement de la complétion (BS). */
    public function completeStore(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->type_projet === 'clm', 404);
        abort_unless($grandProjet->etat === 'retour_bs', 403);

        // type reçu depuis la vue, mais on garde une inférence côté serveur (sécurité)
        $type = $request->input('clm_type') ?: $this->inferClmType($grandProjet);
        $type = in_array($type, ['morcellement','lotissement','groupe'], true) ? $type : 'autre';

        // Règles communes
        $rules = [
            'date_commission_mixte_effective' => ['nullable','date'],
            'superficie_terrain'              => ['nullable','numeric','min:0'],
            'emplois_prevus'                  => ['nullable','integer','min:0'],
            'nb_logements'                    => ['nullable','integer','min:0'],
            // Champs spécifiques (on les déclare pour éviter “unknown field”)
            'superficie_couverte'             => ['nullable','numeric','min:0'],
            'montant_investissement'          => ['nullable','numeric','min:0'],
            'superficie_morcelee'             => ['nullable','numeric','min:0'],
            'superficie_lotie'                => ['nullable','numeric','min:0'],
            'consistance'                     => ['nullable','string'],
            'clm_type'                        => ['nullable','in:morcellement,lotissement,groupe,autre'],
        ];

        // Validation
        $validated = $request->validate($rules);

        // Normalisation/mise à zéro de ce qui n’a pas lieu d’être selon type
        $payload = [
            'date_commission_mixte_effective' => $validated['date_commission_mixte_effective'] ?? null,
            'superficie_terrain'              => $validated['superficie_terrain'] ?? null,
            'emplois_prevus'                  => $validated['emplois_prevus'] ?? null,
            'nb_logements'                    => $validated['nb_logements'] ?? null,
            // par défaut on met tout à null, puis on remplit le nécessaire
            'superficie_couverte'             => null,
            'montant_investissement'          => null,
            'superficie_morcelee'             => null,
            'superficie_lotie'                => null,
            'consistance'                     => null,
            'bs_completed_at'                 => now(),
            'bs_completed_by'                 => auth()->id(),
        ];

        if ($type === 'morcellement') {
            $payload['superficie_morcelee'] = $validated['superficie_morcelee'] ?? null;
            // pas de couverte / investissement / logements requis
            $payload['nb_logements'] = null;
        } elseif ($type === 'lotissement') {
            $payload['superficie_lotie'] = $validated['superficie_lotie'] ?? null;
            $payload['consistance']      = $validated['consistance'] ?? null;
            // pas de couverte
        } elseif ($type === 'groupe') {
            $payload['superficie_couverte']    = $validated['superficie_couverte'] ?? null;
            $payload['consistance']            = $validated['consistance'] ?? null;
            // nb_logements autorisé (gardé)
        } else { // autre
            // Modèle “générique” : couverte + investissement
            $payload['superficie_couverte']    = $validated['superficie_couverte'] ?? null;
            $payload['montant_investissement'] = $validated['montant_investissement'] ?? null;
        }

        $grandProjet->update($payload);

        return redirect()
            ->route('chef.grandprojets.clm.show', $grandProjet)
            ->with('success','Complétion CLM enregistrée.');
    }

    /** Déduit le type CLM à partir de la catégorie. */
    private function inferClmType(GrandProjet $gp): string
    {
        $s = Str::lower((string) $gp->categorie_projet);
        if (Str::contains($s, ['morcel']))  return 'morcellement';
        if (Str::contains($s, ['lotiss']))  return 'lotissement';
        if (Str::contains($s, ['groupe']))  return 'groupe';
        return 'autre';
    }
}
