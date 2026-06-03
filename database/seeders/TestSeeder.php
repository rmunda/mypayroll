<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        // clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles only — no permission assignments needed for tests
        foreach (['admin', 'hr', 'manager', 'employee'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
