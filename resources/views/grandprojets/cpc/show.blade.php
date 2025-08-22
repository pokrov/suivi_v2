@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow-lg">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
      <h2 class="mb-0">
        <i class="fas fa-info-circle"></i> Détails du Grand Projet - CPC
      </h2>

      {{-- The "back" link depends on the user role --}}
      @if(Auth::user()->hasRole('chef'))
        <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Retour à la liste (Chef)
        </a>
      @elseif(Auth::user()->hasRole('saisie_cpc'))
        <a href="{{ route('saisie_cpc.dashboard') }}" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Retour au Dashboard (Saisie CPC)
        </a>
      @else
        {{-- Fallback if some other role/user accesses --}}
        <a href="#" class="btn btn-secondary">Retour</a>
      @endif
    </div>

    <div class="card-body">
      {{-- Tracker d’étapes --}}
@include('components.tracker', ['etatCourant' => $cpc->etat])

      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          @if($cpc->isFavorable())
            <span class="badge bg-success">Favorable</span>
          @else
            <span class="badge bg-warning text-dark">En instruction — état: {{ $cpc->etat }}</span>
          @endif
        </div>

        {{-- Bouton pour rendre l’avis uniquement si le projet est à la commission interne et pas encore favorable --}}
        @if($cpc->etat === 'comm_interne' && !$cpc->isFavorable())
          @if(Auth::user()->hasRole('chef'))
            <a class="btn btn-primary btn-sm" href="{{ route('chef.grandprojets.cpc.examens.create', $cpc) }}">
              Rendre l’avis (Examen n° {{ $cpc->next_numero_examen }})
            </a>
          @else
            <a class="btn btn-primary btn-sm" href="{{ route('saisie_cpc.cpc.examens.create', $cpc) }}">
              Rendre l’avis (Examen n° {{ $cpc->next_numero_examen }})
            </a>
          @endif
        @endif
      </div>

      <div class="row">
        {{-- LEFT COLUMN --}}
        <div class="col-md-6">
          <h5 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-map-marker-alt"></i> Identification & Localisation
          </h5>

          <ul class="list-group mb-4">
            <li class="list-group-item">
              <strong>Numéro de Dossier :</strong> {{ $cpc->numero_dossier }}
            </li>
            <li class="list-group-item">
              <strong>Province/Préfecture :</strong> {{ $cpc->province }}
            </li>
            <li class="list-group-item">
              <strong>Commune 1 :</strong> {{ $cpc->commune_1 }}
            </li>
            @if($cpc->commune_2)
              <li class="list-group-item">
                <strong>Commune 2 :</strong> {{ $cpc->commune_2 }}
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
                {{ \Carbon\Carbon::parse($cpc->date_commission_interne)->format('d/m/Y') }}
              </li>
            @endif
          </ul>
        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-md-6">
          <h5 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-info"></i> Informations du Projet
          </h5>

          <ul class="list-group mb-4">
            <li class="list-group-item">
              <strong>Pétitionnaire :</strong> {{ $cpc->petitionnaire }}
            </li>
            <li class="list-group-item">
              <strong>Catégorie du Pétitionnaire :</strong> {{ $cpc->categorie_petitionnaire }}
            </li>
            <li class="list-group-item">
              <strong>Intitulé du Projet :</strong> {{ $cpc->intitule_projet }}
            </li>
            <li class="list-group-item">
              <strong>Catégorie du Projet :</strong> {{ $cpc->categorie_projet }}
            </li>
            <li class="list-group-item">
              <strong>Contexte du Projet :</strong> {{ $cpc->contexte_projet }}
            </li>
            <li class="list-group-item">
              <strong>Maître d'Œuvre :</strong> {{ $cpc->maitre_oeuvre }}
            </li>
            <li class="list-group-item">
              <strong>Situation :</strong> {{ $cpc->situation }}
            </li>
            <li class="list-group-item">
              <strong>Références Foncières :</strong> {{ $cpc->reference_fonciere }}
            </li>
            @if($cpc->observations)
              <li class="list-group-item">
                <strong>Observations :</strong> {{ $cpc->observations }}
              </li>
            @endif
          </ul>
        </div>
      </div>

      {{-- ACTION BUTTONS: Only Chef can Edit/Delete --}}
      @if(Auth::user()->hasRole('chef'))
        <div class="d-flex justify-content-between">
          {{-- Edit button --}}
          <a href="{{ route('chef.grandprojets.cpc.edit', $cpc) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Modifier
          </a>
          {{-- Delete form --}}
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
      @endif
    </div>
  </div>
</div>
@endsection
