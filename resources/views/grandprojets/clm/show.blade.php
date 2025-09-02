{{-- resources/views/grandprojets/clm/show.blade.php --}}
@extends('layouts.app')

@section('content')
@php
  use Carbon\Carbon; use Illuminate\Support\Str;
  $finalAvis = optional($grandProjet->lastExamen)->avis; // favorable | defavorable | null
@endphp

<style>
  .gp-hero{position:relative;border-radius:14px;overflow:hidden;background:linear-gradient(135deg,#0ea5e9 0%,#2563eb 50%,#0ea5e9 100%);color:#fff}
  .gp-hero .content{position:relative;z-index:2;padding:22px 24px}
  .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.25rem .6rem;border-radius:999px;font-size:.8rem;font-weight:600;background:rgba(255,255,255,.16);color:#fff;border:1px solid rgba(255,255,255,.25)}
  .chip .dot{width:8px;height:8px;border-radius:999px;background:#22c55e}.chip.danger .dot{background:#ef4444}
  .section-title{font-weight:700;color:#1e293b}
  .list-tile{border:1px solid #eef2f7;border-radius:10px;padding:12px 14px;margin-bottom:8px;background:#fff}
  .tracker-shell{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;margin-top:12px}
</style>

<div class="container">
  <div class="gp-hero mb-4 shadow-sm">
    <div class="content d-flex flex-wrap align-items-center justify-content-between">
      <div class="mb-2">
        <h2 class="mb-1">Détails Grand Projet — <span class="opacity-75">CLM</span></h2>
        <div class="d-flex flex-wrap align-items-center gap-2">
          <span class="chip"><span class="dot"></span> État : {{ $grandProjet->etat }}</span>
          @if($finalAvis === 'favorable')
            <span class="chip"><span class="dot"></span> Avis : Favorable</span>
          @elseif($finalAvis === 'defavorable')
            <span class="chip danger"><span class="dot"></span> Avis : Défavorable</span>
          @endif
          <span class="chip"><i class="fas fa-hashtag"></i> {{ $grandProjet->numero_dossier }}</span>
          @if($grandProjet->lien_ged)
            <a class="chip text-decoration-none" href="{{ $grandProjet->lien_ged }}" target="_blank"><i class="fas fa-folder-open"></i> GED</a>
          @endif
        </div>
      </div>

      <div class="d-flex flex-wrap gap-2">
        @if(Auth::user()->hasRole('chef'))
          <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @else
          <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Retour</a>
        @endif
      </div>
    </div>

    <div class="content pt-0">
      <div class="tracker-shell">
        @include('components.tracker', ['etatCourant'=>$grandProjet->etat, 'finalAvis'=>$finalAvis])
      </div>
    </div>
  </div>

  {{-- Infos --}}
  <div class="row g-4">
    <div class="col-lg-6">
      <h5 class="section-title mb-3"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Identification & Localisation</h5>
      <div class="list-tile"><strong>N° Dossier :</strong> {{ $grandProjet->numero_dossier }}</div>
      <div class="list-tile"><strong>Province / Préfecture :</strong> {{ $grandProjet->province }}</div>
      <div class="list-tile"><strong>Commune principale :</strong> {{ $grandProjet->commune_1 }}</div>
      @if($grandProjet->commune_2)
        <div class="list-tile"><strong>Commune à cheval :</strong> {{ $grandProjet->commune_2 }}</div>
      @endif
      <div class="list-tile"><strong>Date d’Arrivée :</strong> {{ $grandProjet->date_arrivee ? Carbon::parse($grandProjet->date_arrivee)->format('d/m/Y') : '—' }}</div>
    </div>
    <div class="col-lg-6">
      <h5 class="section-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informations du Projet</h5>
      <div class="list-tile"><strong>Pétitionnaire :</strong> {{ $grandProjet->petitionnaire }}</div>
      <div class="list-tile"><strong>Catégorie pétitionnaire :</strong> {{ $grandProjet->categorie_petitionnaire }}</div>
      <div class="list-tile"><strong>Catégorie projet :</strong> {{ $grandProjet->categorie_projet }}</div>
      <div class="list-tile"><strong>Intitulé :</strong> {{ $grandProjet->intitule_projet }}</div>
    </div>
  </div>

  {{-- Historique des avis --}}
  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-white"><h5><i class="fas fa-gavel me-2 text-primary"></i>Historique des avis</h5></div>
    <div class="card-body">
      @if($grandProjet->examens->count())
        <table class="table table-hover align-middle">
          <thead><tr><th>#</th><th>Date</th><th>Avis</th><th>Par</th><th>Observations</th></tr></thead>
          <tbody>
            @foreach($grandProjet->examens as $ex)
              <tr>
                <td>{{ $ex->numero_examen }}</td>
                <td>{{ ($ex->date_examen ?? $ex->date_commission) ? Carbon::parse($ex->date_examen ?? $ex->date_commission)->format('d/m/Y') : '—' }}</td>
                <td>{{ ucfirst($ex->avis) }}</td>
                <td>{{ optional($ex->auteur)->name ?? '—' }}</td>
                <td>{{ \Illuminate\Support\Str::limit($ex->observations,100) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @else
        <div class="alert alert-info mb-0">Aucun examen enregistré.</div>
      @endif
    </div>
  </div>

  {{-- Journal navette (même rendu que CPC) --}}
  @php
    $rows = ($fluxAsc ?? collect())->values();
    $fmt = fn($s)=>$s<=0?'—':(intdiv($s,86400)?intdiv($s,86400).' j ':'').(intdiv($s%86400,3600)?intdiv($s%86400,3600).' h ':'').(intdiv($s%3600,60)).' min';
    $timeline=[];
    if($rows->count()){
      $created=Carbon::parse($grandProjet->created_at); $entered=[];
      $first=$rows->first(); $initial=$first->from_etat??'transmis_dajf'; $entered[$initial]=$created;
      foreach($rows as $flux){
        $at=Carbon::parse($flux->happened_at); $from=$flux->from_etat; $to=$flux->to_etat;
        $enter=$entered[$from]??$created; $secs=$at->diffInSeconds($enter);
        $timeline[]=['etat'=>$from,'entree'=>$enter,'sortie'=>$at,'duree'=>$secs,'par'=>optional($flux->auteur)->name,'note'=>$flux->note];
        $entered[$to]=$at;
      }
      $cur=$grandProjet->etat??$rows->last()->to_etat??$initial; $enter=$entered[$cur]??$created;
      $timeline[]=['etat'=>$cur,'entree'=>$enter,'sortie'=>null,'duree'=>Carbon::now()->diffInSeconds($enter),'par'=>null,'note'=>'État courant'];
      $total=Carbon::now()->diffInSeconds($created);
    }
  @endphp

  <div class="card mt-4 shadow-sm">
    <div class="card-header bg-white"><h5><i class="fas fa-route me-2 text-primary"></i>Journal de circulation</h5></div>
    <div class="card-body">
      @if(!empty($timeline))
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light"><tr class="text-center">
              <th>État</th><th>Entrée</th><th>Sortie</th><th>Durée</th><th>Action par</th><th>Note</th>
            </tr></thead>
            <tbody>
              @foreach($timeline as $row)
                <tr class="{{ $row['sortie'] ? '' : 'table-warning' }}">
                  <td class="fw-bold text-primary">{{ $row['etat'] }}</td>
                  <td class="text-muted">{{ $row['entree']->format('d/m/Y H:i') }}</td>
                  <td>{!! $row['sortie'] ? '<span class="text-success">'.$row['sortie']->format('d/m/Y H:i').'</span>' : '<span class="badge bg-warning text-dark">En cours…</span>' !!}</td>
                  <td><span class="badge bg-info text-dark">{{ $fmt($row['duree']) }}</span></td>
                  <td>{{ $row['par'] ?? '—' }}</td>
                  <td>{{ $row['note'] }}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">Durée totale :</td><td colspan="3"><span class="badge bg-dark text-white">{{ $fmt($total ?? 0) }}</span></td></tr></tfoot>
          </table>
        </div>
      @else
        <div class="alert alert-light border mb-0">Aucun mouvement enregistré.</div>
      @endif
    </div>
  </div>

  {{-- Actions --}}
  <div class="d-flex justify-content-between mt-4">
    @if(Auth::user()->hasRole('chef'))
      <a href="{{ route('chef.grandprojets.clm.edit',$grandProjet) }}" class="btn btn-outline-primary"><i class="fas fa-edit me-1"></i> Modifier</a>
    @else
      <a href="{{ route('saisie_cpc.clm.edit',$grandProjet) }}" class="btn btn-outline-primary"><i class="fas fa-edit me-1"></i> Modifier</a>
    @endif

    @if(method_exists($grandProjet,'canBeCompletedByBS') && $grandProjet->canBeCompletedByBS())
      @if(Auth::user()->hasRole('chef'))
        <a href="{{ route('chef.grandprojets.clm.complete.form',$grandProjet) }}" class="btn btn-success">Compléter (BS)</a>
      @endif
    @endif
  </div>
</div>
@endsection
