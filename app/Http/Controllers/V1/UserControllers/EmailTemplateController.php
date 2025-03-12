<?php

namespace App\Http\Controllers\V1\UserControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Repositories\V1\User\EmailTemplate\EmailTemplateRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EmailTemplateController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new EmailTemplateRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_id' => 'sometimes|exists:email_templates,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->template_id) {
                $data = $this->repository->get($request->template_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Templates fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:email_templates,name',
            'template_type_id' => 'required|exists:email_template_types,id',
            'subject' => 'required|string',
            'body' => 'required',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'is_plain_text' => 'required',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $template = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Template created successfully', $template);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_templates,id',
            'name' => 'required|string',
            'template_type_id' => 'required|exists:email_template_types,id',
            'subject' => 'required|string',
            'body' => 'required',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'is_plain_text' => 'required',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $template = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Template updated successfully', $template);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:email_templates,id',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();
            EmailTemplate::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Email Template deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
