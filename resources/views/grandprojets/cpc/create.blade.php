{{-- resources/views/grandprojets/cpc/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-success text-white d-flex align-items-center justify-content-between">
      <h2 class="mb-0">Enregistrement d'un Grand Projet - CPC</h2>
      <small class="opacity-75">Étape : Saisie initiale</small>
    </div>

    <div class="card-body">
      {{-- Affichage des erreurs de validation serveur (au cas où) --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Choix de la route en fonction du rôle --}}
      @if(Auth::user()->hasRole('chef'))
        <form id="cpcForm" action="{{ route('chef.grandprojets.cpc.store') }}" method="POST" novalidate>
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <form id="cpcForm" action="{{ route('saisie_cpc.cpc.store') }}" method="POST" novalidate>
      @endif
        @csrf

        <div class="row">
          <!-- =========================================
               Colonne gauche : Identification & Localisation
          ========================================= -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>

            {{-- Numéro de Dossier --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Numéro de Dossier <span class="text-danger">*</span></label>
              <input
                type="text"
                name="numero_dossier"
                value="{{ old('numero_dossier') }}"
                class="form-control"
                placeholder="ex: 1234/2025"
                data-validate='{"required":true,"pattern":"^\\d+\\/20\\d{2}$","message":"Format attendu : 1234/20XX"}'
              >
              <div class="invalid-feedback">Format attendu : <code>nombre/20XX</code> (ex. 452/2025)</div>
              <div class="form-text">Format attendu : <code>nombre/20XX</code> (ex. 452/2025)</div>
            </div>

            {{-- Préfecture / Province --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Préfecture / Province <span class="text-danger">*</span></label>
              <select
                name="province"
                id="provinceSelect"
                class="form-select"
                data-validate='{"required":true,"message":"Sélectionnez une province"}'
              >
                <option value="">-- Sélectionner la Province --</option>
                @foreach([
                  'Préfecture Oujda-Angad',
                  'Province Berkane',
                  'Province Jerada',
                  'Province Taourirt',
                  'Province Figuig'
                ] as $prov)
                  <option value="{{ $prov }}" {{ old('province')===$prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Sélectionnez une province.</div>
            </div>

            {{-- Commune Principale --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
              <select
                name="commune_1"
                id="commune1Select"
                class="form-select"
                data-validate='{"required":true,"message":"Sélectionnez une commune"}'
              >
                <option value="">-- Sélectionner la commune --</option>
              </select>
              <div class="invalid-feedback">Sélectionnez une commune.</div>
            </div>

            {{-- Commune Secondaire (à cheval) --}}
            <div class="mb-3">
              <label class="form-label">Commune à cheval (Optionnel)</label>
              <select name="commune_2" id="commune2Select" class="form-select">
                <option value="">-- Sélectionner la commune --</option>
              </select>
            </div>

            {{-- Envoi Papier ? --}}
            <div class="mb-3 form-check">
              <input type="checkbox" name="envoi_papier" class="form-check-input" id="envoi_papier" {{ old('envoi_papier') ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="envoi_papier">Envoi Papier ?</label>
            </div>

            {{-- Détails d'envoi (Référence / Numéro / Numéro d’arrivée déplacé ici) --}}
            <div id="envoi_details" class="mb-3 {{ old('envoi_papier') ? '' : 'd-none' }}">
              <div class="mb-2">
                <label class="form-label">Référence d'Envoi</label>
                <input type="text" name="reference_envoi" value="{{ old('reference_envoi') }}" class="form-control">
              </div>

              <div class="mb-2">
                <label class="form-label">Numéro d'Envoi</label>
                <input type="text" name="numero_envoi" value="{{ old('numero_envoi') }}" class="form-control">
              </div>

              <div class="mb-2">
                <label class="form-label">Numéro d’Arrivée (interne)</label>
                <input type="text" name="numero_arrivee" value="{{ old('numero_arrivee') }}" class="form-control" placeholder="ex: 1234">
              </div>
            </div>

            {{-- Date d'Arrivée --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date d'Arrivée <span class="text-danger">*</span></label>
              <input
                type="date"
                name="date_arrivee"
                value="{{ old('date_arrivee') }}"
                class="form-control"
                data-validate='{"required":true,"message":"La date d’arrivée est obligatoire"}'
              >
              <div class="invalid-feedback">La date d’arrivée est obligatoire.</div>
            </div>

            {{-- Date de Commission MIXTE (Rokhas) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date de Commission Mixte (Rokhas)</label>
              <input type="date" name="date_commission_mixte" value="{{ old('date_commission_mixte') }}" class="form-control">
              <div class="form-text">Optionnel à la saisie initiale. Peut être complété/modifié après retour.</div>
            </div>

            {{-- ⚠️ Pas de date_commission_interne ici --}}
          </div>

          <!-- =========================================
               Colonne droite : Informations du Projet
          ========================================= -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            {{-- Pétitionnaire --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Nom du Pétitionnaire <span class="text-danger">*</span></label>
              <input
                type="text"
                name="petitionnaire"
                value="{{ old('petitionnaire') }}"
                class="form-control"
                data-validate='{"required":true,"message":"Le pétitionnaire est obligatoire"}'
              >
              <div class="invalid-feedback">Le pétitionnaire est obligatoire.</div>
            </div>

            {{-- Propriétaire distinct ? --}}
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="has_proprietaire" {{ old('proprietaire') ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="has_proprietaire">Indiquer un Propriétaire distinct</label>
            </div>

            {{-- Champ Propriétaire (masqué par défaut) --}}
            <div class="mb-3 {{ old('proprietaire') ? '' : 'd-none' }}" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" value="{{ old('proprietaire') }}" class="form-control" placeholder="Nom complet du propriétaire">
            </div>

            {{-- Catégorie du Pétitionnaire (par défaut: Particulier) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Pétitionnaire <span class="text-danger">*</span></label>
              @php
                $cats = ['Amicale','association','Bienfaiteurs','Collectivité locale','coopérative',
                         'Etablissement public','OPH','Particulier','RME','Société Privé','Autre'];
                $oldCat = old('categorie_petitionnaire', 'Particulier'); // défaut Particulier
              @endphp
              <select
                name="categorie_petitionnaire"
                class="form-select"
                data-validate='{"required":true,"message":"Sélectionnez une catégorie"}'
              >
                <option value="">-- Sélectionner --</option>
                @foreach($cats as $c)
                  <option value="{{ $c }}" {{ $oldCat===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Sélectionnez une catégorie.</div>
            </div>

            {{-- Intitulé du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du Projet <span class="text-danger">*</span></label>
              <input
                type="text"
                name="intitule_projet"
                value="{{ old('intitule_projet') }}"
                class="form-control"
                data-validate='{"required":true,"message":"L’intitulé est obligatoire"}'
              >
              <div class="invalid-feedback">L’intitulé est obligatoire.</div>
            </div>

            {{-- Lien vers la GED (optionnel) --}}
            <div class="mb-3">
              <label class="form-label">Lien vers la GED (optionnel)</label>
              <input type="url" name="lien_ged" value="{{ old('lien_ged') }}" class="form-control" placeholder="http://...">
            </div>

            {{-- Catégorie du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Projet <span class="text-danger">*</span></label>
              @php
                $catsProjet = ['Commerce','Culte','Equipement de proximité','équipement public','équipement privé','Immeuble','projet agricole','Projet Industriel','Projet touristique','R+1','R+2','RDC','Services','Villa','Autre'];
              @endphp
              <select
                name="categorie_projet"
                class="form-select"
                data-validate='{"required":true,"message":"Sélectionnez la catégorie du projet"}'
              >
                <option value="">-- Sélectionner --</option>
                @foreach($catsProjet as $c)
                  <option value="{{ $c }}" {{ old('categorie_projet')===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Sélectionnez la catégorie du projet.</div>
            </div>

            {{-- Contexte du Projet (⚠️ plus obligatoire) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Contexte du Projet</label>
              @php
                $ctx = ['Assistance Architecturale Douar délimité','Douar non délimité','GP - Régularisation','INDH','Intégration de lots','PP- Régularisation','Régularisation','Relogement','VSB','ZAP'];
              @endphp
              <select name="contexte_projet" class="form-select">
                <option value="">-- Sélectionner (optionnel) --</option>
                @foreach($ctx as $c)
                  <option value="{{ $c }}" {{ old('contexte_projet')===$c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
            </div>

            {{-- Maître d'Œuvre --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Maître d'Œuvre <span class="text-danger">*</span></label>
              <select
                name="maitre_oeuvre"
                class="form-select"
                data-validate='{"required":true,"message":"Sélectionnez le maître d’œuvre"}'
              >
                <option value="">-- Sélectionner --</option>
                @foreach($maitresOeuvre as $mo)
                  <option value="{{ $mo->nom }}" {{ old('maitre_oeuvre')===$mo->nom ? 'selected' : '' }}>{{ $mo->nom }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback">Sélectionnez le maître d’œuvre.</div>
            </div>

            {{-- Situation / Adresse --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Situation / Adresse <span class="text-danger">*</span></label>
              <textarea
                name="situation"
                class="form-control"
                rows="2"
                placeholder="Ex: Quartier X, Rue Y"
                data-validate='{"required":true,"message":"La situation est obligatoire"}'
              >{{ old('situation') }}</textarea>
              <div class="invalid-feedback">La situation est obligatoire.</div>
            </div>

            {{-- Références Foncières (⚠️ plus de validation) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Références Foncières</label>
              <input type="text" name="reference_fonciere" value="{{ old('reference_fonciere') }}" class="form-control" placeholder="ex: 12345/A/2025">
              <div class="form-text">Champ libre (validation supprimée).</div>
            </div>

            {{-- Observations --}}
            <div class="mb-3">
              <label class="form-label">Observations (optionnel)</label>
              <textarea name="observations" class="form-control" rows="3" placeholder="Commentaire libre...">{{ old('observations') }}</textarea>
            </div>

          </div> <!-- Fin col-md-6 (droite) -->
        </div> <!-- Fin row -->

        <div class="d-flex justify-content-end gap-2">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save me-1"></i> Enregistrer le Projet
          </button>

          {{-- Bouton Annuler adapté au rôle --}}
          @if(Auth::user()->hasRole('chef'))
            <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">Annuler</a>
          @elseif(Auth::user()->hasRole('saisie_cpc'))
            <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-secondary">Annuler</a>
          @endif
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  // ------- UI helpers -------
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => [...r.querySelectorAll(s)];

  // Afficher/masquer le champ 'Propriétaire' si 'has_proprietaire' est coché
  $('#has_proprietaire').addEventListener('change', function() {
    $('#proprietaire_div').classList.toggle('d-none', !this.checked);
  });

  // Afficher/masquer la zone "Détails d'envoi" si "Envoi Papier" est coché (inclut n° d'arrivée)
  $('#envoi_papier').addEventListener('change', function() {
    $('#envoi_details').classList.toggle('d-none', !this.checked);
  });

  // Mapping Province -> Communes
  const provincesCommunes = {
    'Préfecture Oujda-Angad': [
      "Commune d'Ahl Angad","Commune d'Ain Sfa","Commune d'Isly","Commune d'Oujda","Commune d'Oujda Sidi Ziane",
      "Commune de Beni Drar","Commune de Beni Khaled","Commune de Labsara","Commune de Mestferki","Commune de Neima",
      "Commune de Oued Ennachef Sidi Mâafa","Commune de Sidi Boulenouar","Commune de Sidi Driss El Qadi",
      "Commune de Sidi Moussa Lemhaya","Commune de Sidi Yahya"
    ],
    'Province Berkane': [
      "Commune d'Aghbal","Commune d'Ahfir","Commune d'Aklim","Commune de Ain Reggada","Commune de Berkane",
      "Commune de Boughriba","Commune de Chouihya","Commune de Fezouane","Commune de Laâtamna","Commune de Madagh",
      "Commune de Rislane","Commune de Saïdia","Commune de Sidi Bouhria","Commune de Sidi Slimane Cheraa",
      "Commune de Tafoughalt","Commune de Zegzel"
    ],
    'Province Jerada': [
      "Commune de Ain Beni Mathar","Commune de Beni Mathar","Commune de Gafait","Commune de Guenfouda",
      "Commune de Jerada","Commune de Laouinate","Commune de Lebkhata","Commune de Mrija","Commune de Oulad Ghziyel",
      "Commune de Oulad Sidi Abdelhakem","Commune de Ras Asfour","Commune de Sidi Boubker","Commune de Tiouli","Commune de Touissit"
    ],
    'Province Taourirt': [
      "Commune d'Ahl Oued Za","Commune de Ain Lehjer","Commune de Debdou","Commune de Gteter","Commune de Machraa Hammadi",
      "Commune de Melg El Ouidane","Commune de Mestegmer","Commune de Oulad M'Hammed","Commune de Sidi Ali Belkassem",
      "Commune de Sidi Lahcen","Commune de Tancherfi","Commune de Taourirt","Commune d'El Aioun Sidi Mellouk","Commune d'El Atef"
    ],
    'Province Figuig': [
      "Commune de Abbou-Lakhal","Commune de Ain Chair","Commune de Ain Chouater","Commune de Beni Guil",
      "Commune de Beni Tadjite","Commune de Bouanane","Commune de Bouarfa","Commune de Bouchaouene",
      "Commune de Boumerieme","Commune de Figuig","Commune de Maatarka","Commune de Talsint","Commune de Tendrara"
    ]
  };

  const provinceSelect = $('#provinceSelect');
  const commune1Select = $('#commune1Select');
  const commune2Select = $('#commune2Select');

  function fillCommunes() {
    const selectedProvince = provinceSelect.value;
    commune1Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
    commune2Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';

    if (provincesCommunes[selectedProvince]) {
      const communes = provincesCommunes[selectedProvince];
      communes.forEach(function(commune) {
        const option1 = document.createElement('option');
        option1.value = commune; option1.text = commune;
        commune1Select.appendChild(option1);

        const option2 = document.createElement('option');
        option2.value = commune; option2.text = commune;
        commune2Select.appendChild(option2);
      });

      const oldComm1 = @json(old('commune_1'));
      const oldComm2 = @json(old('commune_2'));
      if (oldComm1) commune1Select.value = oldComm1;
      if (oldComm2) commune2Select.value = oldComm2;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    fillCommunes();

    // ------- Validation instantanée (on blur / on input) -------
    const form = $('#cpcForm');
    const fields = $$('[data-validate]', form);

    const validators = {
      required: (v) => (v != null && String(v).trim() !== ''),
      pattern : (v, re) => new RegExp(re).test(String(v).trim())
    };

    function validateEl(el) {
      const cfg = JSON.parse(el.dataset.validate || '{}');
      let ok = true;

      if (cfg.required && !validators.required(el.value)) ok = false;
      if (ok && cfg.pattern && el.value) {
        ok = validators.pattern(el.value, cfg.pattern);
      }

      el.classList.toggle('is-invalid', !ok);
      return ok;
    }

    fields.forEach(el => {
      el.addEventListener('blur', () => validateEl(el));
      el.addEventListener('input', () => el.classList.remove('is-invalid'));
      el.addEventListener('change', () => validateEl(el));
    });

    form.addEventListener('submit', (e) => {
      // validation finale avant envoi
      const allOk = fields.map(validateEl).every(Boolean);
      if (!allOk) {
        e.preventDefault();
        const firstInvalid = $('.is-invalid', form);
        if (firstInvalid) firstInvalid.focus();
      }
    });
  });

  provinceSelect.addEventListener('change', fillCommunes);
</script>
@endsection
