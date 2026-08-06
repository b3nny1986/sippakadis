<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', Role::ADMIN)->firstOrFail();

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@sippakadis.test')],
            [
                'name' => 'Administrator',
                'password' => env('ADMIN_PASSWORD', 'password'),
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );

        $opdRole = Role::where('slug', Role::OPD)->firstOrFail();
        $opd = Opd::first() ?? Opd::create(['kode' => 'OPD-CONTOH', 'nama' => 'CONTOH OPD']);

        User::firstOrCreate(
            ['email' => env('OPD_EMAIL', 'opd@sippakadis.test')],
            [
                'name' => 'User OPD Contoh',
                'password' => env('OPD_PASSWORD', 'password'),
                'role_id' => $opdRole->id,
                'opd_id' => $opd->id,
                'is_active' => true,
            ]
        );
    }
}
