<?php

namespace App\Http\Controllers\V1\AdminControllers;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplateType;
use App\Repositories\V1\Admin\EmailTemplateType\EmailTemplateTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailTemplateTypeController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new EmailTemplateTypeRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_type_id' => 'sometimes|exists:email_template_types,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError($validator->messages()->first(), null , 422);
        }

        try {
            DB::beginTransaction();
            if ($request->template_type_id) {
                $data = $this->repository->get($request->template_type_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Email Template Types fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:email_template_types,name',
            'status_id' => 'required|exists:statuses,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            $type = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Email Template Type created successfully', $type);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_template_types,id',
            'name' => 'required',
            'status_id' => 'required|exists:statuses,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();

            $type = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Email Template Type updated successfully', $type);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_template_types,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            EmailTemplateType::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Email Template Type deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
