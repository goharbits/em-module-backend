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
        Schema::create('list_contact', function (Blueprint $table) {
            $table->id();
            $table->foreignId('list_id')->constrained('contact_lists')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('list_contact');
    }
};
