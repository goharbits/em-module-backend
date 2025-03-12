<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'lead_client');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'lead_id');
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
