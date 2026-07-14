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

#[Fillable(['name', 'email', 'password', 'employee_id', 'status'])]
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
        ];
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
}
