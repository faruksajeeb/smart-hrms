<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_balance_ledgers', function (Blueprint $table) {
            if (!Schema::hasColumn('leave_balance_ledgers', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('leave_type_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('company_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'division_id')) {
                $table->foreignId('division_id')->nullable()->after('branch_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('division_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'section_id')) {
                $table->foreignId('section_id')->nullable()->after('department_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('section_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'designation_id')) {
                $table->foreignId('designation_id')->nullable()->after('unit_id')->constrained('master_data_items')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'employment_type')) {
                $table->string('employment_type')->nullable()->after('designation_id');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'leave_policy_id')) {
                $table->foreignId('leave_policy_id')->nullable()->after('employment_type')->constrained('leave_policies')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'transaction_source')) {
                $table->enum('transaction_source', ['system', 'manual', 'import', 'payroll', 'cron', 'api'])->default('system')->after('transaction_type');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'effective_date')) {
                $table->date('effective_date')->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'credit_days')) {
                $table->decimal('credit_days', 8, 2)->nullable()->after('days');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'debit_days')) {
                $table->decimal('debit_days', 8, 2)->nullable()->after('credit_days');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'transaction_reference')) {
                $table->string('transaction_reference')->nullable()->after('reference_id');
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'performed_by')) {
                $table->foreignId('performed_by')->nullable()->after('created_by')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('performed_by')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('leave_balance_ledgers', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('approved_by')->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            }

            if (!Schema::hasIndex('leave_balance_ledgers', 'lbl_user_type_txn_idx')) {
                $table->index(['user_id', 'leave_type_id', 'transaction_type'], 'lbl_user_type_txn_idx');
            }
            if (!Schema::hasIndex('leave_balance_ledgers', 'lbl_source_idx')) {
                $table->index(['transaction_source'], 'lbl_source_idx');
            }
            if (!Schema::hasIndex('leave_balance_ledgers', 'lbl_effective_date_idx')) {
                $table->index(['effective_date'], 'lbl_effective_date_idx');
            }
            if (!Schema::hasIndex('leave_balance_ledgers', 'lbl_policy_idx')) {
                $table->index(['leave_policy_id'], 'lbl_policy_idx');
            }
            if (!Schema::hasIndex('leave_balance_ledgers', 'lbl_org_idx')) {
                $table->index(['company_id', 'branch_id', 'division_id', 'department_id'], 'lbl_org_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leave_balance_ledgers', function (Blueprint $table) {
            $table->dropIndex('lbl_user_type_txn_idx');
            $table->dropIndex('lbl_source_idx');
            $table->dropIndex('lbl_effective_date_idx');
            $table->dropIndex('lbl_policy_idx');
            $table->dropIndex('lbl_org_idx');

            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['section_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['leave_policy_id']);
            $table->dropForeign(['performed_by']);
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['processed_by']);

            $table->dropColumn([
                'company_id', 'branch_id', 'division_id', 'department_id',
                'section_id', 'unit_id', 'designation_id', 'employment_type',
                'leave_policy_id', 'transaction_source', 'effective_date',
                'credit_days', 'debit_days', 'transaction_reference',
                'performed_by', 'approved_by', 'processed_by',
            ]);
        });
    }
};
