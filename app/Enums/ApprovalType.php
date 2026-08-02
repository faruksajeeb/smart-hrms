<?php

namespace App\Enums;

enum ApprovalType: string
{
    case ROLE = 'ROLE';
    case REPORTING_MANAGER = 'REPORTING_MANAGER';
    case DEPARTMENT_HEAD = 'DEPARTMENT_HEAD';
    case BRANCH_MANAGER = 'BRANCH_MANAGER';
    case HR_MANAGER = 'HR_MANAGER';
    case COMPANY_ADMIN = 'COMPANY_ADMIN';
    case SPECIFIC_USER = 'SPECIFIC_USER';
    case DYNAMIC = 'DYNAMIC';

    public function label(): string
    {
        return match ($this) {
            self::ROLE => 'Role',
            self::REPORTING_MANAGER => 'Reporting Manager',
            self::DEPARTMENT_HEAD => 'Department Head',
            self::BRANCH_MANAGER => 'Branch Manager',
            self::HR_MANAGER => 'HR Manager',
            self::COMPANY_ADMIN => 'Company Admin',
            self::SPECIFIC_USER => 'Specific User',
            self::DYNAMIC => 'Dynamic',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
