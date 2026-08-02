<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\HolidayStatus;

#[Fillable([
    'holiday_name',
    'holiday_code',
    'holiday_date',
    'holiday_type',
    'is_recurring',
    'description',
    'status',
    'created_by',
    'updated_by',
])]
class HolidayCalendar extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
            'is_recurring' => 'boolean',
            'status' => \App\Enums\HolidayStatus::class,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopes(): HasMany
    {
        return $this->hasMany(HolidayScope::class);
    }

            #holiday_date
    public function getHolidayDateAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');  
    }
}
