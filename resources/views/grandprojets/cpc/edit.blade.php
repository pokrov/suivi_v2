{{-- resources/views/grandprojets/cpc/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-warning text-white">
      <h2 class="mb-0">Modifier le Grand Projet - CPC</h2>
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
    <form action="{{ route('chef.grandprojets.cpc.update', $grandProjet) }}" method="POST">
@elseif(Auth::user()->hasRole('saisie_cpc'))
    <form action="{{ route('saisie_cpc.cpc.update', $grandProjet) }}" method="POST">
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
                     placeholder="ex: 1234/2023 (Chiffres/20XX)"
                     required>
              @error('numero_dossier')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            {{-- Province --}}
<div class="mb-3">
  <label class="form-label fw-semibold">Province / Préfecture <span class="text-danger">*</span></label>
  <select name="province" id="provinceSelect" class="form-control" required>
    <option value="">-- Sélectionner la Province --</option>
    <option value="Préfecture Oujda-Angad"
      {{ old('province', $grandProjet->province) == 'Préfecture Oujda-Angad' ? 'selected' : '' }}>
      Préfecture Oujda-Angad
    </option>
    <option value="Province Berkane"
      {{ old('province', $grandProjet->province) == 'Province Berkane' ? 'selected' : '' }}>
      Province Berkane
    </option>
    <option value="Province Jerada"
      {{ old('province', $grandProjet->province) == 'Province Jerada' ? 'selected' : '' }}>
      Province Jerada
    </option>
    <option value="Province Taourirt"
      {{ old('province', $grandProjet->province) == 'Province Taourirt' ? 'selected' : '' }}>
      Province Taourirt
    </option>
    <option value="Province Figuig"
      {{ old('province', $grandProjet->province) == 'Province Figuig' ? 'selected' : '' }}>
      Province Figuig
    </option>
  </select>
</div>

{{-- Commune Principale --}}
<div class="mb-3">
  <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
  <select name="commune_1" id="commune1Select" class="form-control" required>
    <option value="">-- Sélectionner la commune --</option>
    {{-- On ne met pas les <option> statiques pour toutes les communes.
         Le script JS va s'en charger dynamiquement. --}}
  </select>
</div>

{{-- Commune Secondaire --}}
<div class="mb-3">
  <label class="form-label">Commune Secondaire (Optionnel)</label>
  <select name="commune_2" id="commune2Select" class="form-control">
    <option value="">-- Aucune --</option>
  </select>
</div>


            {{-- Envoi Papier ? --}}
            @php
              // Savoir si type_envoi = 'papier'
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

            {{-- Date d'Arrivée --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date d'Arrivée <span class="text-danger">*</span></label>
              <input type="date"
                     name="date_arrivee"
                     class="form-control"
                     value="{{ old('date_arrivee', $grandProjet->date_arrivee) }}"
                     required>
            </div>

            {{-- Date de Commission Interne --}}
            <div class="mb-3">
              <label class="form-label">Date de Commission Interne</label>
              <input type="date"
                     name="date_commission_interne"
                     class="form-control"
                     value="{{ old('date_commission_interne', $grandProjet->date_commission_interne) }}">
            </div>
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
                     class="form-control"
                     value="{{ old('petitionnaire', $grandProjet->petitionnaire) }}"
                     required>
            </div>

            {{-- Propriétaire distinct ? --}}
            @php
              // Si proprietaire non vide, cocher la case
              $hasProprietaire = !empty(old('proprietaire', $grandProjet->proprietaire));
            @endphp
            <div class="mb-3 form-check">
              <input type="checkbox"
                     class="form-check-input"
                     id="has_proprietaire"
                     {{ $hasProprietaire ? 'checked' : '' }}>
              <label class="form-check-label fw-semibold" for="has_proprietaire">
                Indiquer un Propriétaire distinct
              </label>
            </div>

            {{-- Champ Propriétaire (masqué par défaut) --}}
            <div class="mb-3 {{ $hasProprietaire ? '' : 'd-none' }}" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text"
                     name="proprietaire"
                     class="form-control"
                     value="{{ old('proprietaire', $grandProjet->proprietaire) }}">
            </div>

            {{-- Catégorie du Pétitionnaire --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Pétitionnaire <span class="text-danger">*</span></label>
              <select name="categorie_petitionnaire" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Amicale"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Amicale' ? 'selected' : '' }}>
                  Amicale
                </option>
                <option value="association"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'association' ? 'selected' : '' }}>
                  Association
                </option>
                <option value="Bienfaiteurs"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Bienfaiteurs' ? 'selected' : '' }}>
                  Bienfaiteurs
                </option>
                <option value="Collectivité locale"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Collectivité locale' ? 'selected' : '' }}>
                  Collectivité locale
                </option>
                <option value="coopérative"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'coopérative' ? 'selected' : '' }}>
                  coopérative
                </option>
                <option value="Etablissement public"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Etablissement public' ? 'selected' : '' }}>
                  Etablissement public
                </option>
                <option value="OPH"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'OPH' ? 'selected' : '' }}>
                  OPH
                </option>
                <option value="Particulier"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Particulier' ? 'selected' : '' }}>
                  Particulier
                </option>
                <option value="RME"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'RME' ? 'selected' : '' }}>
                  RME
                </option>
                <option value="Société Privé"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Société Privé' ? 'selected' : '' }}>
                  Société Privé
                </option>
                <option value="Autre"
                  {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == 'Autre' ? 'selected' : '' }}>
                  Autre
                </option>
              </select>
            </div>

            {{-- Intitulé du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du Projet <span class="text-danger">*</span></label>
              <input type="text"
                     name="intitule_projet"
                     class="form-control"
                     value="{{ old('intitule_projet', $grandProjet->intitule_projet) }}"
                     required>
            </div>

            {{-- Lien vers la GED --}}
            <div class="mb-3">
              <label class="form-label">Lien vers la GED (optionnel)</label>
              <input type="url"
                     name="lien_ged"
                     class="form-control"
                     value="{{ old('lien_ged', $grandProjet->lien_ged) }}">
            </div>

            {{-- Catégorie du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Projet <span class="text-danger">*</span></label>
              <select name="categorie_projet" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Commerce"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Commerce' ? 'selected' : '' }}>
                  Commerce
                </option>
                <option value="Culte"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Culte' ? 'selected' : '' }}>
                  Culte
                </option>
                <option value="Equipement de proximité"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Equipement de proximité' ? 'selected' : '' }}>
                  Equipement de proximité
                </option>
                <option value="équipement public"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'équipement public' ? 'selected' : '' }}>
                  équipement public
                </option>
                <option value="équipement privé"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'équipement privé' ? 'selected' : '' }}>
                  équipement privé
                </option>
                <option value="Immeuble"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Immeuble' ? 'selected' : '' }}>
                  Immeuble
                </option>
                <option value="projet agricole"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'projet agricole' ? 'selected' : '' }}>
                  projet agricole
                </option>
                <option value="Projet Industriel"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Projet Industriel' ? 'selected' : '' }}>
                  Projet Industriel
                </option>
                <option value="Projet touristique"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Projet touristique' ? 'selected' : '' }}>
                  Projet touristique
                </option>
                <option value="R+1"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'R+1' ? 'selected' : '' }}>
                  R+1
                </option>
                <option value="R+2"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'R+2' ? 'selected' : '' }}>
                  R+2
                </option>
                <option value="RDC"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'RDC' ? 'selected' : '' }}>
                  RDC
                </option>
                <option value="Services"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Services' ? 'selected' : '' }}>
                  Services
                </option>
                <option value="Villa"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Villa' ? 'selected' : '' }}>
                  Villa
                </option>
                <option value="Autre"
                  {{ old('categorie_projet', $grandProjet->categorie_projet) == 'Autre' ? 'selected' : '' }}>
                  Autre
                </option>
              </select>
            </div>

            {{-- Contexte du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Contexte du Projet <span class="text-danger">*</span></label>
              <select name="contexte_projet" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Assistance Architecturale Douar délimité"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'Assistance Architecturale Douar délimité' ? 'selected' : '' }}>
                  Assistance Architecturale Douar délimité
                </option>
                <option value="Douar non délimité"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'Douar non délimité' ? 'selected' : '' }}>
                  Douar non délimité
                </option>
                <option value="GP - Régularisation"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'GP - Régularisation' ? 'selected' : '' }}>
                  GP - Régularisation
                </option>
                <option value="INDH"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'INDH' ? 'selected' : '' }}>
                  INDH
                </option>
                <option value="Intégration de lots"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'Intégration de lots' ? 'selected' : '' }}>
                  Intégration de lots
                </option>
                <option value="PP- Régularisation"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'PP- Régularisation' ? 'selected' : '' }}>
                  PP- Régularisation
                </option>
                <option value="Régularisation"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'Régularisation' ? 'selected' : '' }}>
                  Régularisation
                </option>
                <option value="Relogement"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'Relogement' ? 'selected' : '' }}>
                  Relogement
                </option>
                <option value="VSB"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'VSB' ? 'selected' : '' }}>
                  VSB
                </option>
                <option value="ZAP"
                  {{ old('contexte_projet', $grandProjet->contexte_projet) == 'ZAP' ? 'selected' : '' }}>
                  ZAP
                </option>
              </select>
            </div>

            {{-- Maître d'Œuvre --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Maître d'Œuvre <span class="text-danger">*</span></label>
                <select name="maitre_oeuvre" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($maitresOeuvre as $mo)
                        <option value="{{ $mo->nom }}" 
                            {{ old('maitre_oeuvre', $grandProjet->maitre_oeuvre) == $mo->nom ? 'selected' : '' }}>
                            {{ $mo->nom }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Situation (Adresse) --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Situation / Adresse <span class="text-danger">*</span></label>
              <textarea name="situation"
                        class="form-control"
                        rows="2"
                        required>{{ old('situation', $grandProjet->situation) }}</textarea>
            </div>

            {{-- Références Foncières --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Références Foncières <span class="text-danger">*</span></label>
              <input type="text"
                     name="reference_fonciere"
                     class="form-control"
                     value="{{ old('reference_fonciere', $grandProjet->reference_fonciere) }}"
                     placeholder="ex: 12345/A/2024"
                     required>
            </div>

            {{-- Observations --}}
            <div class="mb-3">
              <label class="form-label">Observations (optionnel)</label>
              <textarea name="observations" class="form-control" rows="3">
                {{ old('observations', $grandProjet->observations) }}
              </textarea>
            </div>
          </div> <!-- Fin col-md-6 -->
        </div> <!-- Fin row -->

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-save"></i> Mettre à jour le projet
          </button>
          <a href="{{ Auth::user()->hasRole('saisie_cpc') ? route('saisie_cpc.dashboard') : route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">
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
  // 1. Mapping Province -> Communes
  const provincesCommunes = {
    'Préfecture Oujda-Angad': [
      "Commune d'Ahl Angad",
      "Commune d'Ain Sfa",
      "Commune d'Isly",
      "Commune d'Oujda",
      "Commune d'Oujda Sidi Ziane",
      "Commune de Beni Drar",
      "Commune de Beni Khaled",
      "Commune de Labsara",
      "Commune de Mestferki",
      "Commune de Neima",
      "Commune de Oued Ennachef Sidi Mâafa",
      "Commune de Sidi Boulenouar",
      "Commune de Sidi Driss El Qadi",
      "Commune de Sidi Moussa Lemhaya",
      "Commune de Sidi Yahya"
    ],
    'Province Berkane': [
      "Commune d'Aghbal",
      "Commune d'Ahfir",
      "Commune d'Aklim",
      "Commune de Ain Reggada",
      "Commune de Berkane",
      "Commune de Boughriba",
      "Commune de Chouihya",
      "Commune de Fezouane",
      "Commune de Laâtamna",
      "Commune de Madagh",
      "Commune de Rislane",
      "Commune de Saïdia",
      "Commune de Sidi Bouhria",
      "Commune de Sidi Slimane Cheraa",
      "Commune de Tafoughalt",
      "Commune de Zegzel"
    ],
    'Province Jerada': [
      "Commune de Ain Beni Mathar",
      "Commune de Beni Mathar",
      "Commune de Gafait",
      "Commune de Guenfouda",
      "Commune de Jerada",
      "Commune de Laouinate",
      "Commune de Lebkhata",
      "Commune de Mrija",
      "Commune de Oulad Ghziyel",
      "Commune de Oulad Sidi Abdelhakem",
      "Commune de Ras Asfour",
      "Commune de Sidi Boubker",
      "Commune de Tiouli",
      "Commune de Touissit"
    ],
    'Province Taourirt': [
      "Commune d'Ahl Oued Za",
      "Commune de Ain Lehjer",
      "Commune de Debdou",
      "Commune de Gteter",
      "Commune de Machraa Hammadi",
      "Commune de Melg El Ouidane",
      "Commune de Mestegmer",
      "Commune de Oulad M'Hammed",
      "Commune de Sidi Ali Belkassem",
      "Commune de Sidi Lahcen",
      "Commune de Tancherfi",
      "Commune de Taourirt",
      "Commune d'El Aioun Sidi Mellouk",
      "Commune d'El Atef"
    ],
    'Province Figuig': [
      "Commune de Abbou-Lakhal",
      "Commune de Ain Chair",
      "Commune de Ain Chouater",
      "Commune de Beni Guil",
      "Commune de Beni Tadjite",
      "Commune de Bouanane",
      "Commune de Bouarfa",
      "Commune de Bouchaouene",
      "Commune de Boumerieme",
      "Commune de Figuig",
      "Commune de Maatarka",
      "Commune de Talsint",
      "Commune de Tendrara"
    ]
  };

  // 2. Récupérer les <select> dans le DOM
  const provinceSelect = document.getElementById('provinceSelect');
  const commune1Select = document.getElementById('commune1Select');
  const commune2Select = document.getElementById('commune2Select');

  // 2.a fonction pour charger les communes correspondant à la province
  // + gérer la commune sélectionnée
  function loadCommunes(province, selectedCommune1 = '', selectedCommune2 = '') {
    // vider les options existantes
    commune1Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
    commune2Select.innerHTML = '<option value="">-- Aucune --</option>';

    // vérifier si la province est dans l'objet
    if (provincesCommunes[province]) {
      const communes = provincesCommunes[province];
      communes.forEach((c) => {
        // Pour commune_1
        const opt1 = document.createElement('option');
        opt1.value = c;
        opt1.text = c;
        if (c === selectedCommune1) {
          opt1.selected = true; // si c'est la commune enregistrée, on la sélectionne
        }
        commune1Select.appendChild(opt1);

        // Pour commune_2
        const opt2 = document.createElement('option');
        opt2.value = c;
        opt2.text = c;
        if (c === selectedCommune2) {
          opt2.selected = true;
        }
        commune2Select.appendChild(opt2);
      });
    }
  }

  // 2.b écouter le changement de la province
  provinceSelect.addEventListener('change', function() {
    const prov = this.value;
    // On ne sait pas quelle commune est choisie => on n'en met pas pour le moment
    loadCommunes(prov);
  });

  // 3. Au chargement de la page, si le projet a déjà une province/commune
  document.addEventListener('DOMContentLoaded', function() {
    // Récupérer la province sélectionnée
    const currentProvince = provinceSelect.value;
    // Récupérer la commune1/commune2 du grandProjet
    const currentCommune1 = "{{ old('commune_1', $grandProjet->commune_1) }}";
    const currentCommune2 = "{{ old('commune_2', $grandProjet->commune_2) }}";

    // si on a déjà une province, on charge ses communes
    if (currentProvince) {
      loadCommunes(currentProvince, currentCommune1, currentCommune2);
    }
  });
</script>

@endsection
