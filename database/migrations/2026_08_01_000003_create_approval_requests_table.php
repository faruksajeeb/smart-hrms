<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('workflow_id')
                ->constrained('approval_workflows')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('module_name');
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('requested_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->integer('current_level')->default(1);
            $table->enum('current_status', [
                'pending',
                'approved',
                'rejected',
                'cancelled',
            ])->default('pending');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_name', 'reference_type', 'reference_id']);
            $table->index(['requested_by', 'current_status']);
            $table->index(['current_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};
