<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // e.g. "Morning", "Evening", "Night"
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_overnight')->default(false); // true if end_time < start_time
            $table->string('color', 7)->nullable();          // hex color for calendar UI, e.g. #4f46e5
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};