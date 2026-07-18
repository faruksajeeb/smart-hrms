<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeWeeklyOffAssignment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'weekly_off_policy_id',
        'user_id',
        'effective_from',
        'effective_to',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'status' => 'boolean',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(WeeklyOffPolicy::class, 'weekly_off_policy_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
