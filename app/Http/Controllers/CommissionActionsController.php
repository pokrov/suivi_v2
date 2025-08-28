<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\FluxEtape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionActionsController extends Controller
{
    /**
     * Réception par la commission interne
     * vers_comm_interne  →  comm_interne
     */
    public function receive(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'vers_comm_interne', 403, 'Action possible uniquement à la réception.');

        DB::transaction(function () use ($grandProjet) {
            $from = $grandProjet->etat;
            $to   = 'comm_interne';

            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Dossier reçu par la Commission interne.',
            ]);
        });

        return back()->with('success', 'Dossier reçu par la Commission interne.');
    }

    /**
     * Commission mixte (avis interne favorable)
     * comm_mixte  →  signature_3
     */
    public function mixteToSignature(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'comm_mixte', 403, 'Action possible uniquement depuis Commission mixte.');

        DB::transaction(function () use ($grandProjet) {
            $from = $grandProjet->etat;
            $to   = 'signature_3';

            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Commission mixte → 3ᵉ signature (avis interne favorable).',
            ]);
        });

        return back()->with('success', 'Dossier envoyé à la 3ᵉ signature.');
    }

    /**
     * Commission mixte (avis interne défavorable)
     * comm_mixte  →  retour_bs
     */
    public function mixteToBs(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'comm_mixte', 403, 'Action possible uniquement depuis Commission mixte.');

        DB::transaction(function () use ($grandProjet) {
            $from = $grandProjet->etat;
            $to   = 'retour_bs';

            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Commission mixte → Bureau d’ordre (avis interne défavorable).',
            ]);
        });

        return back()->with('success', 'Dossier envoyé au Bureau d’ordre.');
    }

    /**
     * 3ᵉ signature validée par le responsable
     * signature_3  →  retour_bs
     */
    public function markSigned(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'signature_3', 403, 'Action possible uniquement depuis 3ᵉ signature.');

        DB::transaction(function () use ($grandProjet) {
            $from = $grandProjet->etat;
            $to   = 'retour_bs';

            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Transmis au Bureau d’ordre après signature.',
            ]);
        });

        return back()->with('success', 'Envoyé au Bureau d’ordre.');
    }

    /**
     * Bureau d’ordre / de suivi → Archivage
     * retour_bs  →  archive
     */
    public function archive(Request $request, GrandProjet $grandProjet)
    {
        abort_unless($grandProjet->etat === 'retour_bs', 403, 'Action possible uniquement depuis Bureau d’ordre.');

        DB::transaction(function () use ($grandProjet) {
            $from = $grandProjet->etat;
            $to   = 'archive';

            $grandProjet->update(['etat' => $to]);

            FluxEtape::create([
                'grand_projet_id' => $grandProjet->id,
                'from_etat'       => $from,
                'to_etat'         => $to,
                'happened_at'     => now(),
                'by_user'         => Auth::id(),
                'note'            => 'Clôture et archivage du dossier.',
            ]);
        });

        return back()->with('success', 'Dossier archivé.');
    }
}
