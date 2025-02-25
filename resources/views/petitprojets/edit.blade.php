@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('petitprojets.index') }}">Petits Projets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modifier un Petit Projet</li>
@endsection

@section('content')
<div class="container">
    <h1>Modifier le Petit Projet</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('petitprojets.update', $petitProjet->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Exemple de champ pour le numéro du projet -->
        <div class="mb-3">
            <label for="numero_projet" class="form-label">N° Projet</label>
            <input type="text" class="form-control" name="numero_projet" value="{{ old('numero_projet', $petitProjet->numero_projet) }}" required>
        </div>

        <!-- Répète pour les autres champs (titre, province, etc.) -->
        <div class="mb-3">
            <label for="titre_projet" class="form-label">Titre du Projet</label>
            <input type="text" class="form-control" name="titre_projet" value="{{ old('titre_projet', $petitProjet->titre_projet) }}" required>
        </div>

        <!-- Localisation -->
        <div class="mb-3">
            <label for="province" class="form-label">Province/Préfecture</label>
            <input type="text" class="form-control" name="province" value="{{ old('province', $petitProjet->province) }}" required>
        </div>

        <div class="mb-3">
            <label for="commune" class="form-label">Commune</label>
            <select name="commune" class="form-control" required>
                <option value="">Sélectionner la commune</option>
                <option value="Commune 1" {{ old('commune', $petitProjet->commune) == 'Commune 1' ? 'selected' : '' }}>Commune 1</option>
                <option value="Commune 2" {{ old('commune', $petitProjet->commune) == 'Commune 2' ? 'selected' : '' }}>Commune 2</option>
                <!-- Ajoute d'autres options selon ta configuration -->
            </select>
        </div>

        <!-- Pour les autres champs, reproduis un format similaire -->
        <!-- Commission -->
        <div class="mb-3">
            <label for="commission_numero" class="form-label">Numéro Commission</label>
            <input type="text" class="form-control" name="commission_numero" value="{{ old('commission_numero', $petitProjet->commission_numero) }}">
        </div>

        <div class="mb-3">
            <label for="commission_annee" class="form-label">Année Commission</label>
            <input type="text" class="form-control" name="commission_annee" value="{{ old('commission_annee', $petitProjet->commission_annee) }}">
        </div>

        <div class="mb-3">
            <label for="avis_commission" class="form-label">Avis de la Commission</label>
            <select name="avis_commission" class="form-control">
                <option value="">Sélectionner l'avis</option>
                <option value="favorable" {{ old('avis_commission', $petitProjet->avis_commission) == 'favorable' ? 'selected' : '' }}>Favorable</option>
                <option value="defavorable" {{ old('avis_commission', $petitProjet->avis_commission) == 'defavorable' ? 'selected' : '' }}>Défavorable</option>
                <option value="reexamen" {{ old('avis_commission', $petitProjet->avis_commission) == 'reexamen' ? 'selected' : '' }}>Réexamen</option>
            </select>
        </div>

        <!-- Ajoute les autres champs du formulaire en utilisant la même méthode pour pré-remplir -->
        <!-- Par exemple pour motivation_avis, observations, pétitionnaire, etc. -->

        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="{{ route('petitprojets.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
