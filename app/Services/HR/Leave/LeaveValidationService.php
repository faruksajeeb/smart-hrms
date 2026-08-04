<?php

namespace App\Services\HR\Leave;

use App\Enums\LeaveApplicationStatus;
use App\Enums\DelegateStatus;
use App\Models\EmployeeEmploymentHistory;
use App\Models\HolidayCalendar;
use App\Models\LeaveApplication;
use App\Models\LeaveBalanceLedger;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyAssignment;
use App\Models\LeavePolicyDetail;
use App\Models\MasterDataItem;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\EmployeeWeeklyOffAssignment;
use Illuminate\Support\Facades\DB;

class LeaveValidationService
{
    public function __construct(
        protected LeaveBalanceService $balanceService,
        protected LeaveCalculationService $calculationService,
    ) {}
    public function resolvePolicy(User $employee, \DateTimeInterface $date): ?LeavePolicyAssignment
    {
        $assignment = $this->getActivePolicyAssignment($employee, $date);

        if ($assignment) {
            return $assignment;
        }

        $user = $employee;
       
        if ($user->employment_type_id) {
            $employmentType = \App\Models\MasterDataItem::find($user->employment_type_id);

            if ($employmentType) {
                $assignment = LeavePolicyAssignment::where('employment_type', $employmentType->code)
                    ->orWhere('employment_type', $employmentType->name)
                    ->where('effective_from', '<=', $date->format('Y-m-d'))
                    ->where(function ($query) use ($date) {
                        $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                    })
                    ->where('status', 'active')
                    ->latest('effective_from')
                    ->first();

                if ($assignment) {
                    return $assignment;
                }
            }
        }

        if ($user->designation_id) {
            $assignment = LeavePolicyAssignment::where('designation_id', $user->designation_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->unit_id) {
            $assignment = LeavePolicyAssignment::where('unit_id', $user->unit_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->section_id) {
            $assignment = LeavePolicyAssignment::where('section_id', $user->section_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->department_id) {
            $assignment = LeavePolicyAssignment::where('department_id', $user->department_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->division_id) {
            $assignment = LeavePolicyAssignment::where('division_id', $user->division_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->branch_id) {
            $assignment = LeavePolicyAssignment::where('branch_id', $user->branch_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        if ($user->company_id) {
            
            $assignment = LeavePolicyAssignment::where('company_id', $user->company_id)
                ->where('effective_from', '<=', $date->format('Y-m-d'))
                ->where(function ($query) use ($date) {
                    $query->whereNull('effective_to')->orWhere('effective_to', '>=', $date->format('Y-m-d'));
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            if ($assignment) {
                return $assignment;
            }
        }

        return null;
    }

    public function validate(User $employee, LeaveType $leaveType, LeavePolicy $policy, \DateTimeInterface $startDate, \DateTimeInterface $endDate, ?LeaveApplication $existingApplication = null): array
    {
        $errors = [];

        if ($employee->status !== User::STATUS_ACTIVE) {
            $errors[] = 'Employee is not active.';
        }

        $employmentHistory = $this->getEffectiveEmploymentHistory($employee, $startDate);
        if (!$employmentHistory) {
            $errors[] = 'No active employment history found for the leave start date.';
        }

        $assignment = $this->getActivePolicyAssignment($employee, $startDate);
        if (!$assignment) {
            $errors[] = 'No active leave policy assignment found for the employee.';
        } elseif ($assignment->leave_policy_id !== $policy->id) {
            $errors[] = 'Selected leave policy is not assigned to the employee.';
        }

        if ($startDate < \Carbon\Carbon::parse($employee->joining_date)) {
            $errors[] = 'Cannot apply for leave before joining date.';
        }

        $resignation = $employmentHistory?->event_type === 'resignation';
        if ($resignation && $endDate > \Carbon\Carbon::parse($employmentHistory->effective_to ?? '9999-12-31')) {
            $errors[] = 'Cannot apply for leave after resignation date.';
        }

        if ($startDate < \Carbon\Carbon::parse($policy->effective_from)) {
            $errors[] = 'Leave start date is before policy effective date.';
        }

        if ($policy->effective_to && $endDate > \Carbon\Carbon::parse($policy->effective_to)) {
            $errors[] = 'Leave end date is after policy expiry date.';
        }

        $balance = $this->balanceService->getBalance($employee, $leaveType, $startDate->format('Y-m-d'));
        if ($balance === null || $balance < 0) {
            $errors[] = 'Leave balance is not available.';
        }

        if ($existingApplication) {
            $overlap = LeaveApplication::where('user_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('id', '!=', $existingApplication->id)
                ->whereIn('status', [
                    LeaveApplicationStatus::Submitted->value,
                    LeaveApplicationStatus::Pending->value,
                    LeaveApplicationStatus::Approved->value,
                ])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                                ->where('end_date', '>=', $endDate->format('Y-m-d'));
                        });
                })
                ->exists();

            if ($overlap) {
                $errors[] = 'Overlapping leave application already exists.';
            }
        } else {
            $overlap = LeaveApplication::where('user_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->whereIn('status', [
                    LeaveApplicationStatus::Submitted->value,
                    LeaveApplicationStatus::Pending->value,
                    LeaveApplicationStatus::Approved->value,
                ])
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                                ->where('end_date', '>=', $endDate->format('Y-m-d'));
                        });
                })
                ->exists();

            if ($overlap) {
                $errors[] = 'Overlapping leave application already exists.';
            }
        }

        $attachmentRequired = $this->isAttachmentRequired($policy, $leaveType);
        if ($attachmentRequired) {
            $requiredAfterDays = $this->getAttachmentRequiredAfterDays($policy, $leaveType);
            $requestedDays = $existingApplication
                ? $existingApplication->requested_days
                : $this->calculateDaysBetween($startDate, $endDate);

            $isRequired = $requiredAfterDays === null || $requestedDays >= $requiredAfterDays;

            if ($isRequired) {
                if (!$existingApplication || $existingApplication->attachments()->count() === 0) {
                    $errors[] = $requiredAfterDays !== null
                        ? "Attachment is required because requested days ({$requestedDays}) exceed {$requiredAfterDays} days."
                        : 'Attachment is required for this leave type.';
                }
            }
        }

        $medicalCertificateRequired = $this->isMedicalCertificateRequired($policy, $leaveType);
        if ($medicalCertificateRequired) {
            if (!$existingApplication || $existingApplication->attachments()->where('mime_type', 'like', 'application/pdf')->count() === 0) {
                $errors[] = 'Medical certificate is required for this leave type.';
            }
        }

        $delegateRequired = $this->isDelegateRequired($policy, $leaveType);
        if ($delegateRequired) {
            if (!$existingApplication || !$existingApplication->delegate_user_id) {
                $errors[] = 'Delegate (Acting Person) is required for this leave type.';
            } else {
                $delegate = User::find($existingApplication->delegate_user_id);
                if (!$delegate || $delegate->status !== User::STATUS_ACTIVE) {
                    $errors[] = 'Selected delegate must be an active employee.';
                }
                if ($delegate->company_id !== $employee->company_id) {
                    $errors[] = 'Delegate must belong to the same company.';
                }
                if ($delegate->branch_id && $employee->branch_id && $delegate->branch_id !== $employee->branch_id) {
                    $errors[] = 'Delegate must belong to the same branch.';
                }
            }
        }

        return $errors;
    }

    public function getEffectiveEmploymentHistory(User $employee, \DateTimeInterface $date): ?EmployeeEmploymentHistory
    {
        return EmployeeEmploymentHistory::where('user_id', $employee->id)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date->format('Y-m-d'));
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    public function getActivePolicyAssignment(User $employee, \DateTimeInterface $date): ?LeavePolicyAssignment
    {
        return LeavePolicyAssignment::where('user_id', $employee->id)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date->format('Y-m-d'));
            })
            ->where('status', 'active')
            ->latest('effective_from')
            ->first();
    }

    private function isAttachmentRequired(LeavePolicy $policy, LeaveType $leaveType): bool
    {
        $detail = $policy->details()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'active')
            ->first();

        return $detail ? (bool) $detail->attachment_required : false;
    }

    private function getAttachmentRequiredAfterDays(LeavePolicy $policy, LeaveType $leaveType): ?float
    {
        $detail = $policy->details()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'active')
            ->first();

        return $detail ? ($detail->attachment_required_after_days ?? null) : null;
    }

    private function calculateDaysBetween(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        return \Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) + 1;
    }

    private function isMedicalCertificateRequired(LeavePolicy $policy, LeaveType $leaveType): bool
    {
        $detail = $policy->details()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'active')
            ->first();

        return $detail ? (bool) $detail->medical_certificate_required : false;
    }

    private function isDelegateRequired(LeavePolicy $policy, LeaveType $leaveType): bool
    {
        $detail = $policy->details()
            ->where('leave_type_id', $leaveType->id)
            ->where('status', 'active')
            ->first();

        return $detail ? (bool) $detail->delegate_required : false;
    }
}
