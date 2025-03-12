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
        Schema::create('imaps', function (Blueprint $table) {
            $table->id();
            $table->string('imap_host_name');
            $table->string('imap_host');
            $table->string('imap_username')->unique();
            $table->string('imap_password');
            $table->foreignId('status_id')->constrained('statuses')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imaps');
    }
};
