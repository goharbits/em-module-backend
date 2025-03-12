<?php

namespace App\Jobs;

use App\Models\ContactList;
use Illuminate\Bus\Queueable;
use App\Events\AttachContactEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\V1\User\List\ListRepository;
use Illuminate\Support\Facades\Artisan;

class AttachContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $contacts_ids, $contacts, $list_id, $listRepository;

    public function __construct($contacts_ids, $contacts, $list_id)
    {
        $this->contacts_ids = $contacts_ids;
        $this->contacts = $contacts;
        $this->list_id = $list_id;
        $this->listRepository = new ListRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $contacts = $this->listRepository->getContacts($this->contacts_ids, $this->contacts);

            $this->listRepository->createAndAttachContacts($this->list_id, $contacts);

        } catch (\Exception $e) {
            info($e->getMessage());
            $this->listRepository->updateStatus($this->list_id, 'failed');
            event(new AttachContactEvent(['list_id' => $this->list_id, 'status' => 'failed']));
            Artisan::call('queue:work --once');
        }
    }
}
