@extends('layouts.app')

@section('content')
<div class="container">
  <h2 class="mb-3">Petits Projets</h2>

  {{-- Filtres --}}
  <form method="get" class="row g-2 mb-3">
    <div class="col-md-4">
      <input name="search" value="{{ $search }}" class="form-control" placeholder="Recherche (n° dossier, pétitionnaire, commune...)">
    </div>
    <div class="col-md-3">
      <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" placeholder="Du">
    </div>
    <div class="col-md-3">
      <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" placeholder="Au">
    </div>
    <div class="col-md-2 d-grid">
      <button class="btn btn-primary"><i class="fas fa-search me-1"></i> Filtrer</button>
    </div>
  </form>

  <div class="mb-3">
    <a href="{{ route('chef.petitprojets.create') }}" class="btn btn-success">
      <i class="fas fa-plus-circle me-1"></i> Nouveau Petit Projet
    </a>
  </div>

  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-dark">
        <tr>
          <th>N° Dossier</th>
          <th>Commune</th>
          <th>Pétitionnaire</th>
          <th>Intitulé</th>
          <th>Arrivée</th>
          <th>Avis Rokhas</th>
          <th>État</th>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $pp)
          <tr class="pp-row" role="button"
              data-href="{{ route('chef.petitprojets.show', $pp) }}"
              tabindex="0"
              style="cursor:pointer">
            {{-- Lien direct vers SHOW pour fonctionner même sans JS --}}
            <td>
              <a href="{{ route('chef.petitprojets.show', $pp) }}" class="text-decoration-none fw-bold"
                 onclick="event.stopPropagation()">
                {{ $pp->numero_dossier }}
              </a>
            </td>

            <td>{{ $pp->commune_1 }}</td>
            <td>{{ $pp->petitionnaire }}</td>
            <td>{{ $pp->intitule_projet }}</td>

            {{-- Si le cast n'est pas mis dans le modèle, on parse avec Carbon côté vue --}}
            <td>{{ $pp->date_arrivee ? \Carbon\Carbon::parse($pp->date_arrivee)->format('Y-m-d') : '' }}</td>

            <td>
              @if($pp->rokhas_avis)
                <span class="badge bg-{{ [
                  'favorable'=>'success',
                  'defavorable'=>'danger',
                  'sous_reserve'=>'warning',
                  'sans_objet'=>'secondary'
                ][$pp->rokhas_avis] ?? 'secondary' }}">
                  {{ ucfirst(str_replace('_',' ',$pp->rokhas_avis)) }}
                </span>
              @else
                —
              @endif
            </td>

            <td><span class="badge bg-light text-dark">{{ $pp->etat }}</span></td>

            <td class="text-end">
              {{-- Edit (unicode fallback visible même si Font Awesome n’est pas chargé) --}}
              <a href="{{ route('chef.petitprojets.edit', $pp) }}"
                 class="me-3 text-decoration-none"
                 title="Modifier" aria-label="Modifier"
                 onclick="event.stopPropagation()">
                <span class="text-primary" style="font-size:1.1rem;">✎</span>
              </a>

              {{-- Delete --}}
              <form action="{{ route('chef.petitprojets.destroy', $pp) }}" method="post" class="d-inline"
                    onsubmit="event.stopPropagation(); return confirm('Supprimer ce petit projet ?');">
                @csrf @method('DELETE')
                <button type="submit"
                        class="p-0 border-0 bg-transparent"
                        title="Supprimer" aria-label="Supprimer"
                        onclick="event.stopPropagation()">
                  <span class="text-danger" style="font-size:1.1rem;">🗑️</span>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted">Aucun résultat.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $items->links() }}
  </div>
</div>

{{-- JS inline (pas de @push pour être sûr qu’il soit chargé) --}}
<script>
  // Click sur toute la ligne (sauf éléments interactifs)
  document.addEventListener('click', function(e){
    const row = e.target.closest('tr.pp-row');
    if (!row) return;

    const interactive = e.target.closest('a, button, input, textarea, label, select, .btn');
    if (interactive) return;

    const url = row.getAttribute('data-href');
    if (!url) return;

    if (e.ctrlKey || e.metaKey) {
      window.open(url, '_blank');
    } else {
      window.location.href = url;
    }
  });

  // Accessibilité clavier: Enter / Espace
  document.addEventListener('keydown', function(e){
    if (!['Enter',' '].includes(e.key)) return;
    const row = document.activeElement?.closest?.('tr.pp-row');
    if (!row) return;
    const url = row.getAttribute('data-href');
    if (!url) return;
    e.preventDefault();
    window.location.href = url;
  });
</script>

{{-- Mini CSS hover --}}
<style>
  .pp-row:hover { background: #f8fafc; }
</style>
@endsection
