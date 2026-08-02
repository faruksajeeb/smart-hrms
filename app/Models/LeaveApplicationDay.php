<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\LeaveDayType;

#[Fillable([
    'leave_application_id',
    'leave_date',
    'day_type',
    'session',
    'is_holiday',
    'is_weekly_off',
    'counts_as_leave',
    'leave_days',
    'remarks',
])]
class LeaveApplicationDay extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'leave_date' => 'date',
            'day_type' => LeaveDayType::class,
            'is_holiday' => 'boolean',
            'is_weekly_off' => 'boolean',
            'counts_as_leave' => 'boolean',
            'leave_days' => 'decimal:2',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class, 'leave_application_id');
    }
}
