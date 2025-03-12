<?php

namespace App\Services\V1;

use App\Jobs\SendEmailJob;
use App\Models\QueuedJob;
use PHPMailer\PHPMailer\PHPMailer;
use Illuminate\Support\Facades\RateLimiter;

class CampaignEmailService
{
    public function configureUserMailer($smtp, $is_plain_text)
    {
        // Create an instance of PHPMailer
        $mail = new PHPMailer(true); // Set true for exceptions
        // Set PHPMailer to use Guzzle HTTP Client for requests
        $mail->isSMTP();

        $mail->isHTML($is_plain_text);

        $mail->Host = $smtp->smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp->smtp_username;
        $mail->Password = $smtp->smtp_password;
        $mail->SMTPSecure = $smtp->smtp_encryption;
        $mail->Port = $smtp->smtp_port;

        // Disable X-Mailer header
        $mail->XMailer = ''; // Set XMailer property to an empty string

        // Set email details
        $mail->setFrom($smtp->sender_email, $smtp->sender_name);

        // Set proxy configuration in Guzzle request options
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
            'http' => [
                'proxy' => getProxyIpUri(),
                'request_fulluri' => true
            ]
        ];

        return $mail;
    }

    // public function configureUserMailer($smtp)
    // {
    //     // Set dynamic mail configuration
    //     config([
    //         'mail.mailers.smtp.host' => $smtp->smtp_host,
    //         'mail.mailers.smtp.port' => $smtp->smtp_port,
    //         'mail.mailers.smtp.username' => $smtp->smtp_username,
    //         'mail.mailers.smtp.password' => $smtp->smtp_password,
    //         'mail.mailers.smtp.encryption' => $smtp->smtp_encryption,
    //         'mail.from.address' => $smtp->sender_email,
    //         'mail.from.name' => $smtp->sender_name,
    //     ]);
    // }


    // public function dispatchSendEmailJob($campaign, $client)
    // {
    //     if ($this->shouldDelayEmailSending()) {
    //         $this->delayedDispatchSendEmailJob($campaign, $client);
    //     } else {
    //         SendEmailJob::dispatch($campaign, $client);
    //     }
    // }

    public function shouldDelayEmailSending()
    {
        return RateLimiter::tooManyAttempts('send-email', 1);
    }

    public function delayedDispatchSendEmailJob($campaign, $client, $smtp = null, $email)
    {
        $uniqueJobKey = $this->generateUniqueJobKey($campaign, $client, $smtp);

        // Check if a similar job is already in the queue or has been processed recently
        if (!$this->isJobAlreadyQueued($uniqueJobKey)) {
            // If not, dispatch the job
            if ($smtp->delay_range_start > $smtp->delay_range_end) {
                $delayInSeconds = $smtp->delay_range_start; // Fallback to delay_range_start if data is inconsistent
            } else {
                $delayInSeconds = rand($smtp->delay_range_start, $smtp->delay_range_end);
            }
            $delayTime  = now()->addSeconds($delayInSeconds); // Random number of seconds between 2 and 5
            SendEmailJob::dispatch($campaign, $client, $smtp, $email)->delay($delayTime);
            // SendEmailJob::dispatch($campaign, $client, $smtp);
            // Mark the job as queued to prevent duplicates
            $this->markJobAsQueued($uniqueJobKey);
        }
    }

    public function generateUniqueJobKey($campaign, $client, $smtp)
    {
        // Generate a unique key based on campaign and client information
        return "send-email:campaign-{$campaign->id}:lead-{$campaign->lead_id}:segment-{$campaign->segment_id}:client-{$client->id}:smtp-{$smtp->id}";
    }

    public function isJobAlreadyQueued($uniqueJobKey)
    {
        // Check if the unique key exists in the database
        return QueuedJob::where('key', $uniqueJobKey)->exists();
    }

    public function markJobAsQueued($uniqueJobKey)
    {
        // Insert the unique key into the database
        QueuedJob::create(['key' => $uniqueJobKey]);
    }

    public function generateUniqueEmailToken($email)
    {
        $uniqueEmailToken = generateRandomString() . uniqid() . $email->id . uniqid() . generateRandomString() . $email->campaign_id . uniqid() . generateRandomString() . $email->client_id . uniqid() . generateRandomString() . uniqid();

        return $uniqueEmailToken;
    }
}
