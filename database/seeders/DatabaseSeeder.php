<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador por defecto
        User::firstOrCreate(
            ['email' => 'admin@mitiendita.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin123*'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Cajero por defecto
        User::firstOrCreate(
            ['email' => 'cajero@mitiendita.com'],
            [
                'name' => 'Cajero Principal',
                'password' => Hash::make('Cajero123*'),
                'role' => User::ROLE_CASHIER,
                'email_verified_at' => now(),
            ]
        );
    }
}
