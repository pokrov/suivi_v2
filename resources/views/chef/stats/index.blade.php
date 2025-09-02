@extends('layouts.app')

@section('content')
@php
  $fmt = fn($n) => number_format((float)$n, 0, ',', ' ');
@endphp

<style>
  .kpi-card{border:1px solid #eef2f7;border-radius:14px}
  .kpi-card .value{font-size:1.9rem;font-weight:800;letter-spacing:.3px}
  .tiny{font-size:.85rem;color:#64748b}
  .chart-box{height:320px}
  .chart-box-lg{height:360px}
  .table thead th{background:#f8fafc}
  .badge-soft{background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a}
</style>

<div class="container">

  <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
    <div>
      <h2 class="mb-1">📊 Statistiques — {{ strtoupper($type) === 'ALL' ? 'Tous (CPC + CLM)' : strtoupper($type) }}</h2>
      <div class="tiny">Mise à jour : {{ now()->format('d/m/Y H:i') }}</div>
    </div>

    {{-- Filtres --}}
    <form class="row g-2" method="GET">
      <div class="col-auto">
        <select name="type" class="form-select">
          <option value="cpc" {{ $type==='cpc'?'selected':'' }}>CPC</option>
          <option value="clm" {{ $type==='clm'?'selected':'' }}>CLM</option>
          <option value="all" {{ $type==='all'?'selected':'' }}>Tous</option>
        </select>
      </div>
      <div class="col-auto">
        <input type="date" name="from" class="form-control" value="{{ $from }}">
      </div>
      <div class="col-auto">
        <input type="date" name="to" class="form-control" value="{{ $to }}">
      </div>
      <div class="col-auto">
        <select name="province" class="form-select">
          <option value="">Toutes provinces</option>
          @foreach($provinces as $p)
            <option value="{{ $p }}" {{ $prov===$p?'selected':'' }}>{{ $p }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <select name="user_id" class="form-select">
          <option value="">Tous agents</option>
          @foreach($users as $u)
            <option value="{{ $u->id }}" {{ (string)$agent===(string)$u->id?'selected':'' }}>{{ $u->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-primary"><i class="fas fa-filter me-1"></i>Filtrer</button>
      </div>
    </form>
  </div>

  {{-- ====== KPIs ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Total dossiers</div>
        <div class="value">{{ $fmt($totalProjets ?? 0) }}</div>
        <div class="tiny">Tous états confondus</div>
      </div>
    </div>

    @php
      $enCours = collect($projetsParEtat ?? [])->whereNotIn('etat', ['favorable','defavorable','archive','signature_3','retour_bs'])->sum('total');
      $favorables = collect($avisBreakdown ?? [])->firstWhere('avis','favorable')->total ?? 0;
      $defavorables = collect($avisBreakdown ?? [])->firstWhere('avis','defavorable')->total ?? 0;
      $avgGlobale = collect($avgDelaiParEtat ?? [])->avg('avg_jours') ?? null;
    @endphp

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Dossiers en cours</div>
        <div class="value">{{ $fmt($enCours) }}</div>
        <div class="tiny">Hors états finaux</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Emplois prévus</div>
        <div class="value text-success">{{ $fmt($emploisPrevus ?? 0) }}</div>
        <div class="tiny">Potentiel total</div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Logements prévus</div>
        <div class="value text-success">{{ $fmt($logementsPrevus ?? 0) }}</div>
        <div class="tiny">Potentiel total</div>
      </div>
    </div>
  </div>

  {{-- ====== KPIs 2 (pertes potentielles & délai) ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Avis favorables</div>
        <div class="value text-success">{{ $fmt($favorables) }}</div>
        <div class="tiny">Dernier avis</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Avis défavorables</div>
        <div class="value text-danger">{{ $fmt($defavorables) }}</div>
        <div class="tiny">Dernier avis</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Emplois “perdus”</div>
        <div class="value text-danger">{{ $fmt($emploisPerdus ?? 0) }}</div>
        <div class="tiny">Sur dossiers défavorables</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="p-3 kpi-card bg-white shadow-sm">
        <div class="tiny">Logements “perdus”</div>
        <div class="value text-danger">{{ $fmt($logementsPerdus ?? 0) }}</div>
        <div class="tiny">Sur dossiers défavorables</div>
      </div>
    </div>
  </div>

  {{-- ====== Charts rangée 1 ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition par état</div>
        <div class="card-body">
          @if(collect($projetsParEtat)->count())
            <div class="chart-box"><canvas id="chartEtats"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucune donnée d’état.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition par avis (dernier)</div>
        <div class="card-body">
          @if(collect($avisBreakdown)->count())
            <div class="chart-box"><canvas id="chartAvis"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucun avis enregistré.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Répartition par province</div>
        <div class="card-body">
          @if(collect($projetsParPrefecture)->count())
            <div class="chart-box"><canvas id="chartPref"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucune donnée de province.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Charts rangée 2 ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Flux mensuel (créations vs clos)</div>
        <div class="card-body">
          @if(collect($throughputMois)->count() || collect($projetsParMois)->count())
            <div class="chart-box-lg"><canvas id="chartThroughput"></canvas></div>
            <div class="tiny mt-2">“Clos” = états finaux (signature_3 / retour_bs / archive / favorable / défavorable).</div>
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
          @if(collect($agingBuckets)->count())
            <div class="chart-box"><canvas id="chartAging"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucun bucket d’âge fourni.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Délais moyens & Top communes ====== --}}
  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Délais moyens par état (jours)</div>
        <div class="card-body">
          @if(collect($avgDelaiParEtat)->count())
            <div class="chart-box"><canvas id="chartLeadTimes"></canvas></div>
          @else
            <div class="alert alert-light border mb-0">Aucun calcul de délais moyens fourni.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Top 5 communes</div>
        <div class="card-body">
          @if(collect($topCommunes)->count())
            <ul class="list-group list-group-flush">
              @foreach($topCommunes as $c)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span>{{ $c->commune_1 ?? '—' }}</span>
                  <span class="badge bg-primary rounded-pill">{{ $fmt($c->total ?? 0) }}</span>
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

  {{-- ====== Trois listes opérationnelles ====== --}}
  <div class="row g-3">
    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Catégories (volume)</div>
        <div class="card-body">
          @if(collect($projetsParCategorie)->count())
            <ul class="list-group list-group-flush">
              @foreach($projetsParCategorie as $row)
                <li class="list-group-item d-flex justify-content-between">
                  <span>{{ $row->categorie }}</span>
                  <span class="fw-semibold">{{ $fmt($row->total) }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="alert alert-light border mb-0">Aucune catégorie trouvée.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Agents (volume)</div>
        <div class="card-body">
          @if(collect($projetsParUser)->count())
            <ul class="list-group list-group-flush">
              @foreach($projetsParUser as $row)
                <li class="list-group-item d-flex justify-content-between">
                  <span>{{ $row->user_name }}</span>
                  <span class="fw-semibold">{{ $fmt($row->total) }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div class="alert alert-light border mb-0">Aucun agent associé.</div>
          @endif
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Performance par province</div>
        <div class="card-body">
          @if(collect($perfProvince)->count())
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead><tr><th>Province</th><th class="text-end">Total</th><th class="text-end">Délai moyen</th></tr></thead>
                <tbody>
                  @foreach($perfProvince as $r)
                    <tr>
                      <td>{{ $r->province ?? '—' }}</td>
                      <td class="text-end">{{ $fmt($r->total ?? 0) }}</td>
                      <td class="text-end">{{ $r->avg_jours ? number_format($r->avg_jours,1,',',' ') : '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-light border mb-0">Aucune province.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- ====== Dossiers les plus lents ====== --}}
  <div class="row g-3 mt-3">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-header bg-white fw-semibold">Top 10 — Dossiers les plus “lents”</div>
        <div class="card-body">
          @if(collect($slowestProjects)->count())
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
                  @foreach($slowestProjects as $i => $p)
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
            <div class="alert alert-light border mb-0">Aucun dossier “lent”.</div>
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
  const has = (arr) => Array.isArray(arr) && arr.length > 0;
  const ctx = id => document.getElementById(id)?.getContext('2d');

  // Données
  const etats  = @json(collect($projetsParEtat)->pluck('etat'));
  const vEtats = @json(collect($projetsParEtat)->pluck('total'));

  const avisLabs  = @json(collect($avisBreakdown)->pluck('avis'));
  const avisVals  = @json(collect($avisBreakdown)->pluck('total'));

  const prefs = @json(collect($projetsParPrefecture)->pluck('province'));
  const vPref = @json(collect($projetsParPrefecture)->pluck('total'));

  const thMois = @json(collect($throughputMois)->pluck('mois'));
  const thC    = @json(collect($throughputMois)->pluck('crees'));
  const thF    = @json(collect($throughputMois)->pluck('clos'));

  const agingLabs = @json(collect($agingBuckets)->pluck('bucket'));
  const agingVals = @json(collect($agingBuckets)->pluck('total'));

  const ltLabs = @json(collect($avgDelaiParEtat)->pluck('etat'));
  const ltVals = @json(collect($avgDelaiParEtat)->pluck('avg_jours'));

  // Etats
  if (has(etats) && ctx('chartEtats')) {
    new Chart(ctx('chartEtats'), {
      type: 'doughnut',
      data: { labels: etats, datasets: [{ data: vEtats }] },
      options: { plugins: { legend: { position: 'bottom' }}, maintainAspectRatio:false }
    });
  }
  // Avis
  if (has(avisLabs) && ctx('chartAvis')) {
    new Chart(ctx('chartAvis'), {
      type: 'pie',
      data: { labels: avisLabs, datasets: [{ data: avisVals }] },
      options: { plugins: { legend: { position: 'bottom' }}, maintainAspectRatio:false }
    });
  }
  // Provinces
  if (has(prefs) && ctx('chartPref')) {
    new Chart(ctx('chartPref'), {
      type: 'bar',
      data: { labels: prefs, datasets: [{ label:'Projets', data: vPref }] },
      options: { plugins:{ legend:{ display:false }}, maintainAspectRatio:false }
    });
  }
  // Throughput
  if (has(thMois) && ctx('chartThroughput')) {
    new Chart(ctx('chartThroughput'), {
      type: 'line',
      data: {
        labels: thMois,
        datasets: [
          { label:'Créés', data: thC, tension:.3, fill:true },
          { label:'Clos',  data: thF, tension:.3, fill:true }
        ]
      },
      options: { plugins:{ legend:{ position:'bottom' }}, maintainAspectRatio:false }
    });
  }
  // Aging
  if (has(agingLabs) && ctx('chartAging')) {
    new Chart(ctx('chartAging'), {
      type: 'bar',
      data: { labels: agingLabs, datasets: [{ label:'Dossiers', data: agingVals }] },
      options: { indexAxis:'y', plugins:{ legend:{ display:false }}, maintainAspectRatio:false }
    });
  }
  // Lead time
  if (has(ltLabs) && ctx('chartLeadTimes')) {
    new Chart(ctx('chartLeadTimes'), {
      type: 'bar',
      data: { labels: ltLabs, datasets: [{ label:'Jours', data: ltVals }] },
      options: { plugins:{ legend:{ display:false }}, maintainAspectRatio:false }
    });
  }
})();
</script>
@endsection
