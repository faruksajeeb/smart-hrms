<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LeavePolicyAssignmentStatus;

#[Fillable([
    'leave_policy_id',
    'company_id',
    'branch_id',
    'division_id',
    'department_id',
    'section_id',
    'unit_id',
    'designation_id',
    'employment_type',
    'user_id',
    'effective_from',
    'effective_to',
    'remarks',
    'status',
    'created_by',
    'updated_by',
])]
class LeavePolicyAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
            'status' => LeavePolicyAssignmentStatus::class,
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
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
