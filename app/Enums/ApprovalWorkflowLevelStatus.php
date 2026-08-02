<?php

namespace App\Enums;

enum ApprovalWorkflowLevelStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
