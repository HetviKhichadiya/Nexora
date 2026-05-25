<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['permission_key' => 'organization.view'],
            ['permission_key' => 'organization.create'],
            ['permission_key' => 'organization.update'],
            ['permission_key' => 'organization.delete'],

            ['permission_key' => 'organization.user.view'],
            ['permission_key' => 'organization.user.create'],
            ['permission_key' => 'organization.user.update'],
            ['permission_key' => 'organization.user.delete'],

            ['permission_key' => 'role.view'],
            ['permission_key' => 'role.create'],
            ['permission_key' => 'role.update'],
            ['permission_key' => 'role.delete'],

            ['permission_key' => 'user.role.assign'],
            ['permission_key' => 'user.role.remove'],

            ['permission_key' => 'permission.view'],
            ['permission_key' => 'permission.create'],
            ['permission_key' => 'permission.update'],
            ['permission_key' => 'permission.delete'],

            ['permission_key' => 'role.permission.assign'],
            ['permission_key' => 'role.permission.remove'],
        ];

        foreach($permissions as $permission) {
            Permission::updateOrCreate(
                ['permission_key' => $permission['permission_key']],
                []
            );
        }
    }
}
