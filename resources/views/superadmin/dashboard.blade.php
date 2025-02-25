@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard Super Administrateur</li>
@endsection

@section('content')
<div class="container">
    <h1 class="text-center">Dashboard Super Administrateur</h1>
    <p class="text-center">Bienvenue, {{ auth()->user()->name }} !</p>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Gérer les utilisateurs</h5>
                    <a href="{{ route('superadmin.users.index') }}" class="btn btn-primary">Accéder</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-shield fa-3x text-secondary mb-3"></i>
                    <h5 class="card-title">Gérer les rôles</h5>
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Accéder</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Paramètres</h5>
                    <button class="btn btn-warning" disabled>En développement</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
