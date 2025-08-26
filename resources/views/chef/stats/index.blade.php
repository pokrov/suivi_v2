@extends('layouts.app')

@section('content')
@php
  // ====== Garde-fous si certains jeux de données ne sont pas passés par le contrôleur
  $totalProjets          = $totalProjets          ?? 0;

  // Collections/arrays de {etat, total} ; {province, total} ; {mois, total}
  $projetsParEtat        = collect($projetsParEtat        ?? []);
  $projetsParPrefecture  = collect($projetsParPrefecture  ?? []);
  $projetsParMois        = collect($projetsParMois        ?? []);
  $topCommunes           = collect($topCommunes           ?? []);
  $agingBuckets          = collect($agingBuckets          ?? []);     // {bucket, total}
  $avgDelaiParEtat       = collect($avgDelaiParEtat       ?? []);     // {etat, avg_jours}
  $slowestProjects       = collect($slowestProjects       ?? []);     // collection de GrandProjet avec ->numero_dossier, ->commune_1, ->etat, ->age_jours
  $throughputMois        = collect($throughputMois        ?? []);     // {mois, crees, clos}

  // Petites aides d’affichage
  $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp

<style>
  .kpi-card{border:1px solid #eef2f7;border-radius:14px}
  .kpi-card .value{font-size:2rem;font-weight:800;letter-spacing:.3px}
  .tiny{font-size:.85rem;color:#64748b}
  .chart-box{height:320px}
  .chart-box-lg{height:360px}
  .table thead th{background:#f8fafc}
  .badge-soft{background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a}
</style>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="mb-0">📊 Statistiques — Projets CPC</h2>
    <div class="tiny">Mise à jour : {{ now()->format('d/m/Y H:i') }}</div>
  </div>

  {{-- ====== KPIs ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Total des projets</div>
        <div class="value">{{ $fmt($totalProjets) }}</div>
        <div class="tiny">Tous états confondus</div>
      </div>
    </div>

    @php
      $enCours = $projetsParEtat->whereNotIn('etat', ['favorable','defavorable','archive'])->sum('total');
      $favorables = $projetsParEtat->where('etat','favorable')->sum('total');
      $defavorables = $projetsParEtat->where('etat','defavorable')->sum('total');
      // Durée moyenne globale si fournie via avgDelaiParEtat (moyenne simple des moyennes)
      $avgGlobale = $avgDelaiParEtat->avg('avg_jours') ?? null;
    @endphp

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Dossiers en cours</div>
        <div class="value">{{ $fmt($enCours) }}</div>
        <div class="tiny">Hors favorables/défavorables</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Favorables</div>
        <div class="value text-success">{{ $fmt($favorables) }}</div>
        <div class="tiny">Avis final favorable</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Délai moyen (jours)</div>
        <div class="value">{{ $avgGlobale ? number_format($avgGlobale,1,',',' ') : '—' }}</div>
        <div class="tiny">Moyenne des délais par état</div>
      </div>
    </div>
  </div>

  {{-- ====== Charts rangée 1 ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition par état</div>
        <div class="card-body">
          @if($projetsParEtat->count())
            <div class="chart-box"><canvas id="chartEtats"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucune donnée d’état.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition par préfecture</div>
        <div class="card-body">
          @if($projetsParPrefecture->count())
            <div class="chart-box"><canvas id="chartPref"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucune donnée de préfecture.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Charts rangée 2 ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Flux mensuel (créations vs dossiers clos)</div>
        <div class="card-body">
          @if($throughputMois->count() || $projetsParMois->count())
            <div class="chart-box-lg"><canvas id="chartThroughput"></canvas></div>
            <div class="tiny mt-2">“Clos” = favorables + défavorables (ou tout état final si vous en avez d’autres).</div>
          @else
            <div class="alert alert-light border mb-0">Pas de séries mensuelles disponibles.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Âge des dossiers (buckets)</div>
        <div class="card-body">
          @if($agingBuckets->count())
            <div class="chart-box"><canvas id="chartAging"></canvas></div>
            <div class="tiny mt-2">Permet de repérer le stock “vieux” &agrave; traiter.</div>
          @else
            <div class="alert alert-light border mb-0">Aucun bucket d’âge fourni.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Délais moyens par état + Top communes ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Délais moyens par état (jours)</div>
        <div class="card-body">
          @if($avgDelaiParEtat->count())
            <div class="chart-box"><canvas id="chartLeadTimes"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucun calcul de délais moyens fourni.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Top 5 communes (volume)</div>
        <div class="card-body">
          @if($topCommunes->count())
            <ul class="list-group list-group-flush">
              @foreach($topCommunes as $c)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>{{ $c->commune_1 ?? $c['commune_1'] ?? '—' }}</span>
                  <span class="badge bg-primary rounded-pill">{{ $fmt($c->total ?? $c['total'] ?? 0) }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="alert alert-light border mb-0">Pas de top communes.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Tables opérationnelles ====== --}}
  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Dossiers les plus lents (top 10 par ancienneté)</div>
        <div class="card-body">
          @if($slowestProjects->count())
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>N° dossier</th>
                    <th>Commune</th>
                    <th>État</th>
                    <th>Âge (jours)</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($slowestProjects->take(10) as $i => $p)
                    <tr>
                      <td>{{ $i+1 }}</td>
                      <td class="fw-semibold">{{ $p->numero_dossier ?? '—' }}</td>
                      <td>{{ $p->commune_1 ?? '—' }}</td>
                      <td><span class="badge badge-soft">{{ $p->etat ?? '—' }}</span></td>
                      <td class="fw-semibold">{{ $p->age_jours ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-light border mb-0">Aucun dossier “lent” détecté.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition brute (liste)</div>
        <div class="card-body">
          @if($projetsParEtat->count())
            <ul class="list-group list-group-flush">
              @foreach($projetsParEtat as $row)
                <li class="list-group-item d-flex justify-content-between">
                  <span class="text-capitalize">{{ $row->etat ?? $row['etat'] }}</span>
                  <span class="fw-semibold">{{ $fmt($row->total ?? $row['total']) }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="alert alert-light border mb-0">Aucune répartition disponible.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ====== Chart.js ====== --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
  // Helpers
  const has = (arr) => Array.isArray(arr) && arr.length > 0;

  // ====== Données depuis PHP
  const etats      = @json($projetsParEtat->pluck('etat'));
  const etatsVals  = @json($projetsParEtat->pluck('total'));

  const prefs      = @json($projetsParPrefecture->pluck('province'));
  const prefsVals  = @json($projetsParPrefecture->pluck('total'));

  // Throughput : si throughputMois est fournit on l’utilise, sinon on retombe sur $projetsParMois (créations)
  const thMois     = @json($throughputMois->pluck('mois'));
  const thCrees    = @json($throughputMois->pluck('crees'));
  const thClos     = @json($throughputMois->pluck('clos'));
  const moisAlt    = @json($projetsParMois->pluck('mois'));
  const moisAltVal = @json($projetsParMois->pluck('total'));

  const agingLabs  = @json($agingBuckets->pluck('bucket'));
  const agingVals  = @json($agingBuckets->pluck('total'));

  const ltLabs     = @json($avgDelaiParEtat->pluck('etat'));
  const ltVals     = @json($avgDelaiParEtat->pluck('avg_jours'));

  // ====== Charts
  const mk = (id) => document.getElementById(id)?.getContext('2d');

  // Etats (doughnut)
  if (has(etats) && mk('chartEtats')) {
    new Chart(mk('chartEtats'), {
      type: 'doughnut',
      data: { labels: etats, datasets: [{ data: etatsVals }] },
      options: { plugins:{ legend:{ position:'bottom' }}, maintainAspectRatio:false }
    });
  }

  // Préfectures (bar)
  if (has(prefs) && mk('chartPref')) {
    new Chart(mk('chartPref'), {
      type: 'bar',
      data: { labels: prefs, datasets: [{ label:'Projets', data: prefsVals }] },
      options: { plugins:{ legend:{ display:false }}, maintainAspectRatio:false }
    });
  }

  // Throughput (line ou stacked)
  if ((has(thMois) || has(moisAlt)) && mk('chartThroughput')) {
    const labels = has(thMois) ? thMois : moisAlt;
    const dataC  = has(thMois) ? thCrees : moisAltVal;
    const dataF  = has(thMois) ? thClos  : new Array(labels.length).fill(0);

    new Chart(mk('chartThroughput'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label:'Créés', data: dataC, tension:.3, fill:true },
          { label:'Clos',  data: dataF, tension:.3, fill:true }
        ]
      },
      options: { maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
    });
  }

  // Aging buckets (bar horizontal)
  if (has(agingLabs) && mk('chartAging')) {
    new Chart(mk('chartAging'), {
      type: 'bar',
      data: { labels: agingLabs, datasets: [{ label:'Dossiers', data: agingVals }] },
      options: {
        indexAxis: 'y',
        plugins:{ legend:{ display:false } },
        maintainAspectRatio:false
      }
    });
  }

  // Lead time par état (bar)
  if (has(ltLabs) && mk('chartLeadTimes')) {
    new Chart(mk('chartLeadTimes'), {
      type: 'bar',
      data: { labels: ltLabs, datasets: [{ label:'Jours', data: ltVals }] },
      options: { plugins:{ legend:{ display:false }}, maintainAspectRatio:false }
    });
  }
})();
</script>
@endsection
