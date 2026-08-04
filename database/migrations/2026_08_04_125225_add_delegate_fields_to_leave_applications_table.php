<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->foreignId('delegate_user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete()
                ->after('updated_by');

            $table->enum('delegate_status', ['pending', 'accepted', 'declined'])
                ->nullable()
                ->after('delegate_user_id');

            $table->dateTime('delegate_responded_at')
                ->nullable()
                ->after('delegate_status');

            $table->text('delegate_remarks')
                ->nullable()
                ->after('delegate_responded_at');

            $table->index(['delegate_user_id', 'delegate_status'], 'la_delegate_idx');
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropIndex('la_delegate_idx');
            $table->dropForeign(['delegate_user_id']);
            $table->dropColumn([
                'delegate_user_id',
                'delegate_status',
                'delegate_responded_at',
                'delegate_remarks',
            ]);
        });
    }
};
