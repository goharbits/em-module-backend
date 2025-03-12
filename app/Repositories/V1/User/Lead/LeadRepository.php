<?php

namespace App\Repositories\V1\User\Lead;

use App\Events\AttachLeadClientEvent;
use App\Http\Traits\CommonTrait;
use App\Jobs\AttachClientJob;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Status;
use App\Repositories\V1\User\Client\ClientRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class LeadRepository
{
    use CommonTrait;
    private $clientRepository;

    public function __construct()
    {
        $this->clientRepository = new ClientRepository;
    }
    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $leads = Lead::query();

        if ($request->created_by) {
            $leads->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $leads->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $leads->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $leads->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $leads->count();
        if (isset($request->page) && $request->page > 0) {
            $leads = $leads->with('created_by_user', 'assigned_to_user', 'status')->paginate($per_page);
        } else {
            $leads = $leads->with('created_by_user', 'assigned_to_user', 'status')->withCount('clients')->get();
        }

        return [
            'total' => $total,
            'leads' => $leads,
        ];
    }

    public function get($lead_id)
    {
        $lead = Lead::with([
            'clients' => function ($query) {
                $query->select('clients.id', 'clients.first_name','clients.last_name', 'clients.email');
            },
            'created_by_user:id,name',
            'assigned_to_user:id,name',
            'status:id,name,slug',
        ])
            ->select('leads.id', 'leads.name', 'leads.assigned_to', 'leads.created_by')
            ->find($lead_id);


        return $lead;
    }
    public function store(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $lead = Lead::create([
            'name' => $request->name,
            'status_id' => $this->getStatusId('lead', 'processing'),
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
        ]);

        $this->dispatchAttachClients($request->clients_ids, $request->clients, $lead->id);

        return $lead;
    }

    public function update(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $lead = Lead::find($request->id);

        if ($lead->name != $request->name && Lead::where('name', $request->name)->first()) {
            throw new Exception('Lead name has already been taken');
        }

        $lead->update([
            'name' => $request->name,
            'status_id' => $this->getStatusId('lead', 'processing'),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
        ]);

        $this->dispatchAttachClients($request->clients_ids, $request->clients, $lead->id);

        return $lead;
    }

    public function updateStatus($lead_id, $status)
    {
        $lead = Lead::find($lead_id);

        $lead->update([
            'status_id' => $this->getStatusId('lead', $status),
        ]);
    }

    public function getClients($clientIds, $clientsFromRequest)
    {
        $clients = [];

        $clientIds = json_decode($clientIds, true);

        // Set your desired chunk size
        $chunkSize = 10000;

        if ($clientIds) {
            // Split the client IDs array into chunks
            $clientIdsChunks = array_chunk($clientIds, $chunkSize);

            foreach ($clientIdsChunks as $idsChunk) {
                $clientsFromIds = Client::whereIn('id', $idsChunk)
                    ->get(['id', 'first_name', 'last_name', 'email'])
                    ->toArray();

                // Merge the clients from the current chunk into the existing $clients array
                $clients = array_merge($clients, $clientsFromIds);
            }
        }

        // Merge 'clientsFromRequest' data into the existing $clients array
        if ($clientsFromRequest) {
            $clients = array_merge($clients, $clientsFromRequest);
        }

        return $clients;
    }


    public function createAndAttachClients($lead_id, $clients)
    {
        $clientsArr = [];

        $lead = Lead::find($lead_id);
        
        foreach ($clients as $key => $client) {
            $client['created_by'] = $client->created_by ?? $lead->created_by;
            $clientsArr[] = $this->clientRepository->createOrUpdateClient($client);
        }

        $data['lead_id'] = $lead_id;
        $data['clients_ids'] = collect($clientsArr)->pluck('id');
        
        $this->attachClients($data);

        $this->updateStatus($lead_id, 'completed');

        event(new AttachLeadClientEvent(['lead_id' => $lead->id, 'status' => 'completed']));
    }

    public function attachClients($data)
    {
        $lead = Lead::find($data['lead_id']);
        if ($lead) {
            $lead->clients()->sync($data['clients_ids']);
        } else {
            throw new Exception("Can't find the lead");
        }
    }

    public function dispatchAttachClients($clients_ids, $clients, $lead_id)
    {
        event(new AttachLeadClientEvent(['lead_id' => $lead_id, 'status' => 'processing']));
        Artisan::call('queue:work --once');
        AttachClientJob::dispatch(json_encode($clients_ids), $clients, $lead_id);
    }
}
