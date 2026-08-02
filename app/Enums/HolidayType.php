<?php

namespace App\Enums;

enum HolidayType: string
{
    case National = 'national';
    case Religious = 'religious';
    case Company = 'company';
    case Branch = 'branch';
    case Optional = 'optional';

    public function label(): string
    {
        return match ($this) {
            self::National => 'National Holiday',
            self::Religious => 'Religious Holiday',
            self::Company => 'Company Holiday',
            self::Branch => 'Branch Holiday',
            self::Optional => 'Optional Holiday',
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
