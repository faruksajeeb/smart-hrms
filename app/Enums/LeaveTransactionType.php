<?php

namespace App\Enums;

enum LeaveTransactionType: string
{
    case Opening = 'opening';
    case Accrual = 'accrual';
    case CarryForward = 'carry_forward';
    case LeaveApproved = 'leave_approved';
    case LeaveCancelled = 'leave_cancelled';
    case Adjustment = 'adjustment';
    case Encashment = 'encashment';
    case Expiry = 'expiry';

    public function label(): string
    {
        return match ($this) {
            self::Opening => 'Opening Balance',
            self::Accrual => 'Accrual',
            self::CarryForward => 'Carry Forward',
            self::LeaveApproved => 'Leave Approved',
            self::LeaveCancelled => 'Leave Cancelled',
            self::Adjustment => 'Adjustment',
            self::Encashment => 'Encashment',
            self::Expiry => 'Expiry',
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
