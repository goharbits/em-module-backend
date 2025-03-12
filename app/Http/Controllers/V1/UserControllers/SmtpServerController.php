<?php

namespace App\Http\Controllers\V1\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\SmtpServer;
use App\Repositories\V1\User\SmtpServer\SmtpServerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SmtpServerController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new SmtpServerRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_id' => 'sometimes|exists:smtp_servers,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->smtp_id) {
                $data = $this->repository->get($request->smtp_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Lead fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_name' => 'required',
            'sender_image' => 'nullable|image',
            'sender_email' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_username' => 'required|unique:smtp_servers,smtp_username',
            'smtp_password' => 'required',
            'smtp_encryption' => 'required',
            'smtp_timeout' => 'sometimes',
            'second_limit' => 'sometimes',
            'minute_limit' => 'sometimes',
            'hourly_limit' => 'sometimes',
            'daily_limit' => 'sometimes',
            'monthly_limit' => 'sometimes',
            'delay_range_start' => 'sometimes',
            'delay_range_end' => 'sometimes',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'imap_host_name' => 'sometimes',
            'imap_host' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $smtp = $this->repository->store($request);

            DB::commit();
            return sendSuccess('SMTP server connected successfully', $smtp);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:smtp_servers,id',
            'sender_name' => 'required',
            'sender_image' => 'nullable|image',
            'sender_email' => 'required',
            'smtp_host' => 'required',
            'smtp_port' => 'required',
            'smtp_username' => 'required',
            'smtp_password' => 'required',
            'smtp_encryption' => 'required',
            'smtp_timeout' => 'sometimes',
            'second_limit' => 'sometimes',
            'minute_limit' => 'sometimes',
            'hourly_limit' => 'sometimes',
            'daily_limit' => 'sometimes',
            'monthly_limit' => 'sometimes',
            'delay_range_start' => 'sometimes',
            'delay_range_end' => 'sometimes',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
            'imap_id' => 'sometimes|exists:imaps,id',
            'imap_host_name' => 'sometimes',
            'imap_host' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $smtp = $this->repository->update($request);

            DB::commit();
            return sendSuccess('SMTP server connected successfully', $smtp);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:smtp_servers,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            SmtpServer::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('SMTP Server deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
