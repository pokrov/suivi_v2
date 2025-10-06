@extends('layouts.app')

@push('styles')
<style>
  .pp-header {
    border-radius: 18px;
    background: linear-gradient(120deg, #10b981 0%, #059669 40%, #0ea5e9 120%);
    color: #fff;
    padding: 22px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(16,185,129,.25);
  }
  .pp-header .title { font-weight: 900; letter-spacing:.3px; }
  .pp-header .subtitle { opacity:.95; font-weight: 600; }
  .chip {
    display:inline-flex; align-items:center; gap:.4rem;
    border-radius: 999px; padding:.35rem .7rem; font-weight:700; font-size:.85rem;
    background: rgba(255,255,255,.18); color:#fff; border:1px solid rgba(255,255,255,.35);
  }
  .card-pp {
    border-radius:18px; background:#fff; border:1px solid rgba(0,0,0,.06);
    box-shadow: 0 8px 28px rgba(0,0,0,.06); height:100%;
  }
  .section-title {
    font-weight: 800; letter-spacing:.25px; margin-bottom:.6rem;
  }
  .kv { display:grid; grid-template-columns: 220px 1fr; gap:.5rem 1rem; }
  .kv .k { color:#64748b; font-weight:700; }
  .badge-cat { font-weight:800; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
  .badge-rokhas {
    font-weight:800;
  }
  .copy-btn {
    border-radius:10px; border:1px dashed #d1d5db; background:#f9fafb; padding:.2rem .5rem;
    font-size:.8rem; font-weight:700; color:#374151;
  }
  .toolbar .btn { border-radius:12px; font-weight:800; }
  @media (max-width: 768px) { .kv { grid-template-columns: 1fr; } }
  @media print {
    .no-print { display:none !important; }
    .pp-header { box-shadow:none; }
    .card-pp { box-shadow:none; }
  }
</style>
@endpush

@section('content')
@php
  /** @var \App\Models\PetitProjet $petitprojet */
  $pp = $petitprojet;
  $avisColor = [
    'favorable'    => 'bg-success',
    'defavorable'  => 'bg-danger',
    'sous_reserve' => 'bg-warning text-dark',
    'sans_objet'   => 'bg-secondary'
  ][$pp->rokhas_avis ?? ''] ?? 'bg-secondary';

  $etatColor = $pp->etat === 'archive' ? 'secondary' : 'dark';
  $cats = is_array($pp->categorie_projet ?? null) ? $pp->categorie_projet : (empty($pp->categorie_projet) ? [] : (array)$pp->categorie_projet);

  $fmt = function($d) { return $d ? \Carbon\Carbon::parse($d)->format('Y-m-d') : ''; };
@endphp

<div class="container">

  {{-- Header --}}
  <div class="pp-header mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
      <div>
        <div class="title h3 mb-1">
          <i class="fas fa-home me-2"></i>
          Petit Projet — {{ $pp->numero_dossier }}
        </div>
        <div class="subtitle">
          {{ $pp->intitule_projet ?: 'Sans intitulé' }}
        </div>
      </div>
      <div class="d-flex align-items-center gap-2">
        <span class="chip"><i class="fas fa-map-marker-alt"></i> {{ $pp->commune_1 ?: '—' }}</span>
        <span class="chip"><i class="fas fa-user"></i> {{ $pp->petitionnaire ?: '—' }}</span>
        <span class="chip"><i class="fas fa-flag"></i> État : {{ ucfirst($pp->etat) }}</span>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 no-print toolbar">
    <div class="d-flex gap-2">
      <a href="{{ route('chef.petitprojets.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Retour à la liste
      </a>
      <a href="{{ route('chef.petitprojets.edit', $pp) }}" class="btn btn-primary">
        <i class="fas fa-pen me-1"></i> Modifier
      </a>
      <button onclick="window.print()" class="btn btn-outline-dark">
        <i class="fas fa-print me-1"></i> Imprimer
      </button>
    </div>
    <form action="{{ route('chef.petitprojets.destroy',$pp) }}" method="post"
          onsubmit="return confirm('Supprimer ce petit projet ?');">
      @csrf @method('DELETE')
      <button class="btn btn-outline-danger"><i class="fas fa-trash-alt me-1"></i> Supprimer</button>
    </form>
  </div>

  <div class="row g-3">
    {{-- Colonne 1 --}}
    <div class="col-lg-6">
      <div class="card card-pp p-3">
        <div class="section-title"><i class="fas fa-id-badge me-2 text-success"></i>Identification</div>
        <div class="kv">
          <div class="k">N° dossier</div>
          <div>
            <span class="fw-bold">{{ $pp->numero_dossier }}</span>
            <button class="copy-btn ms-2" onclick="copyVal('{{ addslashes($pp->numero_dossier) }}')">
              <i class="far fa-copy me-1"></i>Copier
            </button>
          </div>

          <div class="k">Province</div><div>{{ $pp->province ?: '—' }}</div>
          <div class="k">Commune 1</div><div>{{ $pp->commune_1 ?: '—' }}</div>
          <div class="k">Commune 2</div><div>{{ $pp->commune_2 ?: '—' }}</div>

          <div class="k">Date d'arrivée</div><div>{{ $fmt($pp->date_arrivee) }}</div>
          <div class="k">N° d'arrivée</div><div>{{ $pp->numero_arrivee ?: '—' }}</div>
        </div>
      </div>

      <div class="card card-pp p-3 mt-3">
        <div class="section-title"><i class="fas fa-users me-2 text-success"></i>Acteurs</div>
        <div class="kv">
          <div class="k">Pétitionnaire</div><div>{{ $pp->petitionnaire ?: '—' }}</div>
          <div class="k">Propriétaire</div>
          <div>
            @if($pp->a_proprietaire) <span class="badge text-bg-dark me-1">Oui</span> @else <span class="badge text-bg-light me-1">Non</span> @endif
            {{ $pp->proprietaire ?: '—' }}
          </div>
          <div class="k">Cat. pétitionnaire</div><div>{{ $pp->categorie_petitionnaire ?: '—' }}</div>
          <div class="k">Maître d'œuvre</div><div>{{ $pp->maitre_oeuvre ?: '—' }}</div>
        </div>
      </div>

      <div class="card card-pp p-3 mt-3">
        <div class="section-title"><i class="fas fa-drafting-compass me-2 text-success"></i>Projet</div>
        <div class="kv">
          <div class="k">Intitulé</div><div>{{ $pp->intitule_projet ?: '—' }}</div>

          <div class="k">Catégorie(s)</div>
          <div>
            @forelse($cats as $c)
              <span class="badge badge-cat me-1 mb-1">{{ $c }}</span>
            @empty
              —
            @endforelse
          </div>

          <div class="k">Contexte</div><div>{{ $pp->contexte_projet ?: '—' }}</div>
          <div class="k">Référence foncière</div>
          <div>
            {{ $pp->reference_fonciere ?: '—' }}
            @if($pp->reference_fonciere)
              <button class="copy-btn ms-2" onclick="copyVal('{{ addslashes($pp->reference_fonciere) }}')">
                <i class="far fa-copy me-1"></i>Copier
              </button>
            @endif
          </div>

          <div class="k">Situation</div><div>{{ $pp->situation ?: '—' }}</div>
          <div class="k">Lien GED</div>
          <div>
            @if($pp->lien_ged)
              <a href="{{ $pp->lien_ged }}" target="_blank" class="link-primary fw-bold">
                Ouvrir la GED <i class="fas fa-external-link-alt ms-1"></i>
              </a>
            @else
              —
            @endif
          </div>

          <div class="k">Observations</div><div class="text-prewrap">{{ $pp->observations ?: '—' }}</div>
        </div>
      </div>
    </div>

    {{-- Colonne 2 --}}
    <div class="col-lg-6">
      <div class="card card-pp p-3">
        <div class="section-title"><i class="fas fa-ruler-combined me-2 text-success"></i>Indicateurs</div>
        <div class="kv">
          <div class="k">Superficie terrain (m²)</div><div>{{ $pp->superficie_terrain ?? '—' }}</div>
          <div class="k">Superficie couverte (m²)</div><div>{{ $pp->superficie_couverte ?? '—' }}</div>
          <div class="k">Investissement (MAD)</div><div>{{ $pp->montant_investissement ?? '—' }}</div>
          <div class="k">Emplois prévus</div><div>{{ $pp->emplois_prevus ?? '—' }}</div>
          <div class="k">Nombre de logements</div><div>{{ $pp->nb_logements ?? '—' }}</div>
        </div>
      </div>

      <div class="card card-pp p-3 mt-3">
        <div class="section-title"><i class="fas fa-gavel me-2 text-success"></i>Avis Rokhas</div>
        <div class="kv">
          <div class="k">N° Rokhas</div>
          <div>
            {{ $pp->rokhas_numero ?: '—' }}
            @if($pp->rokhas_numero)
              <button class="copy-btn ms-2" onclick="copyVal('{{ addslashes($pp->rokhas_numero) }}')">
                <i class="far fa-copy me-1"></i>Copier
              </button>
            @endif
          </div>

          <div class="k">Lien Rokhas</div>
          <div>
            @if($pp->rokhas_lien)
              <a href="{{ $pp->rokhas_lien }}" target="_blank" class="link-primary fw-bold">
                Ouvrir Rokhas <i class="fas fa-external-link-alt ms-1"></i>
              </a>
            @else
              —
            @endif
          </div>

          <div class="k">Avis</div>
          <div>
            @if($pp->rokhas_avis)
              <span class="badge badge-rokhas {{ $avisColor }}">
                {{ ucfirst(str_replace('_',' ',$pp->rokhas_avis)) }}
              </span>
            @else
              —
            @endif
          </div>

          <div class="k">Date avis</div><div>{{ $fmt($pp->rokhas_avis_date) }}</div>
          <div class="k">Commentaire</div><div>{{ $pp->rokhas_avis_commentaire ?: '—' }}</div>

          <div class="k">Pièce (PDF)</div>
          <div>
            @if($pp->rokhas_piece_url)
              <a href="{{ $pp->rokhas_piece_url }}" target="_blank" class="link-primary fw-bold">
                Ouvrir la pièce <i class="fas fa-file-pdf ms-1"></i>
              </a>
            @else
              —
            @endif
          </div>
        </div>
      </div>

      <div class="card card-pp p-3 mt-3">
        <div class="section-title"><i class="fas fa-info-circle me-2 text-success"></i>Métadonnées</div>
        <div class="kv">
          <div class="k">Créé le</div><div>{{ $fmt($pp->created_at) }}</div>
          <div class="k">Mis à jour le</div><div>{{ $fmt($pp->updated_at) }}</div>
          <div class="k">Créé par</div><div>{{ optional($pp->user)->name ?: '—' }}</div>
          <div class="k">État</div>
          <div>
            <span class="badge text-bg-{{ $etatColor }}">{{ ucfirst($pp->etat) }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

{{-- Helpers --}}
<script>
  function copyVal(val) {
    if (!val) return;
    navigator.clipboard.writeText(val).then(() => {
      const toast = document.createElement('div');
      toast.textContent = 'Copié ✔';
      toast.style.position = 'fixed';
      toast.style.bottom = '16px';
      toast.style.right  = '16px';
      toast.style.background = '#111827';
      toast.style.color = '#fff';
      toast.style.padding = '8px 12px';
      toast.style.borderRadius = '10px';
      toast.style.zIndex = '9999';
      document.body.appendChild(toast);
      setTimeout(()=> toast.remove(), 1200);
    });
  }
</script>
@endsection
