<?php

namespace App\Http\Controllers\V1\UserControllers;

use App\Models\Imap;
use Illuminate\Http\Request;
use App\Services\V1\ImapService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Repositories\V1\User\Imap\ImapRepository;

class ImapController extends Controller
{

    private $repository, $service;

    public function __construct()
    {
        $this->repository = new ImapRepository;
        $this->service = new ImapService;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imap_id' => 'sometimes|exists:imaps,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->imap_id) {
                $data = $this->repository->get($request->imap_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('IMAP fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function get_hosts()
    {
        try {
            DB::beginTransaction();

            $data = $this->service->getImapHosts();

            DB::commit();
            return sendSuccess('IMAP Hosts fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function get_emails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imap_id' => 'required|exists:imaps,id',
            'mail_box' => 'required|string',
        ]);

        if ($validator->fails()) {
            return sendError($validator->messages()->first(), null, 422);
        }

        try {
            DB::beginTransaction();

            $data = $this->repository->getMailBoxEmails($request);

            DB::commit();
            return sendSuccess('Emails fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'imap_host_name' => 'required',
            'imap_host' => 'required',
            'imap_username' => 'required|unique:imaps,imap_username',
            'imap_password' => 'required',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $imap = $this->repository->store($request);

            DB::commit();
            return sendSuccess('IMAP Connection established successfully.', $imap);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:imaps,id',
            'imap_host_name' => 'required',
            'imap_host' => 'required',
            'imap_username' => 'required',
            'imap_password' => 'required',
            'assigned_to' => 'sometimes|exists:users,id',
            'status_id' => 'required|exists:statuses,id',        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $smtp = $this->repository->update($request);

            DB::commit();
            return sendSuccess('IMAP Connection established successfully.', $smtp);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:imaps,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            Imap::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('IMAP Server deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
