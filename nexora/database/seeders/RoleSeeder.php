<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'owner',
                'description' => 'Organization owner with full access',
                'status' => 1,
            ],
            [
                'name' => 'admin',
                'description' => 'Administrator with management permissions',
                'status' => 1,
            ],
            [
                'name' => 'manager',
                'description' => 'Manager with project and team permissions',
                'status' => 1,
            ],
            [
                'name' => 'member',
                'description' => 'Regular organization member',
                'status' => 1,
            ],
            [
                'name' => 'guest',
                'description' => 'Limited access user',
                'status' => 1,
            ],
        ];

        foreach($roles as $role) {
            Role::updateOrCreate(
                ['role_name' => $role['name']],
                ['description' => $role['description'], 'status' => $role['status']]
            );
        }
    }
}
    