<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_payroll_periods', function (Blueprint $table) {
            $table->id(); $table->string('period_name'); $table->string('period_code')->unique(); $table->date('period_from'); $table->date('period_to'); $table->date('payroll_cutoff_date')->nullable(); $table->string('status')->default('open'); $table->timestamp('locked_at')->nullable(); $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('reopened_at')->nullable(); $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete(); $table->text('remarks')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['period_from','period_to','status'], 'att_pay_period_dates_idx');
        });
        Schema::create('attendance_payroll_summaries', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('attendance_payroll_period_id'); $table->foreign('attendance_payroll_period_id', 'att_pay_summary_period_fk')->references('id')->on('attendance_payroll_periods')->cascadeOnDelete(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            foreach (['company_id','branch_id','division_id','department_id','section_id','unit_id','designation_id','employment_type_id'] as $field) $table->unsignedBigInteger($field)->nullable();
            foreach (['working_days','payable_days','present_days','absent_days','paid_leave_days','unpaid_leave_days','weekly_off_days','holiday_days','half_days','unpaid_absence_days','deduction_days','final_payable_days'] as $field) $table->decimal($field,8,2)->default(0);
            foreach (['late_occurrences','late_minutes','early_out_occurrences','early_out_minutes'] as $field) $table->unsignedInteger($field)->default(0);
            $table->decimal('overtime_hours',8,2)->default(0); $table->string('status')->default('draft'); $table->timestamp('finalized_at')->nullable(); $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->unique(['attendance_payroll_period_id','user_id'], 'att_pay_summary_period_user_unique'); $table->index(['attendance_payroll_period_id','status'], 'att_pay_summary_status_idx'); $table->index(['department_id','attendance_payroll_period_id'], 'att_pay_summary_dept_idx');
        });
        Schema::create('attendance_payroll_reprocessing_logs', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('attendance_payroll_period_id'); $table->foreign('attendance_payroll_period_id', 'att_pay_log_period_fk')->references('id')->on('attendance_payroll_periods')->cascadeOnDelete(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action'); $table->json('previous_values')->nullable(); $table->json('new_values')->nullable(); $table->text('reason')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->index(['attendance_payroll_period_id','action'], 'att_pay_log_period_action_idx');
        });
    }
    public function down(): void { Schema::dropIfExists('attendance_payroll_reprocessing_logs'); Schema::dropIfExists('attendance_payroll_summaries'); Schema::dropIfExists('attendance_payroll_periods'); }
};
