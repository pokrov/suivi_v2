@extends('layouts.app')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Petits Projets</li>
@endsection

@section('content')
<div class="container">
    <h1 class="mb-4">Liste des Petits Projets</h1>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <a href="{{ route('petitprojets.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ajouter un Petit Projet
    </a>

    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>N° Projet</th>
                    <th>Titre</th>
                    <th>Province</th>
                    <th>Commune</th>
                    <th>Statut</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($petitsProjets as $projet)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $projet->numero_projet }}</td>
                    <td>{{ $projet->titre_projet }}</td>
                    <td>{{ $projet->province }}</td>
                    <td>{{ $projet->commune }}</td>
                    <td>{{ ucfirst($projet->statut) }}</td>
                    <td class="text-center">
                        <!-- Boutons d'action : édition, visualisation, etc. (à compléter plus tard) -->
                        <a href="#" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="#" class="btn btn-sm btn-info">Détails</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Aucun petit projet trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $petitsProjets->withQueryString()->links() }}
    </div>
</div>
@endsection
