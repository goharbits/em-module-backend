<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplateType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function email_template()
    {
        return $this->hasMany(EmailTemplate::class, 'template_type_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
