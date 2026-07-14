<?php

use App\Models\EmployeeProfile;
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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('employment_status')->default(EmployeeProfile::STATUS_ONBOARDING);
            $table->string('department')->nullable();
            $table->string('designation')->nullable();
            $table->string('employment_type')->default('full_time');
            $table->string('work_location')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('probation_starts_on')->nullable();
            $table->date('probation_ends_on')->nullable();
            $table->string('probation_status')->default(EmployeeProfile::PROBATION_PENDING);
            $table->date('confirmation_date')->nullable();
            $table->string('leave_policy_name')->nullable();
            $table->unsignedSmallInteger('annual_leave_days')->default(0);
            $table->unsignedSmallInteger('sick_leave_days')->default(0);
            $table->unsignedSmallInteger('casual_leave_days')->default(0);
            $table->unsignedSmallInteger('carry_forward_leave_days')->default(0);
            $table->decimal('salary_amount', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('BDT');
            $table->string('pay_frequency')->default('monthly');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('tax_identifier')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('termination_date')->nullable();
            $table->string('termination_type')->nullable();
            $table->text('termination_reason')->nullable();
            $table->date('last_rejoined_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
