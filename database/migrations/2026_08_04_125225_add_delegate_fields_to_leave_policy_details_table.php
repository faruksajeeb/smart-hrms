<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_policy_details', function (Blueprint $table) {
            $table->boolean('delegate_required')->default(false)->after('medical_certificate_required');
            $table->boolean('delegate_acknowledgement_required')->default(false)->after('delegate_required');
        });
    }

    public function down(): void
    {
        Schema::table('leave_policy_details', function (Blueprint $table) {
            $table->dropColumn([
                'delegate_acknowledgement_required',
                'delegate_required',
            ]);
        });
    }
};
