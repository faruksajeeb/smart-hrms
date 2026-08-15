<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('code')->unique(); $table->string('description')->nullable();
            $table->string('color')->nullable(); $table->boolean('is_working')->default(false); $table->boolean('is_active')->default(true); $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->softDeletes();
            $table->index(['is_active', 'sort_order'], 'att_status_active_sort_idx');
        });

        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id(); $table->string('policy_name'); $table->string('policy_code')->unique(); $table->text('description')->nullable();
            $table->date('effective_from'); $table->date('effective_to')->nullable(); $table->string('status')->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->softDeletes();
            $table->index(['status', 'effective_from', 'effective_to'], 'att_policy_effective_idx');
        });

        Schema::create('attendance_policy_rules', function (Blueprint $table) {
            $table->id(); $table->foreignId('attendance_policy_id')->constrained('attendance_policies')->cascadeOnDelete();
            $table->decimal('expected_working_hours', 5, 2)->default(8); $table->decimal('minimum_working_hours', 5, 2)->default(0); $table->decimal('maximum_working_hours', 5, 2)->nullable();
            $table->unsignedInteger('late_grace_minutes')->default(0); $table->unsignedInteger('early_out_grace_minutes')->default(0); $table->unsignedInteger('late_threshold_minutes')->default(0); $table->unsignedInteger('early_out_threshold_minutes')->default(0);
            $table->boolean('late_affects_status')->default(true); $table->boolean('early_out_affects_status')->default(true); $table->decimal('half_day_threshold_hours', 5, 2)->default(4); $table->decimal('minimum_required_hours', 5, 2)->default(0);
            $table->boolean('overtime_allowed')->default(false); $table->unsignedInteger('minimum_overtime_minutes')->default(0); $table->string('overtime_rounding_rule')->nullable(); $table->unsignedInteger('maximum_overtime_minutes')->nullable();
            $table->unsignedInteger('minimum_punches')->default(1); $table->unsignedInteger('maximum_punches')->nullable(); $table->boolean('allow_multiple_punches')->default(true); $table->string('missing_punch_handling')->default('incomplete');
            $table->boolean('allow_cross_midnight')->default(false); $table->unsignedInteger('maximum_attendance_span_minutes')->nullable(); $table->boolean('allow_holiday_attendance')->default(true); $table->boolean('allow_weekly_off_attendance')->default(true); $table->boolean('holiday_weekly_off_overtime')->default(false);
            $table->json('settings')->nullable(); $table->timestamps(); $table->softDeletes(); $table->unique('attendance_policy_id', 'att_policy_rule_unique');
        });

        Schema::create('attendance_policy_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('attendance_policy_id')->constrained('attendance_policies')->restrictOnDelete(); $table->foreignId('company_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('branch_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('division_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('department_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('section_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('unit_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->foreignId('designation_id')->nullable()->constrained('master_data_items')->nullOnDelete(); $table->string('employment_type')->nullable(); $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); $table->date('effective_from'); $table->date('effective_to')->nullable(); $table->string('status')->default('active'); $table->text('remarks')->nullable(); $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->softDeletes();
            $table->index(['user_id', 'effective_from', 'effective_to'], 'att_assign_user_effective_idx'); $table->index(['company_id', 'branch_id', 'department_id'], 'att_assign_org_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_policy_assignments'); Schema::dropIfExists('attendance_policy_rules'); Schema::dropIfExists('attendance_policies'); Schema::dropIfExists('attendance_statuses');
    }
};
