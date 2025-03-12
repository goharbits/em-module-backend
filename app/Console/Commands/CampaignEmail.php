<?php

namespace App\Console\Commands;

use App\Http\Traits\CommonTrait;
use Carbon\Carbon;
use App\Models\Campaign;
use App\Jobs\SendCampaignJob;
use Illuminate\Console\Command;

class CampaignEmail extends Command
{
    use CommonTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send the campaigns emails to selected lead clients';

    /**
     * Execute the console command.
     */


    public function handle()
    {
        //Fetch campaigns whose status is not completed and start date is not future
        $campaigns = Campaign::where('is_draft', false)->whereNot('status_id', $this->getStatusId('campaign', 'completed'))->get();

        foreach ($campaigns as $campaign) {
            $timezone = new \DateTimeZone($campaign->send_on_timezone);
            $campaignStartTime = Carbon::parse($campaign->start_at, $timezone);
            $currentTime = Carbon::now($timezone);

            if ($campaignStartTime <= $currentTime) {
                SendCampaignJob::dispatch($campaign);
            }
        }
    }
}
