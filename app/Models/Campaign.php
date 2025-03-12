<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function smtp_servers()
    {
        return $this->belongsToMany(SmtpServer::class, 'campaign_smtp_servers', 'campaign_id', 'smtp_server_id')->withPivot('smtp_limit');
    }
    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
    public function segment()
    {
        return $this->belongsTo(Segment::class, 'segment_id');
    }
    public function list()
    {
        return $this->belongsTo(ContactList::class, 'list_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function email_template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
    public function emails()
    {
        return $this->hasMany(Email::class, 'campaign_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeByCreatedBy($query, $createdBy)
    {
        return $query->where('created_by', $createdBy);
    }

    public function scopeByAssignedTo($query, $assignedTo)
    {
        return $query->where('assigned_to', $assignedTo);
    }

    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('start_at', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('send_on_timezone', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('daily_limit', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('is_prevent_duplicate', 'LIKE', '%' . $searchTerm . '%')
                ->orWhereHas('email_template', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('lead', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('segment', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('assigned_to_user', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('created_by_user', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('status', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('list', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                })
                ->orWhereHas('smtp_servers', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', '%' . $searchTerm . '%');
                });
        });
    }


    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId)->orWhere('assigned_to', $userId);
    }
}
