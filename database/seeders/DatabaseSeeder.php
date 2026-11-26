<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Sengaja tidak membuat user bawaan: tidak boleh ada akun/password default
     * (lihat docs/security/hospital-tenancy-rbac.md).
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
    }
}
