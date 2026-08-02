<?php

namespace App\Enums;

enum MaritalStatusRestriction: string
{
    case Single = 'single';
    case Married = 'married';
    case Any = 'any';

    public function label(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Married => 'Married',
            self::Any => 'Any',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $restriction) => ['value' => $restriction->value, 'label' => $restriction->label()],
            self::cases()
        );
    }
}
