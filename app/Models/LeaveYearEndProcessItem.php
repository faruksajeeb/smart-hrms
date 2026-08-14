<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveYearEndProcessItem extends Model
{
    protected $fillable = [
        'year_end_process_id', 'user_id', 'leave_type_id', 'leave_policy_id',
        'previous_balance', 'eligible_carry_forward', 'expired_days',
        'encashment_days', 'closing_balance', 'status', 'remarks',
    ];

    protected function casts(): array
    {
        return [
            'previous_balance' => 'decimal:2', 'eligible_carry_forward' => 'decimal:2',
            'expired_days' => 'decimal:2', 'encashment_days' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }

    public function process(): BelongsTo { return $this->belongsTo(LeaveYearEndProcess::class, 'year_end_process_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class, 'leave_type_id'); }
    public function leavePolicy(): BelongsTo { return $this->belongsTo(LeavePolicy::class, 'leave_policy_id'); }
}
