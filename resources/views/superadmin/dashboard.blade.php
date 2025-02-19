@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="text-center">Dashboard Super Administrateur</h1>
    <p>Bienvenue, {{ auth()->user()->name }} !</p>

    <div class="row">
        <div class="col-md-4">
            <a href="" class="btn btn-primary btn-block">Gérer les utilisateurs</a>
        </div>
        <div class="col-md-4">
            <a href="" class="btn btn-secondary btn-block">Gérer les rôles</a>
        </div>
        <div class="col-md-4">
            <a href="" class="btn btn-warning btn-block">Paramètres</a>
        </div>
    </div>
</div>
@endsection
