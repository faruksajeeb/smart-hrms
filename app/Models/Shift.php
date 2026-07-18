<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'shift_name',
        'shift_code',
        'description',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'grace_time',
        'working_hours',
        'late_after',
        'half_day_after',
        'minimum_work_hours',
        'is_flexible',
        'is_night_shift',
        'color',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'grace_time' => 'integer',
            'working_hours' => 'decimal:2',
            'late_after' => 'integer',
            'half_day_after' => 'integer',
            'minimum_work_hours' => 'decimal:2',
            'is_flexible' => 'boolean',
            'is_night_shift' => 'boolean',
            'status' => 'boolean',
        ];
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function employeeAssignments()
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }
}
