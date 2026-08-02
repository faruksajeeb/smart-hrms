<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_opening_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('leave_type_id')
                ->constrained('leave_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->decimal('opening_balance', 8, 2);
            $table->date('effective_date');
            $table->text('remarks')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'leave_type_id', 'effective_date'], 'lob_user_type_date_unique');
            $table->index(['user_id', 'leave_type_id'], 'lob_user_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_opening_balances');
    }
};
