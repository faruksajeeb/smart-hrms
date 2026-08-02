<?php

namespace App\Enums;

enum GenderRestriction: string
{
    case Male = 'male';
    case Female = 'female';
    case Any = 'any';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
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
