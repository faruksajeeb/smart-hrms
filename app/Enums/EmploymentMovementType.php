<?php

namespace App\Enums;

enum EmploymentMovementType: string
{
    case InitialAppointment = 'initial_appointment';
    case Transfer = 'transfer';
    case Promotion = 'promotion';
    case Demotion = 'demotion';
    case OrganizationChange = 'organization_change';
    case Confirmation = 'confirmation';
    case EmploymentTypeChange = 'employment_type_change';
    case ReportingManagerChange = 'reporting_manager_change';
    case Reassignment = 'reassignment';

    public function label(): string
    {
        return match ($this) {
            self::InitialAppointment => 'Initial Appointment',
            self::Transfer => 'Transfer',
            self::Promotion => 'Promotion',
            self::Demotion => 'Demotion',
            self::OrganizationChange => 'Organization Change',
            self::Confirmation => 'Confirmation',
            self::EmploymentTypeChange => 'Employment Type Change',
            self::ReportingManagerChange => 'Reporting Manager Change',
            self::Reassignment => 'Reassignment',
        };
    }

    public function fields(): array
    {
        return match ($this) {
            self::InitialAppointment,
            self::Transfer,
            self::OrganizationChange,
            self::Reassignment => [
                'company',
                'branch',
                'cluster',
                'division',
                'department',
                'section',
                'unit',
            ],

            self::Promotion,
            self::Demotion => [
                'designation',
                'employment_type',
            ],

            self::ReportingManagerChange => [
                'reporting_manager',
            ],

            self::Confirmation => [],

            self::EmploymentTypeChange => [
                'employment_type',
            ],
        };
    }

    public static function values(): array
    {
        return array_map(
            fn (self $type) => $type->value,
            self::cases()
        );
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
