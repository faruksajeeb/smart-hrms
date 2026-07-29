<?php

use App\Models\MasterDataItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('employee_id');

            $table->foreignId('branch_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('company_id');

            $table->foreignId('cluster_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('branch_id');

            $table->foreignId('division_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('cluster_id');

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('division_id');

            $table->foreignId('section_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('department_id');

            $table->foreignId('unit_id')
                ->nullable()
                ->constrained('master_data_items')
                ->nullOnDelete()
                ->after('section_id');

            $table->index(['company_id', 'branch_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['cluster_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['company_id']);

            $table->dropColumn([
                'company_id',
                'branch_id',
                'cluster_id',
                'division_id',
                'department_id',
                'section_id',
                'unit_id',
            ]);

            $table->dropIndex(['company_id', 'branch_id', 'department_id']);
        });
    }
};
