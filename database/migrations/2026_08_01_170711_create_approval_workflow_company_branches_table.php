<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflow_company_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_id')
                ->constrained('approval_workflows')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained('master_data_items')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['workflow_id', 'company_id', 'branch_id'], 'awcb_unique');
            $table->index(['company_id', 'branch_id'], 'awcb_company_branch_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflow_company_branches');
    }
};
