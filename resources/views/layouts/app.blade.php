<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SUVI') }}</title>

    {{-- Fonts --}}
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    {{-- Vite (Bootstrap + JS de l’app) --}}
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    {{-- Hook pour styles additionnels des vues --}}
    @stack('styles')

    <style>
      /* Petits ajustements cosmétiques globaux */
      .navbar-brand { letter-spacing: .5px; }
      .app-alerts .alert { border-radius: 12px; }
      .app-card { border-radius: 14px; }
       body {
    background: url("{{ asset('images/bg.png') }}") no-repeat center center fixed;
    background-size: cover;
    margin: 0;
    padding: 0;
  }

  /* Optionnel : un voile pour lisibilité */
  body::before {
    content: "";
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255, 255, 255, 0.6); /* blanc semi-transparent */
    z-index: -1;
  }*/* Pour toutes les tables */
.table-responsive, 
.table {
  background: rgba(255, 255, 255, 0.6); /* blanc avec opacité */
  border-radius: 8px;
  padding: 10px;
}

/* Pour les cartes Bootstrap */
.card {
  background: rgba(255, 255, 255, 0.8) !important; /* plus opaque */
  border-radius: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

/* Pour les headers */
.table thead {
  background: rgba(0, 0, 0, 0.9); /* noir quasi opaque */
  color: white;
}

    </style>
</head>
<body>
<div id="app">

  {{-- NAVBAR (accessible & simplifié) --}}
{{-- NAVBAR compacte, sans dropdowns, tout visible --}}
<header class="bg-dark text-white border-bottom shadow-sm">
  <div class="container py-2 d-flex justify-content-between align-items-center" style="gap:12px;">
    <a class="navbar-brand fw-bold d-flex align-items-center gap-2 text-white text-decoration-none" href="{{ route('home') }}">
      <i class="fas fa-layer-group"></i> {{ config('app.name', 'SUVI') }}
    </a>

    @auth
      <div class="d-flex align-items-center gap-3">
        <span class="small text-white-50 d-none d-md-inline">{{ Auth::user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="m-0">
          @csrf
          <button class="btn btn-outline-light btn-sm"><i class="fas fa-right-from-bracket me-1"></i>Déconnexion</button>
        </form>
      </div>
    @endauth
  </div>

  @auth
  <div class="bg-black-50">
    <div class="container py-2">

      {{-- Règles de style communes --}}
      <style>
        .role-block + .role-block { margin-top: .35rem; }
        .role-title {
          font-weight: 800; letter-spacing: .3px; font-size: .95rem;
          margin: 0 0 .25rem 0; color: #e5e7eb;
        }
        .nav-chips { display:flex; flex-wrap:wrap; gap:.5rem; }
        .btn-nav {
          display:inline-flex; align-items:center; gap:.5rem;
          padding:.45rem .8rem; border-radius:999px; border:2px solid transparent;
          background:#ffffff; color:#111827; font-weight:800; letter-spacing:.2px;
          box-shadow:0 1px 0 rgba(0,0,0,.06);
        }
        .btn-nav:hover { text-decoration:none; filter:brightness(0.96); }
        .btn-nav.active { background:#0d6efd; color:#fff; border-color:#0d6efd; }
        .btn-nav i { opacity:.9; }
        .role-sep { height:1px; background:rgba(255,255,255,.12); margin:.5rem 0; }
        @media (max-width: 768px) {
          .btn-nav { font-size: .95rem; }
        }
      </style>

      {{-- CHEF ---------------------------------------------------------------}}
      @if(Auth::user()->hasRole('chef'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-user-tie me-1"></i> Espace Chef</div>
          <div class="nav-chips">
            <a class="btn-nav {{ request()->routeIs('chef.dashboard') ? 'active' : '' }}" href="{{ route('chef.dashboard') }}">
              <i class="fas fa-house"></i> Dashboard
            </a>
            <a class="btn-nav {{ request()->is('chef/grandprojets/cpc*') ? 'active' : '' }}" href="{{ route('chef.grandprojets.cpc.index') }}">
              <i class="fas fa-city"></i> Grand Projets CPC
            </a>
            <a class="btn-nav {{ request()->is('chef/grandprojets/clm*') ? 'active' : '' }}" href="{{ route('chef.grandprojets.clm.index') }}">
              <i class="fas fa-warehouse"></i> Grand Projets CLM
            </a>
            <a class="btn-nav {{ request()->routeIs('chef.stats.index') ? 'active' : '' }}" href="{{ route('chef.stats.index') }}">
              <i class="fas fa-chart-line"></i> Statistiques
            </a>
            {{-- Assignations (la bonne route est chef.assignments.index) --}}
            <a class="btn-nav {{ request()->routeIs('chef.assignments.index') ? 'active' : '' }}" href="{{ route('chef.assignments.index') }}">
              <i class="fas fa-share-nodes"></i> Attribution des dossiers
            </a>
          </div>
        </div>
        <div class="role-sep"></div>
      @endif

      {{-- SAISIE CPC ---------------------------------------------------------}}
      @if(Auth::user()->hasRole('saisie_cpc'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-keyboard me-1"></i> Saisie CPC</div>
          <div class="nav-chips">
            <a class="btn-nav {{ request()->routeIs('saisie_cpc.dashboard') ? 'active' : '' }}" href="{{ route('saisie_cpc.dashboard') }}">
              <i class="fas fa-list-check"></i> Tableau de bord
            </a>
            <a class="btn-nav {{ request()->routeIs('saisie_cpc.cpc.create') ? 'active' : '' }}" href="{{ route('saisie_cpc.cpc.create') }}">
              <i class="fas fa-file-circle-plus"></i> Nouveau CPC
            </a>
            <a class="btn-nav {{ request()->routeIs('saisie_cpc.clm.create') ? 'active' : '' }}" href="{{ route('saisie_cpc.clm.create') }}">
              <i class="fas fa-file-circle-plus"></i> Nouveau CLM
            </a>
          </div>
        </div>
        <div class="role-sep"></div>
      @endif

      {{-- DAJF (CPC / CLM & Inbox / Outbox) ---------------------------------}}
      @if(Auth::user()->hasRole('dajf'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-briefcase me-1"></i> DAJF</div>
          <div class="nav-chips">
            <a class="btn-nav {{ request()->is('dajf/inbox*') && request('type','cpc')==='cpc' ? 'active' : '' }}"
               href="{{ route('dajf.inbox',['type'=>'cpc']) }}"><i class="fas fa-inbox"></i> CPC — À traiter</a>
            <a class="btn-nav {{ request()->is('dajf/outbox*') && request('type','cpc')==='cpc' ? 'active' : '' }}"
               href="{{ route('dajf.outbox',['type'=>'cpc']) }}"><i class="fas fa-paper-plane"></i> CPC — Envoyés</a>
            <a class="btn-nav {{ request()->is('dajf/inbox*') && request('type')==='clm' ? 'active' : '' }}"
               href="{{ route('dajf.inbox',['type'=>'clm']) }}"><i class="fas fa-inbox"></i> CLM — À traiter</a>
            <a class="btn-nav {{ request()->is('dajf/outbox*') && request('type')==='clm' ? 'active' : '' }}"
               href="{{ route('dajf.outbox',['type'=>'clm']) }}"><i class="fas fa-paper-plane"></i> CLM — Envoyés</a>
          </div>
        </div>
        <div class="role-sep"></div>
      @endif

      {{-- DGU (CPC / CLM & Inbox / Outbox) ----------------------------------}}
      @if(Auth::user()->hasRole('dgu'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-building-columns me-1"></i> DGU</div>
          <div class="nav-chips">
            <a class="btn-nav {{ request()->is('dgu/inbox*') && request('type','cpc')==='cpc' ? 'active' : '' }}"
               href="{{ route('dgu.inbox',['type'=>'cpc']) }}"><i class="fas fa-inbox"></i> CPC — À traiter</a>
            <a class="btn-nav {{ request()->is('dgu/outbox*') && request('type','cpc')==='cpc' ? 'active' : '' }}"
               href="{{ route('dgu.outbox',['type'=>'cpc']) }}"><i class="fas fa-paper-plane"></i> CPC — Envoyés</a>
            <a class="btn-nav {{ request()->is('dgu/inbox*') && request('type')==='clm' ? 'active' : '' }}"
               href="{{ route('dgu.inbox',['type'=>'clm']) }}"><i class="fas fa-inbox"></i> CLM — À traiter</a>
            <a class="btn-nav {{ request()->is('dgu/outbox*') && request('type')==='clm' ? 'active' : '' }}"
               href="{{ route('dgu.outbox',['type'=>'clm']) }}"><i class="fas fa-paper-plane"></i> CLM — Envoyés</a>
          </div>
        </div>
        <div class="role-sep"></div>
      @endif

      {{-- COMMISSION ---------------------------------------------------------}}
      @if(Auth::user()->hasRole('comm_interne'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-people-group me-1"></i> Commission</div>
          <div class="nav-chips">
            {{-- CPC --}}
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type','cpc')==='cpc' && request('scope','recevoir')==='recevoir' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'cpc','scope'=>'recevoir']) }}"><i class="fas fa-download"></i> CPC — À recevoir</a>
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type','cpc')==='cpc' && request('scope')==='interne' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'cpc','scope'=>'interne']) }}"><i class="fas fa-gavel"></i> CPC — Interne</a>
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type','cpc')==='cpc' && request('scope')==='mixte' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'cpc','scope'=>'mixte']) }}"><i class="fas fa-users-gear"></i> CPC — Mixte</a>
            {{-- CLM --}}
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type')==='clm' && request('scope','recevoir')==='recevoir' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'clm','scope'=>'recevoir']) }}"><i class="fas fa-download"></i> CLM — À recevoir</a>
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type')==='clm' && request('scope')==='interne' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'clm','scope'=>'interne']) }}"><i class="fas fa-gavel"></i> CLM — Interne</a>
            <a class="btn-nav {{ request()->routeIs('comm.dashboard') && request('type')==='clm' && request('scope')==='mixte' ? 'active' : '' }}"
               href="{{ route('comm.dashboard', ['type'=>'clm','scope'=>'mixte']) }}"><i class="fas fa-users-gear"></i> CLM — Mixte</a>
          </div>
        </div>
        <div class="role-sep"></div>
      @endif

      {{-- SUPER ADMIN --------------------------------------------------------}}
      @if(Auth::user()->hasRole('super_admin'))
        <div class="role-block">
          <div class="role-title"><i class="fas fa-user-shield me-1"></i> Administration</div>
          <div class="nav-chips">
            <a class="btn-nav {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}" href="{{ route('superadmin.dashboard') }}">
              <i class="fas fa-gauge-high"></i> Dashboard
            </a>
            <a class="btn-nav {{ request()->is('superadmin/users*') ? 'active' : '' }}" href="{{ route('superadmin.users.index') }}">
              <i class="fas fa-users"></i> Utilisateurs
            </a>
            <a class="btn-nav {{ request()->is('superadmin/roles*') ? 'active' : '' }}" href="{{ route('superadmin.roles.index') }}">
              <i class="fas fa-id-badge"></i> Rôles
            </a>
            <a class="btn-nav {{ request()->is('superadmin/maitres-oeuvre*') ? 'active' : '' }}" href="{{ route('superadmin.maitres-oeuvre.index') }}">
              <i class="fas fa-user-cog"></i> Maîtres d’Œuvre
            </a>
          </div>
        </div>
      @endif

    </div>
  </div>
  @endauth
</header>


@push('scripts')
<script>
  // Sauvegarde taille de police (A-/A+) dans localStorage
  (function () {
    const html = document.documentElement;
    const key  = 'ui-font-scale';
    const min = 0.9, max = 1.35, step = 0.05;
    function setScale(v){ html.style.setProperty('--font-scale', v); localStorage.setItem(key, v); }
    function getScale(){ return parseFloat(localStorage.getItem(key) || '1'); }
    function clamp(v){ return Math.max(min, Math.min(max, v)); }
    setScale(getScale());
    document.getElementById('fontPlus') ?.addEventListener('click', ()=> setScale(clamp(getScale()+step)));
    document.getElementById('fontMinus')?.addEventListener('click', ()=> setScale(clamp(getScale()-step)));
  })();
</script>
@endpush

@push('styles')
<style>
  :root { --font-scale: 1; }
  html { font-size: calc(16px * var(--font-scale)); }
  .navbar .nav-link { font-weight: 700; letter-spacing:.2px; }
  .dropdown-menu { font-size: 1rem; }
</style>
@endpush


  {{-- CONTENU PRINCIPAL --}}
  <main class="py-4">
    <div class="container">
      {{-- ALERTES GLOBALES --}}
      <div class="app-alerts mb-3">
        @if (session('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Veuillez vérifier les champs :</strong>
            <ul class="mb-0 mt-1">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
      </div>

      @yield('content')
    </div>
  </main>

</div>

{{-- Hook pour scripts additionnels des vues --}}
@stack('scripts')
</body>
</html>
