import { useEffect, useMemo } from 'react';

export default function MovementTypeFields({
    type,
    companies = [],
    branches = [],
    clusters = [],
    divisions = [],
    departments = [],
    sections = [],
    units = [],
    designations = [],
    employmentTypes = [],
    managers = [],
    data,
    setData,
    errors,
    currentEmployee,
    orgLookup,
}) {
    useEffect(() => {
        if (!currentEmployee || type) {
            return;
        }

        const fields = type.fields();

        if (!fields.includes('company') && !data.company_id) {
            setData('company_id', currentEmployee.company_id ?? '');
        }
        if (!fields.includes('branch') && !data.branch_id) {
            setData('branch_id', currentEmployee.branch_id ?? '');
        }
        if (!fields.includes('designation') && !data.designation_id) {
            setData('designation_id', currentEmployee.designation_id ?? '');
        }
        if (!fields.includes('employment_type') && !data.employment_type_id) {
            setData('employment_type_id', currentEmployee.employment_type_id ?? '');
        }
        if (!fields.includes('reporting_manager') && !data.reporting_manager_id) {
            setData('reporting_manager_id', currentEmployee.reporting_manager_id ?? '');
        }
    }, [type, currentEmployee]);

    const renderSelect = (label, field, options, placeholder) => (
        <div>
            <label className="block text-sm font-medium text-slate-700">
                {label}
            </label>

            <select
                value={data[field] ?? ''}
                onChange={(e) => setData(field, e.target.value)}
                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
            >
                <option value="">{placeholder}</option>

                {options.map((option) => (
                    <option key={option.id} value={option.id}>
                        {option.code
                            ? `${option.code} - ${option.name}`
                            : option.name}
                    </option>
                ))}
            </select>

            {errors[field] && (
                <p className="mt-1 text-sm text-red-600">{errors[field]}</p>
            )}
        </div>
    );

    if (!type) {
        return null;
    }

    const fields = type.fields();

    return (
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {fields.includes('company') &&
                renderSelect('Company', 'company_id', companies, 'Select Company')}

            {fields.includes('branch') &&
                renderSelect('Branch', 'branch_id', branches, 'Select Branch')}

            {fields.includes('cluster') &&
                renderSelect('Cluster', 'cluster_id', clusters, 'Select Cluster')}

            {fields.includes('division') &&
                renderSelect('Division', 'division_id', divisions, 'Select Division')}

            {fields.includes('department') &&
                renderSelect('Department', 'department_id', departments, 'Select Department')}

            {fields.includes('section') &&
                renderSelect('Section', 'section_id', sections, 'Select Section')}

            {fields.includes('unit') &&
                renderSelect('Unit', 'unit_id', units, 'Select Unit')}

            {fields.includes('designation') &&
                renderSelect('Designation', 'designation_id', designations, 'Select Designation')}

            {fields.includes('employment_type') &&
                renderSelect('Employment Type', 'employment_type_id', employmentTypes, 'Select Employment Type')}

            {fields.includes('reporting_manager') && (
                <div>
                    <label className="block text-sm font-medium text-slate-700">
                        Reporting Manager
                    </label>

                    <select
                        value={data.reporting_manager_id ?? ''}
                        onChange={(e) =>
                            setData('reporting_manager_id', e.target.value)
                        }
                        className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                    >
                        <option value="">Select Manager</option>

                        {managers.map((manager) => (
                            <option key={manager.id} value={manager.id}>
                                {manager.employee_id} - {manager.name}
                            </option>
                        ))}
                    </select>

                    {errors.reporting_manager_id && (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.reporting_manager_id}
                        </p>
                    )}
                </div>
            )}
        </div>
    );
}
