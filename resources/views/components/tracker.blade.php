@php
  // $etatCourant attendu: string (ex: 'enregistrement', 'recu_dajf'...)
  $steps = [
    'enregistrement' => 'Saisie',
    'recu_dajf'      => 'DAJF',
    'transmis_dgu'   => 'Vers DGU',
    'recu_dgu'       => 'DGU',
    'comm_interne'   => 'Commission',
    'favorable'      => 'Favorable',
    'archive'        => 'Archivé',
  ];

  $order = array_keys($steps);
  $currentIndex = array_search($etatCourant, $order, true);
@endphp

{{-- WRAPPER: keeps tracker inside the card --}}
<div class="tracker-wrap">
  <div class="tracker">
    @foreach($order as $idx => $state)
      <div class="tracker-step {{ $idx <= $currentIndex ? 'done' : '' }}">
        <div class="bubble">{{ $idx+1 }}</div>
        <div class="label">{{ $steps[$state] }}</div>
        @unless($loop->last)
          <div class="bar"></div>
        @endunless
      </div>
    @endforeach
  </div>
</div>

<style>
  .tracker-wrap{
    width: 100%;
    padding: .25rem 0;
  }

  .tracker{
    display: flex;
    align-items: center;
    justify-content: space-between; /* Étale sur la largeur disponible */
    flex-wrap: wrap;               /* Permet retour ligne si trop serré */
    gap: 10px;
  }

  .tracker-step{
    display: flex;
    align-items: center;
    flex: 1;                       /* Étapes prennent largeur égale */
    min-width: 80px;
    justify-content: center;
  }

  .bubble{
    width: 26px; height: 26px;
    border-radius: 50%;
    font-size: .75rem;
    display: flex; align-items: center; justify-content: center;
    background: #adb5bd; color: #fff; font-weight: 600;
  }
  .tracker-step.done .bubble{ background: #0d6efd; }

  .label{
    margin-left: 4px; font-size: .75rem; white-space: nowrap;
    color: #0d6efd; font-weight: 600;
  }
  .tracker-step:not(.done) .label{ color:#6c757d; font-weight:500; }

  .bar{
    flex: 1;
    height: 3px; border-radius: 2px;
    background: #c9cdd2; margin: 0 4px;
  }
  .tracker-step.done + .bar,
  .tracker-step.done .bar{ background:#0d6efd; }
</style>

