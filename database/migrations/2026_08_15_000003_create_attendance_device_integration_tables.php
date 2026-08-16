<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_devices', function (Blueprint $t) {
            $t->id(); $t->string('device_name'); $t->string('device_code')->unique();
            $t->string('device_type')->default('biometric'); $t->string('vendor')->nullable(); $t->string('model')->nullable(); $t->string('serial_number')->nullable();
            $t->string('connection_type')->default('api'); $t->string('ip_address')->nullable(); $t->unsignedInteger('port')->nullable(); $t->text('api_url')->nullable();
            $t->text('api_key')->nullable(); $t->text('api_secret')->nullable(); $t->text('username')->nullable(); $t->text('password')->nullable();
            $t->string('timezone')->default('UTC'); $t->string('location')->nullable(); $t->foreignId('company_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $t->foreignId('branch_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $t->string('sync_mode')->default('manual'); $t->string('status')->default('inactive'); $t->timestamp('last_sync_at')->nullable(); $t->timestamp('last_successful_sync_at')->nullable(); $t->text('remarks')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $t->softDeletes(); $t->timestamps();
            $t->index(['status','sync_mode'],'att_device_status_mode_idx');
        });
        Schema::create('attendance_device_employee_mappings', function (Blueprint $t) {
            $t->id(); $t->foreignId('device_id')->constrained('attendance_devices')->cascadeOnDelete(); $t->foreignId('user_id')->constrained()->cascadeOnDelete(); $t->string('external_employee_id'); $t->string('external_user_code')->nullable(); $t->date('effective_from'); $t->date('effective_to')->nullable(); $t->string('status')->default('active'); $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $t->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
            $t->index(['device_id','external_employee_id','status'],'att_map_device_external_idx'); $t->index(['user_id','effective_from','effective_to'],'att_map_user_effective_idx');
        });
        Schema::create('attendance_device_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('device_id')->constrained('attendance_devices')->cascadeOnDelete(); $t->string('external_log_id')->nullable(); $t->string('employee_identifier'); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->dateTime('punch_datetime'); $t->string('punch_type')->default('unknown'); $t->string('verification_type')->nullable(); $t->dateTime('device_timestamp')->nullable(); $t->timestamp('received_at'); $t->json('raw_payload'); $t->json('normalized_payload')->nullable(); $t->string('processing_status')->default('pending'); $t->timestamp('processed_at')->nullable(); $t->text('error_message')->nullable(); $t->unsignedInteger('retry_count')->default(0); $t->boolean('is_duplicate')->default(false); $t->string('fingerprint',64); $t->timestamps();
            $t->unique(['device_id','external_log_id'],'att_devlog_device_external_unique'); $t->unique(['device_id','fingerprint'],'att_devlog_device_fingerprint_unique'); $t->index(['device_id','punch_datetime'],'att_devlog_device_date_idx'); $t->index(['user_id','punch_datetime'],'att_devlog_user_date_idx'); $t->index(['processing_status','created_at'],'att_devlog_status_created_idx');
        });
        Schema::create('attendance_device_sync_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('device_id')->constrained('attendance_devices')->cascadeOnDelete(); $t->string('sync_type')->default('manual'); $t->timestamp('started_at'); $t->timestamp('completed_at')->nullable(); $t->dateTime('from_datetime'); $t->dateTime('to_datetime'); $t->unsignedInteger('records_fetched')->default(0); $t->unsignedInteger('records_inserted')->default(0); $t->unsignedInteger('records_duplicate')->default(0); $t->unsignedInteger('records_failed')->default(0); $t->unsignedInteger('records_processed')->default(0); $t->string('status')->default('running'); $t->text('error_message')->nullable(); $t->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete(); $t->timestamps();
            $t->index(['device_id','started_at'],'att_sync_device_date_idx'); $t->index(['status','started_at'],'att_sync_status_date_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_device_sync_logs'); Schema::dropIfExists('attendance_device_logs'); Schema::dropIfExists('attendance_device_employee_mappings'); Schema::dropIfExists('attendance_devices'); }
};
