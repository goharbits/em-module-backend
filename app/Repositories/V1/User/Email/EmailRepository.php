<?php

namespace App\Repositories\V1\User\Email;

use App\Models\Email;
use App\Http\Traits\CommonTrait;
use App\Mail\CampaignMail;
use App\Services\V1\CampaignEmailService;
use Illuminate\Support\Facades\Mail;

class EmailRepository
{
    use CommonTrait;
    private $emailService;

    public function __construct()
    {
        $this->emailService = new CampaignEmailService;
    }

    public function send($campaign, $client, $smtp, $email)
    {
        $email_template = $campaign->email_template;

        $subject = $email_template->subject;
        $body = $email_template->body;
        $is_plain_text = $email_template->is_plain_text;

        if (!$is_plain_text) {
            // Customize email body with tracking token
            $body = $this->updateBodyWithTracking($body, $email->token);
        }

        // Customize email body with placeholders
        $body = $this->updateBodyWithPlaceholders($body, $client);

        // Update Email
        $email->is_send = true;
        $email->save();

        try {
            // Configure mailer dynamically
            $mail = $this->emailService->configureUserMailer($smtp, $is_plain_text);

            $mail->addAddress($client->email);
            $mail->Subject = $subject;
            $mail->Body = $body;

            // Send email
            $mail->send();

        } catch (\Throwable $th) {
            info($th->getMessage());
        }

        // Send the email
        // Mail::to($client->email)->send(new CampaignMail($subject, $body));
    }

    private function updateBodyWithPlaceholders($body, $client)
    {
        // Define the placeholders and their corresponding client attributes
        $placeholders = [
            '{{first_name}}' => $client->first_name,
            '{{last_name}}' => $client->last_name,
            '{{email}}' => $client->email,
            '{{job_title}}' => $client->job_title,
            '{{industry}}' => $client->industry,
            '{{parent_industry}}' => $client->parent_industry,
            '{{company}}' => $client->company->name ?? '',
        ];

        // Replace all placeholders with actual client data in one pass
        return str_replace(array_keys($placeholders), array_values($placeholders), $body);
    }


    private function updateBodyWithTracking($htmlBody, $token)
    {
        // Create a tracking URL for email opens
        $routeOpen = route('track.email.open', ['token' => $token]);
        $trackOpen = "<img src='$routeOpen' width='1px' height='1px'>";

        // Create a new DOMDocument
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);

        // Wrap the HTML fragment in a complete HTML structure
        $htmlBody = "<html><head></head><body>$htmlBody</body></html>";
        // Load the HTML content into the DOMDocument
        $dom->loadHTML($htmlBody, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Create a DOMXPath object
        $xpath = new \DOMXPath($dom);

        // Get all anchor tags
        $anchorNodes = $xpath->query('//a[@href]');

        // Iterate over each anchor tag
        foreach ($anchorNodes as $anchorNode) {
            // Check if the anchor tag has an href attribute
            if ($anchorNode->hasAttribute('href')) {
                // Get the original href attribute
                $originalHref = $anchorNode->getAttribute('href');

                // Create a tracking URL
                $url = urlencode($originalHref);
                $routeLink = route('track.email.click', ['token' => $token, 'url' => $url]);

                // Set the new href attribute
                $anchorNode->setAttribute('data-original-href', $originalHref); // Save original URL
                $anchorNode->setAttribute('href', $routeLink); // Update href attribute
            }
        }

        // Save the modified HTML content
        $modifiedHtml = $dom->saveHTML();

        // Clean up
        libxml_clear_errors();

        return $trackOpen . $modifiedHtml;
    }


    public function store($campaign, $client)
    {
        $email = Email::create([
            'campaign_id' => $campaign->id,
            'client_id' => $campaign->lead_id || $campaign->segment_id ? $client->id : null,
            'contact_id' => $campaign->list_id ? $client->id : null,
            'email_template_id' => $campaign->email_template_id,
            'created_by' => $campaign->created_by,
            'assigned_to' => $campaign->assigned_to,
        ]);

        $email->token = $this->emailService->generateUniqueEmailToken($email);

        $email->update();

        return $email;
    }
}
