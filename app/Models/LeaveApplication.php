<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\LeaveApplicationStatus;
use App\Enums\LeaveApplicationType;
use App\Enums\DelegateStatus;

#[Fillable([
    'application_no',
    'user_id',
    'leave_policy_id',
    'leave_type_id',
    'application_type',
    'start_date',
    'end_date',
    'total_days',
    'requested_days',
    'is_half_day',
    'half_day_session',
    'is_emergency',
    'reason',
    'status',
    'submitted_at',
    'cancelled_at',
    'withdrawn_at',
    'approved_at',
    'rejected_at',
    'remarks',
    'delegate_user_id',
    'delegate_status',
    'delegate_responded_at',
    'delegate_remarks',
    'created_by',
    'updated_by',
])]
class LeaveApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_half_day' => 'boolean',
            'is_emergency' => 'boolean',
            'total_days' => 'decimal:2',
            'requested_days' => 'decimal:2',
            'status' => LeaveApplicationStatus::class,
            'application_type' => LeaveApplicationType::class,
            'delegate_status' => DelegateStatus::class,
            'submitted_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'withdrawn_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'delegate_responded_at' => 'datetime',
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

    public function leavePolicy(): BelongsTo
    {
        return $this->belongsTo(LeavePolicy::class, 'leave_policy_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function delegate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegate_user_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(LeaveApplicationDay::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LeaveAttachment::class);
    }

    public function canEdit(): bool
    {
        return $this->status === LeaveApplicationStatus::Draft;
    }

    public function canSubmit(): bool
    {
        return $this->status === LeaveApplicationStatus::Draft;
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [
            LeaveApplicationStatus::Submitted,
            LeaveApplicationStatus::Pending,
            LeaveApplicationStatus::Approved,
        ]);
    }

    public function canWithdraw(): bool
    {
        return $this->status === LeaveApplicationStatus::Pending;
    }

    public function canDelete(): bool
    {
        return $this->status === LeaveApplicationStatus::Draft;
    }
}
