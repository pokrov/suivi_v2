@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Créer un nouvel utilisateur</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.users.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">Nom :</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email :</label>
            <input type="email" class="form-control" name="email" required>
        </div>

        <div class="form-group">
            <label for="password">Mot de passe :</label>
            <input type="password" class="form-control" name="password" required>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmer le mot de passe :</label>
            <input type="password" class="form-control" name="password_confirmation" required>
        </div>

        <div class="form-group">
            <label for="role">Rôle :</label>
            <select name="role" class="form-control" required>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-success mt-3">Créer l'utilisateur</button>
        <a href="{{ route('superadmin.users.index') }}" class="btn btn-secondary mt-3">Annuler</a>
    </form>
</div>
@endsection
