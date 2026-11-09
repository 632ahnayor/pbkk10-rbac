<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Matriks peran => izin. Admin memegang seluruh izin.
     *
     * @var array<string, list<string>>
     */
    public const MATRIX = [
        'admin' => ['lihat-proposal', 'kelola-nilai', 'hapus-proposal'],
        'staf' => ['lihat-proposal'],
        'dosen' => ['lihat-proposal', 'kelola-nilai'],
        'asdos' => ['lihat-proposal', 'kelola-nilai'],
        'mahasiswa' => ['ajukan-proposal'],
    ];

    public function run(): void
    {
        // Spatie meng-cache izin; kosongkan agar perubahan langsung berlaku.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (collect(self::MATRIX)->flatten()->unique() as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (self::MATRIX as $role => $permissions) {
            Role::firstOrCreate(['name' => $role])->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
