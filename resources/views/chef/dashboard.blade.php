@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center mb-4">Dashboard Chef</h1>
    <p class="text-center">Bienvenue, {{ Auth::user()->name }}. Voici votre tableau de bord de gestion.</p>

    <div class="row">
        <!-- Carte pour Petit Projet -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-success">
                <div class="card-body text-center">
                    <i class="fas fa-home fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Petit Projet</h5>
                    <p class="card-text">Gérez tous les petits projets.</p>
                    <a href="" class="btn btn-success">Accéder</a>
                </div>
            </div>
        </div>

        <!-- Carte pour Grand Projet avec sous-menu -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Grand Projet</h5>
                    <p class="card-text">Gérez les grands projets.</p>
                    <button class="btn btn-primary" onclick="toggleSubMenu()">Accéder</button>

                    <div id="grandProjetSubMenu" class="mt-3 d-none">
                        <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-outline-primary w-100 mb-2">CPC - Projet de Construction</a>
                        <a href="" class="btn btn-outline-primary w-100">CLM - Lotissement & Morcellement</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte pour Statistiques -->
        <div class="col-md-4 mb-4">
            <div class="card shadow border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-chart-line fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Statistiques</h5>
                    <p class="card-text">Visualisez les rapports et indicateurs clés.</p>
                    <a href="{{ route('chef.stats.index') }}" class="btn btn-warning">Accéder</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript pour afficher/masquer le sous-menu -->
<script>
function toggleSubMenu() {
    const subMenu = document.getElementById('grandProjetSubMenu');
    subMenu.classList.toggle('d-none');
}
</script>
@endsection
