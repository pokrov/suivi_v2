{{-- resources/views/grandprojets/cpc/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-warning text-white d-flex align-items-center justify-content-between">
      <h2 class="mb-0">Modifier le Grand Projet - CPC</h2>
      <small class="opacity-75">Étape : Mise à jour de la saisie</small>
    </div>

    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      {{-- Formulaire d'édition du CPC --}}
      @if(Auth::user()->hasRole('chef'))
        <form action="{{ route('chef.grandprojets.cpc.update', $grandProjet) }}" method="POST" novalidate>
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <form action="{{ route('saisie_cpc.cpc.update', $grandProjet) }}" method="POST" novalidate>
      @endif
        @csrf
        @method('PUT')

        <div class="row">
          <!-- =========================================
               Colonne gauche : Identification & Localisation
          ========================================= -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>

            {{-- Numéro de Dossier --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Numéro de Dossier <span class="text-danger">*</span></label>
              <input type="text"
                     name="numero_dossier"
                     class="form-control @error('numero_dossier') is-invalid @enderror"
                     value="{{ old('numero_dossier', $grandProjet->numero_dossier) }}"
                     placeholder="ex: 1234/2025 (Chiffres/20XX)"
                     required>
              @error('numero_dossier')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
              <div class="form-text">Format attendu : <code>nombre/20XX</code> (ex. 452/2025)</div>
            </div>

            {{-- Province / Préfecture --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Province / Préfecture <span class="text-danger">*</span></label>
              <select name="province" id="provinceSelect" class="form-control @error('province') is-invalid @enderror" required>
                <option value="">-- Sélectionner la Province --</option>
                @foreach([
                  'Préfecture Oujda-Angad',
                  'Province Berkane',
                  'Province Jerada',
                  'Province Taourirt',
                  'Province Figuig'
                ] as $prov)
                  <option value="{{ $prov }}" {{ old('province', $grandProjet->province) === $prov ? 'selected' : '' }}>
                    {{ $prov }}
                  </option>
                @endforeach
              </select>
              @error('province') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Commune Principale --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
              <select name="commune_1" id="commune1Select" class="form-control @error('commune_1') is-invalid @enderror" required>
                <option value="">-- Sélectionner la commune --</option>
              </select>
              @error('commune_1') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Commune Secondaire (Optionnel) --}}
            <div class="mb-3">
              <label class="form-label">Commune Secondaire (Optionnel)</label>
              <select name="commune_2" id="commune2Select" class="form-control">
                <option value="">-- Aucune --</option>
              </select>
            </div>

            {{-- Envoi Papier ? --}}
            @php
              $isPapier = old('type_envoi', $grandProjet->type_envoi) === 'papier';
            @endphp
            <div class="mb-3 form-check">
              <input type="checkbox"
                     name="envoi_papier"
                     class="form-check-input"
                     id="envoi_papier"
                     {{ $isPapier ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="envoi_papier">Envoi Papier ?</label>
            </div>

            {{-- Détails d'envoi (Référence / Numéro) --}}
            <div id="envoi_details" class="mb-3 {{ $isPapier ? '' : 'd-none' }}">
              <label class="form-label">Référence d'Envoi</label>
              <input type="text"
                     name="reference_envoi"
                     class="form-control"
                     value="{{ old('reference_envoi', $grandProjet->reference_envoi) }}">

              <label class="form-label mt-2">Numéro d'Envoi</label>
              <input type="text"
                     name="numero_envoi"
                     class="form-control"
                     value="{{ old('numero_envoi', $grandProjet->numero_envoi) }}">
            </div>

            {{-- Numéro d’Arrivée --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Numéro d’Arrivée</label>
              <input type="text"
                     name="numero_arrivee"
                     class="form-control"
                     value="{{ old('numero_arrivee', $grandProjet->numero_arrivee) }}"
                     placeholder="ex: 1234">
              <div class="form-text">Numéro d’enregistrement interne (si disponible).</div>
            </div>

            {{-- Date d'Arrivée --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date d'Arrivée <span class="text-danger">*</span></label>
              <input type="date"
                     name="date_arrivee"
                     class="form-control @error('date_arrivee') is-invalid @enderror"
                     value="{{ old('date_arrivee', $grandProjet->date_arrivee) }}"
                     required>
              @error('date_arrivee') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Date de Commission MIXTE (Rokhas) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date de Commission Mixte (Rokhas)</label>
              <input type="date"
                     name="date_commission_mixte"
                     class="form-control"
                     value="{{ old('date_commission_mixte', $grandProjet->date_commission_mixte) }}">
              <div class="form-text">Peut être modifiée après retour de navette si la date Rokhas change.</div>
            </div>

            {{-- ⚠️ Pas de date_commission_interne ici : gérée en phase Commission interne --}}
          </div>

          <!-- =========================================
               Colonne droite : Informations du Projet
          ========================================= -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            {{-- Pétitionnaire --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Nom du Pétitionnaire <span class="text-danger">*</span></label>
              <input type="text"
                     name="petitionnaire"
                     class="form-control @error('petitionnaire') is-invalid @enderror"
                     value="{{ old('petitionnaire', $grandProjet->petitionnaire) }}"
                     required>
              @error('petitionnaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Propriétaire distinct ? --}}
            @php
              $hasProprietaire = !empty(old('proprietaire', $grandProjet->proprietaire));
            @endphp
            <div class="mb-3 form-check">
              <input type="checkbox"
                     class="form-check-input"
                     id="has_proprietaire"
                     {{ $hasProprietaire ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="has_proprietaire">Indiquer un Propriétaire distinct</label>
            </div>

            {{-- Champ Propriétaire (masqué par défaut) --}}
            <div class="mb-3 {{ $hasProprietaire ? '' : 'd-none' }}" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text"
                     name="proprietaire"
                     class="form-control"
                     value="{{ old('proprietaire', $grandProjet->proprietaire) }}"
                     placeholder="Nom complet du propriétaire">
            </div>

            {{-- Catégorie du Pétitionnaire --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Pétitionnaire <span class="text-danger">*</span></label>
              <select name="categorie_petitionnaire" class="form-control @error('categorie_petitionnaire') is-invalid @enderror" required>
                <option value="">-- Sélectionner --</option>
                @foreach([
                  'Amicale','association','Bienfaiteurs','Collectivité locale','coopérative',
                  'Etablissement public','OPH','Particulier','RME','Société Privé','Autre'
                ] as $cat)
                  <option value="{{ $cat }}" {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
              </select>
              @error('categorie_petitionnaire') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Intitulé du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du Projet <span class="text-danger">*</span></label>
              <input type="text"
                     name="intitule_projet"
                     class="form-control @error('intitule_projet') is-invalid @enderror"
                     value="{{ old('intitule_projet', $grandProjet->intitule_projet) }}"
                     required>
              @error('intitule_projet') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Lien vers la GED (optionnel) --}}
            <div class="mb-3">
              <label class="form-label">Lien vers la GED (optionnel)</label>
              <input type="url"
                     name="lien_ged"
                     class="form-control"
                     value="{{ old('lien_ged', $grandProjet->lien_ged) }}"
                     placeholder="http://...">
            </div>

            {{-- Catégorie du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Projet <span class="text-danger">*</span></label>
              @php
                $cats = ['Commerce','Culte','Equipement de proximité','équipement public','équipement privé','Immeuble','projet agricole','Projet Industriel','Projet touristique','R+1','R+2','RDC','Services','Villa','Autre'];
              @endphp
              <select name="categorie_projet" class="form-control @error('categorie_projet') is-invalid @enderror" required>
                <option value="">-- Sélectionner --</option>
                @foreach($cats as $c)
                  <option value="{{ $c }}" {{ old('categorie_projet', $grandProjet->categorie_projet) === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              @error('categorie_projet') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Contexte du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Contexte du Projet <span class="text-danger">*</span></label>
              @php
                $ctx = ['Assistance Architecturale Douar délimité','Douar non délimité','GP - Régularisation','INDH','Intégration de lots','PP- Régularisation','Régularisation','Relogement','VSB','ZAP'];
              @endphp
              <select name="contexte_projet" class="form-control @error('contexte_projet') is-invalid @enderror" required>
                <option value="">-- Sélectionner --</option>
                @foreach($ctx as $c)
                  <option value="{{ $c }}" {{ old('contexte_projet', $grandProjet->contexte_projet) === $c ? 'selected' : '' }}>{{ $c }}</option>
                @endforeach
              </select>
              @error('contexte_projet') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Maître d'Œuvre --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Maître d'Œuvre <span class="text-danger">*</span></label>
                <select name="maitre_oeuvre" class="form-control @error('maitre_oeuvre') is-invalid @enderror" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($maitresOeuvre as $mo)
                        <option value="{{ $mo->nom }}"
                          {{ old('maitre_oeuvre', $grandProjet->maitre_oeuvre) === $mo->nom ? 'selected' : '' }}>
                          {{ $mo->nom }}
                        </option>
                    @endforeach
                </select>
                @error('maitre_oeuvre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Situation (Adresse) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Situation / Adresse <span class="text-danger">*</span></label>
              <textarea name="situation"
                        class="form-control @error('situation') is-invalid @enderror"
                        rows="2"
                        required>{{ old('situation', $grandProjet->situation) }}</textarea>
              @error('situation') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Références Foncières --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Références Foncières <span class="text-danger">*</span></label>
              <input type="text"
                     name="reference_fonciere"
                     class="form-control @error('reference_fonciere') is-invalid @enderror"
                     value="{{ old('reference_fonciere', $grandProjet->reference_fonciere) }}"
                     placeholder="ex: 12345/A/2025"
                     required>
              @error('reference_fonciere') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Observations --}}
            <div class="mb-3">
              <label class="form-label">Observations (optionnel)</label>
              <textarea name="observations" class="form-control" rows="3">{{ old('observations', $grandProjet->observations) }}</textarea>
            </div>
          </div> <!-- Fin col-md-6 -->
        </div> <!-- Fin row -->

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-save me-1"></i> Mettre à jour le projet
          </button>
          <a href="{{ Auth::user()->hasRole('saisie_cpc') ? route('saisie_cpc.dashboard') : route('chef.grandprojets.cpc.index') }}"
             class="btn btn-secondary">
            Annuler
          </a>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  // Afficher / masquer le champ 'propriétaire'
  document.getElementById('has_proprietaire').addEventListener('change', function() {
    document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
  });

  // Afficher / masquer la zone "Détails d'envoi" si "Envoi Papier" est coché
  document.getElementById('envoi_papier').addEventListener('change', function() {
    document.getElementById('envoi_details').classList.toggle('d-none', !this.checked);
  });
</script>
<script>
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

  const provinceSelect = document.getElementById('provinceSelect');
  const commune1Select = document.getElementById('commune1Select');
  const commune2Select = document.getElementById('commune2Select');

  function loadCommunes(province, selectedCommune1 = '', selectedCommune2 = '') {
    commune1Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
    commune2Select.innerHTML = '<option value="">-- Aucune --</option>';

    if (provincesCommunes[province]) {
      const communes = provincesCommunes[province];
      communes.forEach((c) => {
        const opt1 = document.createElement('option');
        opt1.value = c; opt1.text = c;
        if (c === selectedCommune1) opt1.selected = true;
        commune1Select.appendChild(opt1);

        const opt2 = document.createElement('option');
        opt2.value = c; opt2.text = c;
        if (c === selectedCommune2) opt2.selected = true;
        commune2Select.appendChild(opt2);
      });
    }
  }

  provinceSelect.addEventListener('change', function() {
    loadCommunes(this.value);
  });

  document.addEventListener('DOMContentLoaded', function() {
    const currentProvince = provinceSelect.value;
    const currentCommune1 = @json(old('commune_1', $grandProjet->commune_1));
    const currentCommune2 = @json(old('commune_2', $grandProjet->commune_2));

    if (currentProvince) {
      loadCommunes(currentProvince, currentCommune1, currentCommune2);
    }
  });
</script>
@endsection
