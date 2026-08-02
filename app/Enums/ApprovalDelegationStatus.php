<?php

namespace App\Enums;

enum ApprovalDelegationStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Expired = 'expired';
}
