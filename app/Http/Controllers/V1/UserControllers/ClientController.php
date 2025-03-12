<?php

namespace App\Http\Controllers\V1\UserControllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\ClientsImport;
use App\Models\Client;
use App\Repositories\V1\User\Client\ClientRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClientController extends Controller
{
    private $repository;

    public function __construct()
    {
        $this->repository = new ClientRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'sometimes|exists:clients,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->client_id) {
                $data = $this->repository->get($request->client_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Client fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function get_all_ids(Request $request)
    {
        try {
            DB::beginTransaction();

            $clients_ids = Client::pluck('id');

            DB::commit();
            return sendSuccess('Clients ids fetched successfully', $clients_ids);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:clients,email',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $client = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Client created successfully', $client);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:clients,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $client = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Client updated successfully', $client);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:clients,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            Client::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Client deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'sometimes|nullable|exists:leads,id',
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();

            Excel::import(new ClientsImport($request->lead_id), $request->file('file'));

            DB::commit();
            return sendSuccess('Clients imported successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function attach_leads(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'leads_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $this->repository->attachLeads($request->toArray());

            DB::commit();
            return sendSuccess('Leads attached with client successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
