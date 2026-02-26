<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer un admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'matricule' => 'ADMIN001',
            'email_verified_at' => now(),
        ]);

        // Créer 10 employés avec des données réalistes
        $employes = [
            ['name' => 'Jean Dupont', 'matricule' => 'EMP001', 'email' => 'jean.dupont@example.com'],
            ['name' => 'Marie Martin', 'matricule' => 'EMP002', 'email' => 'marie.martin@example.com'],
            ['name' => 'Pierre Durand', 'matricule' => 'EMP003', 'email' => 'pierre.durand@example.com'],
            ['name' => 'Sophie Bernard', 'matricule' => 'EMP004', 'email' => 'sophie.bernard@example.com'],
            ['name' => 'Lucas Petit', 'matricule' => 'EMP005', 'email' => 'lucas.petit@example.com'],
            ['name' => 'Emma Robert', 'matricule' => 'EMP006', 'email' => 'emma.robert@example.com'],
            ['name' => 'Thomas Richard', 'matricule' => 'EMP007', 'email' => 'thomas.richard@example.com'],
            ['name' => 'Julie Moreau', 'matricule' => 'EMP008', 'email' => 'julie.moreau@example.com'],
            ['name' => 'Nicolas Simon', 'matricule' => 'EMP009', 'email' => 'nicolas.simon@example.com'],
            ['name' => 'Camille Laurent', 'matricule' => 'EMP010', 'email' => 'camille.laurent@example.com'],
        ];

        foreach ($employes as $employe) {
            User::create([
                'name' => $employe['name'],
                'email' => $employe['email'],
                'password' => Hash::make('password'),
                'role' => 'employe',
                'matricule' => $employe['matricule'],
                'email_verified_at' => now(),
            ]);
        }

        $this->command->info('✅ 1 admin et 10 employés créés avec succès !');
    }
}
