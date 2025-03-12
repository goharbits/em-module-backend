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
        Schema::create('campaign_smtp_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_server_id')->constrained('smtp_servers')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('campaign_id')->constrained('campaigns')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->unsignedBigInteger('smtp_limit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_smtp_servers');
    }
};
