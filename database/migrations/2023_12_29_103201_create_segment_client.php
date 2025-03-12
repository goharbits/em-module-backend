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
        Schema::create('segment_client', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained('segments')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('client_id')->constrained('clients')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segment_client');
    }
};
