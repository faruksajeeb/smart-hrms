import { useEffect, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import Swal from 'sweetalert2';

import HRLayout from '@/Layouts/HRLayout';

export default function Index({
    transfers,
    employees,
    companies = [],
    branches = [],
    departments = [],
    sections = [],
    statuses = [],
    reasons = [],
    filters = {},
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [companyId, setCompanyId] = useState(filters.company_id ?? '');
    const [branchId, setBranchId] = useState(filters.branch_id ?? '');
    const [departmentId, setDepartmentId] = useState(
        filters.department_id ?? ''
    );
    const [sectionId, setSectionId] = useState(filters.section_id ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [reason, setReason] = useState(filters.reason ?? '');
    const [effectiveFrom, setEffectiveFrom] = useState(
        filters.effective_from ?? ''
    );

    // console.log(transfers);

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            router.get(
                route('hr.transfers.index'),
                {
                    search,
                    company_id: companyId,
                    branch_id: branchId,
                    department_id: departmentId,
                    section_id: sectionId,
                    status,
                    reason,
                    effective_from: effectiveFrom,
                },
                {
                    preserveState: true,
                    replace: true,
                }
            );
        }, 300);

        return () => clearTimeout(delayDebounceFn);
    }, [search, companyId, branchId, departmentId, sectionId, status, reason, effectiveFrom]);

    const deleteTransfer = (id) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to recover this transfer!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                router.delete(route('hr.transfers.destroy', id), {
                    preserveScroll: true,
                    onSuccess: () => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Transfer deleted successfully.',
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
        setStatus('');
        setReason('');
        setEffectiveFrom('');
    };

    const statusBadge = (value) => {
        const map = {
            draft: 'bg-gray-100 text-gray-700',
            pending: 'bg-yellow-100 text-yellow-700',
            approved: 'bg-green-100 text-green-700',
            rejected: 'bg-red-100 text-red-700',
        };

        return (
            <span
                className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${map[value] || 'bg-gray-100 text-gray-700'}`}
            >
                {value.charAt(0).toUpperCase() + value.slice(1)}
            </span>
        );
    };

    return (
        <HRLayout
            heading="Employee Transfers"
            subheading="Manage employee transfers across organizational units."
        >
            <Head title="Employee Transfers" />

            <div className="space-y-6">
                {/* Filters */}
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
                                Status
                            </label>

                            <select
                                value={status}
                                onChange={(e) => setStatus(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Statuses</option>

                                {statuses.map((s) => (
                                    <option key={s.value} value={s.value}>
                                        {s.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Reason
                            </label>

                            <select
                                value={reason}
                                onChange={(e) => setReason(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Reasons</option>

                                {reasons.map((r) => (
                                    <option key={r.value} value={r.value}>
                                        {r.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective From
                            </label>

                            <input
                                type="date"
                                value={effectiveFrom}
                                onChange={(e) =>
                                    setEffectiveFrom(e.target.value)
                                }
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

                {/* Transfer List */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Transfers
                            </h2>
                            <Link
                                href={route('hr.transfers.create')}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                + New Transfer
                            </Link>
                        </div>

                        {transfers.data.length > 0 ? (
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200">
                                    <thead className="bg-slate-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Employee
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Company
                                            </th>
                                             <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Branch
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Current Department
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                                Effective From
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
                                        {transfers.data.map((transfer) => (
                                            <tr
                                                key={transfer.id}
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    <div>
                                                        <p className="font-medium">
                                                            {transfer.employee?.name}
                                                        </p>
                                                        <p className="text-xs text-slate-500">
                                                            {transfer.employee?.employee_id}
                                                        </p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {transfer.to_company?.name ?? '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {transfer.to_branch?.name ?? '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {transfer.to_department?.name ??
                                                        '-'}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {transfer.effective_from}
                                                </td>
                                                <td className="px-6 py-4 text-sm">
                                                    {statusBadge(
                                                        transfer.approval_status
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                    <Link
                                                        href={route(
                                                            'hr.transfers.show',
                                                            transfer.id
                                                        )}
                                                        className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                    >
                                                        View
                                                    </Link>

                                                    <Link
                                                        href={route(
                                                            'hr.transfers.history',
                                                            transfer.employee?.id
                                                        )}
                                                        className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                    >
                                                        History
                                                    </Link>

                                                    {transfer.approval_status === 'approved' && (
                                                        <span className="text-xs text-slate-400">
                                                            Approved
                                                        </span>
                                                    )}

                                                    {transfer.approval_status !== 'approved' && (
                                                        <button
                                                            onClick={() =>
                                                                deleteTransfer(
                                                                    transfer.id
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
                                    No transfers found.
                                </p>
                            </div>
                        )}

                        {/* Pagination */}
                        {transfers.last_page > 1 && (
                            <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
                                <div className="text-sm text-slate-600">
                                    Showing{' '}
                                    <span className="font-medium">
                                        {transfers.from}
                                    </span>{' '}
                                    to{' '}
                                    <span className="font-medium">
                                        {transfers.to}
                                    </span>{' '}
                                    of{' '}
                                    <span className="font-medium">
                                        {transfers.total}
                                    </span>{' '}
                                    transfers
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {transfers.links.map((link, index) => (
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
