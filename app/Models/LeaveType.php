<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LeaveTypeStatus;

#[Fillable([
    'leave_name',
    'leave_code',
    'description',
    'is_paid',
    'display_color',
    'display_order',
    'status',
    'created_by',
    'updated_by',
])]
class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'status' => LeaveTypeStatus::class,
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

    public function policyDetails(): HasMany
    {
        return $this->hasMany(LeavePolicyDetail::class);
    }

    public function openingBalances(): HasMany
    {
        return $this->hasMany(LeaveOpeningBalance::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LeaveBalanceLedger::class);
    }
}
