<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\HolidayStatus;

#[Fillable([
    'holiday_id',
    'company_id',
    'branch_id',
    'division_id',
    'department_id',
])]
class HolidayScope extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => HolidayStatus::class,
        ];
    }

    public function holiday(): BelongsTo
    {
        return $this->belongsTo(HolidayCalendar::class, 'holiday_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'branch_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'division_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'department_id');
    }
}
