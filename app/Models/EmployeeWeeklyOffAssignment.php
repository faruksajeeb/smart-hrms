<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'weekly_off_policy_id',
    'effective_from',
    'effective_to',
    'assignment_type',
    'is_current',
    'remarks',
    'created_by',
    'updated_by',
])]
class EmployeeWeeklyOffAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to'   => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function weeklyOffPolicy(): BelongsTo
    {
        return $this->belongsTo(WeeklyOffPolicy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeCurrent(Builder $query): Builder
    {
        return $query
            ->whereDate('effective_from', '<=', today())
            ->where(function ($query) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', today());
            });
    }

    public function scopeEffectiveOn(Builder $query, $date): Builder
    {
        return $query
            ->whereDate('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $date);
            });
    }

    #accessors
    #effective_from
    public function getEffectiveFromAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');  
    }

    #effective_to
    public function getEffectiveToAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');  
    }
}