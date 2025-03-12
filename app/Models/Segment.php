<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'segment_client');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'segment_id');
    }

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
