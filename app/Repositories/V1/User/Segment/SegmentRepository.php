<?php

namespace App\Repositories\V1\User\Segment;

use App\Events\AttachSegmentClientEvent;
use App\Http\Traits\CommonTrait;
use App\Jobs\AttachSegmentClientJob;
use App\Models\Client;
use App\Models\Segment;
use App\Repositories\V1\User\Analytics\AnalyticsRepository;
use App\Repositories\V1\User\Client\ClientRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SegmentRepository
{
    use CommonTrait;
    private $repository, $clientRepository;

    public function __construct()
    {
        $this->repository = new AnalyticsRepository;
        $this->clientRepository = new ClientRepository;
    }

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $segments = Segment::query();

        if ($request->created_by) {
            $segments->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $segments->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $segments->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $segments->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $segments->count();

        if (isset($request->page) && $request->page > 0) {
            $segments = $segments->with('clients', 'created_by_user', 'assigned_to_user', 'status')->paginate($per_page);
        } else {
            $segments = $segments->with('clients','created_by_user', 'assigned_to_user', 'status')->withCount('clients')->get();
        }

        return [
            'total' => $total,
            'segments' => $segments,
        ];
    }

    public function get($segment_id)
    {
        $segment = Segment::with('clients', 'created_by_user', 'assigned_to_user')->find($segment_id);

        return $segment;
    }

    public function store(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $segment = Segment::create([
            'name' => $request->name,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $this->getStatusId('segment', 'processing'),
        ]);

        $this->dispatchAttachClients($request->clients_ids, $request->clients, $segment->id);

        return $segment;
    }

    public function update(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $segment = Segment::find($request->id);

        if ($segment->name != $request->name && Segment::where('name', $request->name)->first()) {
            throw new Exception('Segment name has already been taken');
        }

        $segment->update([
            'name' => $request->name,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $this->getStatusId('segment', 'processing'),
        ]);

        $this->dispatchAttachClients($request->clients_ids, $request->clients, $segment->id);

        return $segment;
    }

    public function updateStatus($segment_id, $status)
    {

        $segment = Segment::find($segment_id);

        $segment->update([
            'status_id' => $this->getStatusId('segment', $status),
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


    public function createAndAttachClients($segment_id, $clients)
    {
        $clientsArr = [];

        $segment = Segment::find($segment_id);

        foreach ($clients as $key => $client) {
            $client['created_by'] = $segment->created_by;
            $clientsArr[] = $this->clientRepository->createOrUpdateClient($client);
        }

        $data['segment_id'] = $segment_id;
        $data['clients_ids'] = collect($clientsArr)->pluck('id');

        $this->attachClients($data);

        $this->updateStatus($segment_id, 'completed');

        event(new AttachSegmentClientEvent(['segment_id' => $segment->id, 'status' => 'completed']));
    }

    public function attachClients($data)
    {
        $segment = Segment::find($data['segment_id']);
        if ($segment) {
            $segment->clients()->sync($data['clients_ids']);
        } else {
            throw new Exception("Can't find the segment");
        }
    }

    public function dispatchAttachClients($clients_ids, $clients, $segment_id)
    {
        event(new AttachSegmentClientEvent(['segment_id' => $segment_id, 'status' => 'processing']));
        Artisan::call('queue:work --once');
        AttachSegmentClientJob::dispatch(json_encode($clients_ids), $clients, $segment_id);
    }
}
