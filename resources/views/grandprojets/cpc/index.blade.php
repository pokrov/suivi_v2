@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4 text-center">Liste des Projets CPC</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('chef.grandprojets.cpc.create') }}" class="btn btn-success mb-3">
        <i class="fas fa-plus"></i> Ajouter un projet CPC
    </a>

    @if($grandProjets->count())
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
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
                    onclick="window.location='{{ route('chef.grandprojets.cpc.show', $projet) }}'">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $projet->numero_dossier }}</td>
                    <td>{{ $projet->intitule_projet }}</td>
                    <td>{{ $projet->commune_1 }}</td>
                    <td>
                        {{ $projet->date_arrivee 
                            ? \Carbon\Carbon::parse($projet->date_arrivee)->format('d/m/Y') 
                            : 'Non définie'
                        }}
                    </td>
                    <td>
                        <span class="badge bg-{{ $projet->etat === 'favorable' ? 'success' 
                            : ($projet->etat === 'defavorable' ? 'danger' : 'secondary') }}">
                            {{ ucfirst($projet->etat) }}
                        </span>
                    </td>
                    <td>
                        {{-- Edit button --}}
                        <a href="{{ route('chef.grandprojets.cpc.edit', $projet) }}"
                           class="btn btn-warning btn-sm">
                            Modifier
                        </a>

                        {{-- Delete form --}}
                        <form action="{{ route('chef.grandprojets.cpc.destroy', $projet) }}"
                              method="POST"
                              class="d-inline"
                              onsubmit="return confirm('Supprimer ce projet ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $grandProjets->links() }}
    </div>
    @else
    <p class="text-center text-muted">Aucun projet CPC disponible.</p>
    @endif
</div>
@endsection
