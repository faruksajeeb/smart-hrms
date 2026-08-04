<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_attachments', function (Blueprint $table) {
            $table->string('original_file_name')->nullable()->after('mime_type');
            $table->string('stored_file_name')->nullable()->after('original_file_name');
            $table->string('storage_disk')->default('private')->after('stored_file_name');
            $table->string('status')->default('pending')->after('storage_disk');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('remarks')->nullable()->after('verified_at');

            $table->index(['status', 'verified_at'], 'la_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('leave_attachments', function (Blueprint $table) {
            $table->dropIndex('la_status_idx');
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'remarks',
                'verified_at',
                'verified_by',
                'status',
                'storage_disk',
                'stored_file_name',
                'original_file_name',
            ]);
        });
    }
};
