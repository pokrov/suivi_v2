{{-- resources/views/grandprojets/clm/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-warning text-dark d-flex align-items-center justify-content-between">
      <h2 class="mb-0">Modifier le Grand Projet - CLM</h2>
      <small class="opacity-75">Étape : Édition</small>
    </div>

    <div class="card-body">
      {{-- Flash erreurs back-end --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Form: route selon le rôle (Chef / Saisie) --}}
      @if(Auth::user()->hasRole('chef'))
        <form action="{{ route('chef.grandprojets.clm.update', $grandProjet) }}" method="POST" id="clmEditForm" novalidate>
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <form action="{{ route('chef.grandprojets.clm.update', $grandProjet) }}" method="POST" id="clmEditForm" novalidate>
      @else
        <form action="{{ route('chef.grandprojets.clm.update', $grandProjet) }}" method="POST" id="clmEditForm" novalidate>
      @endif
        @csrf
        @method('PUT')
        <input type="hidden" name="type_projet" value="clm">

        <div class="row">
          {{-- ================================
               Colonne gauche : Identification
          ================================== --}}
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>

            {{-- Numéro de Dossier (requis) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Numéro de Dossier <span class="text-danger">*</span></label>
              <input type="text"
                     name="numero_dossier"
                     value="{{ old('numero_dossier', $grandProjet->numero_dossier) }}"
                     class="form-control @error('numero_dossier') is-invalid @enderror"
                     placeholder="ex: 1234/2025 (Chiffres/20XX)"
                     required>
              @error('numero_dossier') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Province --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Préfecture / Province <span class="text-danger">*</span></label>
              @php
                $provinces = [
                  'Préfecture Oujda-Angad',
                  'Province Berkane',
                  'Province Jerada',
                  'Province Taourirt',
                  'Province Figuig'
                ];
              @endphp
              <select name="province" id="provinceSelect" class="form-control @error('province') is-invalid @enderror" required>
                <option value="">-- Sélectionner la Province --</option>
                @foreach($provinces as $prov)
                  <option value="{{ $prov }}" {{ old('province', $grandProjet->province)===$prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
              </select>
              @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Commune principale --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
              <select name="commune_1" id="commune1Select" class="form-control @error('commune_1') is-invalid @enderror" required>
                <option value="">-- Sélectionner la commune --</option>
              </select>
              @error('commune_1') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Commune secondaire (à cheval) --}}
            <div class="mb-3">
              <label class="form-label">Commune à cheval (Optionnel)</label>
              <select name="commune_2" id="commune2Select" class="form-control">
                <option value="">-- Sélectionner la commune --</option>
              </select>
            </div>

            {{-- Envoi Papier ? --}}
            @php
              $envoiPapierChecked = old('envoi_papier', (bool) $grandProjet->envoi_papier) ? true : false;
            @endphp
            <div class="mb-3 form-check">
              <input type="checkbox" name="envoi_papier" class="form-check-input" id="envoi_papier" {{ $envoiPapierChecked ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="envoi_papier">Envoi Papier ?</label>
            </div>

            {{-- Zone Détails d'envoi + Numéro d'arrivée --}}
            <div id="envoi_details" class="mb-3 {{ $envoiPapierChecked ? '' : 'd-none' }}">
              <label class="form-label">Référence d'Envoi</label>
              <input type="text" name="reference_envoi" value="{{ old('reference_envoi', $grandProjet->reference_envoi) }}" class="form-control" autocomplete="off">

              <label class="form-label mt-2">Numéro d'Envoi</label>
              <input type="text" name="numero_envoi" value="{{ old('numero_envoi', $grandProjet->numero_envoi) }}" class="form-control" autocomplete="off">

              <label class="form-label mt-2">Numéro d’Arrivée</label>
              <input type="text" name="numero_arrivee" value="{{ old('numero_arrivee', $grandProjet->numero_arrivee) }}" class="form-control" placeholder="ex: 1234" autocomplete="off">
              <div class="form-text">Numéro d’enregistrement interne.</div>
            </div>

            {{-- Date d'arrivée (requis) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date d'Arrivée <span class="text-danger">*</span></label>
              <input type="date"
                     name="date_arrivee"
                     value="{{ old('date_arrivee', $grandProjet->date_arrivee ? \Carbon\Carbon::parse($grandProjet->date_arrivee)->format('Y-m-d') : '') }}"
                     class="form-control @error('date_arrivee') is-invalid @enderror"
                     required>
              @error('date_arrivee') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Date de Commission MIXTE (optionnelle) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date de Commission Mixte (Rokhas)</label>
              <input type="date"
                     name="date_commission_mixte"
                     value="{{ old('date_commission_mixte', $grandProjet->date_commission_mixte ? \Carbon\Carbon::parse($grandProjet->date_commission_mixte)->format('Y-m-d') : '') }}"
                     class="form-control">
              <div class="form-text">Optionnel. Peut être complété/modifié après navette.</div>
            </div>
          </div>

          {{-- =================================
               Colonne droite : Infos Projet
          ================================== --}}
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            {{-- Pétitionnaire (requis) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Nom du Pétitionnaire <span class="text-danger">*</span></label>
              <input type="text" name="petitionnaire" value="{{ old('petitionnaire', $grandProjet->petitionnaire) }}" class="form-control @error('petitionnaire') is-invalid @enderror" required>
              @error('petitionnaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Propriétaire distinct ? --}}
            @php $hasProprio = (bool) old('proprietaire', $grandProjet->proprietaire); @endphp
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="has_proprietaire" {{ $hasProprio ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="has_proprietaire">Indiquer un Propriétaire distinct</label>
            </div>

            {{-- Propriétaire (masqué par défaut) --}}
            <div class="mb-3 {{ $hasProprio ? '' : 'd-none' }}" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" value="{{ old('proprietaire', $grandProjet->proprietaire) }}" class="form-control" placeholder="Nom complet du propriétaire">
            </div>

            {{-- Catégorie du Pétitionnaire --}}
            @php
              $catsPetitionnaire = [
                'Amicale','association','Bienfaiteurs','Collectivité locale','coopérative',
                'Etablissement public','OPH','Particulier','RME','Société Privé','Autre'
              ];
            @endphp
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Pétitionnaire <span class="text-danger">*</span></label>
              <select name="categorie_petitionnaire" class="form-control @error('categorie_petitionnaire') is-invalid @enderror" required>
                <option value="">-- Sélectionner --</option>
                @foreach($catsPetitionnaire as $cat)
                  <option value="{{ $cat }}" {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire ?: 'Particulier')===$cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
              </select>
              @error('categorie_petitionnaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Intitulé du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du Projet <span class="text-danger">*</span></label>
              <input type="text" name="intitule_projet" value="{{ old('intitule_projet', $grandProjet->intitule_projet) }}" class="form-control @error('intitule_projet') is-invalid @enderror" required>
              @error('intitule_projet') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- {{-- Lien GED --}}
            <div class="mb-3">
              <label class="form-label">Lien vers la GED (optionnel)</label>
              <input type="url" name="lien_ged" value="{{ old('lien_ged', $grandProjet->lien_ged) }}" class="form-control" placeholder="http://...">
            </div> -->

            {{-- Catégorie du Projet (CLM) — Cases à cocher (multi) --}}
            @php
              $clmCategories = ['Lotissement','Morcellement',"Groupe d'habitation"];
              // Déduire sélection : supporte CSV "A | B" ou array JSON
              $rawCats = old('categorie_projet', $grandProjet->categorie_projet);
              if (is_array($rawCats)) {
                $selectedCats = $rawCats;
              } elseif (is_string($rawCats) && trim($rawCats) !== '') {
                $selectedCats = array_map('trim', preg_split('/\s*\|\s*/', $rawCats));
              } else {
                $selectedCats = [];
              }
            @endphp
            <div class="mb-3">
              <label class="form-label fw-semibold d-block">Catégorie du Projet (CLM) <span class="text-danger">*</span></label>
              <div class="border rounded p-2" id="catsBox">
                @foreach($clmCategories as $c)
                  @php $id = 'cat_'.md5($c); @endphp
                  <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           id="{{ $id }}"
                           name="categorie_projet[]"
                           value="{{ $c }}"
                           {{ in_array($c, (array) $selectedCats, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="{{ $id }}">{{ $c }}</label>
                  </div>
                @endforeach
              </div>
              <div class="form-text">Un projet peut relever de plusieurs catégories (ex: Lotissement + Groupe d’habitation).</div>
              @error('categorie_projet') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>

            {{-- Contexte du Projet (facultatif) --}}
            @php
              $ctx = [
                'Assistance Architecturale Douar délimité',
                'Douar non délimité',
                'GP - Régularisation',
                'INDH',
                'Intégration de lots',
                'PP- Régularisation',
                'Régularisation',
                'Relogement',
                'VSB',
                'ZAP'
              ];
            @endphp
            <div class="mb-3">
              <label class="form-label fw-semibold">Contexte du Projet</label>
              <select name="contexte_projet" class="form-control">
                <option value="">-- (facultatif) --</option>
                @foreach($ctx as $c)
                  <option value="{{ $c }}" {{ old('contexte_projet', $grandProjet->contexte_projet)===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
            </div>

            {{-- Maître d'Oeuvre (requis) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Maître d'Œuvre <span class="text-danger">*</span></label>
              <select name="maitre_oeuvre" class="form-control @error('maitre_oeuvre') is-invalid @enderror" required>
                <option value="">-- Sélectionner --</option>
                @foreach(($maitresOeuvre ?? []) as $mo)
                  <option value="{{ $mo->nom }}" {{ old('maitre_oeuvre', $grandProjet->maitre_oeuvre)===$mo->nom ? 'selected' : '' }}>{{ $mo->nom }}</option>
                @endforeach
              </select>
              @error('maitre_oeuvre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Situation / Adresse (requis) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Situation / Adresse <span class="text-danger">*</span></label>
              <textarea name="situation" class="form-control @error('situation') is-invalid @enderror" rows="2" placeholder="Ex: Quartier X, Rue Y" required>{{ old('situation', $grandProjet->situation) }}</textarea>
              @error('situation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Références Foncières --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Références Foncières</label>
              <input type="text" name="reference_fonciere" value="{{ old('reference_fonciere', $grandProjet->reference_fonciere) }}" class="form-control" placeholder="ex: 12345/A/2025">
            </div>

            {{-- Observations --}}
            <div class="mb-3">
              <label class="form-label">Observations (optionnel)</label>
              <textarea name="observations" class="form-control" rows="3" placeholder="Commentaire libre...">{{ old('observations', $grandProjet->observations) }}</textarea>
            </div>

          </div> {{-- fin col droite --}}
        </div> {{-- fin row --}}

        <div class="d-flex justify-content-end gap-2">
          <button class="btn btn-warning"><i class="fas fa-save me-1"></i> Mettre à jour</button>

          @if(Auth::user()->hasRole('chef'))
            <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-secondary">Annuler</a>
          @elseif(Auth::user()->hasRole('saisie_cpc'))
            <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-secondary">Annuler</a>
          @else
            <a href="{{ route('home') }}" class="btn btn-secondary">Annuler</a>
          @endif
        </div>

      </form>
    </div>
  </div>
</div>

{{-- ========== Scripts front ========== --}}
<script>
  // Mapping provinces -> communes (objet JS pur)
  const provincesCommunes = {
    "Préfecture Oujda-Angad": [
      "Commune d'Ahl Angad","Commune d'Ain Sfa","Commune d'Isly","Commune d'Oujda","Commune d'Oujda Sidi Ziane",
      "Commune de Beni Drar","Commune de Beni Khaled","Commune de Labsara","Commune de Mestferki","Commune de Neima",
      "Commune de Oued Ennachef Sidi Mâafa","Commune de Sidi Boulenouar","Commune de Sidi Driss El Qadi",
      "Commune de Sidi Moussa Lemhaya","Commune de Sidi Yahya"
    ],
    "Province Berkane": [
      "Commune d'Aghbal","Commune d'Ahfir","Commune d'Aklim","Commune de Ain Reggada","Commune de Berkane",
      "Commune de Boughriba","Commune de Chouihya","Commune de Fezouane","Commune de Laâtamna","Commune de Madagh",
      "Commune de Rislane","Commune de Saïdia","Commune de Sidi Bouhria","Commune de Sidi Slimane Cheraa",
      "Commune de Tafoughalt","Commune de Zegzel"
    ],
    "Province Jerada": [
      "Commune de Ain Beni Mathar","Commune de Beni Mathar","Commune de Gafait","Commune de Guenfouda",
      "Commune de Jerada","Commune de Laouinate","Commune de Lebkhata","Commune de Mrija","Commune de Oulad Ghziyel",
      "Commune de Oulad Sidi Abdelhakem","Commune de Ras Asfour","Commune de Sidi Boubker","Commune de Tiouli","Commune de Touissit"
    ],
    "Province Taourirt": [
      "Commune d'Ahl Oued Za","Commune de Ain Lehjer","Commune de Debdou","Commune de Gteter","Commune de Machraa Hammadi",
      "Commune de Melg El Ouidane","Commune de Mestegmer","Commune de Oulad M'Hammed","Commune de Sidi Ali Belkassem",
      "Commune de Sidi Lahcen","Commune de Tancherfi","Commune de Taourirt","Commune d'El Aioun Sidi Mellouk","Commune d'El Atef"
    ],
    "Province Figuig": [
      "Commune de Abbou-Lakhal","Commune de Ain Chair","Commune de Ain Chouater","Commune de Beni Guil",
      "Commune de Beni Tadjite","Commune de Bouanane","Commune de Bouarfa","Commune de Bouchaouene",
      "Commune de Boumerieme","Commune de Figuig","Commune de Maatarka","Commune de Talsint","Commune de Tendrara"
    ]
  };

  const provinceSelect = document.getElementById('provinceSelect');
  const c1 = document.getElementById('commune1Select');
  const c2 = document.getElementById('commune2Select');

  function fillCommunes() {
    const p = provinceSelect.value;
    c1.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
    c2.innerHTML = '<option value="">-- Sélectionner la commune --</option>';

    (provincesCommunes[p] || []).forEach(v => {
      const o1 = document.createElement('option');
      o1.value = v; o1.text = v;
      c1.appendChild(o1);

      const o2 = document.createElement('option');
      o2.value = v; o2.text = v;
      c2.appendChild(o2);
    });
  }

  // Validation de groupe: au moins UNE catégorie CLM cochée
  function validateClmCategories() {
    const group = document.querySelectorAll('input[name="categorie_projet[]"]');
    const anyChecked = Array.from(group).some(i => i.checked);
    const box = document.getElementById('catsBox');
    if (box) box.classList.toggle('border-danger', !anyChecked);
    return anyChecked;
  }

  document.addEventListener('DOMContentLoaded', function () {
  // Pré-sélection depuis le backend (pas de directive json Blade ici)
  const oldProvince = {!! json_encode(old('province', $grandProjet->province)) !!};
  const oldComm1    = {!! json_encode(old('commune_1', $grandProjet->commune_1)) !!};
  const oldComm2    = {!! json_encode(old('commune_2', $grandProjet->commune_2)) !!};

    if (oldProvince) provinceSelect.value = oldProvince;
    fillCommunes();
    if (oldComm1) c1.value = oldComm1;
    if (oldComm2) c2.value = oldComm2;

    // Toggle propriétaire
    document.getElementById('has_proprietaire').addEventListener('change', function(){
      document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
    });

    // Toggle détails envoi
    document.getElementById('envoi_papier').addEventListener('change', function(){
      document.getElementById('envoi_details').classList.toggle('d-none', !this.checked);
    });

    // Validation "à la volée" des champs requis
    const form = document.getElementById('clmEditForm');
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach((el) => {
      el.addEventListener('blur', () => {
        if (!el.checkValidity()) el.classList.add('is-invalid');
        else el.classList.remove('is-invalid');
      });
    });

    // Validation de groupe CLM
    const cats = document.querySelectorAll('input[name="categorie_projet[]"]');
    cats.forEach(i => i.addEventListener('change', validateClmCategories));
    validateClmCategories();

    // Empêcher submit si invalide
    form.addEventListener('submit', (e) => {
      let ok = true;
      requiredFields.forEach((el) => {
        if (!el.checkValidity()) { el.classList.add('is-invalid'); ok = false; }
      });
      if (!validateClmCategories()) ok = false;
      if (!ok) e.preventDefault();
    });

    provinceSelect.addEventListener('change', fillCommunes);
  });
</script>
@endsection
