<?php

namespace App\Repositories\V1\User\EmailTemplate;

use App\Http\Traits\CommonTrait;
use App\Models\EmailTemplate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailTemplateRepository
{
    use CommonTrait;

    public function getAll(Request $request)
    {
        $per_page = $request->per_page ?? 20;

        $templates = EmailTemplate::query();

        if ($request->created_by) {
            $templates->where('created_by', $request->created_by);
        }
        if ($request->assigned_to) {
            $templates->where('assigned_to', $request->assigned_to);
        }
        if ($request->search) {
            $templates->where('name', 'LIKE', '%' . $request->search . '%');
        }
        if (isUser()) {
            $templates->where('created_by', Auth::id())->orWhere('assigned_to', Auth::id());
        }

        $total = $templates->count();
        if (isset($request->page) && $request->page > 0) {
            $templates = $templates->with('email_template_type', 'status')->paginate($per_page);
        } else {
            $templates = $templates->with('email_template_type', 'status')->get();
        }

        return [
            'total' => $total,
            'email_templates' => $templates,
        ];
    }

    public function get($template_id)
    {
        $template = EmailTemplate::with('email_template_type', 'status')->find($template_id);

        return $template;
    }

    public function store(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $template = EmailTemplate::create([
            'name' => $request->name,
            'template_type_id' => $request->template_type_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'created_by' => Auth::id(),
            'status_id' => $request->status_id,
            'is_plain_text' => $request->is_plain_text

        ]);

        return $template;
    }

    public function update(Request $request)
    {
        $this->errorIfAdminAndNotAssignee($request->assigned_to);

        $template = EmailTemplate::find($request->id);

        if ($template->name != $request->name && EmailTemplate::where('name', $request->name)->first()) {
            throw new Exception('Template has already been taken');
        }

        $template->update([
            'name' => $request->name,
            'template_type_id' => $request->template_type_id,
            'subject' => $request->subject,
            'body' => $request->body,
            'assigned_to' => $request->assigned_to ?? Auth::id(),
            'status_id' => $request->status_id,
            'is_plain_text' => $request->is_plain_text
        ]);

        return $template;
    }
}
