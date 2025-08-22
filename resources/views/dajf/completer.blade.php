@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">DAJF — Compléter le dossier</h3>

  <div class="card">
    <div class="card-body">
      <p><strong>N° Dossier :</strong> {{ $grandProjet->numero_dossier }}</p>
      <p><strong>Intitulé :</strong> {{ $grandProjet->intitule_projet }}</p>
      <p class="text-muted">Formulaire à venir (pièces, remarques, champs internes...).</p>

      <a href="{{ route('dajf.inbox') }}" class="btn btn-secondary">Retour</a>
      <a href="{{ route('cpc.show.any', $grandProjet) }}" class="btn btn-link" target="_blank">Voir détails</a>
    </div>
  </div>
</div>
@endsection
