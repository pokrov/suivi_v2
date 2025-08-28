@php
  // Inputs expected: $etatCourant (string), $finalAvis ('favorable'|'defavorable'|null)
  $steps = [
    'enregistrement'   => ['#'=>1,  'label'=>'Saisie'],
    'transmis_dajf'    => ['#'=>2,  'label'=>'Vers DAJF'],
    'recu_dajf'        => ['#'=>3,  'label'=>'DAJF'],
    'transmis_dgu'     => ['#'=>4,  'label'=>'Vers DGU'],
    'recu_dgu'         => ['#'=>5,  'label'=>'DGU'],
    'vers_comm_interne'=> ['#'=>6,  'label'=>'Vers Comm. Interne'],
    'comm_interne'     => ['#'=>7,  'label'=>'Comm. Interne'],
    'comm_mixte'       => ['#'=>8,  'label'=>'Comm. Mixte'],
    'signature_3'      => ['#'=>9,  'label'=>'3ᵉ signature'],
    'retour_bs'        => ['#'=>10, 'label'=>'Bureau de suivi'],
    'archive'          => ['#'=>11, 'label'=>'Archivé'],
  ];

  // Index (1-based) of current state to color previous items as "done"
  $order = array_keys($steps);
  $currentIndex = array_search($etatCourant, $order, true);
  if ($currentIndex === false) $currentIndex = 0;

  // defavorable highlight: force step 9 red badge
  $isDefavorable = ($finalAvis ?? null) === 'defavorable';
@endphp

<style>
  /* --- Tracker grid --- */
  .gp-grid {
    display: grid;
    grid-template-columns: repeat( auto-fit, minmax(140px, 1fr) );
    gap: 10px;
  }
  .gp-step {
    position: relative;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    padding: 10px 12px 12px 12px;
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 56px;
    box-shadow: 0 3px 8px rgba(0,0,0,.03);
  }
  .gp-step.done        { background: #f0f7ff; border-color:#cfe3ff; }
  .gp-step.current     { background: #e8f5ff; border-color:#60a5fa; box-shadow: 0 6px 16px rgba(29,78,216,.12); }
  .gp-step.blocked     { background: #fff4f4; border-color:#fca5a5; }
  .gp-badge {
    width: 30px; height: 30px; border-radius: 999px;
    display:flex; align-items:center; justify-content:center;
    font-weight: 800; font-size:.9rem; color:#fff; background:#94a3b8; /* default slate */
    flex: 0 0 30px;
  }
  .gp-step.done   .gp-badge { background:#2563eb; }        /* blue for done */
  .gp-step.current .gp-badge{ background:#1d4ed8; }        /* darker blue current */
  .gp-step.blocked .gp-badge{ background:#dc2626; }        /* red for blocked */
  .gp-label{
    line-height:1.2;
    font-weight: 700; color:#0f172a; font-size:.92rem;
  }
  .gp-sub {
    font-size:.75rem; color:#64748b; margin-top:2px;
  }
  /* Small helpers */
  .gp-legend{ margin-top:8px; font-size:.85rem; color:#475569;}
  .gp-legend .chip{ display:inline-flex; align-items:center; gap:.4rem; padding:.15rem .5rem; border-radius:999px; border:1px solid #e2e8f0; margin-right:6px; background:#fff;}
  .gp-dot{ width:8px;height:8px;border-radius:999px; display:inline-block; background:#94a3b8;}
  .chip.done .gp-dot{ background:#2563eb;}
  .chip.current .gp-dot{ background:#1d4ed8;}
  .chip.blocked .gp-dot{ background:#dc2626;}
</style>

<div class="gp-grid">
  @foreach($steps as $key => $meta)
    @php
      $idx   = array_search($key, $order, true);
      $state = 'default';
      if ($idx !== false) {
        if ($idx < $currentIndex) $state = 'done';
        elseif ($idx === $currentIndex) $state = 'current';
      }

      // If avis interne est défavorable, visually "block" step 9 (3e signature)
      $forceBlocked = $isDefavorable && $key === 'signature_3';
      $classes = 'gp-step';
      $classes .= $forceBlocked ? ' blocked' : '';
      if (!$forceBlocked) {
        $classes .= $state === 'done' ? ' done' : ($state === 'current' ? ' current' : '');
      }
    @endphp

    <div class="{{ $classes }}">
      <div class="gp-badge">{{ $meta['#'] }}</div>
      <div>
        <div class="gp-label">{{ $meta['label'] }}</div>
        @if($key === $etatCourant)
          <div class="gp-sub">État en cours</div>
        @elseif($isDefavorable && $key==='signature_3')
          <div class="gp-sub">Non atteint (avis défavorable)</div>
        @endif
      </div>
    </div>
  @endforeach
</div>

{{-- Legend (optional, keep or remove) --}}
<div class="gp-legend">
  <span class="chip done"><span class="gp-dot"></span> Terminé</span>
  <span class="chip current"><span class="gp-dot"></span> En cours</span>
  <span class="chip"><span class="gp-dot"></span> À venir</span>
  @if($isDefavorable)
    <span class="chip blocked"><span class="gp-dot"></span> Bloqué (défavorable)</span>
  @endif
</div>
