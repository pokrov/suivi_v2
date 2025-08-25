{{-- resources/views/admin/maitres_oeuvre/edit.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <h3 class="mb-3">Modifier : {{ $item->nom }}</h3>

  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('superadmin.maitres-oeuvre.update', $item) }}" class="card shadow p-3">
    @csrf
    @method('PUT')
    <div class="mb-3">
      <label class="form-label fw-semibold">Nom *</label>
      <input type="text" name="nom" class="form-control" value="{{ old('nom', $item->nom) }}" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="{{ old('email', $item->email) }}">
    </div>
    <div class="mb-3">
      <label class="form-label">Téléphone</label>
      <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $item->telephone) }}">
    </div>
    <div class="mb-3">
      <label class="form-label">Adresse</label>
      <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $item->adresse) }}">
    </div>

    <div class="d-flex gap-2">
      <button class="btn btn-primary">Mettre à jour</button>
      <a class="btn btn-secondary" href="{{ route('superadmin.maitres-oeuvre.index') }}">Annuler</a>
    </div>
  </form>
</div>
@endsection
