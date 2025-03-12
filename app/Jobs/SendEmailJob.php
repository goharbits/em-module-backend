<?php

namespace App\Jobs;

use App\Repositories\V1\User\Email\EmailRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $campaign, $client, $emailRepository, $smtp, $email;

    public function __construct($campaign, $client, $smtp, $email)
    {
        $this->campaign = $campaign;
        $this->client = $client;
        $this->smtp = $smtp;
        $this->email = $email;
        $this->emailRepository = new EmailRepository;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Your logic for sending a single email
        $this->emailRepository->send($this->campaign, $this->client, $this->smtp, $this->email);
    }
}
