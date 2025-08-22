<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\FluxEtape;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransitionController extends Controller
{
    public function change(Request $request, GrandProjet $grandProjet)
    {
        $request->validate([
            'etat' => 'required|string',
            'note' => 'nullable|string',
        ]);

        $user  = auth()->user();
        $role  = $this->detectRole($user); // dajf | dgu | comm_interne | null
        $from  = $grandProjet->etat;
        $to    = $request->etat;

        // 1) Vérifier que la transition est autorisée par la machine globale
        $allowedGlobal = $grandProjet->allowedTransitions();
        abort_unless(in_array($to, $allowedGlobal, true), 422, 'Transition non autorisée depuis l’état courant (règle globale).');

        // 2) Vérifier que ce rôle a le droit de faire cette transition
        $roleMap = GrandProjet::roleTransitionMap();
        abort_unless($role && isset($roleMap[$role][$from]) && in_array($to, $roleMap[$role][$from], true),
            403, 'Transition non autorisée pour votre rôle.');

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

    private function detectRole($user): ?string
    {
        if ($user->hasRole('dajf')) return 'dajf';
        if ($user->hasRole('dgu'))  return 'dgu';
        if ($user->hasRole('comm_interne')) return 'comm_interne'; // (ici on ne change pas l'état directement)
        return null;
    }
}
