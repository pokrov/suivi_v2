<?php

// app/Http/Controllers/MaitreOeuvreController.php
namespace App\Http\Controllers;

use App\Models\MaitreOeuvre;
use Illuminate\Http\Request;

class MaitreOeuvreController extends Controller
{
    // Protégé à l'identique de ton admin (super_admin)
    public function __construct()
    {
        $this->middleware(['auth','role:super_admin']);
    }

    public function index()
    {
        $items = MaitreOeuvre::orderBy('nom')->paginate(15);
        return view('maitres_oeuvre.index', compact('items'));
    }

    public function create()
    {
        return view('maitres_oeuvre.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse'   => 'nullable|string|max:255',
        ]);

        MaitreOeuvre::create($data);
        return redirect()->route('superadmin.maitres-oeuvre.index')
            ->with('success', 'Maître d’Œuvre ajouté.');
    }

    public function edit(MaitreOeuvre $maitres_oeuvre) // param name = slug ressource
    {
        $item = $maitres_oeuvre;
        return view('maitres_oeuvre.edit', compact('item'));
    }

    public function update(Request $request, MaitreOeuvre $maitres_oeuvre)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:255',
            'email'     => 'nullable|email|max:255',
            'telephone' => 'nullable|string|max:50',
            'adresse'   => 'nullable|string|max:255',
        ]);

        $maitres_oeuvre->update($data);
        return redirect()->route('.maitres-oeuvre.index')
            ->with('success', 'Maître d’Œuvre mis à jour.');
    }

    public function destroy(MaitreOeuvre $maitres_oeuvre)
    {
        $maitres_oeuvre->delete();
        return back()->with('success', 'Maître d’Œuvre supprimé.');
    }
}
