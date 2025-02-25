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

      <form action="{{ route('chef.grandprojets.cpc.update', $grandProjet->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
          <!-- Colonne gauche : Identification & Localisation -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Identification & Localisation</h5>

            <div class="mb-3">
              <label class="form-label">Numéro de Dossier</label>
              <input type="text" name="numero_dossier" class="form-control" value="{{ old('numero_dossier', $grandProjet->numero_dossier) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Province/Préfecture</label>
              <select name="province" class="form-control" required>
                @foreach(['Préfecture Oujda-Angad', 'Province Berkane', 'Province Jerada', 'Province Taourirt', 'Province Figuig'] as $province)
                  <option value="{{ $province }}" {{ old('province', $grandProjet->province) == $province ? 'selected' : '' }}>
                    {{ $province }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Commune 1</label>
              <select name="commune_1" class="form-control" required>
                <option value="">Sélectionner la commune</option>
                @foreach(['Commune A', 'Commune B', 'Commune C'] as $commune)
                  <option value="{{ $commune }}" {{ old('commune_1', $grandProjet->commune_1) == $commune ? 'selected' : '' }}>
                    {{ $commune }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Commune 2 (Optionnel)</label>
              <select name="commune_2" class="form-control">
                <option value="">Sélectionner la commune</option>
                @foreach(['Commune A', 'Commune B'] as $commune)
                  <option value="{{ $commune }}" {{ old('commune_2', $grandProjet->commune_2) == $commune ? 'selected' : '' }}>
                    {{ $commune }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Type d'envoi</label>
              <select name="type_envoi" class="form-control" id="type_envoi">
                <option value="">Sélectionner le type d'envoi</option>
                <option value="papier" {{ old('type_envoi', $grandProjet->type_envoi) == 'papier' ? 'selected' : '' }}>Papier</option>
                <option value="email" {{ old('type_envoi', $grandProjet->type_envoi) == 'email' ? 'selected' : '' }}>Email</option>
              </select>
            </div>

            <div id="envoi_details" class="mb-3 {{ old('type_envoi', $grandProjet->type_envoi) == 'papier' ? '' : 'd-none' }}">
              <label class="form-label">Référence d'Envoi</label>
              <input type="text" name="reference_envoi" class="form-control" value="{{ old('reference_envoi', $grandProjet->reference_envoi) }}">
              <label class="form-label mt-2">Numéro d'Envoi</label>
              <input type="text" name="numero_envoi" class="form-control" value="{{ old('numero_envoi', $grandProjet->numero_envoi) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Date d'Arrivée</label>
              <input type="date" name="date_arrivee" class="form-control" value="{{ old('date_arrivee', $grandProjet->date_arrivee) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Date de Commission Interne</label>
              <input type="date" name="date_commission_interne" class="form-control" value="{{ old('date_commission_interne', $grandProjet->date_commission_interne) }}">
            </div>
          </div>

          <!-- Colonne droite : Informations du Projet -->
          <div class="col-md-6">
            <h5 class="mb-3 text-secondary border-bottom pb-2">Informations du Projet</h5>

            <div class="mb-3">
              <label class="form-label">Pétitionnaire</label>
              <input type="text" name="petitionnaire" class="form-control" value="{{ old('petitionnaire', $grandProjet->petitionnaire) }}" required>
            </div>

            <div class="mb-3 form-check">
              <input type="checkbox" class="form-check-input" id="has_proprietaire" {{ old('proprietaire', $grandProjet->proprietaire) ? 'checked' : '' }}>
              <label class="form-check-label" for="has_proprietaire">Indiquer le Propriétaire</label>
            </div>

            <div class="mb-3 {{ old('proprietaire', $grandProjet->proprietaire) ? '' : 'd-none' }}" id="proprietaire_div">
              <label class="form-label">Propriétaire</label>
              <input type="text" name="proprietaire" class="form-control" value="{{ old('proprietaire', $grandProjet->proprietaire) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Catégorie du Pétitionnaire</label>
              <select name="categorie_petitionnaire" class="form-control" required>
                <option value="">Sélectionner la catégorie</option>
                @foreach(['Particulier', 'Entreprise', 'Collectivité'] as $categorie)
                  <option value="{{ $categorie }}" {{ old('categorie_petitionnaire', $grandProjet->categorie_petitionnaire) == $categorie ? 'selected' : '' }}>
                    {{ $categorie }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Intitulé du Projet</label>
              <input type="text" name="intitule_projet" class="form-control" value="{{ old('intitule_projet', $grandProjet->intitule_projet) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Lien vers la GED</label>
              <input type="url" name="lien_ged" class="form-control" value="{{ old('lien_ged', $grandProjet->lien_ged) }}">
            </div>

            <div class="mb-3">
              <label class="form-label">Catégorie du Projet</label>
              <select name="categorie_projet" class="form-control" required>
                <option value="CPC" {{ old('categorie_projet', $grandProjet->categorie_projet) == 'CPC' ? 'selected' : '' }}>CPC (Projet de Construction)</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Contexte du Projet</label>
              <select name="contexte_projet" class="form-control" required>
                @foreach(['Urbanisation', 'Renouvellement', 'Extension'] as $context)
                  <option value="{{ $context }}" {{ old('contexte_projet', $grandProjet->contexte_projet) == $context ? 'selected' : '' }}>
                    {{ $context }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Maître d'Œuvre</label>
              <select name="maitre_oeuvre" class="form-control" required>
                @foreach(['Entreprise A', 'Entreprise B'] as $maitre)
                  <option value="{{ $maitre }}" {{ old('maitre_oeuvre', $grandProjet->maitre_oeuvre) == $maitre ? 'selected' : '' }}>
                    {{ $maitre }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Situation (Adresse)</label>
              <textarea name="situation" class="form-control" rows="2" required>{{ old('situation', $grandProjet->situation) }}</textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Références Foncières</label>
              <input type="text" name="reference_fonciere" class="form-control" value="{{ old('reference_fonciere', $grandProjet->reference_fonciere) }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Observations</label>
              <textarea name="observations" class="form-control" rows="3">{{ old('observations', $grandProjet->observations) }}</textarea>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end">
          <button type="submit" class="btn btn-primary me-2">
            <i class="fas fa-save"></i> Mettre à jour le projet
          </button>
          <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">Annuler</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('has_proprietaire').addEventListener('change', function() {
    document.getElementById('proprietaire_div').classList.toggle('d-none', !this.checked);
  });

  document.getElementById('type_envoi').addEventListener('change', function() {
    document.getElementById('envoi_details').classList.toggle('d-none', this.value !== 'papier');
  });
</script>
@endsection
