@extends('layouts.app')

@section('content')
<div class="container">

  @php
    $isInbox = ($scope ?? 'inbox') === 'inbox';
    $isCpc   = ($type ?? 'cpc') === 'cpc';
    $title   = 'DAJF — '.($isCpc?'CPC':'CLM').' — '.($isInbox?'À traiter':'Envoyés');

    $baseRoute = $isInbox ? 'dajf.inbox' : 'dajf.outbox';
    $mineCur   = request('mine','1') === '1';
  @endphp
  <h3 class="mb-3">{{ $title }}</h3>

  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <a href="{{ route('dajf.inbox',  ['type'=>'cpc','mine'=>$mineCur?1:0]) }}" class="btn-quick w-100 {{ $isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC à traiter</div>
        <div class="quick-count">{{ $counts['cpc_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dajf.outbox', ['type'=>'cpc','mine'=>$mineCur?1:0]) }}" class="btn-quick w-100 {{ !$isInbox && $isCpc ? 'active' : '' }}">
        <div class="quick-title">CPC envoyés</div>
        <div class="quick-count">{{ $counts['cpc_outbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dajf.inbox',  ['type'=>'clm','mine'=>$mineCur?1:0]) }}" class="btn-quick w-100 {{ $isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM à traiter</div>
        <div class="quick-count">{{ $counts['clm_inbox'] ?? '—' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('dajf.outbox', ['type'=>'clm','mine'=>$mineCur?1:0]) }}" class="btn-quick w-100 {{ !$isInbox && !$isCpc ? 'active' : '' }}">
        <div class="quick-title">CLM envoyés</div>
        <div class="quick-count">{{ $counts['clm_outbox'] ?? '—' }}</div>
      </a>
    </div>
  </div>

  <div class="d-flex justify-content-end mb-3">
    <div class="btn-group" role="group" aria-label="Filtre propriétaire">
      <a href="{{ route($baseRoute, ['type'=>$isCpc?'cpc':'clm','mine'=>1]) }}"
         class="btn btn-sm {{ $mineCur ? 'btn-primary' : 'btn-outline-primary' }}">Mes dossiers</a>
      <a href="{{ route($baseRoute, ['type'=>$isCpc?'cpc':'clm','mine'=>0]) }}"
         class="btn btn-sm {{ !$mineCur ? 'btn-primary' : 'btn-outline-primary' }}">Tous</a>
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
                <th>Assigné</th>
                <th>Date arrivée</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($items as $i => $item)
              <tr>
                <td>{{ $items->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td class="text-truncate" style="max-width:280px">{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="badge bg-secondary">{{ $item->etat }}</span></td>
                <td>
                  @if($item->assigned_dajf_id)
                    <span class="badge bg-info">{{ optional($item->assigneeDajf)->name ?? '—' }}</span>
                  @else
                    <span class="text-muted">Non affecté</span>
                  @endif
                </td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">

                  {{-- Compléter --}}
                  <a href="{{ $isCpc ? route('dajf.cpc.completer', $item) : route('dajf.clm.completer', $item) }}"
                     class="btn btn-info btn-sm me-1">Compléter</a>

                  {{-- Prendre en charge --}}
                  @if(in_array($item->etat, ['enregistrement','transmis_dajf'], true))
                    <form class="d-inline" method="POST"
                          action="{{ $isCpc ? route('dajf.cpc.transition', $item) : route('dajf.clm.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="recu_dajf">
                      <button class="btn btn-primary btn-sm">Marquer reçu</button>
                    </form>
                  @endif

                  {{-- Envoyer DGU (avec assignation obligatoire) --}}
                  @if($item->etat === 'recu_dajf')
                    <form class="d-inline" method="POST"
                          action="{{ $isCpc ? route('dajf.cpc.transition', $item) : route('dajf.clm.transition', $item) }}">
                      @csrf
                      <input type="hidden" name="etat" value="transmis_dgu">

                      <select name="assigned_dgu_id" class="form-select form-select-sm d-inline w-auto me-1" required>
                        <option value="">— Choisir agent DGU —</option>
                        @foreach(($dguUsers ?? collect()) as $u)
                          <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                      </select>

                      <button class="btn btn-outline-dark btn-sm">Transmettre DGU</button>
                    </form>
                  @endif

                  {{-- Détails --}}
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
