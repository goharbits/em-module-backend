<?php

namespace App\Repositories\V1\Admin\RolePermission;

use App\Http\Traits\CommonTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $roles = Role::query();

        if ($request->search) {
            $roles->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $total = $roles->count();

        if (isset($request->page) && $request->page > 0) {
            $roles = $roles->with('permissions')->paginate($per_page);
        } else {
            $roles = $roles->with('permissions')->get();
        }

        return [
            'total' => $total,
            'roles' => $roles,
        ];
    }

    public function get($role_id)
    {
        $role = Role::with('permissions')->find($role_id);

        return $role;
    }

    public function store(Request $request)
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);

        $role = $this->assignPermissionsToRole($role, $request->permissions_ids);

        return $role;
    }

    public function update(Request $request)
    {
        $role = Role::find($request->id);

        if ($role->name != $request->name && Role::where('name', $request->name)->first()) {
            throw new Exception('Role has already been taken');
        }

        $role->update([
            'name' => $request->name,
            'guard_name' => 'api',
        ]);

        $role = $this->assignPermissionsToRole($role, $request->permissions_ids);

        return $role;
    }

    public function assignPermissionsToRole($role, $permissions_ids)
    {
        $permissions = Permission::whereIn('id', $permissions_ids)->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        return $role;
    }

    public function assignPermissions(Request $request)
    {
        $role = Role::find($request->role_id);

        $permissions = Permission::whereIn('id', $request->permissions_ids)->pluck('name')->toArray();

        $role->syncPermissions($permissions);

        return $role;
    }

}
