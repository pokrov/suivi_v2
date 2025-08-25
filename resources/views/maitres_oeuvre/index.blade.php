{{-- resources/views/admin/maitres_oeuvre/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Maîtres d’Œuvre</h3>
    <a href="{{ route('superadmin.maitres-oeuvre.create') }}" class="btn btn-success">
      <i class="fas fa-plus"></i> Ajouter
    </a>
  </div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($items->count())
    <div class="table-responsive shadow-sm">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Adresse</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($items as $i => $mo)
            <tr>
              <td>{{ $items->firstItem() + $i }}</td>
              <td>{{ $mo->nom }}</td>
              <td>{{ $mo->email ?? '—' }}</td>
              <td>{{ $mo->telephone ?? '—' }}</td>
              <td>{{ $mo->adresse ?? '—' }}</td>
              <td class="text-end">
                <a href="{{ route('superadmin.maitres-oeuvre.edit', $mo) }}" class="btn btn-warning btn-sm">Modifier</a>
                <form action="{{ route('superadmin.maitres-oeuvre.destroy', $mo) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer cet enregistrement ?');">
                  @csrf
                  @method('DELETE')
                  <button class="btn btn-danger btn-sm">Supprimer</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-2">{{ $items->links('vendor.pagination.bootstrap-5') }}</div>
  @else
    <div class="alert alert-info">Aucun maître d’Œuvre.</div>
  @endif
</div>
@endsection
