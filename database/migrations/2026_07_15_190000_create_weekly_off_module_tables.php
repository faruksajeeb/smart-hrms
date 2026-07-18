<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('weekly_off_policies')) {
            Schema::create('weekly_off_policies', function (Blueprint $table) {
                $table->id();

                $table->foreignId('company_id')
                    ->constrained('master_data_items')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->string('policy_name', 100);
                $table->string('policy_code', 30)->nullable();
                $table->text('description')->nullable();

                $table->boolean('status')->default(true);

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

                $table->unique(['company_id', 'policy_code'], 'wo_policy_code_unique');
                $table->index(['company_id', 'status']);
                $table->index('policy_name');
            });
        }

        if (! Schema::hasTable('weekly_off_policy_days')) {
            Schema::create('weekly_off_policy_days', function (Blueprint $table) {
                $table->id();

                $table->foreignId('weekly_off_policy_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->unsignedTinyInteger('day_of_week');

                $table->enum('week_type', [
                    'every',
                    'odd',
                    'even',
                    'specific',
                ])->default('every');

                $table->unsignedTinyInteger('week_number')
                    ->nullable();

                $table->enum('off_type', [
                    'full_day',
                    'first_half',
                    'second_half',
                ])->default('full_day');

                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();

                $table->boolean('status')->default(true);

                $table->timestamps();

                $table->index(['weekly_off_policy_id', 'day_of_week']);
                $table->index(['effective_from', 'effective_to']);

                $table->unique([
                    'weekly_off_policy_id',
                    'day_of_week',
                    'week_type',
                    'week_number',
                ], 'wo_policy_day_unique');
            });
        }

        if (! Schema::hasTable('employee_weekly_off_assignments')) {
            Schema::create('employee_weekly_off_assignments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('weekly_off_policy_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();

                $table->date('effective_from')->nullable();
                $table->date('effective_to')->nullable();

                $table->boolean('status')->default(true);

                $table->foreignId('created_by')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                $table->index(['weekly_off_policy_id', 'user_id'], 'ewoa_policy_user_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_weekly_off_assignments');
        Schema::dropIfExists('weekly_off_policy_days');
        Schema::dropIfExists('weekly_off_policies');
    }
};
