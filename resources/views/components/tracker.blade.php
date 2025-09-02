{{-- resources/views/components/tracker.blade.php --}}
@php
  /**
   * Inputs attendus :
   * - $etatCourant : string
   * - $finalAvis   : 'favorable'|'defavorable'|null (optionnel)
   *
   * Mappage des étapes (ordre & libellés 1 → 11)
   */
  $steps = [
    'enregistrement'    => ['#'=>1,  'label'=>'Saisie'],
    'transmis_dajf'     => ['#'=>2,  'label'=>'Vers DAJF'],
    'recu_dajf'         => ['#'=>3,  'label'=>'DAJF'],
    'transmis_dgu'      => ['#'=>4,  'label'=>'Vers DGU'],
    'recu_dgu'          => ['#'=>5,  'label'=>'DGU'],
    'vers_comm_interne' => ['#'=>6,  'label'=>'Vers Comm. Interne'],
    'comm_interne'      => ['#'=>7,  'label'=>'Comm. Interne'],
    'comm_mixte'        => ['#'=>8,  'label'=>'Comm. Mixte'],
    'signature_3'       => ['#'=>9,  'label'=>'3ᵉ signature'],
    'retour_bs'         => ['#'=>10, 'label'=>'Bureau de suivi'],
    'archive'           => ['#'=>11, 'label'=>'Archivé'],
  ];

  // Index (0-based) de l’état courant
  $order = array_keys($steps);
  $currentIndex = array_search($etatCourant ?? '', $order, true);
  if ($currentIndex === false) $currentIndex = -1;

  // Avis défavorable => on “bloque” visuellement la 3ᵉ signature (étape #9)
  $isDefavorable = ($finalAvis ?? null) === 'defavorable';
@endphp

<style>
  /* ====== Tracker (responsive grid) ====== */
  .gp-grid {
    display: grid;
    grid-template-columns: repeat( auto-fit, minmax(160px, 1fr) );
    gap: 10px;
  }
  .gp-step {
    position: relative;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 10px 12px 12px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 58px;
    box-shadow: 0 3px 8px rgba(0,0,0,.03);
    transition: background .15s ease, box-shadow .15s ease, border-color .15s ease;
  }
  .gp-step .gp-badge {
    width: 32px; height: 32px; border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    font-weight: 800; font-size:.92rem; color:#fff; background:#94a3b8; /* slate par défaut */
    flex: 0 0 32px;
  }
  .gp-step .gp-label{ line-height:1.2; font-weight: 800; color:#0f172a; font-size:.95rem; }
  .gp-step .gp-sub  { font-size:.75rem; color:#64748b; margin-top:2px; }

  /* États visuels */
  .gp-step.done        { background: #f1f5ff; border-color:#d6e4ff; }
  .gp-step.done  .gp-badge { background:#2563eb; }                /* bleu “fait” */
  .gp-step.current     { background: #79b8f8ff; border-color:#60a5fa; box-shadow: 0 6px 16px rgba(12, 35, 97, 0.12); }
  .gp-step.current .gp-badge{ background:#1d4ed8; }               /* bleu plus foncé “en cours” */
  .gp-step.blocked     { background: #fff1f2; border-color:#fecdd3; }
  .gp-step.blocked .gp-badge{ background:#dc2626; }               /* rouge “bloqué” */

  /* ====== Légende lisible sur fond clair ====== */
  .gp-legend{
    margin-top:10px;
    display:inline-flex; gap:8px; align-items:center; flex-wrap:wrap;
    padding:6px 10px;
    background:#f8fafc;               /* détaché du blanc */
    border:1px solid #e2e8f0;
    border-radius:10px;
    font-size:.85rem; color:#475569;
  }
  .gp-legend .chip{
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.18rem .55rem; border-radius:999px; font-weight:700;
    border:1px solid transparent;
    box-shadow: 0 1px 0 rgba(0,0,0,.02);
  }
  .gp-legend .gp-dot{ width:8px; height:8px; border-radius:999px; display:inline-block; }

  .gp-legend .chip.done{
    background:#e7f1ff; border-color:#cfe2ff; color:#0b5ed7;
  }
  .gp-legend .chip.done .gp-dot{ background:#0d6efd; }

  .gp-legend .chip.current{
    background:#dbeafe; border-color:#bfdbfe; color:#1d4ed8;
  }
  .gp-legend .chip.current .gp-dot{ background:#1d4ed8; }

  .gp-legend .chip.default{
    background:#f1f5f9; border-color:#e2e8f0; color:#334155;
  }
  .gp-legend .chip.default .gp-dot{ background:#64748b; }

  .gp-legend .chip.blocked{
    background:#fee2e2; border-color:#fecaca; color:#b91c1c;
  }
  .gp-legend .chip.blocked .gp-dot{ background:#dc2626; }
</style>

<div class="gp-grid">
  @foreach($steps as $key => $meta)
    @php
      $idx = array_search($key, $order, true);

      // Par défaut : à venir
      $classes = 'gp-step';
      $isCurrent = ($idx === $currentIndex);
      $isDone    = ($idx !== false && $idx < $currentIndex);

      // Avis défavorable => on force “bloqué” sur 3ᵉ signature
      $forceBlocked = $isDefavorable && $key === 'signature_3';

      if ($forceBlocked) {
        $classes .= ' blocked';
      } elseif ($isCurrent) {
        $classes .= ' current';
      } elseif ($isDone) {
        $classes .= ' done';
      }
    @endphp

    <div class="{{ $classes }}">
      <div class="gp-badge">{{ $meta['#'] }}</div>
      <div>
        <div class="gp-label">{{ $meta['label'] }}</div>

        {{-- Sous-libellé contextuel --}}
        @if($forceBlocked)
          <div class="gp-sub">Non atteint (avis défavorable)</div>
        @elseif($isCurrent)
          <!-- <div class="gp-sub">État en cours</div> -->
        @endif
      </div>
    </div>
  @endforeach
</div>

{{-- Légende (facultative) --}}
<div class="gp-legend">
  <span class="chip done"><span class="gp-dot"></span> Terminé</span>
  <span class="chip current"><span class="gp-dot"></span> En cours</span>
  <span class="chip default"><span class="gp-dot"></span> À venir</span>
  @if($isDefavorable)
    <span class="chip blocked"><span class="gp-dot"></span> Bloqué (défavorable)</span>
  @endif
</div>
