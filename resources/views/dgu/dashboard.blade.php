@extends('layouts.app')

@section('content')
<div class="container">

  @php
    $isInbox = ($scope ?? 'inbox') === 'inbox';
    $isCpc   = ($type ?? 'cpc') === 'cpc';
    $title   = 'DGU — '.($isCpc?'CPC':'CLM').' — '.($isInbox?'À traiter':'Envoyés');
  @endphp

  <h3 class="mb-3">{{ $title }}</h3>

  {{-- 4 gros boutons lisibles --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.inbox',  ['type'=>'cpc']) }}" class="btn btn-quick w-100 {{ $isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC à traiter</div>
        <div class="quick-count">{{ $counts['cpc_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.outbox', ['type'=>'cpc']) }}" class="btn btn-quick w-100 {{ !$isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC envoyés</div>
        <div class="quick-count">{{ $counts['cpc_outbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.inbox',  ['type'=>'clm']) }}" class="btn btn-quick w-100 {{ $isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM à traiter</div>
        <div class="quick-count">{{ $counts['clm_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.outbox', ['type'=>'clm']) }}" class="btn btn-quick w-100 {{ !$isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM envoyés</div>
        <div class="quick-count">{{ $counts['clm_outbox'] ?? '—' }}</div>
      </a>
    </div>
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
                <td><span class="badge bg-secondary">{{ $item->etat }}</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">

                  {{-- Compléter --}}
                  <a href="{{ $isCpc ? route('dgu.cpc.completer', $item) : route('dgu.clm.completer', $item) }}"
                     class="btn btn-info btn-sm me-1">Compléter</a>

                  {{-- Marquer reçu --}}
                  @if($item->etat === 'transmis_dgu')
                    <form class="d-inline" method="POST"
                          action="{{ $isCpc ? route('dgu.cpc.transition', $item) : route('dgu.clm.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="recu_dgu">
                      <button class="btn btn-primary btn-sm">Marquer reçu</button>
                    </form>
                  @endif

                  {{-- Transmettre Commission (vers_comm_interne) --}}
                  @if($item->etat === 'recu_dgu')
                    <form class="d-inline" method="POST"
                          action="{{ $isCpc ? route('dgu.cpc.transition', $item) : route('dgu.clm.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="vers_comm_interne">
                      <button class="btn btn-warning btn-sm">Transmettre Commission</button>
                    </form>
                  @endif

                  {{-- Détails (fiche partagée) --}}
                  <a class="btn btn-link btn-sm"
                     href="{{ $isCpc ? route('cpc.show.shared', $item) : route('clm.show.shared', $item) }}"
                     target="_blank">Détails</a>

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

{{-- Styles gros boutons (cohérents avec DAJF) --}}
<style>
.btn-quick{
  display:block; text-align:left; border:2px solid #0d6efd; border-radius:14px;
  padding:18px 16px; background:#fff; color:#0d6efd; transition:all .15s;
  font-weight:700;
}
.btn-quick .quick-title{ font-size:1.05rem; opacity:.9; }
.btn-quick .quick-count{ font-size:1.8rem; line-height:1; margin-top:6px; }
.btn-quick:hover{ background:#e7f1ff; text-decoration:none; }
.btn-quick.active{ background:#0d6efd; color:#fff; }
.btn-quick.active .quick-count{ color:#fff; }
</style>
@endsection
