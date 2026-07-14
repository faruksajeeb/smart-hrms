<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('religion')->nullable()->after('nationality');
            $table->string('blood_group')->nullable()->after('religion');
            $table->string('marital_status')->nullable()->after('blood_group');
            $table->string('qualification')->nullable()->after('marital_status');
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn(['religion', 'blood_group', 'marital_status', 'qualification']);
        });
    }
};
