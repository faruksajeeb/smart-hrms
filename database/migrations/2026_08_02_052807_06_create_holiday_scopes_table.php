<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_scopes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('holiday_id')
                ->constrained('holiday_calendars')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->timestamps();

            $table->index(['holiday_id', 'company_id', 'branch_id', 'division_id', 'department_id'], 'hs_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_scopes');
    }
};
