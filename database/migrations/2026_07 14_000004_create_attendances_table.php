<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Link back to the planned shift being clocked against, if any
            $table->foreignId('shift_schedule_id')->nullable()->constrained('shift_schedules')->nullOnDelete();

            $table->dateTime('clock_in')->nullable();
            $table->dateTime('clock_out')->nullable();

            $table->decimal('clock_in_latitude', 10, 7)->nullable();
            $table->decimal('clock_in_longitude', 10, 7)->nullable();
            $table->decimal('clock_out_latitude', 10, 7)->nullable();
            $table->decimal('clock_out_longitude', 10, 7)->nullable();

            // on_time | late | early_leave | absent | overtime
            $table->string('status')->nullable();

            // Computed on clock-out for fast reporting queries
            $table->unsignedInteger('total_minutes')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'clock_in']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};