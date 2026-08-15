<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('punch_datetime');
            $table->date('punch_date');
            $table->string('punch_type', 20)->nullable();
            $table->string('source', 30)->default('manual');
            $table->string('device_id')->nullable();
            $table->string('external_reference')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['source', 'external_reference'], 'att_punch_source_ref_unique');
            $table->index(['user_id', 'punch_date', 'punch_datetime'], 'att_punch_user_date_idx');
        });

        Schema::create('attendance_daily_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->foreignId('shift_schedule_id')->nullable()->constrained('shift_schedules')->nullOnDelete();
            $table->foreignId('leave_application_id')->nullable()->constrained('leave_applications')->nullOnDelete();
            $table->foreignId('holiday_id')->nullable()->constrained('holiday_calendars')->nullOnDelete();
            $table->dateTime('first_in')->nullable();
            $table->dateTime('last_out')->nullable();
            $table->unsignedInteger('worked_minutes')->default(0);
            $table->unsignedInteger('scheduled_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_out_minutes')->default(0);
            $table->unsignedInteger('calculated_overtime_minutes')->default(0);
            $table->unsignedInteger('approved_overtime_minutes')->default(0);
            $table->string('attendance_status', 40)->default('not_applicable');
            $table->string('day_status', 30)->default('working_day');
            $table->string('processing_source', 30)->default('system');
            $table->string('lifecycle_status', 20)->default('processed');
            $table->timestamp('processed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'attendance_date'], 'att_daily_user_date_unique');
            $table->index(['attendance_date', 'attendance_status'], 'att_daily_date_status_idx');
            $table->index(['lifecycle_status', 'attendance_date'], 'att_daily_lifecycle_idx');
        });

        Schema::create('attendance_processing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('processing_date')->nullable();
            $table->string('action', 30);
            $table->string('status', 20)->default('success');
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['processing_date', 'status'], 'att_log_date_status_idx');
        });

        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_daily_record_id')->constrained('attendance_daily_records')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->string('regularization_type', 40);
            $table->dateTime('requested_in')->nullable();
            $table->dateTime('requested_out')->nullable();
            $table->string('requested_status', 40)->nullable();
            $table->dateTime('approved_in')->nullable();
            $table->dateTime('approved_out')->nullable();
            $table->string('approved_status', 40)->nullable();
            $table->text('reason');
            $table->text('remarks')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('approval_request_id')->nullable()->constrained('approval_requests')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'attendance_date', 'status'], 'att_reg_user_date_status_idx');
            $table->index(['approval_request_id', 'status'], 'att_reg_approval_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_regularizations');
        Schema::dropIfExists('attendance_processing_logs');
        Schema::dropIfExists('attendance_daily_records');
        Schema::dropIfExists('attendance_punches');
    }
};
