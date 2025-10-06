@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-3">Modifier Petit Projet — {{ $petitprojet->numero_dossier }}</h2>
  <form method="post" action="{{ route('chef.petitprojets.update',$petitprojet) }}" class="card p-3 shadow-sm">
    @csrf @method('PUT')
    @include('petitprojets.partials.form-fields', ['pp' => $petitprojet])
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Mettre à jour</button>
      <a href="{{ route('chef.petitprojets.index') }}" class="btn btn-outline-secondary">Retour</a>
    </div>
  </form>
</div>
@endsection
