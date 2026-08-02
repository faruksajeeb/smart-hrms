<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ApprovalWorkflowLevelStatus;

#[Fillable([
    'workflow_id',
    'level_no',
    'approval_type',
    'role_type',
    'role_id',
    'specific_user_id',
    'dynamic_resolver',
    'minimum_approvals',
    'can_reject',
    'can_delegate',
    'can_skip',
    'is_final_level',
    'status',
    'created_by',
    'updated_by',
])]
class ApprovalWorkflowLevel extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'can_reject' => 'boolean',
            'can_delegate' => 'boolean',
            'can_skip' => 'boolean',
            'is_final_level' => 'boolean',
            'minimum_approvals' => 'integer',
            'level_no' => 'integer',
            'status' => ApprovalWorkflowLevelStatus::class,
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function specificUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'specific_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
