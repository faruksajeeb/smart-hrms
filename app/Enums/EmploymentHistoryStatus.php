<?php

namespace App\Enums;

enum EmploymentHistoryStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
