{{-- resources/views/grandprojets/clm/index.blade.php --}}
@extends('layouts.app')

@section('content')
@php use Illuminate\Support\Str; @endphp
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">CLM — Liste des dossiers</h3>
    @if(Auth::user()->hasRole('chef'))
      <a href="{{ route('chef.grandprojets.clm.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau dossier
      </a>
    @elseif(Auth::user()->hasRole('saisie_cpc'))
      <a href="{{ route('saisie_cpc.clm.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nouveau dossier
      </a>
    @endif
  </div>

  <style>
    .chip{display:inline-flex;align-items:center;gap:.4rem;padding:.28rem .6rem;border-radius:999px;
          font-size:.78rem;font-weight:700;border:1px solid transparent;white-space:nowrap}
    .chip .dot{width:8px;height:8px;border-radius:50%}

    .chip-soft-primary{background:#e7f1ff;color:#0b5ed7;border-color:#cfe2ff}.chip-soft-primary .dot{background:#0d6efd}
    .chip-soft-info{background:#e8f6fb;color:#117a8b;border-color:#cfeaf4}.chip-soft-info .dot{background:#0dcaf0}
    .chip-soft-success{background:#e9f7ef;color:#1e7e34;border-color:#cfe9d9}.chip-soft-success .dot{background:#198754}
    .chip-soft-light{background:#f8f9fa;color:#495057;border-color:#e9ecef}.chip-soft-light .dot{background:#adb5bd}

    .row-defav-retourbs{background:#fdeaea!important}
    tr.clickable{cursor:pointer}
  </style>

  @php
    $etatsOptions = $etatsOptions ?? [
      'transmis_dajf'=>'Saisie','recu_dajf'=>'Vers DAJF','transmis_dgu'=>'DAJF','recu_dgu'=>'Vers DGU',
      'vers_comm_interne'=>'DGU','comm_interne'=>'Comm. Interne','comm_mixte'=>'Comm. Mixte',
      'signature_3'=>'3ᵉ signature','retour_bs'=>'Bureau de suivi','archive'=>'Archivé',
    ];
    $provinces = ['Préfecture Oujda-Angad','Province Berkane','Province Jerada','Province Taourirt','Province Figuig'];

    // Petite fonction pour déduire le type CLM à partir de la catégorie
    $detectType = function ($categorie) {
      $s = Str::lower((string)$categorie);
      if (Str::contains($s, ['morcel']))  return ['Morcellement','chip-soft-info'];
      if (Str::contains($s, ['lotiss']))  return ['Lotissement','chip-soft-primary'];
      if (Str::contains($s, ['groupe']))  return ['Groupe d’habitation','chip-soft-success'];
      return ['Autre','chip-soft-light'];
    };
  @endphp

  <form method="GET" class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Recherche</label>
          <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="n° dossier, intitulé, pétitionnaire…">
        </div>
        <div class="col-md-2">
          <label class="form-label">Du</label>
          <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div class="col-md-2">
          <label class="form-label">Au</label>
          <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Province / Préfecture</label>
          <select name="province" class="form-select">
            <option value="">-- Toutes --</option>
            @foreach($provinces as $prov)
              <option value="{{ $prov }}" {{ request('province')===$prov ? 'selected' : '' }}>{{ $prov }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">État</label>
          <select name="etat" class="form-select">
            <option value="">-- Tous --</option>
            @foreach($etatsOptions as $value => $label)
              <option value="{{ $value }}" {{ request('etat')===$value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filtrer</button>
        @if(Auth::user()->hasRole('chef'))
          <a class="btn btn-outline-secondary" href="{{ route('chef.grandprojets.clm.index') }}">Réinitialiser</a>
        @else
          <a class="btn btn-outline-secondary" href="{{ route('saisie_cpc.dashboard') }}">Réinitialiser</a>
        @endif
      </div>
    </div>
  </form>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div>   @endif

  @if(isset($grandProjets) && $grandProjets->count())
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <!-- <th>#</th> -->
              <th>N° Dossier</th>
              <th>Intitulé</th>
              <th>Commune</th>
              <th>Province</th>
              <th>Type CLM</th>
              <th>Date arrivée</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grandProjets as $i => $item)
              @php
                $isDefavorableRetourBS = optional($item->lastExamen)->avis === 'defavorable' && $item->etat === 'retour_bs';
                [$typeLib,$typeClass] = $detectType($item->categorie_projet);
                $showUrl = route('clm.show.shared', $item); // <- fiche partagée CLM
              @endphp
              <tr class="clickable {{ $isDefavorableRetourBS ? 'row-defav-retourbs' : '' }}"
                  onclick="window.location='{{ $showUrl }}'">
                <!-- <td>{{ $grandProjets->firstItem() + $i }}</td> -->
                <td class="fw-semibold">{{ $item->numero_dossier }}</td>
                <td class="text-truncate" style="max-width:340px">{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td>{{ $item->province }}</td>
                <td>
                  <span class="chip {{ $typeClass }}"><span class="dot"></span> {{ $typeLib }}</span>
                </td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  @if(method_exists($item,'canBeCompletedByBS') && $item->canBeCompletedByBS())
                    @if(Auth::user()->hasRole('chef'))
                      <a href="{{ route('chef.grandprojets.clm.complete.form', $item) }}" class="btn btn-sm btn-success">Compléter</a>
                    @else
                      <a href="{{ route('saisie_cpc.clm.edit', $item) }}" class="btn btn-sm btn-success">Compléter</a>
                    @endif
                  @endif
                  @if(Auth::user()->hasRole('chef'))
                    <a href="{{ route('chef.grandprojets.clm.edit', $item) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                  @else
                    <a href="{{ route('saisie_cpc.clm.edit', $item) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-footer">
        {{ $grandProjets->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  @else
    <div class="alert alert-info">Aucun dossier trouvé.</div>
  @endif
</div>
@endsection
