import { useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

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
    transfer = null,
    currentAssignment = null,
    submitRoute,
    method = 'post',
    isChange = false,
}) {
    const resolvedEmployee = transfer?.employee ?? employee;

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
        };

        return map;
    }, [companies, branches, clusters, divisions, departments, sections, units]);

  
    const { data, setData, put, post, processing, errors } = useForm({
        employee_id: resolvedEmployee?.id ?? '',
        to_company_id: transfer?.to_company_id ?? '',
        to_branch_id: transfer?.to_branch_id ?? '',
        to_cluster_id: transfer?.to_cluster_id ?? '',
        to_division_id: transfer?.to_division_id ?? '',
        to_department_id: transfer?.to_department_id ?? '',
        to_section_id: transfer?.to_section_id ?? '',
        to_unit_id: transfer?.to_unit_id ?? '',
        effective_from: transfer?.effective_from ?? '',
        transfer_reason: transfer?.transfer_reason ?? 'other',
        remarks: transfer?.remarks ?? '',
    });

    useEffect(() => {
        if (!resolvedEmployee) {
            return;
        }

        setSelectedEmployee(resolvedEmployee);
    }, [resolvedEmployee]);

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

    const current = selectedEmployee ?? resolvedEmployee;
  console.log(current);
    return (
        <form onSubmit={submit} className="space-y-6">
            {isChange && currentAssignment && (
                <div className="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-5">
                    <h3 className="text-lg font-semibold text-blue-900">
                        Current Assignment
                    </h3>

                    <div className="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p className="text-sm text-gray-500">Company</p>
                            <p className="font-semibold">
                                {currentAssignment.from_company?.name ?? '-'}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-gray-500">
                                Department
                            </p>

                            <p className="font-semibold">
                                {currentAssignment.fromDepartment?.name ?? '-'}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-gray-500">
                                Effective From
                            </p>

                            <p className="font-semibold">
                                {currentAssignment.effective_from}
                            </p>
                        </div>

                        <div>
                            <p className="text-sm text-gray-500">
                                Effective To
                            </p>

                            <p className="font-semibold">
                                {currentAssignment.effective_to ?? '-'}
                            </p>
                        </div>
                    </div>
                </div>
            )}

            {current && (
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Current Information
                        </h3>

                        <p className="mt-1 text-sm text-slate-500">
                            Current organizational assignment of the employee.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Company
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.company?.name ??
                                    orgLookup.company[current?.company_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Branch
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.branch?.name ??
                                    orgLookup.branch[current?.branch_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Cluster
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.cluster?.name ??
                                    orgLookup.cluster[current?.cluster_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Division
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.division?.name ??
                                    orgLookup.division[current?.division_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Department
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.department?.name ??
                                    orgLookup.department[current?.department_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Section
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.section?.name ??
                                    orgLookup.section[current?.section_id] ??
                                    '-'}
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Unit
                            </label>

                            <p className="mt-1 font-semibold text-slate-900">
                                {current?.unit?.name ??
                                    orgLookup.unit[current?.unit_id] ??
                                    '-'}
                            </p>
                        </div>
                    </div>
                </div>
            )}

            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Transfer To
                    </h3>

                    <p className="mt-1 text-sm text-slate-500">
                        Select the new organizational assignment.
                    </p>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    {method === 'post' && (
                        <div>
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

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Company
                        </label>

                        <select
                            value={data.to_company_id}
                            onChange={(e) =>
                                setData('to_company_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Company</option>

                            {companies.map((company) => (
                                <option key={company.id} value={company.id}>
                                    {company.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_company_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_company_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Branch
                        </label>

                        <select
                            value={data.to_branch_id}
                            onChange={(e) =>
                                setData('to_branch_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Branch</option>

                            {branches.map((branch) => (
                                <option key={branch.id} value={branch.id}>
                                    {branch.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_branch_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_branch_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Cluster
                        </label>

                        <select
                            value={data.to_cluster_id}
                            onChange={(e) =>
                                setData('to_cluster_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Cluster</option>

                            {clusters.map((cluster) => (
                                <option key={cluster.id} value={cluster.id}>
                                    {cluster.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_cluster_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_cluster_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Division
                        </label>

                        <select
                            value={data.to_division_id}
                            onChange={(e) =>
                                setData('to_division_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Division</option>

                            {divisions.map((division) => (
                                <option key={division.id} value={division.id}>
                                    {division.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_division_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_division_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Department
                        </label>

                        <select
                            value={data.to_department_id}
                            onChange={(e) =>
                                setData('to_department_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Department</option>

                            {departments.map((department) => (
                                <option
                                    key={department.id}
                                    value={department.id}
                                >
                                    {department.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_department_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_department_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Section
                        </label>

                        <select
                            value={data.to_section_id}
                            onChange={(e) =>
                                setData('to_section_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Section</option>

                            {sections.map((section) => (
                                <option key={section.id} value={section.id}>
                                    {section.code ?? section.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_section_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_section_id}
                            </p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-medium text-slate-700">
                            Unit
                        </label>

                        <select
                            value={data.to_unit_id}
                            onChange={(e) =>
                                setData('to_unit_id', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="">Select Unit</option>

                            {units.map((unit) => (
                                <option key={unit.id} value={unit.id}>
                                    {unit.code ?? unit.name}
                                </option>
                            ))}
                        </select>

                        {errors.to_unit_id && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.to_unit_id}
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
                            Transfer Reason
                            <span className="text-red-500">*</span>
                        </label>

                        <select
                            value={data.transfer_reason}
                            onChange={(e) =>
                                setData('transfer_reason', e.target.value)
                            }
                            className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                        >
                            <option value="promotion">Promotion</option>
                            <option value="business_requirement">
                                Business Requirement
                            </option>
                            <option value="department_restructure">
                                Department Restructure
                            </option>
                            <option value="branch_relocation">
                                Branch Relocation
                            </option>
                            <option value="employee_request">
                                Employee Request
                            </option>
                            <option value="temporary_assignment">
                                Temporary Assignment
                            </option>
                            <option value="project_assignment">
                                Project Assignment
                            </option>
                            <option value="administrative_decision">
                                Administrative Decision
                            </option>
                            <option value="other">Other</option>
                        </select>

                        {errors.transfer_reason && (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.transfer_reason}
                            </p>
                        )}
                    </div>

                    <div className="lg:col-span-2">
                        <label className="block text-sm font-medium text-slate-700">
                            Remarks
                        </label>

                        <textarea
                            value={data.remarks}
                            onChange={(e) =>
                                setData('remarks', e.target.value)
                            }
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
                    href={route('hr.transfers.index')}
                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    disabled={processing}
                    className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                >
                    {processing ? 'Saving...' : method === 'post' ? 'Create Transfer' : 'Update Transfer'}
                </button>
            </div>
        </form>
    );
}
