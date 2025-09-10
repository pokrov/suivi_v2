@extends('layouts.app')

@section('content')
@php
  // On essaie d’inférer le type en amont si le contrôleur ne l’a pas déjà passé
  $clmType = $clmType
    ?? (str_contains(Str::lower($grandProjet->categorie_projet ?? ''), 'morcel')
          ? 'morcellement'
          : (str_contains(Str::lower($grandProjet->categorie_projet ?? ''), 'lotiss')
              ? 'lotissement'
              : (str_contains(Str::lower($grandProjet->categorie_projet ?? ''), 'groupe')
                  ? 'groupe'
                  : 'autre')));
@endphp

<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Compléter (CLM) — {{ $grandProjet->numero_dossier }}</h3>
    <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-light">Retour</a>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">

      {{-- Badge type --}}
      <div class="mb-3">
        <!-- <span class="badge bg-dark me-2">Type CLM détecté</span> -->
        @switch($clmType)
          @case('morcellement') <span class="badge bg-info">Morcellement</span> @break
          @case('lotissement')  <span class="badge bg-primary">Lotissement</span> @break
          @case('groupe')       <span class="badge bg-success">Groupe d’habitation</span> @break
          @default              <span class="badge bg-secondary">Autre / Non détecté</span>
        @endswitch
      </div>

      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
      @endif

      <form id="bsCompleteForm" method="POST"
            action="{{ route('chef.grandprojets.clm.complete.store', $grandProjet) }}"
            data-clm-type="{{ $clmType }}">
        @csrf @method('PUT')

        {{-- on transmet le type utilisé par la vue (le contrôleur re-vérifie) --}}
        <input type="hidden" name="clm_type" value="{{ $clmType }}">

        <div class="row g-3">

          {{-- Commun à tous --}}
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

          {{-- Bloc : Superficie couverte (pour Groupe d’habitation uniquement) --}}
          <div class="col-md-4 clm-show--groupe d-none">
            <label class="form-label fw-semibold">Superficie couverte estimative (m²)</label>
            <input type="text" inputmode="decimal" name="superficie_couverte"
                   value="{{ old('superficie_couverte', $grandProjet->superficie_couverte) }}"
                   class="form-control" placeholder="ex: 999,00">
            <div class="form-text">Clé de facturation (montant calculé si vide).</div>
          </div>

          {{-- Bloc : Montant d’investissement (présent uniquement si “autre” — sinon masqué) --}}
          <div class="col-md-4 clm-show--autre d-none">
            <label class="form-label fw-semibold">Montant d’investissement (MAD)</label>
            <input type="text" inputmode="decimal" name="montant_investissement"
                   value="{{ old('montant_investissement', $grandProjet->montant_investissement) }}"
                   class="form-control" placeholder="ex: 123456,78">
            <div class="form-text">Si vide → superficie couverte × taux.</div>
          </div>

          {{-- Bloc : Emplois / Logements (gardés génériques, à ta convenance) --}}
          <div class="col-md-4">
            <label class="form-label fw-semibold">Emplois prévus</label>
            <input type="number" min="0" name="emplois_prevus"
                   value="{{ old('emplois_prevus', $grandProjet->emplois_prevus) }}"
                   class="form-control">
          </div>

          <div class="col-md-4 clm-show--groupe d-none">
            <label class="form-label fw-semibold">Nombre de logements</label>
            <input type="number" min="0" name="nb_logements"
                   value="{{ old('nb_logements', $grandProjet->nb_logements) }}"
                   class="form-control">
          </div>

          {{-- ===== SES CHAMPS SPÉCIFIQUES ===== --}}

          {{-- Morcellement : superficie_morcelee --}}
          <div class="col-md-4 clm-show--morcellement d-none">
            <label class="form-label fw-semibold">Superficie morcelée (m²)</label>
            <input type="text" inputmode="decimal" name="superficie_morcelee"
              value="{{ old('superficie_morcelee', $grandProjet->superficie_morcelee ?? null) }}"
              class="form-control" placeholder="ex: 999,00">
          </div>

          {{-- Lotissement : superficie_lotie --}}
          <div class="col-md-4 clm-show--lotissement d-none">
            <label class="form-label fw-semibold">Superficie lotie (m²)</label>
            <input type="text" inputmode="decimal" name="superficie_lotie"
              value="{{ old('superficie_lotie', $grandProjet->superficie_lotie ?? null) }}"
              class="form-control" placeholder="ex: 999,00">
          </div>

          {{-- Lotissement & Groupe : consistance --}}
          <div class="col-md-8 clm-show--lotissement clm-show--groupe d-none">
            <label class="form-label fw-semibold">Consistance</label>
            <textarea name="consistance" rows="2" class="form-control"
              placeholder="Exemples : 120 lots dont 20 lots équipements / 8 immeubles R+4, etc.">{{ old('consistance', $grandProjet->consistance ?? null) }}</textarea>
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

{{-- Normalisation des décimaux & bascule d’affichage --}}
<script>
(function(){
  const form = document.getElementById('bsCompleteForm');
  const type = form?.dataset?.clmType || 'autre';

  // Afficher/masquer selon type
  const showFor = (t) => {
    // cibles : éléments ayant .clm-show--{type}
    document.querySelectorAll('[class*="clm-show--"]').forEach(el => {
      el.classList.add('d-none');
    });
    document.querySelectorAll('.clm-show--' + t).forEach(el => el.classList.remove('d-none'));

    // règles d’UI simples :
    // - Pour morcellement & lotissement : cacher “montant_investissement” et “superficie_couverte”, “nb_logements”
    // - Pour groupe : montrer “superficie_couverte” + “consistance” + “nb_logements”, cacher “montant_investissement”
    // - Pour autre : montrer “montant_investissement” uniquement
    const hideNames = new Set();
    if (t === 'morcellement') {
      hideNames.add('montant_investissement').add('superficie_couverte').add('nb_logements');
    } else if (t === 'lotissement') {
      hideNames.add('montant_investissement').add('superficie_couverte');
    } else if (t === 'groupe') {
      hideNames.add('montant_investissement');
    } else {
      // autre -> on laisse "montant_investissement", on cache le reste spécifique
    }
    document.querySelectorAll('[name]').forEach(inp => {
      const wrap = inp.closest('.col-md-4, .col-md-8') || inp;
      if (hideNames.has(inp.name)) wrap?.classList?.add('d-none');
    });
  };

  showFor(type);

  // Normaliser décimaux au submit
  form?.addEventListener('submit', function(){
    ['superficie_terrain','superficie_couverte','montant_investissement','superficie_morcelee','superficie_lotie'].forEach(function(name){
      const el = form.querySelector(`[name="${name}"]`);
      if (el && el.value) el.value = el.value.replace(/[^\d,.\-]/g,'').replace(',', '.');
    });
  });
})();
</script>
@endsection
