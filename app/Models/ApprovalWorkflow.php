<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Castable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ApprovalWorkflowStatus;

#[Fillable([
    'workflow_name',
    'workflow_code',
    'module_name',
    'description',
    'status',
    'created_by',
    'updated_by',
])]
class ApprovalWorkflow extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ApprovalWorkflowStatus::class,
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

    public function levels(): HasMany
    {
        return $this->hasMany(ApprovalWorkflowLevel::class, 'workflow_id');
    }

    public function companyBranches(): BelongsToMany
    {
        return $this->belongsToMany(MasterDataItem::class, 'approval_workflow_company_branches', 'workflow_id', 'company_id')
            ->withPivot('branch_id')
            ->withTimestamps();
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(MasterDataItem::class, 'approval_workflow_company_branches', 'workflow_id', 'branch_id')
            ->wherePivot('branch_id', '!=', null)
            ->withPivot('company_id')
            ->withTimestamps();
    }
}
