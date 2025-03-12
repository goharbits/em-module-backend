<?php

namespace App\Http\Controllers\V1\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Repositories\V1\User\Company\CompanyRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new CompanyRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'sometimes|exists:companies,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->company_id) {
                $data = $this->repository->get($request->company_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Companies fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:companies,name',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            $company = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Company created successfully', $company);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
            'name' => 'required|string',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            $company = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Company updated successfully', $company);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            Company::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Company deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
