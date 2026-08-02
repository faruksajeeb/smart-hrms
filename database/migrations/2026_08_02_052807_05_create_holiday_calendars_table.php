<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holiday_calendars', function (Blueprint $table) {
            $table->id();

            $table->string('holiday_name');
            $table->string('holiday_code')->unique();
            $table->date('holiday_date');
            $table->enum('holiday_type', ['national', 'religious', 'company', 'branch', 'optional'])->default('company');
            $table->boolean('is_recurring')->default(false);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['holiday_date', 'status']);
            $table->index(['holiday_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holiday_calendars');
    }
};
