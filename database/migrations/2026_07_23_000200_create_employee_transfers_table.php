<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('from_company_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_company_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_branch_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_branch_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_cluster_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_cluster_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_division_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_division_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_department_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_department_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_section_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_section_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('from_unit_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('to_unit_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->date('effective_from');

            $table->date('effective_to')->nullable();

            $table->enum('transfer_reason', [
                'promotion',
                'business_requirement',
                'department_restructure',
                'branch_relocation',
                'employee_request',
                'temporary_assignment',
                'project_assignment',
                'administrative_decision',
                'other',
            ])->default('other');

            $table->string('remarks')->nullable();

            $table->enum('approval_status', [
                'draft',
                'pending',
                'approved',
                'rejected',
            ])->default('draft');

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

            $table->index(['user_id', 'approval_status']);
            $table->index(['effective_from']);
            $table->index(['created_by']);
            $table->index(['updated_by']);
            $table->index(['approved_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_transfers');
    }
};
