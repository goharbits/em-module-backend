<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_client');
    }

    public function segments()
    {
        return $this->belongsToMany(Segment::class, 'segment_client');
    }

    public function emails()
    {
        return  $this->hasMany(Email::class, 'client_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
