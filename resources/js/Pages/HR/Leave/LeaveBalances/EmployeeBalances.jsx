import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function EmployeeBalancesComponent({ employee, balances = [], asOfDate }) {
    return (
        <HRLayout
            heading={`${employee.name} - Leave Balances`}
            subheading={`All leave balances as of ${asOfDate}`}
        >
            <Head title={`${employee.name} - Leave Balances`} />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link
                        href={route('hr.leave.balances.index')}
                        className="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Back to Balances
                    </Link>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">Employee Information</h3>
                    <div className="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <div>
                            <p className="text-xs text-slate-500">Name</p>
                            <p className="font-medium text-slate-900">{employee.name}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500">Employee ID</p>
                            <p className="font-medium text-slate-900">{employee.employee_id}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500">Email</p>
                            <p className="font-medium text-slate-900">{employee.email}</p>
                        </div>
                        <div>
                            <p className="text-xs text-slate-500">Status</p>
                            <span className="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                {employee.status}
                            </span>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">Leave Balances</h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-6 py-3 font-medium">Leave Type</th>
                                    <th className="px-6 py-3 font-medium">Code</th>
                                    <th className="px-6 py-3 font-medium">Balance</th>
                                    <th className="px-6 py-3 font-medium">Last Transaction</th>
                                    <th className="px-6 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {balances.length === 0 ? (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-8 text-center text-slate-500">
                                            No leave balances found for this employee.
                                        </td>
                                    </tr>
                                ) : (
                                    balances.map((balance) => (
                                        <tr key={balance.leave_type_id} className="hover:bg-slate-50">
                                            <td className="px-6 py-4 font-medium text-slate-900">
                                                {balance.leave_name}
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {balance.leave_code}
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
                                                <Link
                                                    href={route('hr.leave.balances.show', { employee: employee.id, leaveType: balance.leave_type_id })}
                                                    className="text-sky-700 hover:text-sky-800"
                                                >
                                                    View Details
                                                </Link>
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