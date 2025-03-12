<?php

namespace App\Jobs;

use App\Repositories\V1\User\Campaign\CampaignRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $campaign, $campaignRepository;

    public function __construct($campaign)
    {
        $this->campaign = $campaign;
        $this->campaignRepository = new CampaignRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->campaignRepository->sendCampaign($this->campaign);
    }
}
