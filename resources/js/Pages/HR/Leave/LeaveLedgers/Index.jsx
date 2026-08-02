import { Head, usePage, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function IndexComponent({ ledgers, employees = [], leaveTypes = [], transactionTypes = [], filters = {} }) {
    const handleFilterChange = (key, value) => {
        const params = new URLSearchParams(window.location.search);
        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }
        window.location.search = params.toString();
    };

    return (
        <HRLayout
            heading="Leave Ledger"
            subheading="View employee leave ledger entries."
        >
            <Head title="Leave Ledger" />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link
                        href={route("hr.leave.ledgers.summary")}
                        className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                    >
                        Leave Balance Summary
                    </Link>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                            </label>
                            <select
                                value={filters.employee_id || ''}
                                onChange={(e) => handleFilterChange('employee_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Employees</option>
                                {employees.map((employee) => (
                                    <option key={employee.id} value={employee.id}>
                                        {employee.employee_id} - {employee.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Type
                            </label>
                            <select
                                value={filters.leave_type_id || ''}
                                onChange={(e) => handleFilterChange('leave_type_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Leave Types</option>
                                {leaveTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.leave_name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Transaction Type
                            </label>
                            <select
                                value={filters.transaction_type || ''}
                                onChange={(e) => handleFilterChange('transaction_type', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Transaction Types</option>
                                {transactionTypes.map((type) => (
                                    <option key={type.value} value={type.value}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Leave Ledger
                        </h2>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Employee
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Leave Type
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Transaction Type
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Date
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Days
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Balance After
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-slate-200">
                                {ledgers.data.length > 0 ? (
                                    <>
                                        {ledgers.data.map((ledger) => (
                                            <tr
                                                key={ledger.id}
                                                className="hover:bg-slate-50"
                                            >
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.employee?.name ||
                                                        "-"}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.leave_type
                                                        ?.leave_name || "-"}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.transaction_type}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.transaction_date}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.days}
                                                </td>
                                                <td className="px-6 py-4 text-sm text-slate-900">
                                                    {ledger.balance_after}
                                                </td>
                                                <td className="px-6 py-4 text-sm font-medium">
                                                    <Link
                                                        href={route(
                                                            "hr.leave.ledgers.show",
                                                            ledger.id,
                                                        )}
                                                        className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                    >
                                                        View
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </>
                                ) : (
                                    <div className="py-10 text-center">
                                        <p className="text-slate-500">
                                            No ledger entries found.
                                        </p>
                                    </div>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
