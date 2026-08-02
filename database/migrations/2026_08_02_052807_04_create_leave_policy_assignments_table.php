<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policy_assignments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_policy_id')
                ->constrained('leave_policies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            $table->string('employment_type')->nullable()->after('designation_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['leave_policy_id', 'status'], 'lpa_policy_status_idx');
            $table->index(['company_id', 'branch_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'designation_id', 'user_id', 'effective_from', 'effective_to'], 'lpa_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_assignments');
    }
};
