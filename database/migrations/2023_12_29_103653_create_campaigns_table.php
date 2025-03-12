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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('email_template_id')->constrained('email_templates')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('lead_id')->nullable()->constrained('leads')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('segment_id')->nullable()->constrained('segments')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('created_by')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->dateTime('start_at');
            $table->string('send_on_timezone');
            $table->string('daily_limit')->default(0);
            $table->boolean('is_prevent_duplicate')->default(0);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
