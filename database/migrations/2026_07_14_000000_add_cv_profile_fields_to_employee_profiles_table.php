<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('work_location');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('nationality')->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('nationality');
            $table->text('skills')->nullable()->after('address');
            $table->text('experience_summary')->nullable()->after('skills');
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'date_of_birth',
                'nationality',
                'address',
                'skills',
                'experience_summary',
            ]);
        });
    }
};
