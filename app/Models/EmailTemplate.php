<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function email_template_type()
    {
        return $this->belongsTo(EmailTemplateType::class, 'template_type_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
}
