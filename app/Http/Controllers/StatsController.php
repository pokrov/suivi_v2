<?php

namespace App\Http\Controllers;

use App\Models\GrandProjet;
use App\Models\Examen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        // ---- Filtres UI
        $type   = $request->get('type', 'cpc'); // 'cpc' | 'clm' | 'all'
        $from   = $request->get('from');        // YYYY-MM-DD (date arrivée min)
        $to     = $request->get('to');          // YYYY-MM-DD (date arrivée max)
        $prov   = $request->get('province');    // filtre province optionnel
        $agent  = $request->get('user_id');     // filtre user optionnel

        // Etats finals "clos" (à adapter si besoin)
        $finalStates = ['signature_3','retour_bs','archive','favorable','defavorable'];

        // ---- Base query
        $base = GrandProjet::query()
            ->when($type !== 'all', fn($q) => $q->where('type_projet', $type))
            ->when($from, fn($q) => $q->whereDate('date_arrivee', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('date_arrivee', '<=', $to))
            ->when($prov, fn($q) => $q->where('province', $prov))
            ->when($agent,fn($q) => $q->where('user_id', $agent));

        // ---- Total
        $totalProjets = (clone $base)->count();

        // ---- Répartition par état
        $projetsParEtat = (clone $base)
            ->select('etat', DB::raw('COUNT(*) as total'))
            ->groupBy('etat')
            ->orderByDesc('total')
            ->get();

        // ---- Répartition par préfecture/province
        $projetsParPrefecture = (clone $base)
            ->select('province', DB::raw('COUNT(*) as total'))
            ->groupBy('province')
            ->orderByDesc('total')
            ->get();

        // ---- Par mois de création (date_arrivee)
        $projetsParMois = (clone $base)
            ->select(DB::raw("DATE_FORMAT(date_arrivee,'%Y-%m') as mois"), DB::raw('COUNT(*) as total'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // ---- Throughput mensuel (créés vs clos)
        $throughputCrees = (clone $base)
            ->select(DB::raw("DATE_FORMAT(date_arrivee,'%Y-%m') as mois"), DB::raw('COUNT(*) as crees'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('crees','mois');

        // “Clos” = états finals (si tu as une vraie colonne closed_at, remplace par elle)
        $throughputClos = (clone $base)
            ->whereIn('etat', $finalStates)
            ->select(DB::raw("DATE_FORMAT(date_arrivee,'%Y-%m') as mois"), DB::raw('COUNT(*) as clos'))
            ->groupBy('mois')
            ->orderBy('mois')
            ->pluck('clos','mois');

        // Fusionner keyset
        $allMonths = collect($throughputCrees->keys())->merge($throughputClos->keys())->unique()->sort()->values();
        $throughputMois = $allMonths->map(function($m) use ($throughputCrees,$throughputClos){
            return (object)[
                'mois'  => $m,
                'crees' => (int)($throughputCrees[$m] ?? 0),
                'clos'  => (int)($throughputClos[$m] ?? 0),
            ];
        });

        // ---- Buckets d’âge (en jours depuis date_arrivee)
        // 0-14 / 15-30 / 31-60 / 61-90 / 91+
        $agingBuckets = (clone $base)
            ->selectRaw("
                CASE
                    WHEN DATEDIFF(CURDATE(), date_arrivee) <= 14 THEN '0-14j'
                    WHEN DATEDIFF(CURDATE(), date_arrivee) BETWEEN 15 AND 30 THEN '15-30j'
                    WHEN DATEDIFF(CURDATE(), date_arrivee) BETWEEN 31 AND 60 THEN '31-60j'
                    WHEN DATEDIFF(CURDATE(), date_arrivee) BETWEEN 61 AND 90 THEN '61-90j'
                    ELSE '90+ j'
                END AS bucket,
                COUNT(*) AS total
            ")
            ->groupBy('bucket')
            ->orderByRaw("
                FIELD(bucket,'0-14j','15-30j','31-60j','61-90j','90+ j')
            ")
            ->get();

        // ---- Délais moyens par état (age moyen… simplifié)
        $avgDelaiParEtat = (clone $base)
            ->select('etat', DB::raw('AVG(DATEDIFF(CURDATE(), date_arrivee)) as avg_jours'))
            ->groupBy('etat')
            ->orderBy('etat')
            ->get();

        // ---- Top 5 communes
        $topCommunes = (clone $base)
            ->select('commune_1', DB::raw('COUNT(*) as total'))
            ->groupBy('commune_1')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // ---- Dossiers les plus “lents”
        $slowestProjects = (clone $base)
            ->select('id','numero_dossier','commune_1','etat', DB::raw('DATEDIFF(CURDATE(), date_arrivee) as age_jours'))
            ->orderByDesc('age_jours')
            ->limit(10)
            ->get();

        // ---- Répartition par catégorie (si CSV ' | ' ; si JSON adapte)
        // Heuristique simple: on éclate côté PHP (ok pour volumes ~quelques milliers)
        $cats = (clone $base)->whereNotNull('categorie_projet')->pluck('categorie_projet');
        $catCounter = [];
        foreach ($cats as $line) {
            // CSV "A | B | C"
            foreach (array_filter(array_map('trim', explode('|', (string)$line))) as $c) {
                $catCounter[$c] = ($catCounter[$c] ?? 0) + 1;
            }
        }
        $projetsParCategorie = collect($catCounter)
            ->map(fn($v,$k)=> (object)['categorie'=>$k,'total'=>$v])
            ->sortByDesc('total')->values();

        // ---- Répartition par agent (user)
        $projetsParUser = (clone $base)
            ->select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')->orderByDesc('total')->get()
            ->map(function($row){
                $row->user_name = optional(User::find($row->user_id))->name ?? '—';
                return $row;
            });

        // ---- AVIS depuis le dernier examen de chaque dossier (si examens existent)
        // On prend le dernier numero_examen (global), et on groupe par avis
        $lastAvis = Examen::select('grand_projet_id','avis')
            ->whereIn('grand_projet_id', (clone $base)->pluck('id'))
            ->whereIn('id', function($sub){
                $sub->select(DB::raw('MAX(id)'))
                    ->from('examens')
                    ->groupBy('grand_projet_id');
            })
            ->get()
            ->groupBy('avis')
            ->map(fn($c)=> $c->count());

        $avisBreakdown = collect(['favorable','defavorable','ajourne','sans_avis'])
            ->map(fn($k)=> (object)['avis'=>$k,'total'=>(int)($lastAvis[$k] ?? 0)])
            ->filter(fn($o)=> $o->total>0)
            ->values();

        // ---- Emplois & logements (somme)
        $emploisPrevus = (clone $base)->sum('emplois_prevus');
        $logementsPrevus = (clone $base)->sum('nb_logements');

        // ---- “Pertes potentielles” (défavorable)
        $defavQuery = (clone $base)->whereIn('id', function($q){
            $q->select('grand_projet_id')->from('examens')
              ->where('avis','defavorable');
        });
        $emploisPerdus   = (clone $defavQuery)->sum('emplois_prevus');
        $logementsPerdus = (clone $defavQuery)->sum('nb_logements');

        // ---- Provinces: délai moyen jusqu’à “clos” (approx âge sur états finals)
        $perfProvince = (clone $base)
            ->whereIn('etat', $finalStates)
            ->select('province', DB::raw('COUNT(*) as total'),
                     DB::raw('AVG(DATEDIFF(CURDATE(), date_arrivee)) as avg_jours'))
            ->groupBy('province')
            ->orderBy('province')
            ->get();

        // ---- Listes utiles pour filtres
        $provinces = GrandProjet::select('province')->distinct()->orderBy('province')->pluck('province');
        $users     = User::orderBy('name')->get(['id','name']);

        return view('chef.stats.index', compact(
            'type','from','to','prov','agent',
            'totalProjets',
            'projetsParEtat','projetsParPrefecture','projetsParMois',
            'throughputMois','agingBuckets','avgDelaiParEtat',
            'topCommunes','slowestProjects','projetsParCategorie','projetsParUser',
            'avisBreakdown',
            'emploisPrevus','logementsPrevus','emploisPerdus','logementsPerdus',
            'perfProvince','provinces','users'
        ));
    }
}
