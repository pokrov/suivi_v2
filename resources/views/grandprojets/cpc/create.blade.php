{{-- resources/views/grandprojets/cpc/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-success text-white">
      <h2 class="mb-0">Enregistrer un Grand Projet - CPC</h2>
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

      {{-- Depending on the user role, we use different route actions --}}
      @if(Auth::user()->hasRole('chef'))
          <form action="{{ route('chef.grandprojets.cpc.store') }}" method="POST">
      @elseif(Auth::user()->hasRole('saisie_cpc'))
          <form action="{{ route('saisie_cpc.cpc.store') }}" method="POST">
      @endif

        @csrf

        <div class="row">
          <!-- Colonne gauche : Identification & Localisation -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>
            
            <div class="mb-3">
              <label class="form-label">Numéro de Dossier</label>
              <input type="text" name="numero_dossier"
                     class="form-control" placeholder="ex: 1234/2023" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Province/Préfecture</label>
              <select name="province" class="form-control" required>
                <option value="Préfecture Oujda-Angad" selected>Préfecture Oujda-Angad</option>
                <option value="Province Berkane">Province Berkane</option>
                <option value="Province Jerada">Province Jerada</option>
                <option value="Province Taourirt">Province Taourirt</option>
                <option value="Province Figuig">Province Figuig</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Commune 1</label>
              <select name="commune_1" class="form-control" required>
                <option value="">Sélectionner la commune</option>
                <option value="Commune A">Commune A</option>
                <option value="Commune B">Commune B</option>
                <option value="Commune C">Commune C</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Commune 2 (Optionnel)</label>
              <select name="commune_2" class="form-control">
                <option value="">Sélectionner la commune</option>
                <option value="Commune A">Commune A</option>
                <option value="Commune B">Commune B</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Type d'envoi</label>
              <select name="type_envoi" class="form-control" id="type_envoi">
                <option value="">Sélectionner le type d'envoi</option>
                <option value="papier">Papier</option>
                <option value="email">Email</option>
              </select>
            </div>

            <div id="envoi_details" class="mb-3 d-none">
              <label class="form-label">Référence d'Envoi</label>
              <input type="text" name="reference_envoi" class="form-control">
              <label class="form-label mt-2">Numéro d'Envoi</label>
              <input type="text" name="numero_envoi" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Date d'Arrivée</label>
              <input type="date" name="date_arrivee" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Date de Commission Interne</label>
              <input type="date" name="date_commission_interne" class="form-control">
            </div>
          </div>

          <!-- Colonne droite : Informations du Projet -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            <div class="mb-3">
              <label class="form-label">Pétitionnaire</label>
              <input type="text" name="petitionnaire" class="form-control" required>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="has_proprietaire">
              <label class="form-check-label" for="has_proprietaire">Indiquer le Propriétaire</label>
            </div>

            <div class="mb-3 d-none" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Catégorie du Pétitionnaire</label>
              <select name="categorie_petitionnaire" class="form-control" required>
                <option value="">Sélectionner la catégorie</option>
                <option value="Particulier">Particulier</option>
                <option value="Entreprise">Entreprise</option>
                <option value="Collectivité">Collectivité</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Intitulé du Projet</label>
              <input type="text" name="intitule_projet" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Lien vers la GED</label>
              <input type="url" name="lien_ged" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Catégorie du Projet</label>
              <select name="categorie_projet" class="form-control" required>
                <option value="">Sélectionner la catégorie</option>
                <option value="CPC" selected>CPC (Projet de Construction)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Contexte du Projet</label>
              <select name="contexte_projet" class="form-control" required>
                <option value="">Sélectionner le contexte</option>
                <option value="Urbanisation">Urbanisation</option>
                <option value="Renouvellement">Renouvellement</option>
                <option value="Extension">Extension</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Maître d'Œuvre</label>
              <select name="maitre_oeuvre" class="form-control" required>
                <option value="">Sélectionner le maître d'œuvre</option>
                <option value="Entreprise A">Entreprise A</option>
                <option value="Entreprise B">Entreprise B</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Situation (Adresse)</label>
              <textarea name="situation" class="form-control" rows="2" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Références Foncières</label>
              <input type="text" name="reference_fonciere" class="form-control"
                     placeholder="ex: 12345/A/2024" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Observations</label>
              <textarea name="observations" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-success me-2">Enregistrer le Projet</button>

          {{-- Annuler link depends on role --}}
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
  // Toggle Propriétaire field
  document.getElementById('has_proprietaire').addEventListener('change', function() {
    document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
  });

  // Toggle Envoi details if "papier"
  document.getElementById('type_envoi').addEventListener('change', function() {
    document.getElementById('envoi_details').classList.toggle('d-none', this.value !== 'papier');
  });
</script>
@endsection
