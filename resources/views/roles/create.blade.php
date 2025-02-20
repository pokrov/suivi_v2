@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer un nouveau rôle</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.roles.store') }}" method="POST">
        @csrf
        <div class="form-group mb-3">
            <label for="name">Nom du rôle :</label>
            <input type="text" class="form-control" name="name" placeholder="Exemple : chef" required>
        </div>
        <button type="submit" class="btn btn-primary">Créer</button>
        <a href="{{ route('superadmin.roles.index') }}" class="btn btn-secondary">Retour</a>
    </form>
</div>
@endsection
