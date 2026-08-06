<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => Role::ADMIN, 'description' => 'Admin UPTD PPRD / Samsat'],
            ['name' => 'User OPD', 'slug' => Role::OPD, 'description' => 'Pengguna Organisasi Perangkat Daerah'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
