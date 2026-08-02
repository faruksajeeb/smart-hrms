import { useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import { employmentMovementTypeOptions } from '@/Enums/EmploymentMovementType';

export default function Form({
    employee,
    employees = [],
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
    movement = null,
    submitRoute,
    method = 'post',
}) {
    const resolvedEmployee = movement?.employee ?? employee;

    const [selectedEmployee, setSelectedEmployee] = useState(
        resolvedEmployee ?? null
    );

    const orgLookup = useMemo(() => {
        const map = {
            company: Object.fromEntries(companies.map((c) => [c.id, c.name])),
            branch: Object.fromEntries(branches.map((b) => [b.id, b.name])),
            cluster: Object.fromEntries(clusters.map((c) => [c.id, c.name])),
            division: Object.fromEntries(divisions.map((d) => [d.id, d.name])),
            department: Object.fromEntries(departments.map((d) => [d.id, d.name])),
            section: Object.fromEntries(sections.map((s) => [s.id, s.name])),
            unit: Object.fromEntries(units.map((u) => [u.id, u.name])),
            designation: Object.fromEntries(designations.map((d) => [d.id, d.name])),
            employmentType: Object.fromEntries(employmentTypes.map((e) => [e.id, e.name])),
        };

        return map;
    }, [companies, branches, clusters, divisions, departments, sections, units, designations, employmentTypes]);

    const current = selectedEmployee ?? resolvedEmployee;

    const { data, setData, put, post, processing, errors } = useForm({
        employee_id: current?.id ?? '',
        event_type: movement?.event_type ?? '',
        company_id: movement?.company_id ?? current?.company_id ?? '',
        branch_id: movement?.branch_id ?? current?.branch_id ?? '',
        cluster_id: movement?.cluster_id ?? current?.cluster_id ?? '',
        division_id: movement?.division_id ?? current?.division_id ?? '',
        department_id: movement?.department_id ?? current?.department_id ?? '',
        section_id: movement?.section_id ?? current?.section_id ?? '',
        unit_id: movement?.unit_id ?? current?.unit_id ?? '',
        designation_id: movement?.designation_id ?? current?.designation_id ?? '',
        employment_type_id: movement?.employment_type_id ?? current?.employment_type_id ?? '',
        reporting_manager_id: movement?.reporting_manager_id ?? current?.reporting_manager_id ?? '',
        effective_from: movement?.effective_from ?? '',
        effective_to: movement?.effective_to ?? '',
        reason: movement?.reason ?? '',
        remarks: movement?.remarks ?? '',
    });

    useEffect(() => {
        if (!current) {
            return;
        }

        setData({
            employee_id: current.id,
            event_type: movement?.event_type ?? '',
            company_id: movement?.company_id ?? current.company_id ?? '',
            branch_id: movement?.branch_id ?? current.branch_id ?? '',
            cluster_id: movement?.cluster_id ?? current.cluster_id ?? '',
            division_id: movement?.division_id ?? current.division_id ?? '',
            department_id: movement?.department_id ?? current.department_id ?? '',
            section_id: movement?.section_id ?? current.section_id ?? '',
            unit_id: movement?.unit_id ?? current.unit_id ?? '',
            designation_id: movement?.designation_id ?? current.designation_id ?? '',
            employment_type_id: movement?.employment_type_id ?? current.employment_type_id ?? '',
            reporting_manager_id: movement?.reporting_manager_id ?? current.reporting_manager_id ?? '',
            effective_from: movement?.effective_from ?? '',
            effective_to: movement?.effective_to ?? '',
            reason: movement?.reason ?? '',
            remarks: movement?.remarks ?? '',
        });
    }, [current]);

    function handleEmployeeChange(e) {
        const rawValue = e.target.value;
        const id = rawValue ? Number(rawValue) : null;
        setData('employee_id', id || '');
        setSelectedEmployee(
            employees.find((emp) => Number(emp.id) === id) ?? null
        );
    }

    function submit(e) {
        e.preventDefault();

        if (method === 'put') {
            put(submitRoute);
        } else {
            post(submitRoute);
        }
    }

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

    return (
        <form onSubmit={submit} className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Employee
                    </h3>

                    <p className="mt-1 text-sm text-slate-500">
                        Select the employee for this movement.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    {method === 'post' && (
                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                                <span className="text-red-500">*</span>
                            </label>

                            <select
                                value={data.employee_id}
                                onChange={handleEmployeeChange}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Employee</option>

                                {employees.map((emp) => (
                                    <option key={emp.id} value={emp.id}>
                                        {emp.employee_id} - {emp.name}
                                    </option>
                                ))}
                            </select>

                            {errors.employee_id && (
                                <p className="mt-1 text-sm text-red-600">
                                    {errors.employee_id}
                                </p>
                            )}
                        </div>
                    )}

                    {current && (
                        <div className="lg:col-span-2">
                            <div className="rounded-xl border border-blue-200 bg-blue-50 p-4">
                                <h4 className="text-sm font-semibold text-blue-900">
                                    Current Employment Information
                                </h4>

                                <div className="mt-3 grid grid-cols-2 gap-4 md:grid-cols-4">
                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Company
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.company?.name ??
                                                orgLookup.company[current?.company_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Branch
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.branch?.name ??
                                                orgLookup.branch[current?.branch_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Cluster
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.cluster?.name ??
                                                orgLookup.cluster[current?.cluster_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Division
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.division?.name ??
                                                orgLookup.division[current?.division_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Department
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.department?.name ??
                                                orgLookup.department[current?.department_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Section
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.section?.name ??
                                                orgLookup.section[current?.section_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Unit
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.unit?.name ??
                                                orgLookup.unit[current?.unit_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Designation
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.designation?.name ??
                                                orgLookup.designation[current?.designation_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Employment Type
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.employmentType?.name ??
                                                orgLookup.employmentType[current?.employment_type_id] ??
                                                '-'}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-xs text-blue-700">
                                            Reporting Manager
                                        </p>
                                        <p className="text-sm font-medium text-blue-900">
                                            {current?.reportingManager?.name ??
                                                '-'}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Movement Details
                    </h3>

                    <p className="mt-1 text-sm text-slate-500">
                        Update the employment information and save the movement.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div className="lg:col-span-2">
                        <label className="block text-sm font-medium text-slate-700">
                            Movement Type
                        </label>

                        <select
                            value={data.event_type}
                            onChange={(e) => setData('event_type', e.target.value)}
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Movement Type</option>

                            {employmentMovementTypeOptions().map((option) => (
                                <option
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </option>
                            ))}
                        </select>

                        {errors.event_type && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.event_type}
                            </p>
                        )}
                    </div>

                    {renderSelect('Company', 'company_id', companies, 'Select Company')}
                    {renderSelect('Branch', 'branch_id', branches, 'Select Branch')}
                    {renderSelect('Cluster', 'cluster_id', clusters, 'Select Cluster')}
                    {renderSelect('Division', 'division_id', divisions, 'Select Division')}
                    {renderSelect('Department', 'department_id', departments, 'Select Department')}
                    {renderSelect('Section', 'section_id', sections, 'Select Section')}
                    {renderSelect('Unit', 'unit_id', units, 'Select Unit')}
                    {renderSelect('Designation', 'designation_id', designations, 'Select Designation')}
                    {renderSelect('Employment Type', 'employment_type_id', employmentTypes, 'Select Employment Type')}

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

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Effective From
                            <span className="text-red-500">*</span>
                        </label>

                        <input
                            type="date"
                            value={data.effective_from}
                            onChange={(e) =>
                                setData('effective_from', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        />

                        {errors.effective_from && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.effective_from}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Effective To
                        </label>

                        <input
                            type="date"
                            value={data.effective_to}
                            onChange={(e) =>
                                setData('effective_to', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        />

                        {errors.effective_to && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.effective_to}
                            </p>
                        )}
                    </div>

                    <div className="lg:col-span-2">
                        <label className="block text-sm font-medium text-slate-700">
                            Reason
                            <span className="text-red-500">*</span>
                        </label>

                        <input
                            type="text"
                            value={data.reason}
                            onChange={(e) => setData('reason', e.target.value)}
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            placeholder="Reason for this movement"
                        />

                        {errors.reason && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.reason}
                            </p>
                        )}
                    </div>

                    <div className="lg:col-span-2">
                        <label className="block text-sm font-medium text-slate-700">
                            Remarks
                        </label>

                        <textarea
                            value={data.remarks}
                            onChange={(e) => setData('remarks', e.target.value)}
                            rows="3"
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            placeholder="Additional remarks..."
                        />

                        {errors.remarks && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.remarks}
                            </p>
                        )}
                    </div>
                </div>
            </div>

            <div className="flex items-center justify-end gap-3">
                <a
                    href={route('hr.employment-movements.index')}
                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                >
                    {processing
                        ? 'Saving...'
                        : method === 'post'
                          ? 'Record Movement'
                          : 'Update Movement'}
                </button>
            </div>
        </form>
    );
}
