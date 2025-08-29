{{-- resources/views/grandprojets/cpc/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  {{-- ===== Titre + CTA ===== --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">CPC — Liste des dossiers</h3>
    <a href="{{ route('chef.grandprojets.cpc.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Nouveau dossier
    </a>
  </div>

  {{-- ===== Styles lisibilité états + ligne défavorable ===== --}}
  <style>
    /* Soft, readable state chips */
    .chip-state{
      display:inline-flex;align-items:center;gap:.4rem;
      padding:.28rem .6rem;border-radius:999px;font-size:.78rem;font-weight:700;
      border:1px solid transparent; white-space:nowrap;
    }
    .chip-state .dot{width:8px;height:8px;border-radius:50%}

    .chip-soft-secondary{ background:#f5f6f8; color:#3a3f45; border-color:#e4e7eb; }
    .chip-soft-primary  { background:#e7f1ff; color:#0b5ed7; border-color:#cfe2ff; }
    .chip-soft-info     { background:#e8f6fb; color:#117a8b; border-color:#cfeaf4; }
    .chip-soft-warning  { background:#fff4e5; color:#b26a00; border-color:#ffe1bd; }
    .chip-soft-dark     { background:#eceef2; color:#20232a; border-color:#dadee5; }
    .chip-soft-success  { background:#e9f7ef; color:#1e7e34; border-color:#cfe9d9; }
    .chip-soft-light    { background:#f8f9fa; color:#495057; border-color:#e9ecef; }

    .chip-soft-secondary .dot{ background:#6c757d }
    .chip-soft-primary   .dot{ background:#0d6efd }
    .chip-soft-info      .dot{ background:#0dcaf0 }
    .chip-soft-warning   .dot{ background:#ffc107 }
    .chip-soft-dark      .dot{ background:#343a40 }
    .chip-soft-success   .dot{ background:#198754 }
    .chip-soft-light    .dot{ background:#adb5bd }

    /* Ligne rouge claire pour défavorable + retour_bs */
    .row-defav-retourbs { background:#fdeaea !important; }
  </style>

  {{-- ===== Filtres ===== --}}
  @php
    // Options d'états affichables (libellés côté UI)
    $etatsOptions = $etatsOptions
      ?? [
        'transmis_dajf'     => 'Saisie',
        'recu_dajf'         => 'Vers DAJF',
        'transmis_dgu'      => 'DAJF',
        'recu_dgu'          => 'Vers DGU',
        'vers_comm_interne' => 'DGU',
        'comm_interne'      => 'Comm. Interne',
        'comm_mixte'        => 'Comm. Mixte',
        'signature_3'       => '3ᵉ signature',
        'retour_bs'         => 'Bureau de suivi',
        'archive'           => 'Archivé',
      ];

    // Provinces fixes
    $provinces = [
      'Préfecture Oujda-Angad',
      'Province Berkane',
      'Province Jerada',
      'Province Taourirt',
      'Province Figuig',
    ];
  @endphp

  <form method="GET" class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Recherche</label>
          <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                 placeholder="n° dossier, intitulé, pétitionnaire…">
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
        <button class="btn btn-primary">
          <i class="fas fa-filter me-1"></i> Filtrer
        </button>
        <a class="btn btn-outline-secondary" href="{{ route('chef.grandprojets.cpc.index') }}">
          Réinitialiser
        </a>
      </div>
    </div>
  </form>

  {{-- ===== Flash ===== --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- ===== Tableau ===== --}}
  @if(isset($grandProjets) && $grandProjets->count())
    <div class="card shadow-sm">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>N° Dossier</th>
              <th>Intitulé</th>
              <th>Commune</th>
              <th>Province</th>
              <th>État</th>
              <th>Date arrivée</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach($grandProjets as $i => $item)
              @php
                // Ligne rouge claire UNIQUEMENT si avis défavorable ET déjà revenu au bureau de suivi
                $isDefavorableRetourBS = optional($item->lastExamen)->avis === 'defavorable'
                                          && $item->etat === 'retour_bs';

                // Map état -> libellé + style chip lisible
                $stateMap = [
                  'transmis_dajf'     => ['label' => 'Saisie',           'class' => 'chip-soft-secondary'],
                  'recu_dajf'         => ['label' => 'Vers DAJF',        'class' => 'chip-soft-primary'],
                  'transmis_dgu'      => ['label' => 'DAJF',             'class' => 'chip-soft-secondary'],
                  'recu_dgu'          => ['label' => 'Vers DGU',         'class' => 'chip-soft-primary'],
                  'vers_comm_interne' => ['label' => 'DGU',              'class' => 'chip-soft-secondary'],
                  'comm_interne'      => ['label' => 'Comm. Interne',    'class' => 'chip-soft-primary'],
                  'comm_mixte'        => ['label' => 'Comm. Mixte',      'class' => 'chip-soft-info'],
                  'signature_3'       => ['label' => '3ᵉ signature',     'class' => 'chip-soft-warning'],
                  'retour_bs'         => ['label' => 'Bureau de suivi',  'class' => 'chip-soft-dark'],
                  'archive'           => ['label' => 'Archivé',          'class' => 'chip-soft-success'],
                ];
                $meta = $stateMap[$item->etat] ?? ['label' => $item->etat, 'class' => 'chip-soft-light'];
              @endphp

              <tr class="{{ $isDefavorableRetourBS ? 'row-defav-retourbs' : '' }}">
                <td>{{ $grandProjets->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td>{{ $item->province }}</td>

                <td>
                  <span class="chip-state {{ $meta['class'] }}">
                    <span class="dot"></span> {{ $meta['label'] }}
                  </span>
                  <!-- {{-- Affiche un petit tag "Défavorable" si c'est le cas (lisible aussi) --}}
                  @if(optional($item->lastExamen)->avis === 'defavorable')
                    <span class="chip-state chip-soft-warning ms-1">
                      <span class="dot"></span> Avis défavorable
                    </span>
                  @elseif(optional($item->lastExamen)->avis === 'favorable')
                    <span class="chip-state chip-soft-success ms-1">
                      <span class="dot"></span> Avis favorable
                    </span>
                  @endif -->
                </td>

                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>

                <td class="text-end">
                  <a href="{{ route('cpc.show.shared', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                    Détails
                  </a>
                  <a href="{{ route('chef.grandprojets.cpc.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                    Éditer
                  </a>

                  {{-- Bouton "Compléter" seulement si retour_bs + favorable (et si le helper existe) --}}
                  @if(method_exists($item,'canBeCompletedByBS') && $item->canBeCompletedByBS())
                    <a href="{{ route('chef.grandprojets.cpc.complete.form', $item) }}"
                       class="btn btn-sm btn-success">
                      Compléter
                    </a>
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
