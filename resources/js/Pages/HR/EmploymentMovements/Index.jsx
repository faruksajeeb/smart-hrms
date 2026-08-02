import { useEffect, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Swal from 'sweetalert2';
import { getEmploymentMovementTypeLabel } from '@/Enums/EmploymentMovementType';

import HRLayout from '@/Layouts/HRLayout';

export default function Index({
    movements,
    employees,
    companies = [],
    branches = [],
    departments = [],
    sections = [],
    designations = [],
    eventTypes = [],
    filters = {},
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');
    const [branchId, setBranchId] = useState(filters.branch_id ?? '');
    const [departmentId, setDepartmentId] = useState(
        filters.department_id ?? ''
    );
    const [sectionId, setSectionId] = useState(filters.section_id ?? '');
    const [designationId, setDesignationId] = useState(
        filters.designation_id ?? ''
    );
    const [eventType, setEventType] = useState(filters.event_type ?? '');
    const [employee, setEmployee] = useState(filters.employee ?? '');
    const [dateFrom, setDateFrom] = useState(filters.date_from ?? '');
    const [dateTo, setDateTo] = useState(filters.date_to ?? '');

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            router.get(
                route('hr.employment-movements.index'),
                {
                    search,
                    company_id: companyId,
                    branch_id: branchId,
                    department_id: departmentId,
                    section_id: sectionId,
                    designation_id: designationId,
                    event_type: eventType,
                    employee,
                    date_from: dateFrom,
                    date_to: dateTo,
                },
                {
                    preserveState: true,
                    replace: true,
                }
            );
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [search, companyId, branchId, departmentId, sectionId, designationId, eventType, employee, dateFrom, dateTo]);

    const deleteMovement = (id) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this movement!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('hr.employment-movements.destroy', id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Employment movement deleted successfully.',
                            timer: 2000,
                            showConfirmButton: false,
                        });
                    },
                });
            }
        });
    };

    const clearFilters = () => {
        setSearch('');
        setCompanyId('');
        setBranchId('');
        setDepartmentId('');
        setSectionId('');
        setDesignationId('');
        setEventType('');
        setEmployee('');
        setDateFrom('');
        setDateTo('');
    };

    const getEventTypeLabel = (value) => {
        return getEmploymentMovementTypeLabel(value);
    };

    return (
        <HRLayout
            heading="Employment Movements"
            subheading="Manage employee employment movements and history."
        >
            <Head title="Employment Movements" />

            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-4 p-6 lg:grid-cols-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Search
                            </label>

                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Employee ID or Name"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Company
                            </label>

                            <select
                                value={companyId}
                                onChange={(e) => setCompanyId(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Companies</option>

                                {companies.map((company) => (
                                    <option
                                        key={company.id}
                                        value={company.id}
                                    >
                                        {company.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Branch
                            </label>

                            <select
                                value={branchId}
                                onChange={(e) => setBranchId(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Branches</option>

                                {branches.map((branch) => (
                                    <option
                                        key={branch.id}
                                        value={branch.id}
                                    >
                                        {branch.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Department
                            </label>

                            <select
                                value={departmentId}
                                onChange={(e) =>
                                    setDepartmentId(e.target.value)
                                }
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Departments</option>

                                {departments.map((department) => (
                                    <option
                                        key={department.id}
                                        value={department.id}
                                    >
                                        {department.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Section
                            </label>

                            <select
                                value={sectionId}
                                onChange={(e) => setSectionId(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Sections</option>

                                {sections.map((section) => (
                                    <option
                                        key={section.id}
                                        value={section.id}
                                    >
                                        {section.code ?? section.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Designation
                            </label>

                            <select
                                value={designationId}
                                onChange={(e) =>
                                    setDesignationId(e.target.value)
                                }
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Designations</option>

                                {designations.map((designation) => (
                                    <option
                                        key={designation.id}
                                        value={designation.id}
                                    >
                                        {designation.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Movement Type
                            </label>

                            <select
                                value={eventType}
                                onChange={(e) => setEventType(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Types</option>

                                {eventTypes.map((type) => (
                                    <option
                                        key={type.value}
                                        value={type.value}
                                    >
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                            </label>

                            <select
                                value={employee}
                                onChange={(e) => setEmployee(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Employees</option>

                                {employees.map((emp) => (
                                    <option key={emp.id} value={emp.id}>
                                        {emp.employee_id} - {emp.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Date From
                            </label>

                            <input
                                type="date"
                                value={dateFrom}
                                onChange={(e) => setDateFrom(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Date To
                            </label>

                            <input
                                type="date"
                                value={dateTo}
                                onChange={(e) => setDateTo(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>

                        <div className="flex items-end">
                            <button
                                type="button"
                                onClick={clearFilters}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Employment Movements
                            </h2>
                            <Link
                                href={route('hr.employment-movements.create')}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                + New Movement
                            </Link>
                        </div>

                        {movements.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Employee
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current company
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Branch
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Department
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Designation
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Latest Movement
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Effective From
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Effective To
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Status
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-slate-200">
                                        {movements.data.map((movement) => (
                                            <tr
                                                key={movement.id}
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    <div>
                                                        <p className="font-medium">
                                                            {movement.employee?.name}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {movement.employee?.employee_id}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement.company?.name ??
                                                        '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement.branch?.name ??
                                                        '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement?.department?.name ??
                                                        '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement.designation?.name ??
                                                        '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {getEventTypeLabel(movement.event_type)}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement.effective_from}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {movement.effective_to}
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    {movement.effective_to ? (
                                                        <span className="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">
                                                            Closed
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium bg-green-100 text-green-700">
                                                            Active
                                                        </span>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                    <Link
                                                        href={route(
                                                            'hr.employment-movements.show',
                                                            movement.id
                                                        )}
                                                        className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                    >
                                                        View
                                                    </Link>

                                                    <Link
                                                        href={route(
                                                            'hr.employment-movements.history',
                                                            movement.employee?.id
                                                        )}
                                                        className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                    >
                                                        History
                                                    </Link>

                                                    {!movement.effective_to && (
                                                        <button
                                                            onClick={() =>
                                                                deleteMovement(
                                                                    movement.id
                                                                )
                                                            }
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            Delete
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        ) : (
                            <div className="py-10 text-center">
                                <p className="text-slate-500">
                                    No employment movements found.
                                </p>
                            </div>
                        )}

                        {movements.last_page > 1 && (
                            <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
                                <div className="text-sm text-slate-600">
                                    Showing{' '}
                                    <span className="font-medium">
                                        {movements.from}
                                    </span>{' '}
                                    to{' '}
                                    <span className="font-medium">
                                        {movements.to}
                                    </span>{' '}
                                    of{' '}
                                    <span className="font-medium">
                                        {movements.total}
                                    </span>{' '}
                                    movements
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {movements.links.map((link, index) => (
                                        <button
                                            key={index}
                                            disabled={!link.url}
                                            onClick={() =>
                                                router.visit(link.url)
                                            }
                                            className={`px-3 py-1 mx-1 text-sm leading-5 border rounded transition-colors ${
                                                link.active
                                                    ? 'bg-sky-700 border-transparent text-white'
                                                    : 'border-transparent hover:bg-gray-100 hover:text-gray-700'
                                            } ${
                                                !link.url
                                                    ? 'opacity-25 pointer-events-none'
                                                    : ''
                                            }`}
                                        >
                                            {link.label
                                                .replace('&laquo;', '«')
                                                .replace('&raquo;', '»')}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
