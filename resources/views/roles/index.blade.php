@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gestion des rôles</h1>
        <a href="{{ route('superadmin.roles.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Créer un rôle
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Nom du rôle</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $role)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ ucfirst($role->name) }}</td>
                    <td class="text-center">
                        @if (!in_array($role->name, ['super_admin']))
                        <form action="{{ route('superadmin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </form>
                        @else
                        <span class="badge bg-secondary">Non supprimable</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center text-muted">Aucun rôle trouvé.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
