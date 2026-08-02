import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Edit({ balance, leaveTypes = [], users = [], errors = {} }) {
    const { data, setData, put, processing } = useForm({
        user_id: balance.user_id ?? '',
        leave_type_id: balance.leave_type_id ?? '',
        opening_balance: balance.opening_balance ?? '',
        effective_date: balance.effective_date ?? '',
        remarks: balance.remarks ?? '',
        reason: balance.reason ?? '',
    });

    function submit(e) {
        e.preventDefault();
        put(route('hr.leave.opening-balances.update', balance.id));
    }

    return (
        <HRLayout
            heading="Edit Leave Opening Balance"
            subheading="Update employee leave opening balance."
        >
            <Head title="Edit Leave Opening Balance" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Opening Balance Information
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.user_id}
                                onChange={(e) => setData('user_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Employee</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.employee_id} - {user.name}
                                    </option>
                                ))}
                            </select>
                            {errors.user_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.user_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Type <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.leave_type_id}
                                onChange={(e) => setData('leave_type_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Leave Type</option>
                                {leaveTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.leave_name}
                                    </option>
                                ))}
                            </select>
                            {errors.leave_type_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.leave_type_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Opening Balance (Days) <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                value={data.opening_balance}
                                onChange={(e) => setData('opening_balance', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.opening_balance && (
                                <p className="mt-1 text-sm text-red-600">{errors.opening_balance}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective Date <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                value={data.effective_date}
                                onChange={(e) => setData('effective_date', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.effective_date && (
                                <p className="mt-1 text-sm text-red-600">{errors.effective_date}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Remarks
                            </label>
                            <textarea
                                value={data.remarks}
                                onChange={(e) => setData('remarks', e.target.value)}
                                rows="3"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.remarks && (
                                <p className="mt-1 text-sm text-red-600">{errors.remarks}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Reason
                            </label>
                            <textarea
                                value={data.reason}
                                onChange={(e) => setData('reason', e.target.value)}
                                rows="3"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.reason && (
                                <p className="mt-1 text-sm text-red-600">{errors.reason}</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-end gap-3">
                    <a
                        href={route('hr.leave.opening-balances.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Update Opening Balance'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
