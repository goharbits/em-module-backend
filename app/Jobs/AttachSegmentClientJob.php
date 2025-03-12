<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use App\Events\AttachSegmentClientEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\V1\User\Segment\SegmentRepository;

class AttachSegmentClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $clients_ids, $clients, $segment_id, $segmentRepository;

    public function __construct($clients_ids, $clients, $segment_id)
    {
        $this->clients_ids = $clients_ids;
        $this->clients = $clients;
        $this->segment_id = $segment_id;
        $this->segmentRepository = new SegmentRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $clients = $this->segmentRepository->getClients($this->clients_ids, $this->clients);

            $this->segmentRepository->createAndAttachClients($this->segment_id, $clients);
        } catch (\Exception $e) {
            info($e->getMessage());
            $this->segmentRepository->updateStatus($this->segment_id, 'failed');
            event(new AttachSegmentClientEvent(['segment_id' => $this->segment_id, 'status' => 'failed']));
            Artisan::call('queue:work --once');
        }
    }
}
