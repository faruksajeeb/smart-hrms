<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex('approval_workflows_company_id_branch_id_index');
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::table('approval_workflows', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->constrained('master_data_items')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('master_data_items')->nullOnDelete();
        });
    }
};
