<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $includedModules = ['lead', 'segment', 'client', 'list', 'contact','smtp', 'imap', 'email_template'];

        $customUserPermissions = Permission::where('name', 'get_email_template_type')
            ->orWhere(function ($query) use ($includedModules) {
                foreach ($includedModules as $index => $module) {
                    if ($index === 0) {
                        $query->whereRaw('`name` LIKE ?', ['%' . $module . '%']);
                    } else {
                        $query->orWhereRaw('`name` LIKE ?', ['%' . $module . '%']);
                    }
                }
            })->pluck('name');

        $permissions = Permission::pluck('name')->toArray();

        // Define roles to create
        $rolesToCreate = [
            ['name' => 'super_admin', 'guard_name' => 'api'],
            ['name' => 'admin', 'guard_name' => 'api'],
            ['name' => 'user', 'guard_name' => 'api'],
        ];

        foreach ($rolesToCreate as $roleData) {
            // Check if the role already exists based on its name and guard
            $role = Role::where('name', $roleData['name'])->where('guard_name', $roleData['guard_name'])->first();

            // Create the role only if it doesn't exist
            if (!$role) {
                $role = Role::create($roleData);
            }

            if ($role->name == 'super_admin') {
                $role->syncPermissions($permissions);
            }

            if ($role->name == 'user') {
                // Sync permissions for the "user" role based on included modules
                $role->syncPermissions($customUserPermissions);
            }
        }
    }
}
