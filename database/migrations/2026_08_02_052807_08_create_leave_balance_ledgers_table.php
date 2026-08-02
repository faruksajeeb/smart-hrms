<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_ledgers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->enum('transaction_type', [
                'opening',
                'accrual',
                'carry_forward',
                'leave_approved',
                'leave_cancelled',
                'adjustment',
                'encashment',
                'expiry',
            ]);

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('transaction_date');
            $table->decimal('days', 8, 2);
            $table->decimal('balance_after', 8, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'leave_type_id', 'transaction_date'], 'lbl_user_type_date_idx');
            $table->index(['reference_type', 'reference_id'], 'lbl_ref_idx');
            $table->index(['transaction_type'], 'lbl_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_ledgers');
    }
};
