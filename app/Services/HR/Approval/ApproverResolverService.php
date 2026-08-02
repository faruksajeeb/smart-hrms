<?php

namespace App\Services\HR\Approval;

use App\Enums\ApprovalType;
use App\Models\User;
use App\Services\HR\Approval\Resolvers\ApproverResolverInterface;
use App\Services\HR\Approval\Resolvers\BranchManagerResolver;
use App\Services\HR\Approval\Resolvers\CompanyAdminResolver;
use App\Services\HR\Approval\Resolvers\DepartmentHeadResolver;
use App\Services\HR\Approval\Resolvers\HRManagerResolver;
use App\Services\HR\Approval\Resolvers\ReportingManagerResolver;
use App\Services\HR\Approval\Resolvers\RoleResolver;
use App\Services\HR\Approval\Resolvers\SpecificUserResolver;

class ApproverResolverService
{
    public function __construct(
        protected ReportingManagerResolver $reportingManagerResolver,
        protected DepartmentHeadResolver $departmentHeadResolver,
        protected BranchManagerResolver $branchManagerResolver,
        protected HRManagerResolver $hrManagerResolver,
        protected CompanyAdminResolver $companyAdminResolver,
        protected RoleResolver $roleResolver,
        protected SpecificUserResolver $specificUserResolver,
    ) {}

    public function resolve(
        ApprovalType $type,
        User $requestedBy,
        ?array $context = []
    ): ?User {
        $resolver = match ($type) {
            ApprovalType::REPORTING_MANAGER => $this->reportingManagerResolver,
            ApprovalType::DEPARTMENT_HEAD => $this->departmentHeadResolver,
            ApprovalType::BRANCH_MANAGER => $this->branchManagerResolver,
            ApprovalType::HR_MANAGER => $this->hrManagerResolver,
            ApprovalType::COMPANY_ADMIN => $this->companyAdminResolver,
            ApprovalType::ROLE => $this->roleResolver,
            ApprovalType::SPECIFIC_USER => $this->specificUserResolver,
            ApprovalType::DYNAMIC => $this->specificUserResolver,
        };

        return $resolver->resolve($requestedBy, $context);
    }
}
