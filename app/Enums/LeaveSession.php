<?php

namespace App\Enums;

enum LeaveSession: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Morning',
            self::Afternoon => 'Afternoon',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $session) => ['value' => $session->value, 'label' => $session->label()],
            self::cases()
        );
    }
}
