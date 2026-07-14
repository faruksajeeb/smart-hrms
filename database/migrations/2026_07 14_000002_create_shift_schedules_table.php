<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->date('work_date');

            // Optional per-day overrides of the shift template's default times
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // scheduled | confirmed | completed | cancelled | no_show
            $table->string('status')->default('scheduled');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Prevent the same employee being double-booked into the same shift on the same day
            $table->unique(['user_id', 'work_date', 'shift_id']);
            $table->index('work_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
    }
};