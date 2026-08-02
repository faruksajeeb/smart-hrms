<?php

namespace App\Enums;

enum AccrualMethod: string
{
    case None = 'none';
    case Monthly = 'monthly';
    case Quarterly = 'quarterly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::None => 'None',
            self::Monthly => 'Monthly',
            self::Quarterly => 'Quarterly',
            self::Yearly => 'Yearly',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $method) => ['value' => $method->value, 'label' => $method->label()],
            self::cases()
        );
    }
}
