<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'first_name' => 'Project',
            'last_name' => 'Admin',
            'full_name' => 'Project Admin',
            'slug' => 'project-admin',
            'email' => 'projectadmin@mailinator.com',
            'password' => Hash::make('123456'),
            'phone' => '8000000000',
            'role' => 'admin',
            'role_id' => 1,
            'address' => '115 Pitt Street, Sydney NSW, Australia',
            'area' => '115 Pitt St',
            'city' => 'Sydney',
            'state' => 'NSW',
            'country' => 'Australia',
            'country_code' => '61',
            'zipcode' => '2000',
            'latitude' => '-33.8664701',
            'longitude' => '151.2081952',
            'status' => 'active',
        ]);
    }
}
