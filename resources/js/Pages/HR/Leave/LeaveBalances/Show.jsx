import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function ShowComponent({ employee, leaveType, balance, breakdown, ledger, policy, eligibility, asOfDate }) {
    const transactionLabels = {
        'opening': 'Opening Balance',
        'accrual': 'Accrual',
        'carry_forward': 'Carry Forward',
        'leave_approved': 'Leave Approved',
        'leave_cancelled': 'Leave Cancelled',
        'adjustment': 'Adjustment',
        'encashment': 'Encashment',
        'expiry': 'Expiry',
    };

    const transactionColors = {
        'opening': 'text-blue-700 bg-blue-50',
        'accrual': 'text-emerald-700 bg-emerald-50',
        'carry_forward': 'text-purple-700 bg-purple-50',
        'leave_approved': 'text-red-700 bg-red-50',
        'leave_cancelled': 'text-amber-700 bg-amber-50',
        'adjustment': 'text-slate-700 bg-slate-50',
        'encashment': 'text-orange-700 bg-orange-50',
        'expiry': 'text-rose-700 bg-rose-50',
    };

    return (
        <HRLayout
            heading={`${employee.name} - ${leaveType.leave_name}`}
            subheading={`Leave balance as of ${asOfDate}`}
        >
            <Head title={`${employee.name} - ${leaveType.leave_name} Balance`} />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link
                        href={route('hr.leave.balances.index')}
                        className="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Back to Balances
                    </Link>
                    <Link
                        href={route('hr.leave.balances.ledger', { employee: employee.id, leaveType: leaveType.id })}
                        className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                    >
                        View Ledger
                    </Link>
                </div>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-1 space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Employee</h3>
                            <div className="mt-4 space-y-3">
                                <div>
                                    <p className="text-xs text-slate-500">Name</p>
                                    <p className="font-medium text-slate-900">{employee.name}</p>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-500">Employee ID</p>
                                    <p className="font-medium text-slate-900">{employee.employee_id}</p>
                                </div>
                                <div>
                                    <p className="text-xs text-slate-500">Status</p>
                                    <span className="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700">
                                        {employee.status}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {policy && (
                            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                <h3 className="text-lg font-semibold text-slate-900">Applied Policy</h3>
                                <div className="mt-4 space-y-3">
                                    <div>
                                        <p className="text-xs text-slate-500">Policy</p>
                                        <p className="font-medium text-slate-900">{policy.policy_name}</p>
                                    </div>
                                    <div>
                                        <p className="text-xs text-slate-500">Effective From</p>
                                        <p className="font-medium text-slate-900">{policy.effective_from}</p>
                                    </div>
                                    {policy.effective_to && (
                                        <div>
                                            <p className="text-xs text-slate-500">Effective To</p>
                                            <p className="font-medium text-slate-900">{policy.effective_to}</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Eligibility</h3>
                            <div className="mt-4">
                                {eligibility.eligible ? (
                                    <span className="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-sm font-semibold text-emerald-700">
                                        Eligible
                                    </span>
                                ) : (
                                    <div className="space-y-2">
                                        <span className="inline-flex rounded-full bg-red-50 px-2 py-1 text-sm font-semibold text-red-700">
                                            Not Eligible
                                        </span>
                                        {eligibility.reasons?.length > 0 && (
                                            <ul className="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">
                                                {eligibility.reasons.map((reason, index) => (
                                                    <li key={index}>{reason}</li>
                                                ))}
                                            </ul>
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="lg:col-span-2 space-y-6">
                        <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                            <h3 className="text-lg font-semibold text-slate-900">Balance Breakdown</h3>
                            <div className="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Opening</p>
                                    <p className="mt-1 text-lg font-semibold text-slate-900">{Number(breakdown.opening || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Accrued</p>
                                    <p className="mt-1 text-lg font-semibold text-emerald-700">+{Number(breakdown.accrued || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Used</p>
                                    <p className="mt-1 text-lg font-semibold text-red-700">-{Number(breakdown.used || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Adjustment</p>
                                    <p className="mt-1 text-lg font-semibold text-slate-900">{Number(breakdown.adjustment || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Carry Forward</p>
                                    <p className="mt-1 text-lg font-semibold text-purple-700">+{Number(breakdown.carry_forward || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Cancelled</p>
                                    <p className="mt-1 text-lg font-semibold text-amber-700">+{Number(breakdown.cancelled || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center">
                                    <p className="text-xs text-slate-500">Expired</p>
                                    <p className="mt-1 text-lg font-semibold text-rose-700">-{Number(breakdown.expired || 0).toFixed(2)}</p>
                                </div>
                                <div className="rounded-xl border border-2 border-sky-200 bg-sky-50 p-4 text-center">
                                    <p className="text-xs text-sky-700">Available</p>
                                    <p className="mt-1 text-xl font-bold text-sky-900">{Number(breakdown.available || 0).toFixed(2)}</p>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                            <div className="border-b border-slate-200 px-6 py-4">
                                <h3 className="text-lg font-semibold text-slate-900">Recent Transactions</h3>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left text-sm">
                                    <thead className="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th className="px-6 py-3 font-medium">Date</th>
                                            <th className="px-6 py-3 font-medium">Transaction</th>
                                            <th className="px-6 py-3 font-medium">Source</th>
                                            <th className="px-6 py-3 font-medium">Credit</th>
                                            <th className="px-6 py-3 font-medium">Debit</th>
                                            <th className="px-6 py-3 font-medium">Balance</th>
                                            <th className="px-6 py-3 font-medium">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100">
                                        {ledger.length === 0 ? (
                                            <tr>
                                                <td colSpan="7" className="px-6 py-8 text-center text-slate-500">
                                                    No transactions found.
                                                </td>
                                            </tr>
                                        ) : (
                                            ledger.slice(-10).reverse().map((entry) => (
                                                <tr key={entry.id} className="hover:bg-slate-50">
                                                    <td className="px-6 py-4 text-slate-600">{entry.transaction_date}</td>
                                                    <td className="px-6 py-4">
                                                        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${transactionColors[entry.transaction_type] || 'text-slate-700 bg-slate-50'}`}>
                                                            {transactionLabels[entry.transaction_type] || entry.transaction_type}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-slate-600">{entry.transaction_source}</td>
                                                    <td className="px-6 py-4 text-emerald-700">{entry.credit_days > 0 ? Number(entry.credit_days).toFixed(2) : '-'}</td>
                                                    <td className="px-6 py-4 text-red-700">{entry.debit_days > 0 ? Number(entry.debit_days).toFixed(2) : '-'}</td>
                                                    <td className="px-6 py-4 font-medium text-slate-900">{Number(entry.balance_after).toFixed(2)}</td>
                                                    <td className="px-6 py-4 text-slate-600">{entry.remarks || '-'}</td>
                                                </tr>
                                            ))
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}