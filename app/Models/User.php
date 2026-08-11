<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

    use App\Enums\EmploymentMovementType;

    #[Fillable(['name', 'email', 'password', 'employee_id', 'status', 'company_id', 'branch_id', 'cluster_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'designation_id', 'employment_type_id', 'reporting_manager_id', 'joining_date'])]
    #[Hidden(['password', 'remember_token'])]
    class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_HR = 'hr';

    public const ROLE_EMPLOYEE = 'employee';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'joining_date' => 'date',
        ];
    }

    public function getJoiningDateAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }
        return \Carbon\Carbon::parse($value)->format('d-m-Y');
    }

    public function primaryRole(): ?string
    {
        return match (true) {
            $this->hasRole(self::ROLE_ADMIN) => self::ROLE_ADMIN,
            $this->hasRole(self::ROLE_HR) => self::ROLE_HR,
            $this->hasRole(self::ROLE_EMPLOYEE) => self::ROLE_EMPLOYEE,
            default => $this->getRoleNames()->first(),
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this->primaryRole()) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_HR => 'hr.dashboard',
            default => 'employee.dashboard',
        };
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    public function employeeLifecycleEvents(): HasMany
    {
        return $this->hasMany(EmployeeLifecycleEvent::class);
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'employee_id');
    }

    public function masterDataItems(): BelongsToMany
    {
        return $this->belongsToMany(MasterDataItem::class, 'employee_master_data_item')
            ->withTimestamps();
    }

    // public function department(): BelongsTo
    // {
    //     return $this->belongsTo(Department::class);
    // }

    public function shiftAssignments()
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function currentShiftAssignment()
    {
        return $this->hasOne(EmployeeShiftAssignment::class)
            ->where('is_current', true);
    }


    public function weeklyOffAssignments()
    {
        return $this->hasMany(
            EmployeeWeeklyOffAssignment::class
        );
    }

    public function currentWeeklyOffAssignment()
    {
        return $this->hasOne(
            EmployeeWeeklyOffAssignment::class
        )->where('is_current', true);
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function swapRequests(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'requester_id');
    }

    public function reportingManagerAssignments(): HasMany
    {
        return $this->hasMany(EmployeeReportingManagerAssignment::class);
    }

    public function currentReportingManagerAssignment(): HasOne
    {
        return $this->hasOne(EmployeeReportingManagerAssignment::class)
            ->where('is_current', true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'company_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'branch_id');
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'cluster_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'division_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'department_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'section_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'unit_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(EmployeeTransfer::class);
    }

    public function currentTransfer(): HasOne
    {
        return $this->hasOne(EmployeeTransfer::class)
            ->where('approval_status', 'approved')
            ->whereNull('effective_to')
            ->latest('effective_from');
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmployeeEmploymentHistory::class);
    }

    public function currentEmploymentHistory(): HasOne
    {
        return $this->hasOne(EmployeeEmploymentHistory::class)
            ->whereNull('effective_to')
            ->latest('effective_from');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'designation_id');
    }

    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(MasterDataItem::class, 'employment_type_id');
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_manager_id');
    }

    public function populateOrgFromPivot(\Illuminate\Support\Collection $pivotItems): void
    {
        $grouped = $pivotItems->groupBy('category');

        $fields = ['company', 'branch', 'cluster', 'division', 'department', 'section', 'unit'];

        foreach ($fields as $field) {
            $fkField = "{$field}_id";

            if (!$this->getAttribute($fkField)) {
                $pivotItem = $grouped->get($field)?->first();

                if ($pivotItem) {
                    $this->setAttribute($fkField, $pivotItem->id);
                    $this->setRelation($field, $pivotItem);
                }
            }
        }
    }

    public function hasSubsequentEmploymentMovement(): bool
    {
        return $this->employmentHistories()
            ->where('event_type', '!=', EmploymentMovementType::InitialAppointment->value)
            ->exists();
    }
}
