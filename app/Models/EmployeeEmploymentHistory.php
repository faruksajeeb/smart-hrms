<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\EmploymentHistoryStatus;

#[Fillable([
    'user_id',
    'event_type',
    'company_id',
    'branch_id',
    'cluster_id',
    'division_id',
    'department_id',
    'section_id',
    'unit_id',
    'designation_id',
    'employment_type_id',
    'reporting_manager_id',
    'effective_from',
    'effective_to',
    'reason',
    'remarks',
    'status',
    'created_by',
    'updated_by',
])]
class EmployeeEmploymentHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'status' => EmploymentHistoryStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'branch_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'cluster_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'division_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'department_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'section_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'unit_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'designation_id');
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'employment_type_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

        #setAccessors for effective_from and effective_to to format the date in Y-m-d format
    public function getEffectiveFromAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }

    public function getEffectiveToAttribute($value)
    {
        return $value ? date('Y-m-d', strtotime($value)) : null;
    }
}
