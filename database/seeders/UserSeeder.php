<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sigbpjs.com'], // Kunci untuk mencari atau membuat
            [
                'name' => 'Admin SIG',
                'password' => Hash::make('password123'),
                'role' => 'admin', // Set role admin
            ]
        );

        // Jika Anda ingin membuat user biasa default juga:
        // User::updateOrCreate(
        //     ['email' => 'user@sigbpjs.com'],
        //     [
        //         'name' => 'Pengguna Biasa',
        //         'password' => Hash::make('password123'),
        //         'role' => 'user',
        //     ]
        // );
    }
}