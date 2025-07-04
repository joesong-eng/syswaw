<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'arcade-owner']);
        Role::create(['name' => 'arcade-staff']);
        Role::create(['name' => 'machine-owner']);
        Role::create(['name' => 'machine-manager']);
        Role::create(['name' => 'member']);
        Role::create(['name' => 'user']);
    }
}
