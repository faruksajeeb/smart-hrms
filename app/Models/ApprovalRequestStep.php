<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'approval_request_id',
    'workflow_level_id',
    'level_no',
    'approver_id',
    'status',
    'approved_at',
    'remarks',
    'delegated_to',
])]
class ApprovalRequestStep extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => ApprovalStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function approvalRequest(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function workflowLevel(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflowLevel::class, 'workflow_level_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }
}
