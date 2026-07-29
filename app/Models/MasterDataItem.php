<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'category',
    'parent_id',
    'code',
    'name',
    'description',
    'metadata',
    'status',
    'sort_order',
])]
class MasterDataItem extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const CATEGORY_COMPANY = 'company';

    public const CATEGORY_BRANCH = 'branch';

    public const CATEGORY_DIVISION = 'division';

    public const CATEGORY_DEPARTMENT = 'department';

    public const CATEGORY_CLUSTER = 'cluster';

    public const CATEGORY_SECTION = 'section';

    public const CATEGORY_UNIT = 'unit';

    public const CATEGORY_COST_CENTER = 'cost_center';

    public const CATEGORY_LOCATION = 'location';

    public const CATEGORY_DESIGNATION = 'designation';

    public const CATEGORY_RELIGION = 'religion';

    public const CATEGORY_BLOOD_GROUP = 'blood_group';

    public const CATEGORY_MARITAL_STATUS = 'marital_status';

    public const CATEGORY_BANK = 'bank';

    public const CATEGORY_DISTRICT = 'district';

    public const CATEGORY_EMPLOYEE_TYPE = 'employee_type';

    public const CATEGORY_QUALIFICATION = 'qualification';

    public const CATEGORY_PAY_TYPE = 'pay_type';

    public const CATEGORY_CITY = 'city';

    public const CATEGORY_RELATIVE = 'relative';

    public const CATEGORY_JOB_GRADE = 'job_grade';

    public const CATEGORY_LEAVE_TYPE = 'leave_type';

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_master_data_item')
            ->withTimestamps();
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_COMPANY => 'Company',
            self::CATEGORY_BRANCH => 'Branch',
            self::CATEGORY_CLUSTER => 'Cluster',
            self::CATEGORY_DIVISION => 'Division',
            self::CATEGORY_DEPARTMENT => 'Department',
            self::CATEGORY_SECTION => 'Section',
            self::CATEGORY_UNIT => 'Unit',
            self::CATEGORY_COST_CENTER => 'Cost Center',
            self::CATEGORY_LOCATION => 'Location',
            self::CATEGORY_DESIGNATION => 'Designation',
            self::CATEGORY_RELIGION => 'Religion',
            self::CATEGORY_BLOOD_GROUP => 'Blood Group',
            self::CATEGORY_MARITAL_STATUS => 'Marital Status',
            self::CATEGORY_BANK => 'Bank',
            self::CATEGORY_DISTRICT => 'District',
            self::CATEGORY_EMPLOYEE_TYPE => 'Type of Employee',
            self::CATEGORY_QUALIFICATION => 'Qualification',
            self::CATEGORY_PAY_TYPE => 'Pay Type',
            self::CATEGORY_CITY => 'City',
            self::CATEGORY_RELATIVE => 'Relative',
            self::CATEGORY_JOB_GRADE => 'Job Grade',
            self::CATEGORY_LEAVE_TYPE => 'Leave Type',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function parentCategories(): array
    {
        return [
            self::CATEGORY_BRANCH => [self::CATEGORY_COMPANY],
            self::CATEGORY_CLUSTER => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH],
            self::CATEGORY_DIVISION => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH, self::CATEGORY_CLUSTER],
            self::CATEGORY_DEPARTMENT => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH, self::CATEGORY_CLUSTER, self::CATEGORY_DIVISION],
            self::CATEGORY_SECTION => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH, self::CATEGORY_CLUSTER, self::CATEGORY_DIVISION, self::CATEGORY_DEPARTMENT],
            self::CATEGORY_UNIT => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH, self::CATEGORY_CLUSTER, self::CATEGORY_DIVISION, self::CATEGORY_DEPARTMENT, self::CATEGORY_SECTION],
            self::CATEGORY_COST_CENTER => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH],
            self::CATEGORY_LOCATION => [self::CATEGORY_COMPANY, self::CATEGORY_BRANCH],
            self::CATEGORY_DESIGNATION => [self::CATEGORY_DEPARTMENT],
            self::CATEGORY_CITY => [self::CATEGORY_DISTRICT],
        ];
    }
}
