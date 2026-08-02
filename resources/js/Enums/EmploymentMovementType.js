export const EmploymentMovementType = Object.freeze({
    InitialAppointment: 'initial_appointment',
    Transfer: 'transfer',
    Promotion: 'promotion',
    Demotion: 'demotion',
    OrganizationChange: 'organization_change',
    Confirmation: 'confirmation',
    EmploymentTypeChange: 'employment_type_change',
    ReportingManagerChange: 'reporting_manager_change',
    Reassignment: 'reassignment',
});

export const EmploymentMovementTypeOptions = [
    { value: 'initial_appointment', label: 'Initial Appointment', fields: ['company', 'branch', 'cluster', 'division', 'department', 'section', 'unit'] },
    { value: 'transfer', label: 'Transfer', fields: ['company', 'branch', 'cluster', 'division', 'department', 'section', 'unit'] },
    { value: 'promotion', label: 'Promotion', fields: ['designation', 'employment_type'] },
    { value: 'demotion', label: 'Demotion', fields: ['designation', 'employment_type'] },
    { value: 'organization_change', label: 'Organization Change', fields: ['company', 'branch', 'cluster', 'division', 'department', 'section', 'unit'] },
    { value: 'confirmation', label: 'Confirmation', fields: [] },
    { value: 'employment_type_change', label: 'Employment Type Change', fields: ['employment_type'] },
    { value: 'reporting_manager_change', label: 'Reporting Manager Change', fields: ['reporting_manager'] },
    { value: 'reassignment', label: 'Reassignment', fields: ['company', 'branch', 'cluster', 'division', 'department', 'section', 'unit'] },
];

export const EmploymentMovementTypeLabels = Object.fromEntries(
    EmploymentMovementTypeOptions.map((option) => [option.value, option.label])
);

export function getEmploymentMovementTypeLabel(value) {
    return EmploymentMovementTypeLabels[value] || value;
}

export function employmentMovementTypeFrom(value) {
    const option = EmploymentMovementTypeOptions.find((opt) => opt.value === value);
    if (!option) return null;
    return {
        value: option.value,
        label: () => option.label,
        fields: () => option.fields,
    };
}

export function employmentMovementTypeOptions() {
    return EmploymentMovementTypeOptions.map((opt) => ({
        value: opt.value,
        label: opt.label,
    }));
}
