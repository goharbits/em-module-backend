<?php

namespace App\Http\Controllers\V1\AdminControllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\V1\Admin\Status\StatusRepository;

class StatusController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new StatusRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'sometimes|exists:statuses,id',
            'module' => 'sometimes',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError($validator->messages()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            if ($request->status_id) {
                $data = $this->repository->get($request->status_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Status fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:statuses,name',
            'module' => 'required',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            $type = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Status created successfully', $type);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:statuses,id',
            'name' => 'required',
            'module' => 'required',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();

            $type = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Status updated successfully', $type);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:statuses,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            Status::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Status deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
