<?php

namespace App\Repositories\V1\User\SmtpServer;

use Exception;
use App\Models\SmtpServer;
use Illuminate\Http\Request;
use App\Http\Traits\CommonTrait;
use App\Models\Imap;
use App\Repositories\V1\User\Imap\ImapRepository;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;

class SmtpServerRepository
{
    use CommonTrait;

    protected $imapRepository;

    public function __construct()
    {
        $this->imapRepository = new ImapRepository;
    }

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $smtp_servers = SmtpServer::query();


        if ($request->status) {
            $smtp_servers->where('status_id', $this->getStatusId('smtp', $request->status));
        }
        if ($request->created_by) {
            $smtp_servers->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $smtp_servers->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $smtp_servers->where('sender_name', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $smtp_servers->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $smtp_servers->count();

        if (isset($request->page) && $request->page > 0) {
            $smtp_servers = $smtp_servers->with('status')->paginate($per_page);
            $smtp_servers = $this->attachImaps($smtp_servers);
        } else {
            $smtp_servers = $smtp_servers->with('status')->get();
        }

        return [
            'total' => $total,
            'smtp_servers' => $smtp_servers,
        ];
    }

    public function get($smtp_server_id)
    {
        $smtp_server = SmtpServer::with('status')->find($smtp_server_id);
        $smtp_server['imap'] = Imap::where('imap_username', $smtp_server->smtp_username)->first();

        return $smtp_server;
    }

    public function store(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $data = [
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'smtp_username' => $request->smtp_username,
            'smtp_password' => $request->smtp_password,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_encryption' => $request->smtp_encryption,
            'smtp_timeout' => $request->smtp_timeout ?? 10,
            'second_limit' => $request->second_limit ?? 0,
            'minute_limit' => $request->minute_limit ?? 0,
            'hourly_limit' => $request->hourly_limit ?? 0,
            'daily_limit' => $request->daily_limit ?? 0,
            'monthly_limit' => $request->monthly_limit ?? 0,
            'delay_range_start' => $request->delay_range_start ?? 0,
            'delay_range_end' => $request->delay_range_end ?? 0,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $request->status_id,
        ];

        if ($this->verifySmtpDetails($data)) {
            if ($request->imap_host_name && $request->imap_host) {
                $imapData = [
                    'imap_username' => $request->smtp_username,
                    'imap_password' => $request->smtp_password,
                    'imap_host_name' => $request->imap_host_name,
                    'imap_host' => $request->imap_host,
                    'created_by' => Auth::id(),
                    'assigned_to' => $request->assigned_to ?? Auth::id(),
                    'status_id' => $request->status_id,
                ];

                if ($this->imapRepository->validateImap($imapData)) {
                    Imap::create($imapData);
                }
            }

            if ($request->sender_image) {
                $data['sender_image'] = addFile($request->sender_image, 'uploads/smtp/images/');
            }

            $smtp = SmtpServer::create($data);

            return $smtp;
        }
    }

    public function update(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $smtp = SmtpServer::find($request->id);

        if ($smtp->username != $request->username && SmtpServer::where('username', $request->username)->first()) {
            throw new Exception('SMTP has already been taken');
        }

        $data = [
            'sender_name' => $request->sender_name,
            'sender_email' => $request->sender_email,
            'smtp_username' => $request->smtp_username,
            'smtp_password' => $request->smtp_password,
            'smtp_host' => $request->smtp_host,
            'smtp_port' => $request->smtp_port,
            'smtp_encryption' => $request->smtp_encryption,
            'smtp_timeout' => $request->smtp_timeout ?? $smtp->smtp_timeout,
            'second_limit' => $request->second_limit ?? $smtp->second_limit,
            'minute_limit' => $request->minute_limit ?? $smtp->minute_limit,
            'hourly_limit' => $request->hourly_limit ?? $smtp->hourly_limit,
            'daily_limit' => $request->daily_limit ?? $smtp->daily_limit,
            'monthly_limit' => $request->monthly_limit ?? $smtp->monthly_limit,
            'delay_range_start' => $request->delay_range_start ?? $smtp->delay_range_start,
            'delay_range_end' => $request->delay_range_end ?? $smtp->delay_range_end,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $request->status_id,
        ];

        if ($this->verifySmtpDetails($data)) {
            if ($request->imap_id || ($request->imap_host_name && $request->imap_host)) {
                $imapData = [
                    'imap_username' => $request->smtp_username,
                    'imap_password' => $request->smtp_password,
                    'imap_host_name' => $request->imap_host_name,
                    'imap_host' => $request->imap_host,
                    'created_by' => Auth::id(),
                    'assigned_to' => $request->assigned_to ?? Auth::id(),
                    'status_id' => $request->status_id,
                ];

                if ($this->imapRepository->validateImap($imapData)) {
                    if ($request->imap_id) {
                        $imap = Imap::find($request->imap_id);
                        $imap->update($imapData);
                    } else {
                        Imap::create($imapData);
                    }

                }
            }

            if ($request->sender_image) {
                // Delete the existing image if it exists
                if ($smtp->sender_image) {
                    $existingImagePath = public_path($smtp->sender_image);
                    if (file_exists($existingImagePath)) {
                        unlink($existingImagePath);
                    }
                }

                $data['sender_image'] = addFile($request->sender_image, 'uploads/smtp/images/');
            }

            $smtp->update($data);

            return $smtp;
        }
    }

    public function verifySmtpDetails($data)
    {
        $host = $data['smtp_host'];
        $port = $data['smtp_port'];
        $username = $data['smtp_username'];
        $password = $data['smtp_password'];
        $encryption = $data['smtp_encryption']; // SSL or TLS
        $timeout = $data['smtp_timeout'];

        $mail = new PHPMailer(true); // Set true for exceptions
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPSecure = $encryption;
        $mail->Timeout = $timeout;

        // This function returns TRUE if authentication
        // was successful, or throws an exception otherwise
        $isConnect = $mail->SmtpConnect();

        return $isConnect;
    }

    private function attachImaps($smtps)
    {
        foreach ($smtps as $smtp) {
            $smtp->imap = Imap::where('imap_username', $smtp->smtp_username)->first();
        }

        return $smtps;
    }
}
