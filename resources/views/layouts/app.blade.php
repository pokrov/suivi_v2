<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        /* Custom navbar styling if needed */
        .navbar-custom {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="{{ route('home') }}">SUVI</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        @auth
          <li class="nav-item">
            <a class="nav-link {{ request()->is('chef*') ? 'active' : '' }}" href="{{ route('chef.dashboard') }}">
              Grand Projets
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->is('saisie_cpc*') ? 'active' : '' }}" href="{{ route('saisie_cpc.dashboard') }}">
              Saisie CPC
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->is('dajf*') ? 'active' : '' }}" href="{{ route('dajf.dashboard') }}">
              DAJF
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->is('dgu*') ? 'active' : '' }}" href="{{ route('dgu.dashboard') }}">
              DGU
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ request()->is('comm*') ? 'active' : '' }}" href="{{ route('comm.dashboard') }}">
              Commission
            </a>
          </li>
        @endauth
      </ul>

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

        
        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>
