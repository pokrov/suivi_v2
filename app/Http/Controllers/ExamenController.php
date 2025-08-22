<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\Examen;
use App\Models\FluxEtape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamenController extends Controller
{
    public function create(GrandProjet $grandProjet)
    {
        // On ne rend un avis que si le dossier est à la Commission interne
        abort_unless($grandProjet->etat === 'comm_interne', 403, 'Le dossier n’est pas en Commission interne.');

        // Si déjà favorable, on bloque
        abort_if($grandProjet->isFavorable(), 403, 'Dossier déjà favorable.');

        return view('examens.create', [
            'grandprojet' => $grandProjet->load('examens.auteur'),
            'nextNumero'  => $grandProjet->next_numero_examen,
            'history'     => $grandProjet->examens,
        ]);
    }

    public function store(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'comm_interne', 403, 'Le dossier n’est pas en Commission interne.');
        abort_if($grandProjet->isFavorable(), 403, 'Dossier déjà favorable.');

        $data = $request->validate([
            'date_commission' => ['nullable','date'],
            'avis'            => ['required','in:favorable,defavorable,ajourne,sans_avis'],
            'observations'    => ['nullable','string'],
            // vers où renvoyer le dossier après avis
            'rediriger_vers'  => ['required','in:retour_dgu,retour_bs'],
            'note_flux'       => ['nullable','string'],
        ]);

        DB::transaction(function () use ($grandProjet, $data) {
            // 1) Création de l’examen (n° auto)
            $numero = $grandProjet->next_numero_examen;

            $grandProjet->examens()->create([
                'numero_examen'   => $numero,
                'date_commission' => $data['date_commission'] ?? null,
                'avis'            => $data['avis'],
                'observations'    => $data['observations'] ?? null,
                'created_by'      => auth()->id(),
            ]);

            // 2) Transition d’état après commission
            $from = $grandProjet->etat;
            $to   = $data['rediriger_vers']; // retour_dgu ou retour_bs

            $grandProjet->update(['etat' => $to]);

            // 3) Journal (navette)
            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => auth()->id(),
                'note'            => $data['note_flux'] ?? null,
            ]);
        });

        // Redirection contextuelle
        if (auth()->user()->hasRole('chef')) {
            return redirect()
                ->route('chef.grandprojets.cpc.show', $grandProjet)
                ->with('success', 'Avis de la commission enregistré et dossier redirigé.');
        }

        return redirect()
            ->route('saisie_cpc.cpc.show', $grandProjet)
            ->with('success', 'Avis de la commission enregistré et dossier redirigé.');
    }
}
