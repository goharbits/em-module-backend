<?php

namespace App\Repositories\V1\User\Contact;

use App\Http\Traits\CommonTrait;
use App\Models\Contact;
use App\Models\Status;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $contacts = Contact::query();

        // Apply filters
        if ($request->created_by) {
            $contacts->byCreatedBy($request->created_by);
        }
        if ($request->assigned_to) {
            $contacts->byAssignedTo($request->assigned_to);
        }
        if ($request->search) {
            $searchTerm = '%' . $request->search . '%';
            $contacts->search($searchTerm);
        }
        if (isUser()) {
            $contacts->byUser(Auth::id());
        }

        $total = $contacts->count();

        if (isset($request->page) && $request->page > 0) {
            $contacts = $contacts->with('status', 'created_by_user', 'assigned_to_user', 'company','lists')->paginate($per_page);
        } else {
            $contacts = $contacts->with('status', 'created_by_user', 'assigned_to_user', 'company','lists')->get();
        }

        return [
            'total' => $total,
            'contacts' => $contacts,
        ];
    }

    public function get($contact_id)
    {
        $contact = Contact::with('status', 'created_by_user', 'assigned_to_user', 'company','lists')->find($contact_id);

        return $contact;
    }

    public function store(Request $request)
    {
        // $this->errorIfAdminAndNotAssignee($request->assigned_to);
        // $this->contactExistance($request);

        $contact = $this->createOrUpdateContact($request->toArray());

        return $contact;
    }

    public function update(Request $request)
    {
        // $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $contact = Contact::find($request->id);

        // $this->contactExistance($request, $contact);

        if ($contact->email != $request->email && Contact::where('email', $request->email)->first()) {
            throw new Exception('Profile email has already been taken');
        }

        $contact = $this->createOrUpdateContact($request->toArray());

        return $contact;
    }

    private function contactExistance($request, $contact = null)
    {
        $existList = Contact::where('email', $request->email)->whereHas('lists', function ($query) use ($request, $contact) {
            $query->where('list_id', $request->list_id);
            $query->where('list_id', '!=', $request->list_id);
            if ($contact != null) {
                $query->where('list_id', '!=', $contact->lists()->first()->id);
            }

        })->first();

        if ($existList) {
            throw new Exception('Profile is already exist in list.');
        }
    }

    public function createOrUpdateContact($data)
    {
        try {
            $createdBy = $data['created_by'] ?? Auth::id();

            $contactData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'job_title' => $data['title'] ?? $data['job_title'],
                'company_id' => $data['company_id'],
                'location' => $data['location'],
                'industry' => $data['industry'],
                'parent_industry' => $data['parent_industry'],
                'assigned_to' => $data['assigned_to'] ?? $createdBy,
            ];

            if (isset($data['status_id'])){
                $contactData['status_id'] = $data['status_id'];
            } elseif (isset($data['lead_status']) && $this->getStatusByName('contact', $data['lead_status'])){
                $contactData['status_id'] = $this->getStatusId('contact', $data['lead_status']);

            } elseif (isset($data['lead_status']) && !$this->getStatusByName('contact', $data['lead_status'])) {
                $status = Status::create([
                    'name' => $data['lead_status'],
                    'slug' => createSlug($data['lead_status']),
                    'module' => 'contact',
                ]);

                $contactData['status_id'] = $status->id;
            } else {
                $contactData['status_id'] = $this->getStatusId('contact', 'new');
            }

            if (isset($data['id'])) {
                // Update existing contact
                $contact = Contact::find($data['id']);
                $contactData['status_id'] = $contactData['status_id'] ?? $contact->status_id;
                $contact->update($contactData);
            } elseif ($contact = Contact::where('email', $data['email'])->first()) {
                // Update existing contact
                $contactData['status_id'] = $contactData['status_id'] ?? $contact->status_id;
                $contact->update($contactData);
            } else {
                // Create new contact
                $contactData['created_by'] = $createdBy;

                $contact = Contact::create($contactData);
            }

            if (isset($data['list_id'])) {
                $this->attachLists(['contact_id' => $contact->id, 'list_id' => $data['list_id']]);
            }

            return $contact;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function attachLists($data)
    {
        $contact = Contact::find($data['contact_id']);
        if ($contact) {
            $contact->lists()->sync($data['list_id']);
        } else {
            throw new Exception("Can't find the profile");
        }
    }
}
