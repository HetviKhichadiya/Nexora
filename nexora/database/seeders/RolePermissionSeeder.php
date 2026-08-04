<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role_permissions = [

            'owner' => [
                'organization.view',
                'organization.create',
                'organization.update',
                'organization.delete',
                'organization.user.view',
                'organization.user.create',
                'organization.user.update',
                'organization.user.delete',
                'role.view',
                'role.create',
                'role.update',
                'role.delete',
                'user.role.assign',
                'user.role.remove',
                'permission.view',
                'permission.create',
                'permission.update',
                'permission.delete',
                'role.permission.assign',
                'role.permission.remove',
            ],
            'admin' => [
                'organization.view',
                'organization.user.view',
                'organization.user.create',
                'organization.user.update',
                'organization.user.delete',
                'role.view',
                'role.create',
                'role.update',
                'role.delete',
                'user.role.assign',
                'user.role.remove',
                'permission.view',
            ],
            'manager' => [
                'organization.view',
                'organization.user.view',
                'role.view',
            ],
            'member' => [
                'organization.view',
                'organization.user.view',
            ],
            'guest' => [
                'organization.view',
            ],
        ];

        foreach ($role_permissions as $role_name => $permissions) {
            $role = Role::where('role_name', $role_name)->first();
            foreach ($permissions as $permission_key) {
                $permission = Permission::where('permission_key', $permission_key)->first();
                if ($role && $permission) {
                    RolePermission::updateOrCreate([
                        'role_id' => $role->id, 
                        'permission_id' => $permission->id, 
                    ],
                    [
                        'permission_value' => 1
                    ]);
                }
            }
        }
    }
}
