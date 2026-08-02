<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Enums\LeaveTransactionType;

#[Fillable([
    'user_id',
    'leave_type_id',
    'transaction_type',
    'reference_type',
    'reference_id',
    'transaction_reference',
    'transaction_date',
    'effective_date',
    'days',
    'credit_days',
    'debit_days',
    'balance_after',
    'remarks',
    'transaction_source',
    'company_id',
    'branch_id',
    'division_id',
    'department_id',
    'section_id',
    'unit_id',
    'designation_id',
    'employment_type',
    'leave_policy_id',
    'performed_by',
    'approved_by',
    'processed_by',
    'created_by',
])]
class LeaveBalanceLedger extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'transaction_type' => LeaveTransactionType::class,
            'transaction_date' => 'date',
            'effective_date' => 'date',
            'days' => 'decimal:2',
            'credit_days' => 'decimal:2',
            'debit_days' => 'decimal:2',
            'balance_after' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
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

    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }

    #transaction_date
    public function getTransactionDateAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    #effective_date
    public function getEffectiveDateAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }
}
