@extends('layouts.app')

@section('content')
@php
  $isInbox = ($scope ?? 'inbox') === 'inbox';
  $isCpc   = ($type ?? 'cpc') === 'cpc';

  // Compteurs : on privilégie $countsMine si le contrôleur les fournit, sinon fallback sur $counts
  $c = (array) ($countsMine ?? $counts ?? []);
  $title = 'DGU — '.($isCpc?'CPC':'CLM').' — '.($isInbox?'À traiter':'Envoyés');
@endphp

<style>
  .btn-quick{
    display:block; text-align:left; border:2px solid #0d6efd; border-radius:14px;
    padding:18px 16px; background:#fff; color:#0d6efd; transition:all .15s; font-weight:700;
  }
  .btn-quick .quick-title{ font-size:1.05rem; opacity:.9; }
  .btn-quick .quick-count{ font-size:1.8rem; line-height:1; margin-top:6px; }
  .btn-quick:hover{ background:#e7f1ff; text-decoration:none; }
  .btn-quick.active{ background:#0d6efd; color:#fff; }
  .btn-quick.active .quick-count{ color:#fff; }

  .table-hover tbody tr.clickable-row{ cursor: pointer; }
  .table-hover tbody tr.clickable-row:hover{ background: #f8fafc !important; }
  .badge-soft{background:#f1f5f9;border:1px solid #e2e8f0;color:#0f172a}
</style>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">{{ $title }}</h3>
    {{-- Astuce lisibilité : on rappelle la portée des compteurs --}}
    <span class="badge badge-soft">Compteurs affichés&nbsp;: dossiers assignés à moi</span>
  </div>

  {{-- 4 gros boutons lisibles (compteurs "mes dossiers" si fournis) --}}
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.inbox',  ['type'=>'cpc']) }}"
         class="btn btn-quick w-100 {{ $isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC à traiter</div>
        <div class="quick-count">{{ $c['cpc_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.outbox', ['type'=>'cpc']) }}"
         class="btn btn-quick w-100 {{ !$isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC envoyés</div>
        <div class="quick-count">{{ $c['cpc_outbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.inbox',  ['type'=>'clm']) }}"
         class="btn btn-quick w-100 {{ $isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM à traiter</div>
        <div class="quick-count">{{ $c['clm_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dgu.outbox', ['type'=>'clm']) }}"
         class="btn btn-quick w-100 {{ !$isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM envoyés</div>
        <div class="quick-count">{{ $c['clm_outbox'] ?? '—' }}</div>
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
          <table class="table table-hover table-striped align-middle mb-0">
            <thead class="table-dark">
              <tr>
                <th style="width:70px">#</th>
                <th style="width:160px">N° dossier</th>
                <th>Intitulé</th>
                <th style="width:180px">Commune</th>
                <th style="width:160px">État</th>
                <th style="width:140px">Arrivée</th>
                <th class="text-end" style="width:260px">Actions</th>
              </tr>
            </thead>
            <tbody>
            @foreach($items as $i => $item)
              @php
                $rowUrl = $isCpc ? route('cpc.show.shared', $item) : route('clm.show.shared', $item);
              @endphp
              <tr class="clickable-row" data-href="{{ $rowUrl }}">
                <td>{{ $items->firstItem() + $i }}</td>
                <td class="fw-semibold">{{ $item->numero_dossier }}</td>
                <td class="text-truncate" style="max-width:420px" title="{{ $item->intitule_projet }}">
                  {{ $item->intitule_projet }}
                </td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="badge bg-secondary">{{ $item->etat }}</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end" data-no-rowclick>
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

                  {{-- Transmettre Commission --}}
                  @if($item->etat === 'recu_dgu')
                    <form class="d-inline" method="POST"
                          action="{{ $isCpc ? route('dgu.cpc.transition', $item) : route('dgu.clm.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="vers_comm_interne">
                      <button class="btn btn-warning btn-sm">Transmettre Commission</button>
                    </form>
                  @endif
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

@push('scripts')
<script>
  // Navigation par clic sur ligne (sauf sur la cellule d'actions)
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('tr.clickable-row').forEach(function(tr){
      tr.addEventListener('click', function(e){
        // si on clique dans la colonne "Actions", on ne navigue pas
        if (e.target.closest('[data-no-rowclick]')) return;
        const url = tr.getAttribute('data-href');
        if (url) window.open(url, '_blank'); // ouvre la fiche dans un nouvel onglet
      });
    });
  });
</script>
@endpush
@endsection
