@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Commission interne — Dossiers à l’ordre du jour</h3>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if(isset($items) && $items->count())
  <div class="table-responsive shadow-sm">
    <table class="table table-hover align-middle">
      <thead class="table-light">
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
          <td>{{ $item->numero_dossier }}</td>
          <td>{{ $item->intitule_projet }}</td>
          <td>{{ $item->commune_1 }}</td>

          @php
            $etat = $item->etat;
            $badge = match($etat) {
              'comm_interne'  => 'info',
              'recu_dgu'      => 'primary',
              'transmis_dgu'  => 'secondary',
              'favorable'     => 'success',
              'defavorable'   => 'danger',
              default         => 'secondary'
            };
          @endphp
          <td><span class="badge bg-{{ $badge }}">{{ $etat }}</span></td>

          <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
          <td class="text-end">
            @if($item->etat === 'comm_interne' && !$item->isFavorable())
              {{-- Rendre l’avis (Examen n° suivant) --}}
              <a class="btn btn-success btn-sm" href="{{ route('comm.examens.create', $item) }}">
                Rendre l’avis (Examen n° {{ $item->next_numero_examen }})
              </a>
            @endif

            {{-- Détails : utiliser la route PARTAGÉE (sinon 403) --}}
            <a class="btn btn-link btn-sm" href="{{ route('cpc.show.shared', $item) }}" target="_blank">
              Détails
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="mt-2">
    {{ $items->links('vendor.pagination.bootstrap-5') }}
  </div>
  @else
    <div class="alert alert-info">Aucun dossier à traiter.</div>
  @endif
</div>
@endsection
