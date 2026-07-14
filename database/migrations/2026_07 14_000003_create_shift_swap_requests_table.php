<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_swap_requests', function (Blueprint $table) {
            $table->id();

            // The employee giving up their shift, and which schedule row it is
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('requester_schedule_id')->constrained('shift_schedules')->cascadeOnDelete();

            // Optional: a specific colleague being asked (null = open request anyone can pick up)
            $table->foreignId('target_user_id')->nullable()->constrained('users')->cascadeOnDelete();

            // Optional: the schedule offered in exchange (null = one-way giveaway, not a mutual swap)
            $table->foreignId('target_schedule_id')->nullable()->constrained('shift_schedules')->cascadeOnDelete();

            // pending -> accepted (by target/open taker) -> approved (by manager) / rejected / cancelled
            $table->string('status')->default('pending');

            $table->text('reason')->nullable();

            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_swap_requests');
    }
};