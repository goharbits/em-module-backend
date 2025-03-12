<?php

namespace App\Repositories\V1\User\Imap;

use Exception;
use App\Models\Imap;
use Illuminate\Http\Request;
use App\Http\Traits\CommonTrait;
use App\Services\V1\ImapService;
use Illuminate\Support\Facades\Auth;

class ImapRepository
{
    use CommonTrait;

    private $imapService;

    public function __construct()
    {
        $this->imapService = new ImapService();
    }

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $imaps = Imap::query();

        if ($request->created_by) {
            $imaps->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $imaps->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $imaps->where('imap_username', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $imaps->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $imaps->count();
        if (isset($request->page) && $request->page > 0) {
            $imaps = $imaps->with('status')->paginate($per_page);
        } else {
            $imaps = $imaps->with('status')->get();
        }

        return [
            'total' => $total,
            'imaps' => $imaps,
        ];
    }

    public function get($imap_id)
    {
        $imap = Imap::with('status')->find($imap_id);

        $this->imapService->authenticate($imap);

        $data['imap'] = $imap;
        $data['mail_boxes'] = $this->imapService->getMailBoxes();

        return $data;
    }

    public function getMailBoxEmails($request)
    {
        $imap = Imap::find($request->imap_id);

        $this->imapService->authenticate($imap);

        $emails = $this->imapService->getMailBoxEmails();

        return $emails;
    }

    public function store(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $data = [
            'imap_host_name' => $request->imap_host_name,
            'imap_host' => $request->imap_host,
            'imap_username' => $request->imap_username,
            'imap_password' => $request->imap_password,
            'created_by' => Auth::id(),
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $request->status_id,
        ];

        $this->validateImap($data);

        $imap = Imap::create($data);

        return $imap;
    }

    public function update(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $imap = Imap::find($request->id);

        if ($imap->name != $request->name && Imap::where('imap_username', $request->imap_username)->first()) {
            throw new Exception('Imap has already been taken');
        }

        $data = [
            'imap_host_name' => $request->imap_host_name,
            'imap_host' => $request->imap_host,
            'imap_username' => $request->imap_username,
            'imap_password' => $request->imap_password,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $request->status_id,
        ];

        $this->validateImap($data);

        $imap->update($data);


        return $imap;
    }

    public function validateImap($data)
    {
        $imap = imap_open($data['imap_host'], $data['imap_username'], $data['imap_password']);

        // Check if the connection was successful or not
        if ($imap) {
            // Close the IMAP connection
            imap_close($imap);
            return true;
        } else {
            throw new Exception("Invalid IMAP credentials. Connection failed.");
        }
    }
}
