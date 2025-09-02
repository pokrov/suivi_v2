@extends('layouts.app')

@push('styles')
<style>
  .tile { border-radius:16px; background:rgba(255,255,255,.9); border:1px solid #e5e7eb; }
  .btn-pill { border-radius:999px; font-weight:800; }
  .filter-tabs .btn { font-weight:800; }
</style>
@endpush

@section('content')
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">
      <i class="fas fa-user-check me-2"></i>
      Attribution des dossiers — <span class="text-primary">{{ strtoupper($type) }}</span>
    </h3>
    <div class="btn-group filter-tabs" role="group">
      <a href="{{ route('chef.assignments.index', ['type'=>'cpc']) }}"
         class="btn btn-outline-primary {{ $type==='cpc'?'active':'' }}">CPC</a>
      <a href="{{ route('chef.assignments.index', ['type'=>'clm']) }}"
         class="btn btn-outline-info {{ $type==='clm'?'active':'' }}">CLM</a>
    </div>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card tile shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>N° dossier</th>
              <th>Intitulé</th>
              <th>Commune</th>
              <th>Pétitionnaire</th>
              <th style="width:320px;">Action — Envoyer à (DAJF)</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $i => $gp)
              <tr>
                <td>{{ $items->firstItem() + $i }}</td>
                <td><strong>{{ $gp->numero_dossier }}</strong></td>
                <td class="text-truncate" style="max-width:260px;">{{ $gp->intitule_projet }}</td>
                <td>{{ $gp->commune_1 }}</td>
                <td class="text-truncate" style="max-width:220px;">{{ $gp->petitionnaire }}</td>
                <td>
                  <form method="POST" action="{{ route('chef.assignments.assign.dajf', $gp) }}" class="d-flex gap-2">
                    @csrf
                    <select name="user_id" class="form-select form-select-sm" required>
                      <option value="">-- Choisir un agent DAJF --</option>
                      @foreach($dajfUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} — {{ $u->email }}</option>
                      @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary btn-pill">
                      <i class="fas fa-paper-plane me-1"></i> Envoyer
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center py-4">Aucun dossier en attente d’attribution.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer bg-white">
      {{ $items->links('vendor.pagination.bootstrap-5') }}
    </div>
  </div>
</div>
@endsection
