@extends('layouts.app')

@section('content')
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">DAJF — Dossiers</h3>
    <ul class="nav nav-pills">
      <li class="nav-item">
        <a class="nav-link {{ (isset($scope) && $scope==='inbox') ? 'active' : '' }}"
           href="{{ route('dajf.inbox') }}">À traiter</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (isset($scope) && $scope==='outbox') ? 'active' : '' }}"
           href="{{ route('dajf.outbox') }}">Envoyés</a>
      </li>
    </ul>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(isset($items) && $items->count())
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>N° dossier</th>
                <th>Intitulé</th>
                <th>Commune</th>
                <th>État</th>
                <th>Date arrivée</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $i => $item)
              <tr>
                <td>{{ $items->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td>
                  <span class="badge bg-secondary">{{ $item->etat }}</span>
                </td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  {{-- Compléter (toujours dispo) --}}
                  <a href="{{ route('dajf.cpc.completer', $item) }}" class="btn btn-info btn-sm me-1">
                    Compléter
                  </a>

                  {{-- Prendre en charge (enregistrement/transmis_dajf) --}}
                  @if($item->etat === 'enregistrement' || $item->etat === 'transmis_dajf')
                    <form class="d-inline" method="POST" action="{{ route('dajf.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="recu_dajf">
                      <button class="btn btn-primary btn-sm">Marquer reçu (DAJF)</button>
                    </form>
                  @endif

                  {{-- Envoyer DGU --}}
                  @if($item->etat === 'recu_dajf')
                    <form class="d-inline" method="POST" action="{{ route('dajf.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="transmis_dgu">
                      <button class="btn btn-outline-dark btn-sm">Transmettre DGU</button>
                    </form>
                  @endif

                  {{-- Détails ouverts à tous les rôles --}}
                  <a class="btn btn-link btn-sm" href="{{ route('cpc.show.any', $item) }}" target="_blank">
                    Détails
                  </a>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="mt-2">
      {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
  @else
    <div class="alert alert-info">Aucun dossier.</div>
  @endif
</div>
@endsection
