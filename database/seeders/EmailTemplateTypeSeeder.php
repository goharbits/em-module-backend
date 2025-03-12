<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Http\Traits\CommonTrait;
use App\Models\EmailTemplateType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EmailTemplateTypeSeeder extends Seeder
{
    use CommonTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplateType::insert(
            ['name' => 'Subscription', 'status_id' => $this->getStatusId('email_template_type', 'active')],
            ['name' =>'Promotion', 'status_id' => $this->getStatusId('email_template_type', 'active')],
            ['name' =>'Newsletter', 'status_id' => $this->getStatusId('email_template_type', 'active')],
            ['name' =>'Offer', 'status_id' => $this->getStatusId('email_template_type', 'active')],
            ['name' =>'Announcement', 'status_id' => $this->getStatusId('email_template_type', 'active')],
            ['name' => 'Survey'],
            ['name' =>'New Product', 'status_id' => $this->getStatusId('email_template_type', 'active')],
        );

    }
}
