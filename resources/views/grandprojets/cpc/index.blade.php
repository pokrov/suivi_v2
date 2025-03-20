{{-- resources/views/grandprojets/cpc/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Titre principal --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-center mb-0">Liste des Projets CPC (Chef)</h2>
        
        {{-- Bouton d'ajout d'un projet CPC --}}
        <a href="{{ route('chef.grandprojets.cpc.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Ajouter un projet CPC
        </a>
    </div>
    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('chef.grandprojets.cpc.index') }}" class="row g-3 mb-4">
    {{-- Champ texte global --}}
    <div class="col-md-3">
        <input type="text"
               name="search"
               class="form-control"
               placeholder="Recherche..."
               value="{{ request('search') }}">
    </div>

    {{-- Date de début --}}
    <div class="col-md-2">
        <input type="date"
               name="date_from"
               class="form-control"
               placeholder="Date de début"
               value="{{ request('date_from') }}">
    </div>

    {{-- Date de fin --}}
    <div class="col-md-2">
        <input type="date"
               name="date_to"
               class="form-control"
               placeholder="Date de fin"
               value="{{ request('date_to') }}">
    </div>

    {{-- Sélection de la province/préfecture --}}
    <div class="col-md-3">
        <select name="province" class="form-control">
            <option value="">--Toutes les Provinces--</option>
            <option value="Préfecture Oujda-Angad" 
                {{ request('province') == 'Préfecture Oujda-Angad' ? 'selected' : '' }}>
                Préfecture Oujda-Angad
            </option>
            <option value="Province Berkane"
                {{ request('province') == 'Province Berkane' ? 'selected' : '' }}>
                Province Berkane
            </option>
            <option value="Province Jerada"
                {{ request('province') == 'Province Jerada' ? 'selected' : '' }}>
                Province Jerada
            </option>
            <option value="Province Taourirt"
                {{ request('province') == 'Province Taourirt' ? 'selected' : '' }}>
                Province Taourirt
            </option>
            <option value="Province Figuig"
                {{ request('province') == 'Province Figuig' ? 'selected' : '' }}>
                Province Figuig
            </option>
        </select>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Rechercher
        </button>
    </div>
</form>


    {{-- Message de succès --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Vérifie s'il y a des projets --}}
    @if($grandProjets->count())
        <div class="table-responsive shadow-sm">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-dark text-center">
                    <tr>
                        <th>#</th>
                        <th>Numéro de Dossier</th>
                        <th>Intitulé du Projet</th>
                        <th>Commune</th>
                        <th>Date d'Arrivée</th>
                        <th>État</th>
                        <th>Saisi par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grandProjets as $projet)
                    <tr 
                        class="text-center"
                        style="cursor: pointer;"
                        onclick="window.location='{{ route('chef.grandprojets.cpc.show', $projet) }}'">

                        {{-- Ordre d'itération --}}
                        <td>{{ $loop->iteration }}</td>

                        {{-- Numéro de dossier --}}
                        <td>{{ $projet->numero_dossier }}</td>

                        {{-- Intitulé du Projet --}}
                        <td>{{ $projet->intitule_projet }}</td>

                        {{-- Commune --}}
                        <td>{{ $projet->commune_1 }}</td>

                        {{-- Date d'arrivée (formatée) --}}
                        <td>
                            @if($projet->date_arrivee)
                                {{ \Carbon\Carbon::parse($projet->date_arrivee)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">Non définie</span>
                            @endif
                        </td>

                        {{-- État (badge coloré) --}}
                        <td>
                            @php
                                // Choisir la couleur du badge selon l'état
                                switch($projet->etat) {
                                    case 'favorable':
                                        $badgeColor = 'success';
                                        break;
                                    case 'defavorable':
                                        $badgeColor = 'danger';
                                        break;
                                    default:
                                        $badgeColor = 'secondary';
                                        break;
                                }
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">
                                {{ ucfirst($projet->etat) }}
                            </span>
                        </td>

                        {{-- Saisi par (utilisateur) --}}
                        <td>
                            {{ $projet->user ? $projet->user->name : 'N/A' }}
                        </td>

                        {{-- Actions : Modification / Suppression --}}
                        <td class="text-nowrap">
                            <a href="{{ route('chef.grandprojets.cpc.edit', $projet) }}"
                               class="btn btn-warning btn-sm me-1"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-edit"></i> Modifier
                            </a>

                            {{-- Formulaire de suppression --}}
                            <form action="{{ route('chef.grandprojets.cpc.destroy', $projet) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="event.stopPropagation(); return confirm('Supprimer ce projet ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination stylisée --}}
        <div class="mt-3 d-flex justify-content-center">
            {{-- 
                1. On peut utiliser la pagination par défaut : {{ $grandProjets->links() }}
                2. OU la pagination Bootstrap 5 : {{ $grandProjets->links('vendor.pagination.bootstrap-5') }}
            --}}
            {{ $grandProjets->links('vendor.pagination.bootstrap-5') }}
        </div>
    @else
        <p class="text-center text-muted">Aucun projet CPC disponible.</p>
    @endif

</div>
@endsection
