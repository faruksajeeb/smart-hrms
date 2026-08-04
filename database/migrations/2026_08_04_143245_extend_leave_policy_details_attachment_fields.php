<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_policy_details', function (Blueprint $table) {
            $table->decimal('attachment_required_after_days', 5, 2)->nullable()->after('attachment_required');
            $table->string('attachment_document_type')->nullable()->after('attachment_required_after_days');
            $table->unsignedTinyInteger('maximum_attachment_files')->default(1)->after('attachment_document_type');
            $table->unsignedSmallInteger('maximum_attachment_size_mb')->default(5)->after('maximum_attachment_files');
            $table->string('allowed_extensions')->nullable()->after('maximum_attachment_size_mb');
        });
    }

    public function down(): void
    {
        Schema::table('leave_policy_details', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_extensions',
                'maximum_attachment_size_mb',
                'maximum_attachment_files',
                'attachment_document_type',
                'attachment_required_after_days',
            ]);
        });
    }
};
