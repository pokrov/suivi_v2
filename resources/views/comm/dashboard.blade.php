@extends('layouts.app')

@section('content')
@php
  $type  = $type  ?? request('type','cpc');      // cpc | clm
  $scope = $scope ?? request('scope','interne'); // onglet
  $isCpc = $type === 'cpc';

  // UI par état
  $STATE_UI = [
    'vers_comm_interne' => ['À recevoir',      'amber'],
    'comm_interne'      => ['Comm. Interne',   'orange'],
    'comm_mixte'        => ['Comm. Mixte',     'cyan'],
    'signature_3'       => ['3ᵉ signature',    'yellow'],
    'retour_bs'         => ['Bureau de Suivi',  'red'],
    'archive'           => ['Archivé',         'slate'],
  ];
@endphp

<style>
  .btn-quick{
    display:block; text-align:center; border:2px solid #0d6efd; border-radius:14px;
    padding:14px 10px; background:#fff; color:#0d6efd; transition:all .15s; font-weight:800;
  }
  .btn-quick .qt{ font-size:1rem; opacity:.9; }
  .btn-quick .qc{ font-size:1.6rem; line-height:1; margin-top:4px; }
  .btn-quick:hover{ background:#e7f1ff; text-decoration:none; }
  .btn-quick.active{ background:#0d6efd; color:#fff; }

  .nav-scope .nav-link{ border-radius:999px; font-weight:700; }
  .badge-tone{ display:inline-block; font-weight:700; font-size:.72rem; padding:.35rem .6rem; border-radius:999px; }
  .tone-amber  { background:#fef3c7; color:#92400e; }
  .tone-orange { background:#ffedd5; color:#9a3412; }
  .tone-cyan   { background:#cffafe; color:#155e75; }
  .tone-yellow { background:#fef9c3; color:#854d0e; }
  .tone-red    { background:#fee2e2; color:#991b1b; }
  .tone-slate  { background:#e2e8f0; color:#0f172a; }
  .table thead th{ background:#111827; color:#fff; text-transform:uppercase; font-size:.78rem; letter-spacing:.5px; }
  .btn-pill{ border-radius:999px!important; font-weight:700!important; font-size:.78rem!important; padding:.35rem .7rem!important; }
</style>

<div class="container">
  <h3 class="mb-3">Commission — {{ $isCpc ? 'CPC' : 'CLM' }}</h3>

  {{-- 2 gros boutons : type --}}
  <div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
      <a href="{{ route('comm.dashboard', ['type'=>'cpc','scope'=>$scope]) }}"
         class="btn-quick {{ $isCpc ? 'active' : '' }}">
        <div class="qt">Dossiers CPC ({{ $scope }})</div>
        <div class="qc">{{ $counts['cpc'][$scope] ?? '0' }}</div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a href="{{ route('comm.dashboard', ['type'=>'clm','scope'=>$scope]) }}"
         class="btn-quick {{ !$isCpc ? 'active' : '' }}">
        <div class="qt">Dossiers CLM ({{ $scope }})</div>
        <div class="qc">{{ $counts['clm'][$scope] ?? '0' }}</div>
      </a>
    </div>
  </div>

  {{-- Onglets d’étapes (conservent le type sélectionné) --}}
  <ul class="nav nav-pills nav-scope mb-3">
    @foreach(['recevoir'=>'À recevoir','interne'=>'Interne','mixte'=>'Mixte','signature'=>'3ᵉ signature','suivi'=>'Bureau de Suivi','tous'=>'Tous'] as $sc => $label)
      <li class="nav-item me-2 mb-2">
        <a class="nav-link {{ $scope===$sc ? 'active' : '' }}"
           href="{{ route('comm.dashboard', ['type'=>$type,'scope'=>$sc]) }}">
          {{ $label }}
          <span class="badge bg-light text-dark ms-1">{{ $counts[$type][$sc] ?? 0 }}</span>
        </a>
      </li>
    @endforeach
  </ul>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  {{-- ======================= ONGLET MIXTE : 2 LISTES ======================= --}}
  @if($scope === 'mixte')
    @php
      $favorables = $items->filter(fn($gp) => optional($gp->examens->firstWhere('type_examen','interne'))->avis === 'favorable');
      $defavs     = $items->filter(fn($gp) => optional($gp->examens->firstWhere('type_examen','interne'))->avis === 'defavorable');
      $routeToSig = $isCpc ? 'comm.cpc.mixte.toSignature' : 'comm.clm.mixte.toSignature';
      $routeToBs  = $isCpc ? 'comm.cpc.mixte.toBs'        : 'comm.clm.mixte.toBs';
      $routeShow  = $isCpc ? 'cpc.show.shared'            : 'clm.show.shared';
    @endphp

    <h5 class="mt-2 mb-2">Avis interne favorable — <span class="text-muted">À envoyer 3ᵉ signature</span></h5>
    @if($favorables->count())
      <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead><tr>
              <th>#</th><th>N° dossier</th><th>Intitulé</th><th>Commune</th><th>État</th><th>Date arrivée</th><th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
              @foreach($favorables as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="badge-tone tone-cyan">Comm. Mixte</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  <form method="POST" action="{{ route($routeToSig, $item) }}" class="d-inline">@csrf
                    <button class="btn btn-sm btn-outline-primary btn-pill">Envoyer 3ᵉ signature</button>
                  </form>
                  <a href="{{ route($routeShow, $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
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

    <h5 class="mt-2 mb-2">Avis interne défavorable — <span class="text-muted">À envoyer Bureau de Suivi</span></h5>
    @if($defavs->count())
      <div class="card shadow-sm border-0 mb-4">
        <div class="table-responsive">
          <table class="table table-striped align-middle mb-0">
            <thead><tr>
              <th>#</th><th>N° dossier</th><th>Intitulé</th><th>Commune</th><th>État</th><th>Date arrivée</th><th class="text-end">Actions</th>
            </tr></thead>
            <tbody>
              @foreach($defavs as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="badge-tone tone-cyan">Comm. Mixte</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  <form method="POST" action="{{ route($routeToBs, $item) }}" class="d-inline">@csrf
                    <button class="btn btn-sm btn-outline-warning btn-pill">Envoyer Bureau de Suivi</button>
                  </form>
                  <a href="{{ route($routeShow, $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
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

  {{-- ===================== AUTRES ONGLETS : liste simple ===================== --}}
  @elseif(isset($items) && $items->count())
    @php
      $routeRecevoir = $isCpc ? 'comm.cpc.recevoir'      : 'comm.clm.recevoir';
      $routeExam     = $isCpc ? 'comm.cpc.examens.create': 'comm.clm.examens.create';
      $routeSigned   = $isCpc ? 'comm.cpc.markSigned'    : 'comm.clm.markSigned';
      $routeArchive  = $isCpc ? 'comm.cpc.archive'       : 'comm.clm.archive';
      $routeShow     = $isCpc ? 'cpc.show.shared'        : 'clm.show.shared';
    @endphp
    <div class="card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead><tr>
            <th>#</th><th>N° dossier</th><th>Intitulé</th><th>Commune</th><th>État</th><th>Date arrivée</th><th class="text-end">Actions</th>
          </tr></thead>
          <tbody>
            @foreach($items as $i => $item)
              @php [$label,$tone] = $STATE_UI[$item->etat] ?? [$item->etat,'slate']; @endphp
              <tr>
                <td>{{ $items->firstItem() + $i }}</td>
                <td><strong>{{ $item->numero_dossier }}</strong></td>
                <td>{{ $item->intitule_projet }}</td>
                <td>{{ $item->commune_1 }}</td>
                <td><span class="badge-tone tone-{{ $tone }}">{{ $label }}</span></td>
                <td>{{ $item->date_arrivee ? \Carbon\Carbon::parse($item->date_arrivee)->format('d/m/Y') : '-' }}</td>
                <td class="text-end">
                  {{-- Recevoir --}}
                  @if($item->etat === 'vers_comm_interne')
                    <form method="POST" action="{{ route($routeRecevoir, $item) }}" class="d-inline">@csrf
                      <button class="btn btn-sm btn-outline-primary btn-pill">Recevoir</button>
                    </form>
                  @endif

                  {{-- Rendre l’avis (interne) --}}
                  @if($item->etat === 'comm_interne')
                    <a class="btn btn-sm btn-outline-success btn-pill" href="{{ route($routeExam, $item) }}">
                      Avis (interne)
                    </a>
                  @endif

                  {{-- 3e signature -> envoyer Bureau d’ordre --}}
                  @if($item->etat === 'signature_3')
                    <form method="POST" action="{{ route($routeSigned, $item) }}" class="d-inline"
                          onsubmit="return confirm('Confirmer l’envoi au Bureau de Suivi ?');">@csrf
                      <button class="btn btn-sm btn-outline-warning btn-pill">Envoyer Bureau de Suivi</button>
                    </form>
                  @endif

                  {{-- Bureau d’ordre -> Archiver --}}
                  @if($item->etat === 'retour_bs')
                    <form method="POST" action="{{ route($routeArchive, $item) }}" class="d-inline"
                          onsubmit="return confirm('Archiver ce dossier ?');">@csrf
                      <button class="btn btn-sm btn-outline-danger btn-pill">Archiver</button>
                    </form>
                  @endif

                  {{-- Détails --}}
                  <a href="{{ route($routeShow, $item) }}" class="btn btn-sm btn-outline-secondary btn-pill" target="_blank">Détails</a>
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
    <div class="alert alert-info">Aucun dossier.</div>
  @endif
</div>
@endsection
