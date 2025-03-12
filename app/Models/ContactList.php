<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContactList extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($list) {
            DB::transaction(function () use ($list) {
                // Get the IDs of contacts related to the list before detaching
                $contactIds = $list->contacts->pluck('id');

                // Detach all contacts related to the list
                $list->contacts()->detach();

                // Check and delete contacts that are not associated with any other lists
                $contactIds->each(function ($contactId) {
                    $contact = Contact::find($contactId);
                    if ($contact && $contact->lists()->count() === 0) {
                        $contact->delete();
                    }
                });
            });
        });
    }

    public function contacts()
    {
        return $this->belongsToMany(Contact::class, 'list_contact', 'list_id', 'contact_id');
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

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'list_id');
    }
}
