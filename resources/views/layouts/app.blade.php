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

  {{-- NAVBAR --}}
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="{{ route('home') }}">
        {{ config('app.name', 'SUVI') }}
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        {{-- Left --}}
        <ul class="navbar-nav me-auto">
          @auth
            {{-- SUPER ADMIN --}}
            @if(Auth::user()->hasRole('super_admin'))
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('superadmin*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                  Administration
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}"
                       href="{{ route('superadmin.dashboard') }}">
                      Dashboard
                    </a>
                  </li>
                  <li><hr class="dropdown-divider"></li>
                  <li>
                    <a class="dropdown-item {{ request()->is('superadmin/users*') ? 'active' : '' }}"
                       href="{{ route('superadmin.users.index') }}">
                      Utilisateurs
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ request()->is('superadmin/roles*') ? 'active' : '' }}"
                       href="{{ route('superadmin.roles.index') }}">
                      Rôles
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ request()->is('superadmin/maitres-oeuvre*') ? 'active' : '' }}"
                       href="{{ route('superadmin.maitres-oeuvre.index') }}">
                      Maîtres d’Œuvre
                    </a>
                  </li>
                </ul>
              </li>
            @endif

            {{-- CHEF --}}
            @if(Auth::user()->hasRole('chef'))
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->is('chef*') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">
                  Espace Chef
                </a>
                <ul class="dropdown-menu">
                  <li>
                    <a class="dropdown-item {{ request()->routeIs('chef.dashboard') ? 'active' : '' }}"
                       href="{{ route('chef.dashboard') }}">
                      Dashboard
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ request()->is('chef/grandprojets/cpc*') ? 'active' : '' }}"
                       href="{{ route('chef.grandprojets.cpc.index') }}">
                      Grand Projets (CPC)
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ request()->is('chef/grandprojets/clm*') ? 'active' : '' }}"
                       href="{{ route('chef.grandprojets.clm.index') }}">
                      Grand Projets (CLM)
                    </a>
                  </li>
                  <li>
                    <a class="dropdown-item {{ request()->routeIs('chef.stats.index') || request()->routeIs('chef.grandprojets.stats.index') ? 'active' : '' }}"
                       href="{{ route('chef.stats.index') }}">
                      Statistiques
                    </a>
                  </li>
                </ul>
              </li>
            @endif

            {{-- SAISIE CPC --}}
            @if(Auth::user()->hasRole('saisie_cpc'))
              <li class="nav-item">
                <a class="nav-link {{ request()->is('saisie_cpc*') ? 'active' : '' }}"
                   href="{{ route('saisie_cpc.dashboard') }}">
                  Saisie CPC
                </a>
              </li>
            @endif

            {{-- DAJF --}}
            @if(Auth::user()->hasRole('dajf'))
              <li class="nav-item">
                <a class="nav-link {{ request()->is('dajf*') ? 'active' : '' }}"
                   href="{{ route('dajf.dashboard') }}">
                  DAJF
                </a>
              </li>
            @endif

            {{-- DGU --}}
            @if(Auth::user()->hasRole('dgu'))
              <li class="nav-item">
                <a class="nav-link {{ request()->is('dgu*') ? 'active' : '' }}"
                   href="{{ route('dgu.dashboard') }}">
                  DGU
                </a>
              </li>
            @endif

            {{-- COMMISSION (tableaux de bord/accès membres) --}}
            <li class="nav-item">
              <a class="nav-link {{ request()->is('comm*') ? 'active' : '' }}"
                 href="{{ route('comm.dashboard') }}">
                Commission
              </a>
            </li>
          @endauth
        </ul>

        {{-- Right --}}
        <ul class="navbar-nav ms-auto">
          @guest
            <li class="nav-item">
              <a class="nav-link" href="{{ route('login') }}">Connexion</a>
            </li>
          @else
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                {{ Auth::user()->name }}
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header small text-muted">
                  {{ Auth::user()->email }}
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="{{ route('home') }}">Mon espace</a>
                </li>
                <li>
                  <a class="dropdown-item" href="{{ route('force.logout') }}">Changer de compte</a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">Se déconnecter</button>
                  </form>
                </li>
              </ul>
            </li>
          @endguest
        </ul>

      </div>
    </div>
  </nav>

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
