@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">CPC — Liste des dossiers</h3>
    <a href="{{ route('chef.grandprojets.cpc.create') }}" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Nouveau dossier
    </a>
  </div>

  {{-- Filtres --}}
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
          @php
            $provinces = [
              'Préfecture Oujda-Angad',
              'Province Berkane',
              'Province Jerada',
              'Province Taourirt',
              'Province Figuig',
            ];
          @endphp
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
            @foreach(($etatsOptions ?? []) as $value => $label)
              <option value="{{ $value }}" {{ request('etat')===$value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="mt-3 d-flex gap-2">
        <button class="btn btn-primary"><i class="fas fa-filter me-1"></i> Filtrer</button>
        <a class="btn btn-outline-secondary" href="{{ route('chef.grandprojets.cpc.index') }}">Réinitialiser</a>
      </div>
    </div>
  </form>

  {{-- Flash --}}
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- Tableau --}}
  @if($grandProjets->count())
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
              <tr>
                <td>{{ $grandProjets->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td>{{ $item->province }}</td>
                <td>
                  @php
                    $label = $etatsOptions[$item->etat] ?? $item->etat;
                    $badge = match($item->etat) {
                      'transmis_dajf','transmis_dgu','vers_comm_interne' => 'secondary',
                      'recu_dajf','recu_dgu','comm_interne'              => 'primary',
                      'comm_mixte'                                        => 'info',
                      'signature_3'                                       => 'warning',
                      'retour_bs'                                          => 'dark',
                      'archive'                                           => 'success',
                      default                                             => 'light'
                    };
                  @endphp
                  <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                </td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  <a href="{{ route('cpc.show.shared', $item) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                    Détails
                  </a>
                  <a href="{{ route('chef.grandprojets.cpc.edit', $item) }}" class="btn btn-sm btn-outline-primary">
                    Éditer
                  </a>

                  {{-- === Bouton Compléter si retour_bs & favorable === --}}
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
