import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function IndexComponent({ balances }) {
    return (
        <HRLayout
            heading="Employee Leave Opening Balance"
            subheading="Update employee leave opening balance."
        >
            <Head title="Employee Leave Opening Balance" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Opening Balances
                            </h2>
                            <div className="flex items-center gap-3">
                                <form
                                    action={route("hr.leave.opening-balances.sync")}
                                    method="POST"
                                    className="inline"
                                >
                                    <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                    <button
                                        type="submit"
                                        className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Sync to Ledger
                                    </button>
                                </form>
                                <Link
                                    href={route("hr.leave.opening-balances.import")}
                                    className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Import CSV
                                </Link>
                                <Link
                                    href={route("hr.leave.opening-balances.create")}
                                    className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                                >
                                    + New Opening Balance
                                </Link>
                            </div>
                        </div>
                    </div>

                    {balances.data.length > 0 ? (
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
                                            Opening Balance
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Effective Date
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-slate-200">
                                    {balances.data.map((balance) => (
                                        <tr
                                            key={balance.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {balance.employee?.name || "-"}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {balance.leave_type
                                                    ?.leave_name || "-"}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {balance.opening_balance}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {balance.effective_date}
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                <Link
                                                    href={route(
                                                        "hr.leave.opening-balances.edit",
                                                        balance.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    Edit
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    ) : (
                        <div className="py-10 text-center">
                            <p className="text-slate-500">
                                No opening balances found.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
