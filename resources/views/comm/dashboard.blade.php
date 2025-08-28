@extends('layouts.app')

@section('content')
@php
  $scope = $scope ?? request('scope','interne');

  // Libellés & tons visuels par état
  $STATE_UI = [
    'vers_comm_interne' => ['À recevoir',      'amber'],
    'comm_interne'      => ['Comm. Interne',   'orange'],
    'comm_mixte'        => ['Comm. Mixte',     'cyan'],
    'signature_3'       => ['3ᵉ signature',    'yellow'],
    'retour_bs'         => ['Bureau d’ordre',  'red'],
    'archive'           => ['Archivé',         'slate'],
  ];
@endphp

<style>
  .nav-pills .nav-link{ border-radius:10px; font-weight:700; }

  .state-badge{
    display:inline-block; font-weight:700; font-size:.72rem; line-height:1;
    padding:.40rem .62rem; border-radius:999px; border:1px solid transparent; white-space:nowrap;
  }
  .state-amber  { background:#fef3c7; color:#92400e; border-color:#fde68a; }
  .state-orange { background:#ffedd5; color:#9a3412; border-color:#fed7aa; }
  .state-cyan   { background:#cffafe; color:#155e75; border-color:#a5f3fc; }
  .state-yellow { background:#fef9c3; color:#854d0e; border-color:#fef08a; }
  .state-red    { background:#fee2e2; color:#991b1b; border-color:#fecaca; }
  .state-slate  { background:#e2e8f0; color:#0f172a; border-color:#cbd5e1; }

  .table thead th{
    background:#111827; color:#fff!important; border-color:transparent!important;
    text-transform:uppercase; font-size:.78rem; letter-spacing:.5px; font-weight:700;
  }
  .table-hover tbody tr:hover{ background:#f1f5f9; }

  .btn-pill {
    border-radius:999px!important;
    font-weight:700!important;
    font-size:.78rem!important;
    padding:.35rem .7rem!important;
  }
</style>

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Commission — Dossiers</h3>

    <ul class="nav nav-pills">
      <li class="nav-item">
        <a class="nav-link {{ $scope==='recevoir'?'active':'' }}"
           href="{{ route('comm.dashboard', ['scope'=>'recevoir']) }}">À recevoir</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $scope==='interne'?'active':'' }}"
           href="{{ route('comm.dashboard', ['scope'=>'interne']) }}">Commission interne</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $scope==='mixte'?'active':'' }}"
           href="{{ route('comm.dashboard', ['scope'=>'mixte']) }}">Commission mixte</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $scope==='signature'?'active':'' }}"
           href="{{ route('comm.dashboard', ['scope'=>'signature']) }}">3ᵉ signature</a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ $scope==='suivi'?'active':'' }}"
           href="{{ route('comm.dashboard', ['scope'=>'suivi']) }}">Bureau d’ordre</a>
      </li>
    </ul>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  {{-- ======================= ONGLET MIXTE : 2 TABLEAUX ======================= --}}
  @if($scope === 'mixte')

    @php
      // Séparer les items en favorable / défavorable selon le dernier examen interne
      $favorables = $items->filter(function($gp){
        return optional($gp->examens->firstWhere('type_examen','interne'))->avis === 'favorable';
      });
      $defavorables = $items->filter(function($gp){
        return optional($gp->examens->firstWhere('type_examen','interne'))->avis === 'defavorable';
      });
    @endphp

    {{-- ===== Favorables -> Envoyer 3e signature ===== --}}
    <h5 class="mt-2 mb-2">Avis interne favorable — <span class="text-muted">À envoyer 3ᵉ signature</span></h5>

    @if($favorables->count())
      <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
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
              @foreach($favorables as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $item->numero_dossier }}</strong></td>
                  <td>{{ $item->intitule_projet }}</td>
                  <td>{{ $item->commune_1 }}</td>
                  <td><span class="state-badge state-cyan">Comm. Mixte</span></td>
                  <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                  <td class="text-end">
                    <form method="POST" action="{{ route('comm.mixte.toSignature', $item) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-sm btn-outline-primary btn-pill">Envoyer 3ᵉ signature</button>
                    </form>
                    <a href="{{ route('cpc.show.shared', $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @else
      <div class="alert alert-light">Aucun dossier favorable.</div>
    @endif

    {{-- ===== Défavorables -> Envoyer Bureau d’ordre ===== --}}
    <h5 class="mt-2 mb-2">Avis interne défavorable — <span class="text-muted">À envoyer Bureau d’ordre</span></h5>

    @if($defavorables->count())
      <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
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
              @foreach($defavorables as $item)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td><strong>{{ $item->numero_dossier }}</strong></td>
                  <td>{{ $item->intitule_projet }}</td>
                  <td>{{ $item->commune_1 }}</td>
                  <td><span class="state-badge state-cyan">Comm. Mixte</span></td>
                  <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                  <td class="text-end">
                    <form method="POST" action="{{ route('comm.mixte.toBs', $item) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-sm btn-outline-warning btn-pill">Envoyer Bureau d’ordre</button>
                    </form>
                    <a href="{{ route('cpc.show.shared', $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @else
      <div class="alert alert-light">Aucun dossier défavorable.</div>
    @endif

  {{-- ===================== AUTRES ONGLET(S) : liste simple ===================== --}}
  @elseif(isset($items) && $items->count())
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead>
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
              @php
                $etat   = $item->etat;
                [$label,$tone] = $STATE_UI[$etat] ?? [$etat,'slate'];
              @endphp
              <tr>
                <td>{{ $items->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="state-badge state-{{ $tone }}">{{ $label }}</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  {{-- À recevoir -> Recevoir --}}
                  @if($item->etat === 'vers_comm_interne')
                    <form method="POST" action="{{ route('comm.recevoir', $item) }}" class="d-inline">
                      @csrf
                      <button class="btn btn-sm btn-outline-primary btn-pill">Recevoir</button>
                    </form>
                  @endif

                  {{-- Commission interne -> rendre l’avis --}}
                  @if($item->etat === 'comm_interne')
                    <a class="btn btn-sm btn-outline-success btn-pill" href="{{ route('comm.examens.create', $item) }}">
                      Avis (interne) #{{ $item->next_numero_examen }}
                    </a>
                  @endif

                  {{-- 3e signature -> envoyer Bureau d’ordre --}}
                  @if($item->etat === 'signature_3')
                    <form method="POST" action="{{ route('comm.markSigned', $item) }}" class="d-inline"
                          onsubmit="return confirm('Confirmer l’envoi au Bureau d’ordre ?');">
                      @csrf
                      <button class="btn btn-sm btn-outline-warning btn-pill">Envoyer Bureau d’ordre</button>
                    </form>
                  @endif

                  {{-- Bureau d’ordre -> Archiver --}}
                  @if($item->etat === 'retour_bs')
                    <form method="POST" action="{{ route('comm.archive', $item) }}" class="d-inline"
                          onsubmit="return confirm('Archiver ce dossier ?');">
                      @csrf
                      <button class="btn btn-sm btn-outline-danger btn-pill">Archiver</button>
                    </form>
                  @endif

                  {{-- Détails (toujours disponible) --}}
                  <a href="{{ route('cpc.show.shared', $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-white">
        {{ $items->withQueryString()->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  @else
    <div class="alert alert-info">Aucun dossier à traiter.</div>
  @endif
</div>
@endsection
