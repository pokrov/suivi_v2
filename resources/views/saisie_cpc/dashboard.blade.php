{{-- resources/views/saisie_cpc/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">

    {{-- Barre de titre et bouton d'ajout --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Tableau de bord - Saisie CPC</h2>
        <a href="{{ route('saisie_cpc.cpc.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Ajouter un projet CPC
        </a>
    </div>
    {{-- Formulaire de recherche --}}
    <form method="GET" action="{{ route('saisie_cpc.dashboard') }}" class="row g-3 mb-4">
    {{-- Champ texte global --}}
    <div class="col-md-3">
        <input type="text"
               name="search"
               class="form-control"
               placeholder="Recherche..."
               value="{{ request('search') }}">
    </div>

    {{-- Date de début --}}
    <div class="col-md-3">
        <input type="date"
               name="date_from"
               class="form-control"
               placeholder="Date de début"
               value="{{ request('date_from') }}">
    </div>

    {{-- Date de fin --}}
    <div class="col-md-3">
        <input type="date"
               name="date_to"
               class="form-control"
               placeholder="Date de fin"
               value="{{ request('date_to') }}">
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Rechercher
        </button>
    </div>
</form>



    {{-- Message de succès si besoin --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Vérifie s'il y a des projets --}}
    @if($grandProjets->count() > 0)
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grandProjets as $projet)
                        <tr class="text-center"
                            style="cursor: pointer;"
                            onclick="window.location='{{ route('saisie_cpc.cpc.show', $projet) }}'">
                            
                            {{-- Incrementation --}}
                            <td>{{ $loop->iteration }}</td>

                            {{-- Numéro de dossier --}}
                            <td>{{ $projet->numero_dossier }}</td>

                            {{-- Intitulé du Projet --}}
                            <td>{{ $projet->intitule_projet }}</td>

                            {{-- Commune --}}
                            <td>{{ $projet->commune_1 }}</td>

                            {{-- Date d'arrivée --}}
                            <td>
                                @if($projet->date_arrivee)
                                    {{ \Carbon\Carbon::parse($projet->date_arrivee)->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Non définie</span>
                                @endif
                            </td>

                            {{-- État avec un badge coloré --}}
                            <td>
                                @php
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

                            {{-- Bouton "Modifier" seulement (on arrête la propagation du click) --}}
                            <td onclick="event.stopPropagation();" class="text-nowrap">
                                <a href="{{ route('saisie_cpc.cpc.edit', $projet) }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                {{-- Pas de suppression --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination Bootstrap 5 --}}
        <div class="mt-3 d-flex justify-content-center">
            {{-- Nécessite d'avoir publié les vues de pagination, voir explication plus bas --}}
            {{ $grandProjets->links('vendor.pagination.bootstrap-5') }}
        </div>
    @else
        <p class="text-center text-muted">Aucun projet CPC disponible.</p>
    @endif
</div>
@endsection
