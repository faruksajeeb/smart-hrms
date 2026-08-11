<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Castable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'from_company_id',
    'to_company_id',
    'from_branch_id',
    'to_branch_id',
    'from_cluster_id',
    'to_cluster_id',
    'from_division_id',
    'to_division_id',
    'from_department_id',
    'to_department_id',
    'from_section_id',
    'to_section_id',
    'from_unit_id',
    'to_unit_id',
    'effective_from',
    'effective_to',
    'transfer_reason',
    'remarks',
    'approval_status',
    'approved_by',
    'approved_at',
])]
class EmployeeTransfer extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fromCompany(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_company_id');
    }

    public function toCompany(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_company_id');
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_branch_id');
    }

    public function fromCluster(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_cluster_id');
    }

    public function toCluster(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_cluster_id');
    }

    public function fromDivision(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_division_id');
    }

    public function toDivision(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_division_id');
    }

    public function fromDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_department_id');
    }

    public function toDepartment(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_department_id');
    }

    public function fromSection(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_section_id');
    }

    public function toSection(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_section_id');
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'to_unit_id');
    }

    #accessors for effective_from and effective_to to format the date in d-m-Y format
    public function getEffectiveFromAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    public function getEffectiveToAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    public function getApprovedAtAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
    }
}
