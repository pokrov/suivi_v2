@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow-lg rounded-4">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center rounded-top-4">
      <h5 class="mb-0">Modifier l’avis — {{ $grandProjet->numero_dossier }}</h5>
      <a href="{{ route('cpc.show.shared', $grandProjet) }}" class="btn btn-light btn-sm">Retour dossier</a>
    </div>

    <div class="card-body p-4">
      <form method="POST" action="{{ route('comm.examens.update', $examen) }}">
        @csrf
        @method('PUT')

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">N° Examen</label>
            <input type="number" class="form-control" value="{{ $examen->numero_examen }}" disabled>
          </div>

          <div class="col-md-4">
            <label class="form-label">Type d’examen</label>
            <input type="text" class="form-control" value="Commission interne" disabled>
          </div>

          <div class="col-md-4">
            <label class="form-label">Date de la commission (interne)</label>
            <input type="date" name="date_commission" class="form-control"
                   value="{{ old('date_commission', optional($examen->date_commission)->format('Y-m-d')) }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Avis</label>
            <select name="avis" class="form-select" required>
              @foreach(['favorable'=>'Favorable','defavorable'=>'Défavorable','ajourne'=>'Ajourné','sans_avis'=>'Sans avis'] as $k=>$label)
                <option value="{{ $k }}" @selected(old('avis', $examen->avis)===$k)>{{ $label }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-8">
            <label class="form-label">Observations</label>
            <textarea name="observations" class="form-control" rows="3">{{ old('observations', $examen->observations) }}</textarea>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button class="btn btn-warning">Mettre à jour</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
