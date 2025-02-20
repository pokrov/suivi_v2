<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role; 

class UserController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');

    $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
        })
        ->with('roles') // Optimise la récupération des rôles
        ->orderBy('name')
        ->paginate(10); // Affiche 10 utilisateurs par page

    return view('users.index', compact('users'));
}


     // Affiche le formulaire de création d’utilisateur
     public function create()
     {
         $roles = Role::all(); // Récupère tous les rôles pour la sélection
         return view('users.create', compact('roles'));
     }
 
     // Enregistre un nouvel utilisateur
     public function store(Request $request)
     {
         // Validation des champs
         $validated = $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:6|confirmed',
             'role' => 'required|exists:roles,name',
         ]);
 
         // Création de l’utilisateur
         $user = User::create([
             'name' => $validated['name'],
             'email' => $validated['email'],
             'password' => Hash::make($validated['password']),
         ]);
 
         // Assigner le rôle sélectionné
         $user->assignRole($validated['role']);
 
         return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur créé avec succès.');
     }
     public function edit(User $user)
{
    // Récupère tous les rôles pour permettre leur modification
    $roles = \Spatie\Permission\Models\Role::all();
    return view('users.edit', compact('user', 'roles'));
}

public function update(Request $request, User $user)
{
    // Valider les données du formulaire
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'role' => 'required|exists:roles,name',
        'password' => 'nullable|string|min:6|confirmed', // Ajout de la validation pour le mot de passe
    ]);

    // Met à jour les informations de base
    $user->name = $validated['name'];
    $user->email = $validated['email'];

    // Si le mot de passe est fourni, on le met à jour
    if (!empty($validated['password'])) {
        $user->password = Hash::make($validated['password']);
    }

    $user->save();

    // Réassigner le rôle sélectionné
    $user->syncRoles([$validated['role']]);

    return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
}
public function destroy(User $user)
{
    // Empêcher la suppression de soi-même
    if (auth()->id() === $user->id) {
        return redirect()->route('superadmin.users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    }

    // Supprimer l'utilisateur
    $user->delete();

    return redirect()->route('superadmin.users.index')->with('success', 'Utilisateur supprimé avec succès.');
}


}
