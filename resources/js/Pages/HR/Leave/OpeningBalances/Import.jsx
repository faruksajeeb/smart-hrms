import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Import({ leaveTypes = [], errors = {} }) {
    const { data, setData, post, processing, progress } = useForm({
        file: null,
        effective_date: '',
        reason: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('hr.leave.opening-balances.import.preview'), {
            onSuccess: () => {},
        });
    }

    return (
        <HRLayout
            heading="Import Opening Balance"
            subheading="Upload a CSV file to import employee leave opening balances."
        >
            <Head title="Import Opening Balance" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Upload CSV File
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            CSV must contain headers: employee_code, leave_code, opening_balance, remarks
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                CSV File <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="file"
                                accept=".csv,.xlsx,.xls"
                                onChange={(e) => setData('file', e.target.files[0])}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.file && (
                                <p className="mt-1 text-sm text-red-600">{errors.file}</p>
                            )}
                            <a
                                href="/samples/opening_balance_sample.csv"
                                className="mt-2 inline-flex items-center text-sm text-sky-700 hover:text-sky-800"
                            >
                                Download sample CSV
                            </a>
                            {progress && (
                                <div className="mt-2 h-2 w-full rounded-full bg-slate-200">
                                    <div
                                        className="h-2 rounded-full bg-sky-700"
                                        style={{ width: `${progress.percentage}%` }}
                                    />
                                </div>
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
                        {processing ? 'Uploading...' : 'Preview Import'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
