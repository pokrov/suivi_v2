@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Compléter (CLM) — {{ $grandProjet->numero_dossier }}</h3>
    <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-light">Retour</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form id="bsCompleteForm" method="POST" action="{{ route('chef.grandprojets.clm.complete.store', $grandProjet) }}">
        @csrf @method('PUT')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold">Date réelle Commission Mixte</label>
            <input type="date" name="date_commission_mixte_effective"
                   value="{{ old('date_commission_mixte_effective', $grandProjet->date_commission_mixte_effective) }}"
                   class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Superficie du terrain (m²)</label>
            <input type="text" inputmode="decimal" name="superficie_terrain"
                   value="{{ old('superficie_terrain', $grandProjet->superficie_terrain) }}"
                   class="form-control" placeholder="ex: 999,00">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Superficie couverte estimative (m²)</label>
            <input type="text" inputmode="decimal" name="superficie_couverte"
                   value="{{ old('superficie_couverte', $grandProjet->superficie_couverte) }}"
                   class="form-control" placeholder="ex: 999,00">
            <div class="form-text">Clé de facturation (montant calculé si vide).</div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Montant d’investissement (MAD)</label>
            <input type="text" inputmode="decimal" name="montant_investissement"
                   value="{{ old('montant_investissement', $grandProjet->montant_investissement) }}"
                   class="form-control" placeholder="ex: 123456,78">
            <div class="form-text">Si vide → superficie couverte × taux.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Emplois prévus</label>
            <input type="number" min="0" name="emplois_prevus"
                   value="{{ old('emplois_prevus', $grandProjet->emplois_prevus) }}"
                   class="form-control">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold">Nombre de logements</label>
            <input type="number" min="0" name="nb_logements"
                   value="{{ old('nb_logements', $grandProjet->nb_logements) }}"
                   class="form-control">
          </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button class="btn btn-primary">Enregistrer la complétion</button>
          <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Remplacer les virgules par des points au submit (sécurité côté client) --}}
<script>
  document.getElementById('bsCompleteForm').addEventListener('submit', function() {
    ['superficie_terrain','superficie_couverte','montant_investissement'].forEach(function(name){
      const el = document.querySelector('[name="'+name+'"]');
      if (el && el.value) {
        el.value = el.value.replace(/[^\d,.\-]/g,'').replace(',','.');
      }
    });
  });
</script>
@endsection
