<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policy_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_policy_id')
                ->constrained('leave_policies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('annual_entitlement', 8, 2)->nullable();
            $table->enum('accrual_method', ['none', 'monthly', 'quarterly', 'yearly'])->default('none');
            $table->decimal('monthly_accrual', 8, 2)->nullable();
            $table->boolean('carry_forward_allowed')->default(false);
            $table->decimal('maximum_carry_forward', 8, 2)->nullable();
            $table->boolean('encashment_allowed')->default(false);
            $table->decimal('maximum_encashment', 8, 2)->nullable();
            $table->integer('maximum_consecutive_days')->nullable();
            $table->integer('minimum_days_per_application')->default(1);
            $table->integer('maximum_days_per_application')->nullable();
            $table->boolean('half_day_allowed')->default(true);
            $table->boolean('hourly_leave_allowed')->default(false);
            $table->boolean('attachment_required')->default(false);
            $table->boolean('medical_certificate_required')->default(false);
            $table->integer('notice_period_days')->default(0);
            $table->integer('minimum_service_months')->default(0);
            $table->boolean('probation_allowed')->default(true);
            $table->boolean('include_weekly_off')->default(false);
            $table->boolean('include_holiday')->default(false);
            $table->boolean('sandwich_rule')->default(false);
            $table->boolean('allow_negative_balance')->default(false);
            $table->enum('gender_restriction', ['male', 'female', 'any'])->default('any');
            $table->enum('marital_status_restriction', ['single', 'married', 'any'])->default('any');
            $table->boolean('applicable_after_confirmation')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['leave_policy_id', 'leave_type_id']);
            $table->index(['leave_policy_id', 'status']);
            $table->index(['leave_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_details');
    }
};
