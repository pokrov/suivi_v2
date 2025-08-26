{{-- resources/views/examens/create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow-lg">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        <i class="fas fa-gavel"></i>
        Commission interne — Avis ({{ $grandProjet->numero_dossier }})
      </h5>
      <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
        <i class="fas fa-arrow-left"></i> Retour dossier
      </a>
    </div>

    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('comm.examens.store', $grandProjet) }}">
        @csrf

        <div class="row g-3">
          {{-- N° examen (affiché) --}}
          <div class="col-md-2">
            <label class="form-label fw-semibold">N° Examen</label>
            <input type="text" class="form-control" value="{{ $nextNumero }}" readonly>
          </div>
          {{-- SI tu veux aussi l’envoyer: --}}
          <input type="hidden" name="numero_examen" value="{{ $nextNumero }}">

          <div class="col-md-4">
            <label class="form-label fw-semibold">Type d’examen</label>
            <input type="text" class="form-control" value="Commission interne" readonly>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Date de la commission (interne)</label>
            <input type="date" name="date_examen"
                   class="form-control @error('date_examen') is-invalid @enderror"
                   value="{{ old('date_examen') }}" required>
            @error('date_examen') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Avis</label>
            <select name="avis" class="form-select @error('avis') is-invalid @enderror" required>
              <option value="">-- Sélectionner --</option>
              <option value="favorable"   {{ old('avis')=='favorable'   ? 'selected' : '' }}>Favorable</option>
              <option value="defavorable" {{ old('avis')=='defavorable' ? 'selected' : '' }}>Défavorable</option>
              <option value="ajourne"     {{ old('avis')=='ajourne'     ? 'selected' : '' }}>Ajourné</option>
              <option value="sans_avis"   {{ old('avis')=='sans_avis'   ? 'selected' : '' }}>Sans avis</option>
            </select>
            @error('avis') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold">Observations</label>
            <textarea name="observations" rows="2"
                      class="form-control @error('observations') is-invalid @enderror"
                      placeholder="Commentaires...">{{ old('observations') }}</textarea>
            @error('observations') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end">
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-save"></i> Enregistrer l’avis
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
