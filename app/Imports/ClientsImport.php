<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Lead;
use App\Repositories\V1\User\Client\ClientRepository;
use App\Repositories\V1\User\Lead\LeadRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientsImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
     * @param Collection $collection
     */
    private $clientRepository, $leadRepository, $lead_id, $created_by;

    public function __construct($lead_id)
    {
        $this->clientRepository = new ClientRepository;
        $this->leadRepository = new LeadRepository;
        $this->lead_id = $lead_id;
        $this->created_by = Auth::id();
    }

    public function collection(Collection $clients)
    {
        $clientsArr = [];
        $data['lead_id'] = $this->lead_id ?? null;

        foreach ($clients as $key => $client) {
            $isClientExist = Client::where('email', $client['email'])->first();
            if (!$isClientExist) {
                $client['created_by'] = $this->created_by;
                $clientsArr[] = $this->clientRepository->createOrUpdateClient($client);
            } else {
                $clientsArr[] = $isClientExist;
            }
        }

        if (sizeof($clientsArr) > 0 && isset($data['lead_id'])) {
            $data['clients_ids'] = collect($clientsArr)->pluck('id');
            $this->leadRepository->attachClients($data);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
