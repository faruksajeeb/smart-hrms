<?php

namespace App\Enums;

enum LeaveDayType: string
{
    case FullDay = 'full_day';
    case HalfDay = 'half_day';

    public function label(): string
    {
        return match ($this) {
            self::FullDay => 'Full Day',
            self::HalfDay => 'Half Day',
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
