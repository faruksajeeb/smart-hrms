import { Head, Link, usePage } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function IndexComponent({ balances = [], employees = [], leaveTypes = [], filters = {} }) {
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
            heading="Leave Balances"
            subheading="View employee leave balances."
        >
            <Head title="Leave Balances" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
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
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-6 py-3 font-medium">Employee</th>
                                    <th className="px-6 py-3 font-medium">Leave Type</th>
                                    <th className="px-6 py-3 font-medium">Balance</th>
                                    <th className="px-6 py-3 font-medium">Last Transaction</th>
                                    <th className="px-6 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {balances.length === 0 ? (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-8 text-center text-slate-500">
                                            No balance records found.
                                        </td>
                                    </tr>
                                ) : (
                                    balances.map((balance) => (
                                        <tr key={`${balance.employee_id}-${balance.leave_type_id}`} className="hover:bg-slate-50">
                                            <td className="px-6 py-4">
                                                <div>
                                                    <p className="font-medium text-slate-900">{balance.employee?.name}</p>
                                                    <p className="text-xs text-slate-500">{balance.employee?.employee_id}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div>
                                                    <p className="font-medium text-slate-900">{balance.leaveType?.leave_name}</p>
                                                    <p className="text-xs text-slate-500">{balance.leaveType?.leave_code}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`font-semibold ${(balance.balance || 0) >= 0 ? 'text-emerald-700' : 'text-red-700'}`}>
                                                    {Number(balance.balance || 0).toFixed(2)}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {balance.last_transaction_date || '-'}
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex gap-2">
                                                    <Link
                                                        href={route('hr.leave.balances.show', { employee: balance.employee_id, leaveType: balance.leave_type_id })}
                                                        className="text-sky-700 hover:text-sky-800"
                                                    >
                                                        View
                                                    </Link>
                                                    <Link
                                                        href={route('hr.leave.balances.ledger', { employee: balance.employee_id, leaveType: balance.leave_type_id })}
                                                        className="text-slate-600 hover:text-slate-800"
                                                    >
                                                        Ledger
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}