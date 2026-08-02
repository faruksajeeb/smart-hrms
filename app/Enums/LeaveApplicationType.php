<?php

namespace App\Enums;

enum LeaveApplicationType: string
{
    case Normal = 'normal';
    case Emergency = 'emergency';

    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal Leave',
            self::Emergency => 'Emergency Leave',
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
