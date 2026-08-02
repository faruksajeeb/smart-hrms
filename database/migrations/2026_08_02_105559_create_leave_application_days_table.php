<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_application_days', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_application_id')
                ->constrained('leave_applications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->date('leave_date');
            $table->enum('day_type', ['full_day', 'half_day'])->default('full_day');
            $table->enum('session', ['morning', 'afternoon'])->nullable();
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_weekly_off')->default(false);
            $table->boolean('counts_as_leave')->default(true);
            $table->decimal('leave_days', 8, 2)->default(1);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['leave_application_id', 'leave_date'], 'lad_app_date_unique');
            $table->index(['leave_application_id', 'leave_date'], 'lad_app_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_application_days');
    }
};
