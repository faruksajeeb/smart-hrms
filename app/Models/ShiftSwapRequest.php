<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSwapRequest extends Model
{
    protected $fillable = [
        'requester_id', 'requester_schedule_id', 'target_user_id', 'target_schedule_id',
        'status', 'reason', 'accepted_by', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function requesterSchedule(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'requester_schedule_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function targetSchedule(): BelongsTo
    {
        return $this->belongsTo(ShiftSchedule::class, 'target_schedule_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}