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
        Schema::create('smtp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->string('sender_email');
            $table->string('smtp_username');
            $table->string('smtp_password');
            $table->string('smtp_host');
            $table->string('smtp_port');
            $table->string('smtp_encryption');
            $table->unsignedBigInteger('daily_limit')->default(0);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('CASCADE')->onUpdate('CASCADE');
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
        Schema::dropIfExists('smtp_servers');
    }
};
