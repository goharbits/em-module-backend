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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('template_type_id')->constrained('email_template_types')->onDelete('CASCADE')->onUpdate('CASCADE');
            $table->string('subject');
            $table->longText('body');
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
        Schema::dropIfExists('email_templates');
    }
};
