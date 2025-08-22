<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class StatsController extends Controller
{
    public function index()
    {
        // Statistiques sur les projets CPC (type_projet = 'cpc')

        // 1️⃣ Nombre total de projets CPC
        $totalProjets = GrandProjet::where('type_projet', 'cpc')->count();

        // 2️⃣ Répartition des projets par état
        $projetsParEtat = GrandProjet::where('type_projet', 'cpc')
            ->select('etat', DB::raw('count(*) as total'))
            ->groupBy('etat')
            ->get();

        // 3️⃣ Projets par Préfecture
        $projetsParPrefecture = GrandProjet::where('type_projet', 'cpc')
            ->select('province', DB::raw('count(*) as total'))
            ->groupBy('province')
            ->get();

        // 4️⃣ Évolution mensuelle
        $projetsParMois = GrandProjet::where('type_projet', 'cpc')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mois"), DB::raw('count(*) as total'))
            ->groupBy('mois')
            ->orderBy('mois', 'ASC')
            ->get();

        // 5️⃣ Top 5 communes
        $topCommunes = GrandProjet::where('type_projet', 'cpc')
            ->select('commune_1', DB::raw('count(*) as total'))
            ->groupBy('commune_1')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('chef.stats.index', compact(
            'totalProjets',
            'projetsParEtat',
            'projetsParPrefecture',
            'projetsParMois',
            'topCommunes'
        ));
    }
}
