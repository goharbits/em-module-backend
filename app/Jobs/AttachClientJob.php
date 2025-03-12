<?php

namespace App\Jobs;

use App\Events\AttachLeadClientEvent;
use App\Repositories\V1\User\Lead\LeadRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class AttachClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $clients_ids, $clients, $lead_id, $leadRepository;

    public function __construct($clients_ids, $clients, $lead_id)
    {
        $this->clients_ids = $clients_ids;
        $this->clients = $clients;
        $this->lead_id = $lead_id;
        $this->leadRepository = new LeadRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $clients = $this->leadRepository->getClients($this->clients_ids, $this->clients);

            $this->leadRepository->createAndAttachClients($this->lead_id, $clients);

        } catch (\Exception $e) {
            info($e->getMessage());
            $this->leadRepository->updateStatus($this->lead_id, 'failed');
            event(new AttachLeadClientEvent(['lead_id' => $this->lead_id, 'status' => 'failed']));
            Artisan::call('queue:work --once');
        }
    }
}
