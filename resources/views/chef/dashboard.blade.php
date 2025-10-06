@extends('layouts.app')

@push('styles')
<style>
  .hero-title { font-size: clamp(1.6rem, 3vw, 2.2rem); font-weight: 800; letter-spacing:.4px; }
  .hero-sub   { color:#374151; font-weight:600; }
  .tile {
    border-radius:18px; background: rgba(255,255,255,.85);
    box-shadow: 0 8px 30px rgba(0,0,0,.08); padding:22px; height:100%;
    border: 1px solid rgba(0,0,0,.05);
  }
  .tile h5 { font-weight: 800; letter-spacing:.3px; }
  .tile .lead { color:#475569; }
  .big-btn { padding: .9rem 1.1rem; font-size:1.05rem; font-weight:800; border-radius:12px; }
  .quick-links a { display:block; text-align:center; padding:.75rem 1rem; border-radius:12px; border:2px solid #dbeafe; background:#eff6ff; font-weight:800; margin-bottom:.5rem; }
  .quick-links a:last-child { margin-bottom:0; }
  .quick-links a:hover { background:#e0f2fe; border-color:#bfdbfe; text-decoration:none; }
  .cta { display:flex; gap:.5rem; flex-wrap:wrap; }
</style>
@endpush

@section('content')
<div class="container">
  <div class="text-center mb-4">
    <div class="hero-title">Dashboard Chef</div>
    <div class="hero-sub">Bienvenue, {{ Auth::user()->name }}. Choisissez une action ci-dessous.</div>
  </div>

  <div class="row g-4">
    {{-- Petit Projet --}}
<div class="col-md-4">
  <div class="tile">
    <div class="text-center mb-3">
      <i class="fas fa-home fa-3x text-success"></i>
    </div>
    <h5 class="text-center mb-1">Petit Projet</h5>
    <p class="lead text-center mb-3">Gérez tous les petits projets.</p>
    <div class="d-grid">
      <a href="{{ route('chef.petitprojets.index') }}" class="btn btn-success big-btn">
        <i class="fas fa-list-ul me-1"></i> Accéder
      </a>
    </div>
  </div>
</div>


    {{-- Grand Projet --}}
    <div class="col-md-4">
      <div class="tile">
        <div class="text-center mb-3">
          <i class="fas fa-building fa-3x text-primary"></i>
        </div>
        <h5 class="text-center mb-1">Grand Projet</h5>
        <!-- <p class="lead text-center mb-3">CPC et CLM : tout au même endroit.</p> -->

        {{-- Actions principales --}}
        <div class="cta mb-3 justify-content-center">
          <a href="{{ route('chef.grandprojets.cpc.index') }}" class="btn btn-primary big-btn">
            <i class="fas fa-list-ul me-1"></i> Ouvrir CPC
            
          </a>
          <a href="{{ route('chef.grandprojets.clm.index') }}" class="btn btn-outline-primary big-btn">
            <i class="fas fa-list-check me-1"></i> Ouvrir CLM
            
          </a>
        </div>

        {{-- Liens directs --}}
        <div class="quick-links">
          <a href="{{ route('chef.grandprojets.cpc.create') }}"><i class="fas fa-plus-circle me-1"></i> Nouveau dossier CPC</a>
          <a href="{{ route('chef.grandprojets.clm.create') }}"><i class="fas fa-plus-circle me-1"></i> Nouveau dossier CLM</a>
        </div>
      </div>
    </div>

    {{-- Statistiques --}}
    <div class="col-md-4">
      <div class="tile">
        <div class="text-center mb-3">
          <i class="fas fa-chart-line fa-3x text-warning"></i>
        </div>
        <h5 class="text-center mb-1">Statistiques</h5>
        <p class="lead text-center mb-3">Suivez les indicateurs clés.</p>
        <div class="d-grid">
          <a href="{{ route('chef.stats.index') }}" class="btn btn-warning big-btn">
            <i class="fas fa-chart-simple me-1"></i> Accéder
           
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Raccourcis clavier : C= Cpc, L= Clm, S= Stats --}}
<script>
  document.addEventListener('keydown', (e) => {
    if (['INPUT','TEXTAREA','SELECT'].includes((e.target.tagName||''))) return;
    if (e.key.toLowerCase() === 'c') window.location.href = @json(route('chef.grandprojets.cpc.index'));
    if (e.key.toLowerCase() === 'l') window.location.href = @json(route('chef.grandprojets.clm.index'));
    if (e.key.toLowerCase() === 's') window.location.href = @json(route('chef.stats.index'));
  });
</script>
@endsection
