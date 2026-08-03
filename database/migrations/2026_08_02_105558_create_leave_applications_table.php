<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_no')->unique();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('leave_policy_id')
                ->nullable()
                ->constrained('leave_policies')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->enum('application_type', ['normal', 'emergency'])->default('normal');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 8, 2)->default(0);
            $table->decimal('requested_days', 8, 2)->default(0);
            $table->boolean('is_half_day')->default(false);
            $table->enum('half_day_session', ['morning', 'afternoon'])->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'submitted', 'pending', 'approved', 'rejected', 'cancelled', 'withdrawn', 'expired'])->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->dateTime('withdrawn_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->text('remarks')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'la_user_status_idx');
            $table->index(['user_id', 'start_date', 'end_date'], 'la_user_dates_idx');
            $table->index(['status', 'created_at'], 'la_status_created_idx');
            $table->index('application_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};
