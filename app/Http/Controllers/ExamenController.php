<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\Examen;
use App\Models\FluxEtape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamenController extends Controller
{
    /**
     * Formulaire d'avis (commission interne).
     */
    public function create(GrandProjet $grandProjet)
    {
        // Sécurité : seuls les dossiers à la commission interne sont traitables ici
        abort_unless($grandProjet->etat === 'comm_interne', 403, 'Dossier non à la commission interne.');

        // N° d’examen proposé (calcul côté serveur)
        $nextNumero = $grandProjet->next_numero_examen;

        return view('examens.create', [
            'grandProjet' => $grandProjet,
            'nextNumero'  => $nextNumero,
        ]);
    }

    /**
     * Enregistre un avis de commission interne.
     * - Calcule numero_examen côté serveur
     * - Enregistre l'examen
     * - Redirige le dossier: defavorable -> retour_dgu ; sinon -> retour_bs
     * - Journalise la transition
     */
    public function store(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'comm_interne', 403, 'Dossier non à la commission interne.');

        $data = $request->validate([
            'date_examen'   => ['required','date'], // on utilise date_examen
            'avis'          => ['required','in:favorable,defavorable,ajourne,sans_avis'],
            'observations'  => ['nullable','string'],
        ]);

        // Numéro d’examen calculé côté serveur (NE PAS le demander au client)
        $numero = $grandProjet->next_numero_examen;

        DB::transaction(function () use ($grandProjet, $data, $numero) {
            // Créer l’examen (type interne)
            Examen::create([
                'grand_projet_id' => $grandProjet->id,
                'numero_examen'   => $numero,
                'type_examen'     => 'interne',              // la colonne peut exister ou non (fillable côté modèle)
                'date_examen'     => $data['date_examen'],   // nouvelle colonne standard
                // Si ta table a encore "date_commission", tu peux aussi la remplir:
                'date_commission' => $data['date_examen'],
                'avis'            => $data['avis'],
                'observations'    => $data['observations'] ?? null,
                'created_by'      => Auth::id(),
            ]);

            // Choisir la redirection du flux
            $to   = $data['avis'] === 'defavorable' ? 'retour_dgu' : 'retour_bs';
            $from = $grandProjet->etat;

            // Changer l’état + journaliser
            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Avis commission interne (examen n° '.$numero.')',
            ]);
        });

        return redirect()
            ->route('comm.dashboard')
            ->with('success', "Avis enregistré (examen n° {$numero}) et dossier redirigé.");
    }

    /**
     * Formulaire d’édition d’un avis (interne).
     */
    public function edit(Examen $examen)
    {
        abort_unless($examen->type_examen === 'interne' || is_null($examen->type_examen), 403);

        return view('examens.edit', [
            'examen'      => $examen,
            'grandProjet' => $examen->grandProjet,
        ]);
    }

    /**
     * Met à jour l’avis (sans repositionner le flux automatiquement).
     */
    public function update(Request $request, Examen $examen)
    {
        abort_unless($examen->type_examen === 'interne' || is_null($examen->type_examen), 403);

        $data = $request->validate([
            'date_examen'   => ['required','date'],
            'avis'          => ['required','in:favorable,defavorable,ajourne,sans_avis'],
            'observations'  => ['nullable','string'],
        ]);

        $examen->update([
            'date_examen'     => $data['date_examen'],
            'date_commission' => $data['date_examen'], // si la colonne existe toujours
            'avis'            => $data['avis'],
            'observations'    => $data['observations'] ?? null,
        ]);

        return redirect()
            ->route('comm.dashboard')
            ->with('success', 'Avis modifié avec succès.');
    }
}
