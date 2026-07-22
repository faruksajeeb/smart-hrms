import { router } from '@inertiajs/react';
import { useMemo } from 'react';
import Checkbox from '@/Components/Checkbox';
import Pagination from '@/Components/Pagination';
import { usePage } from '@inertiajs/react';

export default function EmployeeTable({ employees, filters = {}, selectedIds = new Set(), onSelectionChange }) {
    const { url } = usePage();
    const currentIds = useMemo(() => new Set((employees.data ?? []).map((e) => e.id)), [employees.data]);

    const toggleSelection = (id) => {
        const next = new Set(selectedIds);
        if (next.has(id)) {
            next.delete(id);
        } else {
            next.add(id);
        }
        onSelectionChange(next);
    };

    const toggleSelectAll = () => {
        const next = new Set(selectedIds);
        const isAllSelected = currentIds.size > 0 && [...currentIds].every((id) => selectedIds.has(id));

        if (isAllSelected) {
            currentIds.forEach((id) => next.delete(id));
        } else {
            currentIds.forEach((id) => next.add(id));
        }
        onSelectionChange(next);
    };

    const clearSelection = () => {
        onSelectionChange(new Set());
    };

    const isAllSelected = currentIds.size > 0 && [...currentIds].every((id) => selectedIds.has(id));
    const isSomeSelected = currentIds.size > 0 && [...currentIds].some((id) => selectedIds.has(id)) && !isAllSelected;

    return (
        <section className="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div className="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 className="text-lg font-semibold text-slate-950">Employees</h2>
                    <p className="mt-0.5 text-sm text-slate-500">
                        Showing {employees.from ?? 0} to {employees.to ?? 0} of {employees.total} employees
                    </p>
                </div>
                <div className="flex items-center gap-3">
                    <button
                        type="button"
                        onClick={toggleSelectAll}
                        className="text-sm font-medium text-sky-700 hover:text-sky-800"
                    >
                        {isAllSelected ? 'Deselect All' : 'Select Visible'}
                    </button>
                    <button
                        type="button"
                        onClick={clearSelection}
                        disabled={selectedIds.size === 0}
                        className="text-sm font-medium text-slate-500 hover:text-slate-700 disabled:opacity-50"
                    >
                        Clear Selection ({selectedIds.size})
                    </button>
                </div>
            </div>

            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-slate-200">
                    <thead className="bg-slate-50">
                        <tr>
                            <th className="px-6 py-3 text-left">
                                <Checkbox
                                    checked={isAllSelected}
                                    onChange={toggleSelectAll}
                                    ref={undefined}
                                />
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Employee ID
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Employee Name
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Company
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Branch
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Department
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Designation
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Current Shift
                            </th>
                            <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Current Weekly Off
                            </th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-200 bg-white">
                        {(employees.data ?? []).map((employee) => (
                            <tr
                                key={employee.id}
                                className={selectedIds.has(employee.id) ? 'bg-sky-50' : 'hover:bg-slate-50'}
                            >
                                <td className="px-6 py-4">
                                    <Checkbox
                                        checked={selectedIds.has(employee.id)}
                                        onChange={() => toggleSelection(employee.id)}
                                        ref={undefined}
                                    />
                                </td>
                                <td className="px-6 py-4 text-sm font-medium text-slate-900">
                                    {employee.employee_id ?? '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-900">
                                    {employee.name}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.company ?? '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.branch ?? '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.department ?? '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.designation ?? '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.current_shift || '-'}
                                </td>
                                <td className="px-6 py-4 text-sm text-slate-600">
                                    {employee.current_weekly_off || '-'}
                                </td>
                            </tr>
                        ))}
                        {(employees.data ?? []).length === 0 && (
                            <tr>
                                <td colSpan={9} className="px-6 py-12 text-center text-sm text-slate-500">
                                    {Object.values(filters).some(Boolean)
                                        ? 'No employees match the selected filters.'
                                        : 'No employees found.'}
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            {employees.last_page > 1 && (
                <div className="border-t border-slate-200 px-6 py-4">
                    <Pagination links={employees.links} />
                </div>
            )}
        </section>
    );
}
