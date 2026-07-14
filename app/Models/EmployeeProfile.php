<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'employment_status',
    'department',
    'designation',
    'employment_type',
    'work_location',
    'phone',
    'date_of_birth',
    'nationality',
    'religion',
    'blood_group',
    'marital_status',
    'qualification',
    'address',
    'skills',
    'experience_summary',
    'joining_date',
    'probation_starts_on',
    'probation_ends_on',
    'probation_status',
    'confirmation_date',
    'leave_policy_name',
    'annual_leave_days',
    'sick_leave_days',
    'casual_leave_days',
    'carry_forward_leave_days',
    'salary_amount',
    'salary_currency',
    'pay_frequency',
    'bank_name',
    'bank_account_number',
    'tax_identifier',
    'emergency_contact_name',
    'emergency_contact_phone',
    'termination_date',
    'termination_type',
    'termination_reason',
    'last_rejoined_on',
    'notes',
])]
class EmployeeProfile extends Model
{
    use HasFactory;

    public const STATUS_ONBOARDING = 'onboarding';

    public const STATUS_PROBATION = 'probation';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_LEFT = 'left';

    public const STATUS_TERMINATED = 'terminated';

    public const STATUS_REJOINED = 'rejoined';

    public const PROBATION_PENDING = 'pending';

    public const PROBATION_CONFIRMED = 'confirmed';

    public const PROBATION_EXTENDED = 'extended';

    public const PROBATION_FAILED = 'failed';

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
            'probation_starts_on' => 'date',
            'probation_ends_on' => 'date',
            'date_of_birth' => 'date',
            'confirmation_date' => 'date',
            'termination_date' => 'date',
            'last_rejoined_on' => 'date',
            'salary_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function salaryDisplay(): Attribute
    {
        return Attribute::get(fn () => $this->salary_amount
            ? "{$this->salary_currency} ".number_format((float) $this->salary_amount, 2)
            : 'Not set');
    }
}
