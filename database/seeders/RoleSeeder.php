<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run()
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $roles = [
            ['name' => 'admin', 'level' => 0, 'guard_name' => 'web'],
            ['name' => 'arcade-owner', 'level' => 10, 'guard_name' => 'web'],
            ['name' => 'arcade-staff', 'level' => 11, 'guard_name' => 'web'],
            ['name' => 'machine-owner', 'level' => 20, 'guard_name' => 'web'],
            ['name' => 'machine-staff', 'level' => 21, 'guard_name' => 'web'],
            ['name' => 'member', 'level' => 100, 'guard_name' => 'web'],
        ];
        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                ['level' => $roleData['level']]
            );
        }
    }
}
