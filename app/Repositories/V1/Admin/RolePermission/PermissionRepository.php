<?php

namespace App\Repositories\V1\Admin\RolePermission;

use App\Http\Traits\CommonTrait;
use Exception;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $permissions = Permission::query();

        if ($request->search) {
            $permissions->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $total = $permissions->count();

        if (isset($request->page) && $request->page > 0) {
            $permissions = $permissions->paginate($per_page);
        } else {
            $permissions = $permissions->get();
        }

        return [
            'total' => $total,
            'permissions' => $permissions,
        ];
    }

    public function get($permission_id)
    {
        $permission = Permission::find($permission_id);

        return $permission;
    }
}
