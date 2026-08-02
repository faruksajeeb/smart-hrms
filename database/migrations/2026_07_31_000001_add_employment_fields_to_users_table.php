<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('designation_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('unit_id');

            $table->foreignId('employment_type_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('designation_id');

            $table->foreignId('reporting_manager_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('employment_type_id');

            $table->date('joining_date')
                ->nullable()
                ->after('reporting_manager_id');

            $table->index(['designation_id', 'employment_type_id', 'reporting_manager_id'], 'users_employment_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropForeign(['employment_type_id']);
            $table->dropForeign(['designation_id']);

            $table->dropColumn([
                'designation_id',
                'employment_type_id',
                'reporting_manager_id',
                'joining_date',
            ]);

            $table->dropIndex(['users_employment_idx']);
        });
    }
};
