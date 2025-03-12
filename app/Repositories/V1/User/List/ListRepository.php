<?php

namespace App\Repositories\V1\User\List;

use App\Events\AttachContactEvent;
use App\Events\ContactImportStatsEvent;
use App\Http\Traits\CommonTrait;
use App\Jobs\AttachContactJob;
use App\Models\Company;
use App\Models\Contact;
use App\Models\ContactList;
use App\Models\Status;
use App\Repositories\V1\User\Contact\ContactRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class ListRepository
{
    use CommonTrait;
    private $contactRepository;

    public function __construct()
    {
        $this->contactRepository = new ContactRepository;
    }

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $lists = ContactList::query();

        if ($request->created_by) {
            $lists->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $lists->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $lists->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $lists->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $lists->count();
        if (isset($request->page) && $request->page > 0) {
            $lists = $lists->with('created_by_user', 'assigned_to_user', 'status')->paginate($per_page);
        } else {
            $lists = $lists->with('created_by_user', 'assigned_to_user', 'status')->withCount('contacts')->get();
        }

        return [
            'total' => $total,
            'lists' => $lists,
        ];
    }

    public function get($list_id)
    {
        $list = ContactList::with([
            'created_by_user:id,name',
            'assigned_to_user:id,name',
            'status:id,name,slug'
        ])
            ->find($list_id);


        return $list;
    }

    public function getListContacts(Request $request)
    {
        $perPage = $request->per_page ?? 10;

        $contacts = Contact::with('lists')->whereHas('lists', function ($query) use ($request) {
            $query->where('list_id', $request->list_id);
        })->paginate($perPage);

        return $contacts;
    }

    public function store(Request $request)
    {
        // $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $isContacts = $request->contacts || $request->contacts_ids;

        $list = ContactList::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $this->getStatusId('list', $isContacts ? 'processing' : 'completed'),
        ]);

        $isContacts && $this->dispatchAttachContacts($list, $request->contacts_ids, $request->contacts);

        return $list;
    }

    public function update(Request $request)
    {
        // $this->errorIfAdminAndNotAssignee($request->assigned_to);
        $isContacts = $request->contacts || $request->contacts_ids;

        $list = ContactList::find($request->id);

        if ($list->name != $request->name && ContactList::where('name', $request->name)->first()) {
            throw new Exception('List name has already been taken');
        }

        $list->update([
            'name' => $request->name,
            'status_id' => $this->getStatusId('list', $isContacts ? 'processing' : 'completed'),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
        ]);

        $isContacts && $this->dispatchAttachContacts($list, $request->contacts_ids, $request->contacts);

        return $list;
    }

    public function updateStatus($list_id, $status)
    {

        $list = ContactList::find($list_id);

        $list->update([
            'status_id' => $this->getStatusId('list', $status),
        ]);
    }

    public function dispatchAttachContacts($list, $contacts_ids, $contacts)
    {
        event(new AttachContactEvent(['list_id' => $list->id, 'status' => 'processing']));
        Artisan::call('queue:work --once');
        AttachContactJob::dispatch(json_encode($contacts_ids), $contacts, $list->id);
    }

    public function getContacts($contactsIds, $contactsFromRequest)
    {
        $contacts = [];

        $contactsIds = json_decode($contactsIds, true);

        // Set your desired chunk size
        $chunkSize = 10000;

        if ($contactsIds) {
            // Split the client IDs array into chunks
            $contactsIdsChunks = array_chunk($contactsIds, $chunkSize);

            foreach ($contactsIdsChunks as $idsChunk) {
                $contactsFromIds = Contact::whereIn('id', $idsChunk)
                    ->get(['id', 'first_name', 'last_name', 'email', 'job_title', 'company_id', 'industry', 'location', 'parent_industry', 'created_by', 'assigned_to'])
                    ->toArray();

                // Merge the contacts from the current chunk into the existing $contacts array
                $contacts = array_merge($contacts, $contactsFromIds);
            }
        }

        // Merge 'contactsFromRequest' data into the existing $contacts array
        if ($contactsFromRequest) {

            foreach ($contactsFromRequest as &$contact) {
                $this->replaceNameWithId(Company::class, 'company', $contact);
                // $country_id = $this->replaceNameWithId(Country::class, 'country', $contact);
                // $this->replaceNameWithId(City::class, 'city', $contact, $country_id);
            }

            $contacts = array_merge($contacts, $contactsFromRequest);
        }

        return $contacts;
    }

    function replaceNameWithId($modelName, $key, &$contact, $country_id = null)
    {
        if (isset($contact[$key])) {
            if ($key == 'company') {
                $model = $modelName::where('name', $contact[$key])->first();
                if (!$model) {
                    $model = $modelName::create([
                        'name' => $contact[$key],
                        'status_id' => $this->getStatusId('company', 'active'),
                    ]);
                }
            } else if ($key == 'city') {
                $model = $modelName::where('name', $contact[$key])->first();
                if (!$model) {
                    $model = $modelName::create([
                        'name' => $contact[$key],
                        'country_id' => $country_id,
                    ]);
                }
            } else {
                $model = $modelName::firstOrCreate(['name' => $contact[$key]]);
                $country_id = $model->id;
            }

            $contact["{$key}_id"] = $model->id;
            unset($contact[$key]);

            return $country_id;
        }
    }

    public function createAndAttachContacts($list_id, $contacts)
    {
        $contactsArr = [];

        $list = ContactList::find($list_id);

        $duplicate = 0;
        $duplicateProfiles = [];
        $create = 0;
        $update = 0;

        foreach ($contacts as $key => $contact) {
            $existList = Contact::where('email', $contact['email'])->whereHas('lists', function ($query) use ($list) {
                $query->where('list_id', '!=', $list->id);
            })->first();

            $exist = Contact::where('email', $contact['email'])->whereHas('lists', function ($query) use ($list) {
                $query->where('list_id', $list->id);
            })->first();

            if ($existList) {
                $duplicate++;
                $duplicateProfiles[] = $existList;
            } else if ($exist) {
                $update++;
            }

            $contact['created_by'] = $list->created_by;
            $contactsArr[] = $this->contactRepository->createOrUpdateContact($contact);
            if (!$exist) {
                $create++;
            }

            $statsData = [
                'create' => $create,
                'update' => $update,
                'duplicate' => $duplicate,
                'duplicateProfiles' => $duplicateProfiles,
            ];

            event(new ContactImportStatsEvent(['list_id' => $list->id, 'data' => $statsData]));
        }

        $data['list_id'] = $list_id;
        $data['contacts_ids'] = collect($contactsArr)->pluck('id');

        $this->attachContacts($data);

        $this->updateStatus($list_id, 'completed');

        event(new AttachContactEvent(['list_id' => $list->id, 'status' => 'completed']));
    }

    public function attachContacts($data)
    {
        $list = ContactList::find($data['list_id']);
        if ($list) {
            $list->contacts()->sync($data['contacts_ids']);
        } else {
            throw new Exception("Can't find the list");
        }
    }
}
