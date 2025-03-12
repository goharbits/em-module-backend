<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->longText('first_name')->nullable();
            $table->longText('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('job_title')->nullable();
            $table->longText('location')->nullable();
            $table->foreignId('company_id')->constrained('companies')->onDelete('CASCADE')->onUpdate('CASCADE');
            // $table->foreignId('country_id')->constrained('countries')->onDelete('CASCADE')->onUpdate('CASCADE');
            // $table->foreignId('city_id')->constrained('cities')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('industry')->nullable();
            $table->string('parent_industry')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('created_by')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
