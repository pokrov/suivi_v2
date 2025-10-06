@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-3">Nouveau Petit Projet</h2>
  <form method="post" action="{{ route('chef.petitprojets.store') }}" class="card p-3 shadow-sm">
    @csrf
    @include('petitprojets.partials.form-fields')
    <div class="mt-3 d-flex gap-2">
      <button class="btn btn-success"><i class="fas fa-save me-1"></i> Enregistrer</button>
      <a href="{{ route('chef.petitprojets.index') }}" class="btn btn-outline-secondary">Annuler</a>
    </div>
  </form>
</div>
@endsection
