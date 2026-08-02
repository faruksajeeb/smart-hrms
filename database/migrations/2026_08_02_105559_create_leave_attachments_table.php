<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('leave_application_id')
                ->constrained('leave_applications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['leave_application_id'], 'lta_app_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
    }
};
