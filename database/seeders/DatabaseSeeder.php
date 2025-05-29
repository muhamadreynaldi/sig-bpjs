<?php

namespace Database\Seeders;

// use App\Models\User; // Tidak perlu jika UserSeeder sudah menangani pembuatan User
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // Ini akan membuat atau memperbarui 'admin@gmail.com'
            PenerimaSeeder::class,
        ]);

        // Baris di bawah ini akan membuat user 'test@example.com'
        // Jika Anda ingin user ini juga dikelola oleh UserSeeder, pindahkan ke sana.
        // Atau, pastikan emailnya unik dan tidak bentrok.
        \App\Models\User::factory()->create([ // Pastikan namespace User benar
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}