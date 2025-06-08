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
            ['email' => 'admin@sigbpjs.com'],
            [
                'name' => 'Admin SIG',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }
}