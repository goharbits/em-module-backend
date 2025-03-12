<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\Contact;
use App\Repositories\V1\User\Contact\ContactRepository;
use App\Repositories\V1\User\List\ListRepository;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class ContactsImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
     * @param Collection $collection
     */
    private $contactRepository, $listRepository, $list_id, $created_by;

    public function __construct($list_id)
    {
        $this->contactRepository = new ContactRepository;
        $this->listRepository = new ListRepository;
        $this->list_id = $list_id;
        $this->created_by = Auth::id();
    }

    public function collection(Collection $contacts)
    {
        try {
            $contactsArr = [];
            $data['list_id'] = $this->list_id ?? null;
            // Filter out rows that are completely null
            $contacts = $contacts->filter(function ($contact) {
                return !collect($contact)->every(function ($value) {
                    return $value === null;
                });
            });
            foreach ($contacts as $key => $contact) {
                // $existList = Contact::where('email', $contact['email'])->whereHas('lists', function ($query) use ($data) {
                //     $query->where('list_id', '!=', $data['list_id']);
                // })->first();

                // if (!$existList) {
                    $contact['created_by'] = $this->created_by;

                    $this->listRepository->replaceNameWithId(Company::class, 'company', $contact);
                    // $country_id = $this->listRepository->replaceNameWithId(Country::class, 'country', $contact);
                    // $this->listRepository->replaceNameWithId(City::class, 'city', $contact, $country_id);

                    $contactsArr[] = $this->contactRepository->createOrUpdateContact($contact);
                // }
            }

            if (sizeof($contactsArr) > 0 && isset($data['list_id'])) {
                $data['contacts_ids'] = collect($contactsArr)->pluck('id');
                $this->listRepository->attachContacts($data);
            }
        } catch (\Exception $e) {
            info($e->getMessage());
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
