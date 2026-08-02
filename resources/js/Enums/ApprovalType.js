export const ApprovalType = Object.freeze({
    ROLE: 'ROLE',
    REPORTING_MANAGER: 'REPORTING_MANAGER',
    DEPARTMENT_HEAD: 'DEPARTMENT_HEAD',
    BRANCH_MANAGER: 'BRANCH_MANAGER',
    HR_MANAGER: 'HR_MANAGER',
    COMPANY_ADMIN: 'COMPANY_ADMIN',
    SPECIFIC_USER: 'SPECIFIC_USER',
    DYNAMIC: 'DYNAMIC',
});

export const ApprovalTypeOptions = [
    { value: 'ROLE', label: 'Role' },
    { value: 'REPORTING_MANAGER', label: 'Reporting Manager' },
    { value: 'DEPARTMENT_HEAD', label: 'Department Head' },
    { value: 'BRANCH_MANAGER', label: 'Branch Manager' },
    { value: 'HR_MANAGER', label: 'HR Manager' },
    { value: 'COMPANY_ADMIN', label: 'Company Admin' },
    { value: 'SPECIFIC_USER', label: 'Specific User' },
    { value: 'DYNAMIC', label: 'Dynamic' },
];

export function approvalTypeOptions() {
    return ApprovalTypeOptions.map((opt) => ({
        value: opt.value,
        label: opt.label,
    }));
}

export function getApprovalTypeLabel(value) {
    const option = ApprovalTypeOptions.find((opt) => opt.value === value);
    return option ? option.label : value;
}
