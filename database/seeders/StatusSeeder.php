<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use App\Http\Traits\CommonTrait;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusSeeder extends Seeder
{
    use CommonTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $moduleStatuses = [
            'user' => ['Active', 'Inactive', 'Blocked'],
            'smtp' => ['Active', 'Inactive'],
            'imap' => ['Active', 'Inactive'],
            'company' => ['Active', 'Inactive'],
            'email_template_type' => ['Active', 'Inactive'],
            'email_template' => ['Active', 'Inactive'],
            'contact' => ['New', 'Subscribed', 'Unsubscribe'],
            'client' => ['New', 'Subscribed', 'Unsubscribe'],
            'campaign' => ['Pending', 'Ongoing', 'Completed'],
            'list' => ['Processing', 'Completed', 'Failed'],
            'lead' => ['Processing', 'Completed', 'Failed'],
            'segment' => ['Processing', 'Completed', 'Failed'],
        ];

        foreach ($moduleStatuses as $module => $statuses) {
            foreach ($statuses as $status) {
                if (!$this->getStatusByName($module, $status)) {
                    Status::create([
                        'name' => $status,
                        'slug' => createSlug($status),
                        'module' => $module,
                    ]);
                }
            }
        }
    }
}
