<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
