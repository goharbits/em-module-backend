<?php

namespace App\Http\Controllers\V1\UserControllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Imports\ContactsImport;
use App\Models\Contact;
use App\Repositories\V1\User\Contact\ContactRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ContactController extends Controller
{
    use CommonTrait;

    private $repository;

    public function __construct()
    {
        $this->repository = new ContactRepository;
    }

    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'sometimes|exists:contacts,id',
            'per_page' => 'sometimes',
            'page' => 'sometimes',
            'search' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            if ($request->contact_id) {
                $data = $this->repository->get($request->contact_id);
            } else {
                $data = $this->repository->getAll($request);
            }

            DB::commit();
            return sendSuccess('Profile fetched successfully', $data);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function get_all_ids(Request $request)
    {
        try {
            DB::beginTransaction();

            $contacts_ids = Contact::pluck('id');

            DB::commit();
            return sendSuccess('Contacts ids fetched successfully', $contacts_ids);
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
            'email' => 'required|unique:contacts,email',
            'job_title' => 'required',
            'location' => 'required',
            'company_id' => 'required|exists:companies,id',
            // 'country_id' => 'required|exists:countries,id',
            // 'city_id' => 'required|exists:cities,id',
            'status_id' => 'required|exists:statuses,id',
            'industry' => 'required',
            'parent_industry' => 'required',
            'assigned_to' => 'sometimes|exists:users,id',
            // 'list_id' => 'nullable|exists:contact_lists,id',
            'list_id' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $contact = $this->repository->store($request);

            DB::commit();
            return sendSuccess('Profile created successfully', $contact);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:contacts,id',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required',
            'job_title' => 'required',
            'company_id' => 'required|exists:companies,id',
            'location' => 'required',
            // 'country_id' => 'required|exists:countries,id',
            // 'city_id' => 'required|exists:cities,id',
            'industry' => 'required',
            'parent_industry' => 'required',
            'status_id' => 'required|exists:statuses,id',
            'assigned_to' => 'sometimes|exists:users,id',
            // 'list_id' => 'nullable|exists:contact_lists,id',
            'list_id' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            $contact = $this->repository->update($request);

            DB::commit();
            return sendSuccess('Profile updated successfully', $contact);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:contacts,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();
            Contact::where('id', $request->id)->delete();

            DB::commit();
            return sendSuccess('Profile deleted successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
    public function import(Request $request)
    {
        Log::info($request->all());
        $validator = Validator::make($request->all(), [
            'list_id' => 'sometimes|nullable|exists:lists,id',
            'file' => 'required|file|mimes:xlsx,csv',
        ]);

        if ($validator->fails()) {
            return sendError($validator->errors()->first(), null, 422);
        }

        try {
            DB::beginTransaction();

            $file = $request->file('file');

            // $this->checkExcelSheetData($file);

            Excel::import(new ContactsImport($request->list_id), $file);

            DB::commit();
            return sendSuccess('Profiles imported successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function attach_lists(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'contact_id' => 'required|exists:contacts,id',
            'lists_ids' => 'required|array',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $this->repository->attachLists($request->toArray());

            DB::commit();
            return sendSuccess('Lists attached with contact successfully', null);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
