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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();

            // Company
            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Shift Information
            $table->string('shift_name', 100);
            $table->string('shift_code', 20)->unique();
            $table->string('description')->nullable();

            // Shift Time
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Break Time
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();

            // Time Rules (Minutes)
            $table->unsignedSmallInteger('grace_time')->default(0)
                ->comment('Allowed late arrival in minutes');

            $table->decimal('working_hours', 4, 2)->default(8.00)
                ->comment('Expected working hours');

            $table->unsignedSmallInteger('late_after')->default(0)
                ->comment('Late after X minutes');

            $table->unsignedSmallInteger('half_day_after')->default(0)
                ->comment('Half-day after X minutes');

            $table->decimal('minimum_work_hours', 4, 2)->default(8.00)
                ->comment('Minimum hours required');

            // Shift Type
            $table->boolean('is_flexible')->default(false);
            $table->boolean('is_night_shift')->default(false);
            $table->string('color', 7)->nullable()
                ->comment('Hex color code for shift representation');

            // Status
            $table->boolean('status')->default(true);

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index('shift_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};