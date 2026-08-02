<?php

namespace App\Enums;

enum ApprovalWorkflowStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
