@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('petitprojets.index') }}">Petits Projets</a></li>
    <li class="breadcrumb-item active" aria-current="page">Créer un Petit Projet</li>
@endsection

@section('content')
<div class="container">
    <div class="card shadow-lg border-primary">
        <div class="card-header bg-primary text-white">
            <h2 class="mb-0">Créer un Petit Projet</h2>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('petitprojets.store') }}" method="POST">
                @csrf
                <div class="row">
                    <!-- Colonne gauche : Informations Générales & Commission -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-secondary border-bottom pb-2">Informations Générales & Commission</h5>

                        <div class="mb-3">
                            <label for="numero_projet" class="form-label">N° Projet</label>
                            <input type="text" class="form-control" name="numero_projet" required>
                        </div>

                        <div class="mb-3">
                            <label for="titre_projet" class="form-label">Titre du Projet</label>
                            <input type="text" class="form-control" name="titre_projet" required>
                        </div>

                        <!-- Localisation -->
                        <div class="mb-3">
                            <label for="province" class="form-label">Province/Préfecture</label>
                            <input type="text" class="form-control" name="province" value="Préfecture Oujda-Angad" readonly>
                        </div>

                        <div class="mb-3">
                            <label for="commune" class="form-label">Commune</label>
                            <select name="commune" class="form-control" required>
                                <option value="">Sélectionner la commune</option>
                                <option value="Commune 1">Commune 1</option>
                                <option value="Commune 2">Commune 2</option>
                                <!-- Ajoutez les autres communes -->
                            </select>
                        </div>

                        <!-- Informations sur la commission -->
                        <div class="mb-3">
                            <label for="commission_numero" class="form-label">Numéro Commission</label>
                            <input type="text" class="form-control" name="commission_numero">
                        </div>

                        <div class="mb-3">
                            <label for="date_commission" class="form-label">Date Commission</label>
                            <input type="date" class="form-control" name="date_commission">
                        </div>

                        <div class="mb-3">
                            <label for="avis_commission" class="form-label">Avis de la Commission</label>
                            <select name="avis_commission" class="form-control">
                                <option value="">Sélectionner l'avis</option>
                                <option value="favorable">Favorable</option>
                                <option value="defavorable">Défavorable</option>
                                <option value="reexamen">Réexamen</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="numero_avis_favorable" class="form-label">N° Avis Favorable</label>
                            <input type="text" class="form-control" name="numero_avis_favorable">
                        </div>

                        <div class="mb-3">
                            <label for="motivation_avis" class="form-label">Motivation de l'avis</label>
                            <textarea class="form-control" name="motivation_avis" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="observations" class="form-label">Observations</label>
                            <textarea class="form-control" name="observations" rows="3"></textarea>
                        </div>
                    </div>

                    <!-- Colonne droite : Détails du Projet et Investissement -->
                    <div class="col-md-6">
                        <h5 class="mb-3 text-secondary border-bottom pb-2">Détails du Projet & Investissement</h5>

                        <div class="mb-3">
                            <label for="petitionnaire" class="form-label">Pétitionnaire</label>
                            <input type="text" class="form-control" name="petitionnaire" required>
                        </div>

                        <div class="mb-3">
                            <label for="categorie_petitionnaire" class="form-label">Catégorie du Pétitionnaire</label>
                            <input type="text" class="form-control" name="categorie_petitionnaire">
                        </div>

                        <div class="mb-3">
                            <label for="categorie_projet" class="form-label">Catégorie du Projet</label>
                            <input type="text" class="form-control" name="categorie_projet">
                        </div>

                        <div class="mb-3">
                            <label for="contexte" class="form-label">Contexte</label>
                            <textarea class="form-control" name="contexte" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="maitre_oeuvre" class="form-label">Maître d'œuvre</label>
                            <input type="text" class="form-control" name="maitre_oeuvre">
                        </div>

                        <div class="mb-3">
                            <label for="situation" class="form-label">Situation</label>
                            <textarea class="form-control" name="situation" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="reference_fonciere" class="form-label">Référence Foncière</label>
                            <input type="text" class="form-control" name="reference_fonciere">
                        </div>

                        <h5 class="mt-4 text-secondary border-bottom pb-2">Investissement & Mesures</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="surface_terrain" class="form-label">Surface Terrain (m²)</label>
                                <input type="number" step="0.01" class="form-control" name="surface_terrain">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="surface_batie" class="form-label">Surface Bâtie/Couverte (m²)</label>
                                <input type="number" step="0.01" class="form-control" name="surface_batie">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="montant_investissement" class="form-label">Montant de l'Investissement</label>
                                <input type="number" step="0.01" class="form-control" name="montant_investissement">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre_logements" class="form-label">Nombre de Logements</label>
                                <input type="number" class="form-control" name="nombre_logements">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="plan_url" class="form-label">URL du Plan</label>
                            <input type="url" class="form-control" name="plan_url">
                        </div>

                        <div class="mb-3">
                            <label for="commission_esthetique" class="form-label">Commission d'Esthétique (format JSON)</label>
                            <textarea class="form-control" name="commission_esthetique" rows="4" placeholder='Exemple: [{"numero": "001", "date": "2023-01-01", "examen": "Bon", "avis": "Favorable"}]'></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="numero_classement" class="form-label">Numéro de Classement</label>
                            <input type="text" class="form-control" name="numero_classement">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success me-2">Créer le projet</button>
                    <a href="{{ route('petitprojets.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
@endsection
