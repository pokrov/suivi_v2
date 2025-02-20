<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Affiche la liste des rôles
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }
    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name|max:50',
        ]);

        Role::create(['name' => strtolower($request->name)]);

        return redirect()->route('superadmin.roles.index')->with('success', 'Rôle créé avec succès.');
    }

}
