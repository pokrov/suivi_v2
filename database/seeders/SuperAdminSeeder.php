<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // Crée le rôle 'super_admin' s'il n'existe pas déjà
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        // Crée l'utilisateur Super Admin s'il n'existe pas déjà
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'), // Modifie le mot de passe après les tests
            ]
        );

        // Assigne le rôle au super administrateur
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole($role);
        }
    }
}
