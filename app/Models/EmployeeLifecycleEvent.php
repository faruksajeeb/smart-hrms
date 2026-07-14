<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'created_by',
    'type',
    'effective_on',
    'title',
    'notes',
    'payload',
])]
class EmployeeLifecycleEvent extends Model
{
    use HasFactory;

    public const TYPE_ONBOARDING = 'onboarding';

    public const TYPE_PROBATION = 'probation_setup';

    public const TYPE_LEAVE = 'leave_setup';

    public const TYPE_SALARY = 'salary_setup';

    public const TYPE_TERMINATION = 'termination';

    public const TYPE_REJOIN = 'rejoin';

    public const TYPE_PROFILE = 'profile_update';

    protected function casts(): array
    {
        return [
            'effective_on' => 'date',
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
