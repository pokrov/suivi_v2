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
     * Formulaire de création d’un examen
     */
    public function create(GrandProjet $grandProjet)
    {
        // Numéro d’examen suivant (provient de l’accessor du modèle)
        $nextNumero = $grandProjet->next_numero_examen;

        // Libellé affiché selon l’état courant
        $typeLabel = match ($grandProjet->etat) {
            'comm_interne' => 'Commission interne',
            'comm_mixte'   => 'Commission mixte',
            default        => 'Examen',
        };

        return view('examens.create', compact('grandProjet', 'nextNumero', 'typeLabel'));
    }

    /**
     * Enregistrement d’un examen + transition automatique
     */
    public function store(Request $request, GrandProjet $grandProjet)
    {
        $data = $request->validate([
            'avis'         => 'required|in:favorable,defavorable,ajourne,sans_avis',
            'observations' => 'nullable|string|max:2000',
            'date_examen'  => 'nullable|date',

            // Nouveaux champs facultatifs (pour défavorable)
            'motifs'       => ['nullable','array'],
            'motifs.*'     => ['in:administratif,juridique_foncier,urbanistique,technique,autre'],
            'motif_autre'  => ['nullable','string','max:255'],
        ]);

        $current = $grandProjet->etat;

        DB::transaction(function () use ($grandProjet, $data, $current) {

            // Déterminer le type d’examen selon l’état courant
            $type = match ($current) {
                'comm_interne' => 'interne',
                'comm_mixte'   => 'mixte',
                default        => 'interne', // fallback
            };

            // Normalisation des motifs : uniquement si avis défavorable
            $motifs = null;
            $motifAutre = null;

            if ($data['avis'] === 'defavorable') {
                $motifs = array_values($data['motifs'] ?? []);
                $motifAutre = $data['motif_autre'] ?? null;

                // Si "autre" n’est pas coché → ignorer motif_autre
                if (!in_array('autre', $motifs, true)) {
                    $motifAutre = null;
                }
            }

            // Créer l’examen
            Examen::create([
                'grand_projet_id' => $grandProjet->id,
                'numero_examen'   => $grandProjet->next_numero_examen,
                'type_examen'     => $type,
                'avis'            => $data['avis'],
                'observations'    => $data['observations'] ?? null,
                'date_examen'     => $data['date_examen'] ?? now(),
                'auteur_id'       => Auth::id(),
                'motifs'          => $motifs,     // JSON
                'motif_autre'     => $motifAutre, // String
            ]);

            // Calcul de la transition selon la règle de gestion
            $to = $current;
            if ($current === 'comm_interne') {
                // Toujours vers la mixte après l’avis interne
                $to = 'comm_mixte';
            } elseif ($current === 'comm_mixte') {
                // Mixte favorable -> signature_3 ; défavorable -> retour_bs
                $to = ($data['avis'] === 'favorable') ? 'signature_3' : 'retour_bs';
            }

            if ($to !== $current) {
                $grandProjet->update(['etat' => $to]);

                FluxEtape::create([
                    'grand_projet_id' => $grandProjet->id,
                    'from_etat'       => $current,
                    'to_etat'         => $to,
                    'happened_at'     => now(),
                    'by_user'         => Auth::id(),
                    'note'            => "Transition automatique après avis ($type).",
                ]);
            }
        });

        // Retour à la partie Commission où l’on était avant l’avis
        $redirectUrl = session('comm_return_url')
            ?? route('comm.dashboard', [
                'type'  => $grandProjet->type_projet,
                'scope' => 'interne'
            ]);

        return redirect($redirectUrl)->with('success', 'Avis enregistré et dossier routé.');
    }
}
