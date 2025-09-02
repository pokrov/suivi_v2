{{-- resources/views/examens/create.blade.php --}}
@extends('layouts.app')

@section('content')
<style>
  .kpi {
    border-radius: 14px; border: 1px solid #e5e7eb; background: #f9fafb;
    padding: 14px 16px; height: 100%;
  }
  .kpi .label { font-size:.78rem; letter-spacing:.3px; color:#6b7280; text-transform:uppercase; font-weight:700; }
  .kpi .value { font-size:1.05rem; font-weight:800; color:#111827; }
  .pill { border-radius:999px; padding:.25rem .6rem; font-weight:700; font-size:.78rem; border:1px solid rgba(0,0,0,.06); }
  .pill-cpc { background:#e0e7ff; color:#1e40af; }
  .pill-clm { background:#cffafe; color:#155e75; }

  .choice-card {
    border:2px solid #e5e7eb; border-radius:14px; padding:14px; cursor:pointer; user-select:none;
    display:flex; align-items:center; gap:10px;
  }
  .choice-card input { display:none; }
  .choice-card .icon { font-size:1.1rem; }
  .choice-card .text { font-weight:800; letter-spacing:.3px; }
  .choice-card.active { border-color:#111827; background:#f3f4f6; }
  .choice-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:10px; }
  @media (max-width: 992px) { .choice-grid { grid-template-columns:repeat(2, minmax(0,1fr)); } }

  .motifs-card { border:1px dashed #cbd5e1; border-radius:12px; padding:14px; background:#f8fafc; }
  .form-check-lg .form-check-input { width:1.2rem; height:1.2rem; }
  .form-check-lg .form-check-label { margin-left:.35rem; font-weight:700; }
</style>

<div class="container">
  <div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <div class="d-flex align-items-center gap-2">
        <i class="fas fa-gavel me-1"></i>
        <span class="fw-bold">Commission interne — Saisie de l’avis</span>
        <span class="pill {{ $grandProjet->type_projet === 'clm' ? 'pill-clm' : 'pill-cpc' }}">
          {{ strtoupper($grandProjet->type_projet) }}
        </span>
      </div>
      <a href="{{ url()->previous() }}" class="btn btn-light btn-sm">
        <i class="fas fa-arrow-left"></i> Retour
      </a>
    </div>

    <div class="card-body">

      {{-- Flash erreurs --}}
      @if($errors->any())
        <div class="alert alert-danger">
          <div class="fw-bold mb-1">Veuillez corriger les points suivants :</div>
          <ul class="mb-0">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      {{-- En-tête dossier (lecture seule) --}}
      <div class="row g-3 mb-3">
        <div class="col-lg-3 col-md-6">
          <div class="kpi">
            <div class="label">N° dossier</div>
            <div class="value">{{ $grandProjet->numero_dossier }}</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="kpi">
            <div class="label">Pétitionnaire</div>
            <div class="value text-truncate" title="{{ $grandProjet->petitionnaire }}">
              {{ $grandProjet->petitionnaire ?? '—' }}
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="kpi">
            <div class="label">Commune</div>
            <div class="value">{{ $grandProjet->commune_1 ?? '—' }}</div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="kpi">
            <div class="label">Date d’arrivée</div>
            <div class="value">
              {{ $grandProjet->date_arrivee ? \Carbon\Carbon::parse($grandProjet->date_arrivee)->format('d/m/Y') : '—' }}
            </div>
          </div>
        </div>
      </div>

      {{-- Infos fixes (non éditables) --}}
      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="kpi h-100">
            <div class="label">N° examen</div>
            <div class="value">{{ $nextNumero }}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="kpi h-100">
            <div class="label">Type d’examen</div>
            <div class="value">Commission interne</div>
          </div>
        </div>
        <div class="col-md-5">
          <div class="kpi h-100">
            <div class="label">Maître d’œuvre</div>
            <div class="value text-truncate" title="{{ $grandProjet->maitre_oeuvre }}">
              {{ $grandProjet->maitre_oeuvre ?? '—' }}
            </div>
          </div>
        </div>
      </div>

      {{-- Formulaire --}}
      <form method="POST"
            action="{{ route('comm.' . $grandProjet->type_projet . '.examens.store', $grandProjet) }}">
        @csrf

        {{-- On garde le numéro & le type pour le contrôleur si nécessaire --}}
        <input type="hidden" name="numero_examen" value="{{ $nextNumero }}">
        <input type="hidden" name="type_examen" value="interne">

        <div class="row g-4">
          <div class="col-lg-6">
            <label class="form-label fw-semibold">Date de la commission</label>
            <input type="date"
                   name="date_examen"
                   class="form-control form-control-lg @error('date_examen') is-invalid @enderror"
                   value="{{ old('date_examen', now()->toDateString()) }}"
                   required>
            @error('date_examen') <div class="invalid-feedback">{{ $message }}</div> @enderror
            <div class="form-text">La date de tenue de la commission interne.</div>
          </div>

          <div class="col-lg-6">
            <label class="form-label fw-semibold d-block mb-2">Avis de la commission</label>
            <div class="choice-grid" id="avisGrid">
              @php
                $oldAvis = old('avis');
                $choices = [
                  ['value'=>'favorable',   'icon'=>'fas fa-check-circle', 'text'=>'Favorable'],
                  ['value'=>'defavorable', 'icon'=>'fas fa-times-circle', 'text'=>'Défavorable'],
                ];
              @endphp
              @foreach($choices as $c)
                <label class="choice-card {{ $oldAvis === $c['value'] ? 'active' : '' }}">
                  <input type="radio" name="avis" value="{{ $c['value'] }}" {{ $oldAvis === $c['value'] ? 'checked' : '' }} required>
                  <span class="icon"><i class="{{ $c['icon'] }}"></i></span>
                  <span class="text">{{ $c['text'] }}</span>
                </label>
              @endforeach
            </div>
            @error('avis') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
          </div>

          {{-- ====== MOTIFS (VISIBLE UNIQUEMENT SI DÉFAVORABLE) ====== --}}
          @php
            $oldMotifs = (array) old('motifs', []);
            $showMotifs = old('avis') === 'defavorable';
          @endphp
          <div class="col-12" id="motifsSection" style="{{ $showMotifs ? '' : 'display:none' }}">
            <div class="motifs-card">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold">
                  <i class="fas fa-list me-1"></i>
                  Motifs (facultatif)
                </h6>
                <span class="text-muted small">Cochez un ou plusieurs motifs si l’avis est défavorable.</span>
              </div>

              <div class="row g-2">
                <div class="col-md-6">
                  <div class="form-check form-check-lg">
                    <input class="form-check-input" type="checkbox" id="m_adm" name="motifs[]"
                           value="administratif" {{ in_array('administratif', $oldMotifs, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="m_adm">Administratif</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-check-lg">
                    <input class="form-check-input" type="checkbox" id="m_jur" name="motifs[]"
                           value="juridique_foncier" {{ in_array('juridique_foncier', $oldMotifs, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="m_jur">Juridique & foncier</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-check-lg">
                    <input class="form-check-input" type="checkbox" id="m_urb" name="motifs[]"
                           value="urbanistique" {{ in_array('urbanistique', $oldMotifs, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="m_urb">Urbanistique</label>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-check form-check-lg">
                    <input class="form-check-input" type="checkbox" id="m_tec" name="motifs[]"
                           value="technique" {{ in_array('technique', $oldMotifs, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="m_tec">Technique</label>
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-check form-check-lg">
                    <input class="form-check-input" type="checkbox" id="m_autre" name="motifs[]"
                           value="autre" {{ in_array('autre', $oldMotifs, true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="m_autre">Autre (préciser)</label>
                  </div>
                  <input type="text"
                         id="motifAutreInput"
                         name="motif_autre"
                         class="form-control mt-2"
                         placeholder="Précisez le motif"
                         value="{{ old('motif_autre') }}"
                         style="{{ in_array('autre', $oldMotifs, true) ? '' : 'display:none' }}">
                  @error('motifs') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                  @error('motifs.*') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                  @error('motif_autre') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                </div>
              </div>
            </div>
          </div>
          {{-- ====== FIN MOTIFS ====== --}}

          <div class="col-12">
            <label class="form-label fw-semibold">Observations (facultatif)</label>
            <textarea name="observations"
                      class="form-control form-control-lg @error('observations') is-invalid @enderror"
                      rows="4"
                      placeholder="Renseignez ici les réserves, recommandations, pièces manquantes, etc.">{{ old('observations') }}</textarea>
            @error('observations') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-end gap-2">
          <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="fas fa-times"></i> Annuler
          </a>
          <button type="submit" class="btn btn-success px-4">
            <i class="fas fa-save"></i> Enregistrer l’avis
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- JS: cartes d’avis + affichage conditionnel des motifs (défavorable + autre) --}}
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('avisGrid');
    const motifsSection = document.getElementById('motifsSection');
    const autreCheckbox = document.getElementById('m_autre');
    const motifAutreInput = document.getElementById('motifAutreInput');

    function refreshMotifsVisibility() {
      const selected = grid.querySelector('input[name="avis"]:checked')?.value;
      if (selected === 'defavorable') {
        motifsSection.style.display = '';
      } else {
        motifsSection.style.display = 'none';
        // On nettoie les cases si on repasse sur "favorable" (optionnel)
        // motifsSection.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
        // motifAutreInput.value = '';
        // motifAutreInput.style.display = 'none';
      }
    }

    function toggleMotifAutre() {
      motifAutreInput.style.display = autreCheckbox?.checked ? '' : 'none';
      if (!autreCheckbox?.checked) motifAutreInput.value = '';
    }

    // Activer style des cartes + déclencher l’affichage des motifs
    grid?.querySelectorAll('.choice-card').forEach(card => {
      card.addEventListener('click', () => {
        grid.querySelectorAll('.choice-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        const input = card.querySelector('input[type=radio]');
        if (input) {
          input.checked = true;
          refreshMotifsVisibility();
        }
      });
    });

    // Écoute directe radio (ex: navigation clavier)
    grid?.querySelectorAll('input[name="avis"]').forEach(r => {
      r.addEventListener('change', refreshMotifsVisibility);
    });

    // Autre -> input
    autreCheckbox?.addEventListener('change', toggleMotifAutre);

    // 1ère passe (au chargement)
    refreshMotifsVisibility();
    toggleMotifAutre();
  });
</script>
@endsection
