<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['name' => 'Staf', 'email' => 'staf@example.com', 'role' => 'staf'],
            ['name' => 'Andi', 'email' => 'andi@example.com', 'role' => 'mahasiswa'],
            ['name' => 'Budi', 'email' => 'budi@example.com', 'role' => 'mahasiswa'],
            ['name' => 'Asdos', 'email' => 'asdos@example.com', 'role' => 'asdos'],
            ['name' => 'Dosen', 'email' => 'dosen@example.com', 'role' => 'dosen'],
        ] as $demoUser) {
            User::updateOrCreate(
                ['email' => $demoUser['email']],
                ['name' => $demoUser['name'], 'password' => 'password', 'email_verified_at' => now()],
            )->syncRoles([$demoUser['role']]);
        }
    }
}
