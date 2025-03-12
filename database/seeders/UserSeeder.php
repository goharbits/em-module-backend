<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    use CommonTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@em.com',
            'user_type' => 'admin',
            'password' => Hash::make('password'),
            'status_id' => $this->getStatusId('user', 'active'),
        ]);

        $user->assignRole('super_admin');

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@em.com',
            'user_type' => 'admin',
            'password' => Hash::make('password'),
            'status_id' => $this->getStatusId('user', 'active'),
        ]);

        $user->assignRole('admin');

        $user = User::create([
            'name' => 'Test Business User',
            'email' => 'testuser@email.com',
            'user_type' => 'user',
            'password' => Hash::make('password'),
            'status_id' => $this->getStatusId('user', 'active'),
        ]);

        $user->assignRole('user');
    }
}
