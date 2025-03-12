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
        Schema::table('smtp_servers', function (Blueprint $table) {
            $table->unsignedBigInteger('smtp_timeout')->default(0);
            $table->unsignedBigInteger('second_limit')->default(0);
            $table->unsignedBigInteger('minute_limit')->default(0);
            $table->unsignedBigInteger('hourly_limit')->default(0);
            $table->unsignedBigInteger('monthly_limit')->default(0);
            $table->unsignedBigInteger('delay_range_start')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('smtp_servers', function (Blueprint $table) {
            //
        });
    }
};
