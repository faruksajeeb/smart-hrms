<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_year_end_processes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('processing_year');
            $table->unsignedSmallInteger('target_year');
            $table->string('batch_key')->unique();
            $table->foreignId('company_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_employees')->default(0);
            $table->unsignedInteger('total_leave_types')->default(0);
            $table->decimal('total_closing_balance', 12, 2)->default(0);
            $table->decimal('total_carry_forward_days', 12, 2)->default(0);
            $table->decimal('total_expired_days', 12, 2)->default(0);
            $table->decimal('total_encashment_days', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['processing_year', 'target_year', 'status'], 'ley_process_year_status_idx');
        });

        Schema::create('leave_year_end_process_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('year_end_process_id')->constrained('leave_year_end_processes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->restrictOnDelete();
            $table->foreignId('leave_policy_id')->nullable()->constrained('leave_policies')->nullOnDelete();
            $table->decimal('previous_balance', 12, 2)->default(0);
            $table->decimal('eligible_carry_forward', 12, 2)->default(0);
            $table->decimal('expired_days', 12, 2)->default(0);
            $table->decimal('encashment_days', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->string('status')->default('preview');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['year_end_process_id', 'user_id', 'leave_type_id'], 'leyi_process_user_type_unique');
            $table->index(['user_id', 'leave_type_id'], 'ley_item_user_type_idx');
        });

        Schema::table('leave_balance_ledgers', function (Blueprint $table) {
            $table->unique('transaction_reference', 'lbl_transaction_reference_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leave_balance_ledgers', fn (Blueprint $table) => $table->dropUnique('lbl_transaction_reference_unique'));
        Schema::dropIfExists('leave_year_end_process_items');
        Schema::dropIfExists('leave_year_end_processes');
    }
};
