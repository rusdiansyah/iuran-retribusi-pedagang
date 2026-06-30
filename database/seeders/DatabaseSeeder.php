<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
            'role' => 'Admin',
        ]);

        \App\Models\Setting::create([
            'nama_aplikasi' => 'Sistem Iuran Retribusi Pedagang',
            'email' => 'admin@example.com',
        ]);
    }
}
