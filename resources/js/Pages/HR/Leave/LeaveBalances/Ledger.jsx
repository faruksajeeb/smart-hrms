import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function LedgerComponent({ employee, leaveType, ledger = [], filters = {} }) {
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
            heading={`${employee.name} - ${leaveType.leave_name} Ledger`}
            subheading="Complete audit trail of leave balance transactions"
        >
            <Head title={`${employee.name} - ${leaveType.leave_name} Ledger`} />
            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Link
                        href={route('hr.leave.balances.index')}
                        className="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Back to Balances
                    </Link>
                    <Link
                        href={route('hr.leave.balances.show', { employee: employee.id, leaveType: leaveType.id })}
                        className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                    >
                        View Balance
                    </Link>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                From Date
                            </label>
                            <input
                                type="date"
                                value={filters.from || ''}
                                onChange={(e) => handleFilterChange('from', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                To Date
                            </label>
                            <input
                                type="date"
                                value={filters.to || ''}
                                onChange={(e) => handleFilterChange('to', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Ledger Entries ({ledger.length})
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-slate-50 text-slate-600">
                                <tr>
                                    <th className="px-6 py-3 font-medium">Date</th>
                                    <th className="px-6 py-3 font-medium">Transaction</th>
                                    <th className="px-6 py-3 font-medium">Source</th>
                                    <th className="px-6 py-3 font-medium">Reference</th>
                                    <th className="px-6 py-3 font-medium">Credit</th>
                                    <th className="px-6 py-3 font-medium">Debit</th>
                                    <th className="px-6 py-3 font-medium">Balance</th>
                                    <th className="px-6 py-3 font-medium">Policy</th>
                                    <th className="px-6 py-3 font-medium">Remarks</th>
                                    <th className="px-6 py-3 font-medium">Performed By</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {ledger.length === 0 ? (
                                    <tr>
                                        <td colSpan="10" className="px-6 py-8 text-center text-slate-500">
                                            No ledger entries found.
                                        </td>
                                    </tr>
                                ) : (
                                    ledger.map((entry) => (
                                        <tr key={entry.id} className="hover:bg-slate-50">
                                            <td className="px-6 py-4 text-slate-600">{entry.transaction_date}</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${transactionColors[entry.transaction_type] || 'text-slate-700 bg-slate-50'}`}>
                                                    {transactionLabels[entry.transaction_type] || entry.transaction_type}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">{entry.transaction_source}</td>
                                            <td className="px-6 py-4 text-slate-600">{entry.transaction_reference || '-'}</td>
                                            <td className="px-6 py-4 text-emerald-700">{entry.credit_days > 0 ? Number(entry.credit_days).toFixed(2) : '-'}</td>
                                            <td className="px-6 py-4 text-red-700">{entry.debit_days > 0 ? Number(entry.debit_days).toFixed(2) : '-'}</td>
                                            <td className="px-6 py-4 font-medium text-slate-900">{Number(entry.balance_after).toFixed(2)}</td>
                                            <td className="px-6 py-4 text-slate-600">{entry.leavePolicy?.policy_name || '-'}</td>
                                            <td className="px-6 py-4 text-slate-600">{entry.remarks || '-'}</td>
                                            <td className="px-6 py-4 text-slate-600">{entry.performedBy?.name || '-'}</td>
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