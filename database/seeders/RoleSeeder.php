<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::insert([
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'sales', 'guard_name' => 'web'],
            ['name' => 'accountant', 'guard_name' => 'web'],
            ['name' => 'inventory_manager', 'guard_name' => 'web'],
        ]);
    }
}
