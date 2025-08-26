@extends('layouts.app')

@section('content')
@php
  use Carbon\Carbon;
  use Illuminate\Support\Str;
@endphp

<style>
  /* ======= Hero / bandeau ======= */
  .gp-hero {
    position: relative;
    border-radius: 14px;
    overflow: hidden;
    background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 50%, #0ea5e9 100%);
    color: #fff;
  }
  .gp-hero .blur-bg {
    position:absolute; inset:0;
    background-image: radial-gradient(1200px 300px at 10% -20%, rgba(255,255,255,.12), transparent),
                      radial-gradient(1000px 300px at 90% 120%, rgba(255,255,255,.10), transparent);
    filter: blur(1px);
  }
  .gp-hero .content { position:relative; z-index:2; padding: 22px 24px; }
  .gp-hero h2 { font-weight: 800; letter-spacing:.3px; }
  .chip { display:inline-flex; align-items:center; gap:.4rem;
          padding:.25rem .6rem; border-radius:999px; font-size:.8rem; font-weight:600;
          background: rgba(255,255,255,.16); color:#fff; border:1px solid rgba(255,255,255,.25); }
  .chip .dot{ width:8px;height:8px;border-radius:999px;background:#22c55e; display:inline-block; }
  .chip.warn .dot{ background:#f59e0b } .chip.danger .dot{ background:#ef4444 }
  .hero-actions .btn { border-radius: 10px; font-weight: 600; box-shadow: 0 4px 14px rgba(0,0,0,.12); }
  .section-title { font-weight: 700; color:#1e293b; letter-spacing:.2px; }
  .list-tile { border:1px solid #eef2f7; border-radius:10px; padding:12px 14px; margin-bottom:8px; background:#fff; }
  .list-tile strong { color:#334155 } .list-tile .muted { color:#64748b }
  .table thead th { font-weight:700; color:#334155; background:#f8fafc!important; }
  .badge-soft { background:#f1f5f9; color:#0f172a; font-weight:700; border:1px solid #e2e8f0; }
  .tracker-shell{ background:#f1f5f9; border:1px solid #e2e8f0; border-radius:10px; padding:10px 12px; margin-top:12px; }
</style>

<div class="container">
  {{-- ====== HERO ====== --}}
  <div class="gp-hero mb-4 shadow-sm">
    <div class="blur-bg"></div>
    <div class="content d-flex flex-wrap align-items-center justify-content-between">
      <div class="mb-2">
        <h2 class="mb-1">Détails du Grand Projet — <span class="opacity-75">CPC</span></h2>
        <div class="d-flex flex-wrap align-items-center gap-2">
          @php
            $etat = $cpc->etat;
            $stateColor = 'chip';
            if (in_array($etat,['retour_bs','retour_dgu'])) $stateColor = 'chip warn';
            if ($etat==='archive') $stateColor='chip danger';
          @endphp
          <span class="{{ $stateColor }}"><span class="dot"></span> État : {{ $etat }}</span>

          @if($cpc->isFavorable())
            <span class="chip"><span class="dot"></span> Avis final : Favorable</span>
          @endif

          <span class="chip" title="Numéro de dossier"><i class="fas fa-hashtag"></i> {{ $cpc->numero_dossier }}</span>

          @if($cpc->lien_ged)
            <a class="chip text-decoration-none" href="{{ $cpc->lien_ged }}" target="_blank"><i class="fas fa-folder-open"></i> GED</a>
          @endif
        </div>
      </div>

      <div class="hero-actions d-flex flex-wrap gap-2">
        @if(Auth::user()->hasRole('chef'))
          <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour (Chef)</a>
        @elseif(Auth::user()->hasRole('saisie_cpc'))
          <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour (Saisie)</a>
        @else
          <a href="#" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @endif

        {{-- Avis Commission interne --}}
        @if($cpc->etat === 'comm_interne' && !$cpc->isFavorable())
          @if(Auth::user()->hasRole('chef'))
            <a class="btn btn-warning text-white" href="{{ route('chef.grandprojets.cpc.examens.create', $cpc) }}">
              <i class="fas fa-gavel me-1"></i> Avis (Examen n° {{ $cpc->next_numero_examen }})
            </a>
          @else
            <a class="btn btn-warning text-white" href="{{ route('saisie_cpc.cpc.examens.create', $cpc) }}">
              <i class="fas fa-gavel me-1"></i> Avis (Examen n° {{ $cpc->next_numero_examen }})
            </a>
          @endif
        @endif
      </div>
    </div>

    <div class="content pt-0">
      <div class="tracker-shell">
        @include('components.tracker', ['etatCourant' => $cpc->etat])
      </div>
    </div>
  </div>

  {{-- ====== INFOS ====== --}}
  <div class="row g-4">
    <div class="col-lg-6">
      <h5 class="section-title mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Identification & Localisation</h5>
      <div class="list-tile"><strong>Numéro de Dossier :</strong> {{ $cpc->numero_dossier }}</div>
      <div class="list-tile"><strong>Province / Préfecture :</strong> {{ $cpc->province }}</div>
      <div class="list-tile"><strong>Commune principale :</strong> {{ $cpc->commune_1 }}</div>
      @if($cpc->commune_2)<div class="list-tile"><strong>Commune à cheval :</strong> {{ $cpc->commune_2 }}</div>@endif
      <div class="list-tile"><strong>Date d’Arrivée :</strong> {{ $cpc->date_arrivee ? Carbon::parse($cpc->date_arrivee)->format('d/m/Y') : '—' }}</div>
    </div>
    <div class="col-lg-6">
      <h5 class="section-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informations du Projet</h5>
      <div class="list-tile"><strong>Pétitionnaire :</strong> {{ $cpc->petitionnaire }}</div>
      <div class="list-tile"><strong>Catégorie :</strong> {{ $cpc->categorie_petitionnaire }}</div>
      <div class="list-tile"><strong>Intitulé :</strong> {{ $cpc->intitule_projet }}</div>
    </div>
  </div>

  {{-- ====== HISTORIQUE DES AVIS ====== --}}
  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-white"><h5><i class="fas fa-gavel me-2 text-primary"></i>Historique des avis</h5></div>
    <div class="card-body">
      @if($cpc->examens->count())
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Type</th><th>Date</th><th>Avis</th><th>Par</th><th>Observations</th></tr></thead>
          <tbody>
            @foreach($cpc->examens as $ex)
              <tr>
                <td>{{ $ex->numero_examen }}</td>
                <td>{{ $ex->type_examen ?? 'interne' }}</td>
                <td>{{ ($ex->date_examen ?? $ex->date_commission) ? Carbon::parse($ex->date_examen ?? $ex->date_commission)->format('d/m/Y') : '—' }}</td>
                <td>{{ ucfirst($ex->avis) }}</td>
                <td>{{ optional($ex->auteur)->name ?? '—' }}</td>
                <td>{{ Str::limit($ex->observations,100) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="alert alert-info">Aucun examen enregistré.</div>
      @endif
    </div>
  </div>

  {{-- ====== JOURNAL NAVETTE ====== --}}
  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-white"><h5><i class="fas fa-route me-2 text-primary"></i>Journal de circulation (navette)</h5></div>
    @php
      $rows = ($fluxAsc ?? collect())->values();
      $fmtDuration = fn($s)=>$s<=0?'—':(intdiv($s,86400)?intdiv($s,86400).' j ':'').(intdiv($s%86400,3600)?intdiv($s%86400,3600).' h ':'').(intdiv($s%3600,60)).' min';
      $timeline=[]; if($rows->count()){ $created=Carbon::parse($cpc->created_at); $entered=[]; $first=$rows->first(); $initial=$first->from_etat??'enregistrement'; $entered[$initial]=$created;
        foreach($rows as $flux){$at=Carbon::parse($flux->happened_at);$from=$flux->from_etat;$to=$flux->to_etat;$enter=$entered[$from]??$created;$secs=$at->diffInSeconds($enter);
          $timeline[]=['etat'=>$from,'entree'=>$enter,'sortie'=>$at,'duree'=>$secs,'par'=>optional($flux->auteur)->name,'note'=>$flux->note];$entered[$to]=$at;}
        $cur=$cpc->etat??$rows->last()->to_etat??$initial;$enter=$entered[$cur]??$created;
        $timeline[]=['etat'=>$cur,'entree'=>$enter,'sortie'=>null,'duree'=>Carbon::now()->diffInSeconds($enter),'par'=>null,'note'=>'État courant'];
        $total=Carbon::now()->diffInSeconds($created);
      }
    @endphp
    <div class="card-body">
      @if(!empty($timeline))
        <table class="table table-sm align-middle">
          <thead><tr><th>État</th><th>Entrée</th><th>Sortie</th><th>Durée</th><th>Action par</th><th>Note</th></tr></thead>
          <tbody>
            @foreach($timeline as $row)
              <tr>
                <td>{{ $row['etat'] }}</td>
                <td>{{ $row['entree']->format('d/m/Y H:i') }}</td>
                <td>@if($row['sortie']){{ $row['sortie']->format('d/m/Y H:i') }}@else<span class="badge badge-soft">en cours…</span>@endif</td>
                <td>{{ $fmtDuration($row['duree']) }}</td>
                <td>{{ $row['par']??'—' }}</td>
                <td>{{ $row['note'] }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot><tr><th colspan="3" class="text-end">Durée totale :</th><th>{{ $fmtDuration($total??0) }}</th><th colspan="2"></th></tr></tfoot>
        </table>
      @else <div class="alert alert-light border">Aucun mouvement enregistré.</div>@endif
    </div>
  </div>

  {{-- ====== ACTIONS ====== --}}
  @if(Auth::user()->hasRole('chef'))
    <div class="d-flex justify-content-between mt-4">
      <a href="{{ route('chef.grandprojets.cpc.edit',$cpc) }}" class="btn btn-outline-primary"><i class="fas fa-edit me-1"></i> Modifier</a>
      <form action="{{ route('chef.grandprojets.cpc.destroy',$cpc) }}" method="POST" onsubmit="return confirm('Supprimer ce projet ?');">
        @csrf @method('DELETE')
        <button class="btn btn-danger"><i class="fas fa-trash me-1"></i> Supprimer</button>
      </form>
    </div>
  @endif
</div>
@endsection
