<?php

namespace App\Http\Controllers\V1\AdminControllers;

use App\Http\Controllers\Controller;
use App\Repositories\V1\Admin\RolePermission\RoleRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new RoleRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'sometimes|exists:roles,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError($validator->messages()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            if ($request->role_id) {
                $data = $this->repository->get($request->role_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Roles fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            'permissions_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $role = $this->repository->store($request);
            DB::commit();
            return sendSuccess('Role created successfully', $role);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:roles,id',
            'name' => 'required',
            'permissions_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $role = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Role updated successfully', $role);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors()->first(), 422);
        }

        try {
            DB::beginTransaction();

            Role::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Role deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function assign_permissions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,id',
            'permissions_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $data = $this->repository->assignPermissions($request);

            DB::commit();
            return sendSuccess('Permissions assign to role successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
