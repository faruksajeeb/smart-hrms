<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_id')
                ->constrained('approval_workflows')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('level_no');
            $table->enum('approval_type', [
                'ROLE',
                'REPORTING_MANAGER',
                'DEPARTMENT_HEAD',
                'BRANCH_MANAGER',
                'HR_MANAGER',
                'COMPANY_ADMIN',
                'SPECIFIC_USER',
                'DYNAMIC',
            ]);

            $table->string('role_type')->nullable();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('dynamic_resolver')->nullable();
            $table->integer('minimum_approvals')->default(1);
            $table->boolean('can_reject')->default(true);
            $table->boolean('can_delegate')->default(false);
            $table->boolean('can_skip')->default(false);
            $table->boolean('is_final_level')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workflow_id', 'level_no']);
            $table->index(['approval_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_levels');
    }
};
