<?php

namespace App\Services\HR\Leave;

use App\Models\LeavePolicy;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

class LeaveEligibilityService
{
    public function __construct(
        protected LeavePolicyResolver $policyResolver,
    ) {}

    /**
     * Check if an employee is eligible for a specific leave type on a specific date.
     *
     * @param User $employee
     * @param int $leaveTypeId
     * @param Carbon $date
     * @return array
     */
    public function canApply(User $employee, int $leaveTypeId, Carbon $date): array
    {
        $reasons = [];

        // 1. Employee exists and is active
        if (!$employee || $employee->status !== User::STATUS_ACTIVE) {
            $reasons[] = 'Employee is not active.';
            return $this->result(false, $reasons);
        }

        // 2. Employee has valid employment status (employment history)
        $employmentHistory = $this->getEffectiveEmploymentHistory($employee, $date);
        if (!$employmentHistory) {
            $reasons[] = 'No active employment history found for the requested date.';
            return $this->result(false, $reasons);
        }

        // 3. Employee has an applicable leave policy assignment
        $assignment = $this->policyResolver->resolve($employee, $date);
        if (!$assignment) {
            $reasons[] = 'No applicable leave policy assignment found for the employee on the requested date.';
            return $this->result(false, $reasons);
        }

        // 4. Leave policy is active
        $policy = $assignment->policy;
        if (!$policy || $policy->status?->value !== 'active') {
            $reasons[] = 'Leave policy is not active.';
            return $this->result(false, $reasons);
        }

        // 5. Leave policy is effective on the requested date
        if ($policy->effective_from && $date->lt(Carbon::parse($policy->effective_from))) {
            $reasons[] = 'Leave policy is not yet effective on the requested date.';
            return $this->result(false, $reasons);
        }
        if ($policy->effective_to && $date->gt(Carbon::parse($policy->effective_to))) {
            $reasons[] = 'Leave policy has expired on the requested date.';
            return $this->result(false, $reasons);
        }

        // 6. Leave policy contains the requested leave type
        $policyDetail = $this->policyResolver->getPolicyDetail($policy, $leaveTypeId);
        if (!$policyDetail) {
            $reasons[] = 'Requested leave type is not covered by the employee\'s leave policy.';
            return $this->result(false, $reasons);
        }

        // 7. Leave policy detail is active
        if ($policyDetail->status?->value !== 'active') {
            $reasons[] = 'Leave policy detail for the requested leave type is not active.';
            return $this->result(false, $reasons);
        }

        // 8. Minimum service requirement
        if (! $this->checkMinimumService($employee, $policyDetail, $date)) {
            $reasons[] = 'Employee has not completed the minimum service period required for this leave type.';
        }

        // 9. Probation restriction
        if (! $this->checkProbationAllowed($employee, $policyDetail)) {
            $reasons[] = 'Leave type is not allowed during probation period.';
        }

        // 10. Confirmation restriction
        if (! $this->checkConfirmationAllowed($employee, $policyDetail, $date)) {
            $reasons[] = 'Leave type is only allowed after confirmation.';
        }

        // 11. Gender restriction
        if (! $this->checkGenderRestriction($employee, $policyDetail)) {
            $reasons[] = 'Leave type is restricted by gender.';
        }

        // 12. Marital-status restriction
        if (! $this->checkMaritalStatusRestriction($employee, $policyDetail)) {
            $reasons[] = 'Leave type is restricted by marital status.';
        }

        // 13. Check if employee is within probation period for this leave type
        if ($this->isInProbation($employee, $date) && ! $policyDetail->probation_allowed) {
            $reasons[] = 'Leave type is not allowed during probation period.';
        }

        return $this->result(
            empty($reasons),
            $reasons,
            $policy,
            $policyDetail,
            $assignment
        );
    }

    /**
     * Get the effective employment history for an employee on a specific date.
     */
    private function getEffectiveEmploymentHistory(User $employee, Carbon $date): ?\App\Models\EmployeeEmploymentHistory
    {
        return \App\Models\EmployeeEmploymentHistory::where('user_id', $employee->id)
            ->where('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date->format('Y-m-d'));
            })
            ->orderBy('effective_from', 'desc')
            ->first();
    }

    /**
     * Check minimum service requirement.
     */
    private function checkMinimumService(User $employee, LeavePolicyDetail $policyDetail, Carbon $date): bool
    {
        if (!$policyDetail->minimum_service_months || $policyDetail->minimum_service_months <= 0) {
            return true;
        }

        $joiningDate = $employee->employeeProfile?->joining_date;
        if (!$joiningDate) {
            return false;
        }

        $joining = Carbon::parse($joiningDate);
        $monthsDiff = $joining->diffInMonths($date);

        return $monthsDiff >= $policyDetail->minimum_service_months;
    }

    /**
     * Check if leave is allowed during probation.
     */
    private function checkProbationAllowed(User $employee, LeavePolicyDetail $policyDetail): bool
    {
        if ($policyDetail->probation_allowed === null) {
            return true;
        }

        if (!$policyDetail->probation_allowed) {
            // Check if employee is currently in probation
            return !$this->isInProbation($employee, Carbon::now());
        }

        return true;
    }

    /**
     * Check if leave is allowed after confirmation.
     */
    private function checkConfirmationAllowed(User $employee, LeavePolicyDetail $policyDetail, Carbon $date): bool
    {
        if (!$policyDetail->applicable_after_confirmation) {
            return true;
        }

        $confirmationDate = $employee->employeeProfile?->confirmation_date;
        if (!$confirmationDate) {
            return false;
        }

        return $date->gte(Carbon::parse($confirmationDate));
    }

    /**
     * Check gender restriction.
     */
    private function checkGenderRestriction(User $employee, LeavePolicyDetail $policyDetail): bool
    {
        $restriction = $policyDetail->gender_restriction?->value;
        
        if (!$restriction || $restriction === 'any') {
            return true;
        }

        $gender = $employee->employeeProfile?->gender;
        if (!$gender) {
            return false;
        }

        return strtolower($gender) === strtolower($restriction);
    }

    /**
     * Check marital status restriction.
     */
    private function checkMaritalStatusRestriction(User $employee, LeavePolicyDetail $policyDetail): bool
    {
        $restriction = $policyDetail->marital_status_restriction?->value;
        
        if (!$restriction || $restriction === 'any') {
            return true;
        }

        $maritalStatus = $employee->employeeProfile?->marital_status;
        if (!$maritalStatus) {
            return false;
        }

        return strtolower($maritalStatus) === strtolower($restriction);
    }

    /**
     * Check if employee is in probation period.
     */
    private function isInProbation(User $employee, Carbon $date): bool
    {
        $probationEnds = $employee->employeeProfile?->probation_ends_on;
        if (!$probationEnds) {
            return false;
        }

        return $date->lte(Carbon::parse($probationEnds));
    }

    /**
     * Build the result array.
     */
    private function result(
        bool $eligible,
        array $reasons,
        ?LeavePolicy $policy = null,
        ?LeavePolicyDetail $policyDetail = null,
        ?LeavePolicyAssignment $assignment = null
    ): array {
        return [
            'eligible' => $eligible,
            'reasons' => $reasons,
            'policy' => $policy,
            'policy_detail' => $policyDetail,
            'assignment' => $assignment,
        ];
    }
}