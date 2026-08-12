<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Enums\ApprovalStatus;

#[Fillable([
    'workflow_id',
    'module_name',
    'reference_type',
    'reference_id',
    'requested_by',
    'current_level',
    'current_status',
    'submitted_at',
    'completed_at',
    'remarks',
])]
class ApprovalRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'completed_at' => 'datetime',
            'current_status' => ApprovalStatus::class,
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalRequestStep::class);
    }

    public function getCurrentStepAttribute()
    {
        return $this->steps
            ->where('level_no', $this->current_level)
            ->where('status', ApprovalStatus::Pending)
            ->first();
    }

    public function reference(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function getSubmittedAtAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
    }

    public function getCompletedAtAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y H:i');
    }
}
