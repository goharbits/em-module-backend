<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($contact) {
            DB::transaction(function () use ($contact) {

                // Detach all contacts related to the list
                $contact->lists()->detach();
            });
        });
    }

    public function lists()
    {
        return $this->belongsToMany(ContactList::class, 'list_contact', 'contact_id', 'list_id');
    }

    public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assigned_to_user()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function emails()
    {
        return  $this->hasMany(Email::class, 'contact_id');
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
            $query->where('first_name', 'LIKE', $searchTerm)
                ->orWhere('last_name', 'LIKE', $searchTerm)
                ->orWhere('email', 'LIKE', $searchTerm)
                ->orWhere('location', 'LIKE', $searchTerm)
                ->orWhere('industry', 'LIKE', $searchTerm)
                ->orWhere('parent_industry', 'LIKE', $searchTerm)
                ->orWhere('job_title', 'LIKE', $searchTerm)
                ->orWhereHas('company', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', $searchTerm);
                })
                ->orWhereHas('lists', function ($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', $searchTerm);
                });
        });
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId)->orWhere('assigned_to', $userId);
    }
}
