@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Statistiques des Projets CPC (Chef)</h2>

    <div class="row">
        {{-- Carte du nombre total de projets --}}
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Total des Projets CPC</div>
                <div class="card-body">
                    <h4 class="card-title text-center">{{ $totalProjets }}</h4>
                </div>
            </div>
        </div>

        {{-- Répartition par état --}}
        <div class="col-md-4">
            <div class="card bg-info text-white mb-3">
                <div class="card-header">Répartition par État</div>
                <div class="card-body">
                    <ul>
                        @foreach ($projetsParEtat as $etat)
                            <li>{{ ucfirst($etat->etat) }} : {{ $etat->total }} projets</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Répartition par préfecture --}}
        <div class="col-md-4">
            <div class="card bg-warning text-dark mb-3">
                <div class="card-header">Répartition par Préfecture</div>
                <div class="card-body">
                    <ul>
                        @foreach ($projetsParPrefecture as $pref)
                            <li>{{ $pref->province }} : {{ $pref->total }} projets</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Nombre de projets par mois (Graphique) --}}
    <div class="card mb-3">
        <div class="card-header bg-secondary text-white">Nombre de projets par mois</div>
        <div class="card-body">
            <canvas id="projetsMoisChart"></canvas>
        </div>
    </div>

    {{-- Top 5 des communes --}}
    <div class="card mb-3">
        <div class="card-header bg-success text-white">Top 5 Communes</div>
        <div class="card-body">
            <ul>
                @foreach ($topCommunes as $commune)
                    <li>{{ $commune->commune_1 }} : {{ $commune->total }} projets</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

{{-- Chart.js pour le graphique --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var ctx = document.getElementById('projetsMoisChart').getContext('2d');
    var chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($projetsParMois->pluck('mois')),
            datasets: [{
                label: 'Projets CPC par Mois',
                data: @json($projetsParMois->pluck('total')),
                borderColor: 'blue',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endsection
