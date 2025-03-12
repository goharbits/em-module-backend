<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules_actions = [
            'lead' => ['create', 'update', 'delete', 'get', 'attach_clients'],
            'segment' => ['create', 'update', 'delete', 'get', 'attach_clients'],
            'client' => ['create', 'update', 'delete', 'get', 'get_all_ids', 'import', 'attach_leads'],
            'list' => ['create', 'update', 'delete', 'get', 'attach_contacts', 'get_contacts'],
            'company' => ['create', 'update', 'delete', 'get'],
            'contact' => ['create', 'update', 'delete', 'get', 'get_all_ids', 'import', 'attach_lists'],
            'smtp' => ['create', 'update', 'delete', 'get'],
            'imap' => ['create', 'update', 'delete', 'get', 'get_hosts', 'get_emails'],
            'email_template' => ['create', 'update', 'delete', 'get'],
            'campaign' => ['create', 'update', 'delete', 'get', 'clone'],
            'email_template_type' => ['create', 'update', 'delete', 'get'],
            'role' => ['create', 'update', 'delete', 'get', 'assign_permissions'],
            'permission' => ['get'],
            'admin_user' => ['create', 'update', 'delete', 'get', 'assign_roles', 'restore', 'change_password'],
            'analytics' => ['get', 'get_lead', 'get_segment', 'get_list', 'get_campaign'],
            'status' => ['create', 'update', 'delete', 'get'],
            'modules' => ['get'],
        ];

        collect($modules_actions)->each(function ($actions, $module) {
            collect($actions)->each(function ($action) use ($module) {
                $permissionName = $action . '_' . $module;

                // Check if the permission already exists
                $permission = Permission::where('name', $permissionName)->where('guard_name', 'api')->first();

                // Create the permission only if it doesn't exist
                if (!$permission) {
                    $permission = Permission::create([
                        'name' => $permissionName,
                        'guard_name' => 'api',
                    ]);
                }
            });
        });
    }
}
