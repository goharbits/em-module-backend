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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('email_template_id')->constrained('email_templates')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('created_by')->constrained('users')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->boolean('is_opened')->default(0);
            $table->boolean('is_link_clicked')->default(0);
            $table->boolean('is_send')->default(0);
            $table->text('token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
