@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center mb-4">👨‍💼 Dashboard Super Administrateur</h1>
    <p class="text-center">Bienvenue, <strong>{{ auth()->user()->name }}</strong> !</p>

    <div class="row mt-5">
        <!-- Gestion des utilisateurs -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Gérer les utilisateurs</h5>
                    <p class="card-text">Ajouter, modifier ou supprimer les utilisateurs de la plateforme.</p>
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-primary">
                        Accéder <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Gestion des rôles -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Gérer les rôles</h5>
                    <p class="card-text">Créer et attribuer des rôles aux utilisateurs.</p>
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-outline-secondary">
                        Accéder <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Paramètres (désactivé) -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100 position-relative">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Paramètres</h5>
                    <p class="card-text text-muted">Bientôt disponible</p>
                    <button class="btn btn-outline-warning" disabled>
                        En développement <i class="fas fa-tools"></i>
                    </button>
                </div>
                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">À venir</span>
            </div>
        </div>
    </div>
</div>
@endsection
