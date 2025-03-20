{{-- resources/views/grandprojets/cpc/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-success text-white">
      <h2 class="mb-0">Enregistrement d'un Grand Projet - CPC</h2>
    </div>

    <div class="card-body">
      {{-- Affichage des erreurs de validation --}}
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
        <form action="{{ route('chef.grandprojets.cpc.store') }}" method="POST">
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <form action="{{ route('saisie_cpc.cpc.store') }}" method="POST">
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
              <input type="text"
                     name="numero_dossier"
                     class="form-control @error('numero_dossier') is-invalid @enderror"
                     placeholder="ex: 1234/2023 (Chiffres/20XX)"
                     required>
              @error('numero_dossier')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <!-- Province -->
<div class="mb-3">
  <label class="form-label fw-semibold">Province / Préfecture <span class="text-danger">*</span></label>
  <select name="province" id="provinceSelect" class="form-control" required>
    <option value="">-- Sélectionner la Province --</option>
    <option value="Préfecture Oujda-Angad">Préfecture Oujda-Angad</option>
    <option value="Province Berkane">Province Berkane</option>
    <option value="Province Jerada">Province Jerada</option>
    <option value="Province Taourirt">Province Taourirt</option>
    <option value="Province Figuig">Province Figuig</option>
  </select>
</div>

<!-- Commune Principale -->
<div class="mb-3">
  <label class="form-label fw-semibold">Commune Principale <span class="text-danger">*</span></label>
  <select name="commune_1" id="commune1Select" class="form-control" required>
    <option value="">-- Sélectionner la commune --</option>
  </select>
</div>

<!-- Commune Secondaire (Optionnel) -->
<div class="mb-3">
  <label class="form-label">Commune Secondaire (Optionnel)</label>
  <select name="commune_2" id="commune2Select" class="form-control">
    <option value="">-- Sélectionner la commune --</option>
  </select>
</div>


            {{-- Envoi Papier ? --}}
            <div class="mb-3 form-check">
              <input type="checkbox"
                     name="envoi_papier"
                     class="form-check-input"
                     id="envoi_papier">
              <label class="form-check-label fw-semibold" for="envoi_papier">Envoi Papier ?</label>
            </div>

            {{-- Détails d'envoi (Référence / Numéro) --}}
            <div id="envoi_details" class="mb-3 d-none">
              <label class="form-label">Référence d'Envoi</label>
              <input type="text" name="reference_envoi" class="form-control">

              <label class="form-label mt-2">Numéro d'Envoi</label>
              <input type="text" name="numero_envoi" class="form-control">
            </div>

            {{-- Date d'Arrivée --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Date d'Arrivée <span class="text-danger">*</span></label>
              <input type="date" name="date_arrivee" class="form-control" required>
            </div>

            {{-- Date de Commission Interne --}}
            <div class="mb-3">
              <label class="form-label">Date de Commission Interne</label>
              <input type="date" name="date_commission_interne" class="form-control">
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
              <input type="text" name="petitionnaire" class="form-control" required>
            </div>

            {{-- Propriétaire distinct ? --}}
            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="has_proprietaire">
              <label class="form-check-label fw-semibold" for="has_proprietaire">
                Indiquer un Propriétaire distinct
              </label>
            </div>

            {{-- Champ Propriétaire (masqué par défaut) --}}
            <div class="mb-3 d-none" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" class="form-control" placeholder="Nom complet du propriétaire">
            </div>

            {{-- Catégorie du Pétitionnaire --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Pétitionnaire <span class="text-danger">*</span></label>
              <select name="categorie_petitionnaire" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Amicale">Amicale</option>
                <option value="association">Association</option>
                <option value="Bienfaiteurs">Bienfaiteurs</option>
                <option value="Collectivité locale">Collectivité locale</option>
                <option value="coopérative">Coopérative</option>
                <option value="Etablissement public">Etablissement public</option>
                <option value="OPH">OPH</option>
                <option value="Particulier">Particulier</option>
                <option value="RME">RME</option>
                <option value="Société Privé">Société Privé</option>
                <option value="Autre">Autre</option>
              </select>
            </div>

            {{-- Intitulé du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Intitulé du Projet <span class="text-danger">*</span></label>
              <input type="text" name="intitule_projet" class="form-control" required>
            </div>

            {{-- Lien vers la GED (optionnel) --}}
            <div class="mb-3">
              <label class="form-label">Lien vers la GED (optionnel)</label>
              <input type="url" name="lien_ged" class="form-control" placeholder="http://...">
            </div>

            {{-- Catégorie du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Catégorie du Projet <span class="text-danger">*</span></label>
              <select name="categorie_projet" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Commerce">Commerce</option>
                <option value="Culte">Culte</option>
                <option value="Equipement de proximité">Equipement de proximité</option>
                <option value="équipement public">équipement public</option>
                <option value="équipement privé">équipement privé</option>
                <option value="Immeuble">Immeuble</option>
                <option value="projet agricole">Projet Agricole</option>
                <option value="Projet Industriel">Projet Industriel</option>
                <option value="Projet touristique">Projet touristique</option>
                <option value="R+1">R+1</option>
                <option value="R+2">R+2</option>
                <option value="RDC">RDC</option>
                <option value="Services">Services</option>
                <option value="Villa">Villa</option>
                <option value="Autre">Autre</option>
              </select>
            </div>

            {{-- Contexte du Projet --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Contexte du Projet <span class="text-danger">*</span></label>
              <select name="contexte_projet" class="form-control" required>
                <option value="">-- Sélectionner --</option>
                <option value="Assistance Architecturale Douar délimité">Assistance Architecturale Douar délimité</option>
                <option value="Douar non délimité">Douar non délimité</option>
                <option value="GP - Régularisation">GP - Régularisation</option>
                <option value="INDH">INDH</option>
                <option value="Intégration de lots">Intégration de lots</option>
                <option value="PP- Régularisation">PP- Régularisation</option>
                <option value="Régularisation">Régularisation</option>
                <option value="Relogement">Relogement</option>
                <option value="VSB">VSB</option>
                <option value="ZAP">ZAP</option>
              </select>
            </div>

            {{-- Maître d'Œuvre --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Maître d'Œuvre <span class="text-danger">*</span></label>
                <select name="maitre_oeuvre" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach($maitresOeuvre as $mo)
                        <option value="{{ $mo->nom }}">{{ $mo->nom }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Maître d'Ouvrage --}}

            {{-- Situation / Adresse --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Situation / Adresse <span class="text-danger">*</span></label>
              <textarea name="situation" class="form-control" rows="2" placeholder="Ex: Quartier X, Rue Y" required></textarea>
            </div>

            {{-- Références Foncières --}}
            <div class="mb-3">
              <label class="form-label fw-semibold">Références Foncières <span class="text-danger">*</span></label>
              <input type="text"
                     name="reference_fonciere"
                     class="form-control"
                     placeholder="ex: 12345/A/2024"
                     required>
            </div>

            {{-- Observations --}}
            <div class="mb-3">
              <label class="form-label">Observations (optionnel)</label>
              <textarea name="observations" class="form-control" rows="3" placeholder="Commentaire libre..."></textarea>
            </div>

          </div> <!-- Fin col-md-6 (droite) -->
        </div> <!-- Fin row -->

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success me-2">
            <i class="fas fa-save"></i> Enregistrer le Projet
          </button>

          {{-- Bouton Annuler adapté au rôle --}}
          @if(Auth::user()->hasRole('chef'))
            <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">
              Annuler
            </a>
          @elseif(Auth::user()->hasRole('saisie_cpc'))
            <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-secondary">
              Annuler
            </a>
          @endif
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  // Afficher/masquer le champ 'Propriétaire' si 'has_proprietaire' est coché
  document.getElementById('has_proprietaire').addEventListener('change', function() {
    document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
  });

  // Afficher/masquer la zone "Détails d'envoi" si "Envoi Papier" est coché
  document.getElementById('envoi_papier').addEventListener('change', function() {
    document.getElementById('envoi_details').classList.toggle('d-none', !this.checked);
  });
</script>
<script>
  // 1. Définir le mapping Province -> Communes
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

  // Récupérer les <select> dans le DOM
  const provinceSelect = document.getElementById('provinceSelect');
  const commune1Select = document.getElementById('commune1Select');
  const commune2Select = document.getElementById('commune2Select');

  // 2. Écouter le changement de la province
  provinceSelect.addEventListener('change', function() {
    // 2.a : Réinitialiser les listes
    commune1Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';
    commune2Select.innerHTML = '<option value="">-- Sélectionner la commune --</option>';

    // 2.b : Récupérer la valeur de la province sélectionnée
    const selectedProvince = this.value;

    // 2.c : Vérifier si la province est bien présente dans l'objet provincesCommunes
    if (provincesCommunes[selectedProvince]) {
      // 2.d : Récupérer le tableau de communes
      const communes = provincesCommunes[selectedProvince];

      // 2.e : Pour chaque commune, créer un <option> et l'ajouter au <select>
      communes.forEach(function(commune) {
        // Commune Principale
        const option1 = document.createElement('option');
        option1.value = commune;
        option1.text = commune;
        commune1Select.appendChild(option1);

        // Commune Secondaire
        const option2 = document.createElement('option');
        option2.value = commune;
        option2.text = commune;
        commune2Select.appendChild(option2);
      });
    }
  });
</script>

@endsection
