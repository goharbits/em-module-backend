<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 100000; $i++) {
            DB::table('clients')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'assigned_to' => 1,
                'created_by' => 1,
            ]);
        }
    }
}
