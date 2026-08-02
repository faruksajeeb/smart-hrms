<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_request_steps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('approval_request_id')
                ->constrained('approval_requests')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('workflow_level_id')
                ->nullable()
                ->constrained('approval_workflow_levels')
                ->nullOnDelete();

            $table->integer('level_no');
            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'approved',
                'rejected',
                'skipped',
                'delegated',
            ])->default('pending');

            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('delegated_to')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['approval_request_id', 'level_no']);
            $table->index(['approver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_request_steps');
    }
};
