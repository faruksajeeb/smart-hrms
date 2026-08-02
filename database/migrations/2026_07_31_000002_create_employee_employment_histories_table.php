<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_employment_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('event_type');

            $table->foreignId('company_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('cluster_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('division_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('designation_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('employment_type_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->foreignId('reporting_manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->date('effective_from');

            $table->date('effective_to')->nullable();

            $table->string('reason')->nullable();

            $table->text('remarks')->nullable();

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

            $table->index(['user_id', 'effective_from']);
            $table->index(['event_type']);
            $table->index(['effective_from']);
            $table->index(['created_by']);
            $table->index(['updated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_employment_histories');
    }
};
