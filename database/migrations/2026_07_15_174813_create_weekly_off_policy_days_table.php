<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weekly_off_policy_days', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Weekly Off Policy
            |--------------------------------------------------------------------------
            */

            $table->foreignId('weekly_off_policy_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Weekly Off Rule
            |--------------------------------------------------------------------------
            */

            // 0 = Sunday
            // 1 = Monday
            // ...
            // 6 = Saturday
            $table->unsignedTinyInteger('day_of_week');

            /*
            |--------------------------------------------------------------------------
            | Week Type
            |--------------------------------------------------------------------------
            |
            | every = Every week
            | odd   = Odd weeks only
            | even  = Even weeks only
            | specific = Specific week(s) of month
            |
            */

            $table->enum('week_type', [
                'every',
                'odd',
                'even',
                'specific',
            ])->default('every');

            /*
            |--------------------------------------------------------------------------
            | Week Number
            |--------------------------------------------------------------------------
            |
            | Used only when week_type = specific
            |
            | 1 = First Week
            | 2 = Second Week
            | 3 = Third Week
            | 4 = Fourth Week
            | 5 = Fifth Week
            |
            */

            $table->unsignedTinyInteger('week_number')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Off Type
            |--------------------------------------------------------------------------
            */

            $table->enum('off_type', [
                'full_day',
                'first_half',
                'second_half',
            ])->default('full_day');

            /*
            |--------------------------------------------------------------------------
            | Effective Date
            |--------------------------------------------------------------------------
            */

            $table->date('effective_from')->nullable();

            $table->date('effective_to')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('status')
                ->default(true);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */

            $table->index([
                'weekly_off_policy_id',
                'day_of_week',
            ]);

            $table->index([
                'effective_from',
                'effective_to',
            ]);

            $table->unique([
                'weekly_off_policy_id',
                'day_of_week',
                'week_type',
                'week_number',
                // 'effective_from',
                // 'effective_to',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_off_policy_days');
    }
};