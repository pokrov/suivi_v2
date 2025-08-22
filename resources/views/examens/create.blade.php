@extends('layouts.app')

@section('content')
<div class="container">
  <div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">
        Avis de la Commission — Dossier {{ $grandprojet->numero_dossier }}
        <span class="badge bg-light text-dark ms-2">Examen n° {{ $nextNumero }}</span>
      </h5>

      @if(Auth::user()->hasRole('chef'))
        <a href="{{ route('chef.grandprojets.cpc.show', $grandprojet) }}" class="btn btn-light btn-sm">Retour</a>
      @else
        <a href="{{ route('saisie_cpc.cpc.show', $grandprojet) }}" class="btn btn-light btn-sm">Retour</a>
      @endif
    </div>

    <div class="card-body">
      @if($errors->any())
        <div class="alert alert-danger">
          <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
          </ul>
        </div>
      @endif

      <form method="POST"
        action="{{ Auth::user()->hasRole('chef')
                    ? route('chef.grandprojets.cpc.examens.store', $grandprojet)
                    : route('saisie_cpc.cpc.examens.store', $grandprojet) }}">
        @csrf

        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Date de Commission</label>
            <input type="date" name="date_commission" class="form-control" value="{{ old('date_commission') }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Avis <span class="text-danger">*</span></label>
            <select name="avis" class="form-select" required>
              <option value="sans_avis"   @selected(old('avis')==='sans_avis')>Sans avis</option>
              <option value="favorable"   @selected(old('avis')==='favorable')>Favorable</option>
              <option value="ajourne"     @selected(old('avis')==='ajourne')>Ajourné</option>
              <option value="defavorable" @selected(old('avis')==='defavorable')>Défavorable</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Observations</label>
            <textarea name="observations" class="form-control" rows="3">{{ old('observations') }}</textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Rediriger vers <span class="text-danger">*</span></label>
            <select name="rediriger_vers" class="form-select" required>
              <option value="retour_dgu" @selected(old('rediriger_vers')==='retour_dgu')>Retour DGU</option>
              <option value="retour_bs"  @selected(old('rediriger_vers')==='retour_bs')>Retour Bureau Suivi</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label">Note (journal navette)</label>
            <input type="text" name="note_flux" class="form-control" value="{{ old('note_flux') }}" placeholder="Ex: Dossier incomplet, demande de pièces...">
          </div>
        </div>

        <div class="mt-3 d-flex justify-content-end">
          <button class="btn btn-success">Enregistrer l’avis et rediriger</button>
        </div>
      </form>

      <hr class="my-4">

      <h6 class="text-secondary">Historique des examens</h6>
      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>#</th><th>Date</th><th>Avis</th><th>Obs.</th><th>Par</th>
            </tr>
          </thead>
          <tbody>
            @forelse($history as $ex)
              <tr>
                <td>{{ $ex->numero_examen }}</td>
                <td>{{ $ex->date_commission ? \Carbon\Carbon::parse($ex->date_commission)->format('d/m/Y') : '—' }}</td>
                <td>
                  <span class="badge
                    @class([
                      'bg-success' => $ex->avis==='favorable',
                      'bg-danger'  => $ex->avis==='defavorable',
                      'bg-warning text-dark' => $ex->avis==='ajourne',
                      'bg-secondary' => $ex->avis==='sans_avis',
                    ])">
                    {{ ucfirst($ex->avis) }}
                  </span>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($ex->observations, 60) }}</td>
                <td>{{ $ex->auteur->name ?? '—' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-muted">Aucun examen pour l’instant.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
