@extends('layouts.app')

@section('content')
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">DGU — Dossiers</h3>
    <ul class="nav nav-pills">
      <li class="nav-item">
        <a class="nav-link {{ (isset($scope) && $scope==='inbox') ? 'active' : '' }}"
           href="{{ route('dgu.inbox') }}">À traiter</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ (isset($scope) && $scope==='outbox') ? 'active' : '' }}"
           href="{{ route('dgu.outbox') }}">Envoyés</a>
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
                  <a href="{{ route('dgu.cpc.completer', $item) }}" class="btn btn-info btn-sm me-1">
                    Compléter
                  </a>

                  {{-- Marquer reçu --}}
                  @if($item->etat === 'transmis_dgu')
                    <form class="d-inline" method="POST" action="{{ route('dgu.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="recu_dgu">
                      <button class="btn btn-primary btn-sm">Marquer reçu (DGU)</button>
                    </form>
                  @endif

                  {{-- Transmettre Commission --}}
                  @if($item->etat === 'recu_dgu')
                    <form class="d-inline" method="POST" action="{{ route('dgu.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="comm_interne">
                      <button class="btn btn-warning btn-sm">Transmettre Commission</button>
                    </form>
                  @endif

                  {{-- Détails (route ouverte) --}}
                  <a class="btn btn-link btn-sm" href="{{ route('cpc.show.shared', $item) }}" target="_blank">
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
