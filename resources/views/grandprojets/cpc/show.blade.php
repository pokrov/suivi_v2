@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow-lg">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
      <h2 class="mb-0">
        <i class="fas fa-info-circle"></i> Détails du Grand Projet - CPC
      </h2>
      <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
    </div>

    <div class="card-body">
      <div class="row">
        {{-- Left column: Identification & Localisation --}}
        <div class="col-md-6">
          <h5 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-map-marker-alt"></i> Identification & Localisation
          </h5>

          <ul class="list-group mb-4">
            <li class="list-group-item">
              <strong>Numéro de Dossier :</strong> 
              {{ $cpc->numero_dossier }}
            </li>
            <li class="list-group-item">
              <strong>Province/Préfecture :</strong> 
              {{ $cpc->province }}
            </li>
            <li class="list-group-item">
              <strong>Commune 1 :</strong> 
              {{ $cpc->commune_1 }}
            </li>
            @if($cpc->commune_2)
            <li class="list-group-item">
              <strong>Commune 2 :</strong> 
              {{ $cpc->commune_2 }}
            </li>
            @endif
            
            <li class="list-group-item">
    <strong>Date d'Arrivée :</strong>
    @if($cpc->date_arrivee)
        {{ \Carbon\Carbon::parse($cpc->date_arrivee)->format('d/m/Y') }}
    @else
        Non définie
    @endif
</li>
            @if($cpc->date_commission_interne)
            <li class="list-group-item">
              <strong>Date de Commission :</strong> 
              <li class="list-group-item">
    <strong>Date d'Arrivée :</strong>
    @if($cpc->date_arrivee)
        {{ \Carbon\Carbon::parse($cpc->date_commission_interne)->format('d/m/Y') }}
    @else
        Non définie
    @endif
</li>
            </li>
            @endif
          </ul>
        </div>

        {{-- Right column: Informations du Projet --}}
        <div class="col-md-6">
          <h5 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-info"></i> Informations du Projet
          </h5>

          <ul class="list-group mb-4">
            <li class="list-group-item">
              <strong>Pétitionnaire :</strong> 
              {{ $cpc->petitionnaire }}
            </li>
            <li class="list-group-item">
              <strong>Catégorie du Pétitionnaire :</strong> 
              {{ $cpc->categorie_petitionnaire }}
            </li>
            <li class="list-group-item">
              <strong>Intitulé du Projet :</strong> 
              {{ $cpc->intitule_projet }}
            </li>
            <li class="list-group-item">
              <strong>Catégorie du Projet :</strong> 
              {{ $cpc->categorie_projet }}
            </li>
            <li class="list-group-item">
              <strong>Contexte du Projet :</strong> 
              {{ $cpc->contexte_projet }}
            </li>
            <li class="list-group-item">
              <strong>Maître d'Œuvre :</strong> 
              {{ $cpc->maitre_oeuvre }}
            </li>
            <li class="list-group-item">
              <strong>Situation :</strong> 
              {{ $cpc->situation }}
            </li>
            <li class="list-group-item">
              <strong>Références Foncières :</strong> 
              {{ $cpc->reference_fonciere }}
            </li>
            @if($cpc->observations)
            <li class="list-group-item">
              <strong>Observations :</strong> 
              {{ $cpc->observations }}
            </li>
            @endif
          </ul>
        </div>
      </div>

      {{-- Buttons: Edit + Delete --}}
      <div class="d-flex justify-content-between">
        <a href="{{ route('chef.grandprojets.cpc.edit', $cpc) }}" class="btn btn-warning">
          <i class="fas fa-edit"></i> Modifier
        </a>
        <form action="{{ route('chef.grandprojets.cpc.destroy', $cpc) }}" 
              method="POST" 
              onsubmit="return confirm('Supprimer ce projet ?');">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-danger">
            <i class="fas fa-trash"></i> Supprimer
          </button>
        </form>
      </div>

    </div>
  </div>
</div>
@endsection
