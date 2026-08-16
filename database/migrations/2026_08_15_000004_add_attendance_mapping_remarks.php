<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('attendance_device_employee_mappings', fn(Blueprint $t)=>$t->text('remarks')->nullable()->after('status')); } public function down(): void { Schema::table('attendance_device_employee_mappings', fn(Blueprint $t)=>$t->dropColumn('remarks')); } };
