import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function ImportPreview({ previewData = [], summary = {}, errors = [], effective_date, reason }) {
    const validRows = previewData.filter((item) => item.valid);

    function commit() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route('hr.leave.opening-balances.import.commit');

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrf) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrf;
            form.appendChild(csrfInput);
        }

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'POST';
        form.appendChild(methodInput);

        const rowsInput = document.createElement('input');
        rowsInput.type = 'hidden';
        rowsInput.name = 'rows';
        rowsInput.value = JSON.stringify(validRows);
        form.appendChild(rowsInput);

        const dateInput = document.createElement('input');
        dateInput.type = 'hidden';
        dateInput.name = 'effective_date';
        dateInput.value = effective_date;
        form.appendChild(dateInput);

        if (reason) {
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    return (
        <HRLayout
            heading="Import Preview"
            subheading="Review the imported data before committing."
        >
            <Head title="Import Preview" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Import Summary
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    Total: {summary.total} | Valid: {summary.valid} | Invalid: {summary.invalid}
                                </p>
                            </div>
                            <div className="flex gap-3">
                                <a
                                    href={route('hr.leave.opening-balances.import')}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Back
                                </a>
                                <button
                                    onClick={commit}
                                    disabled={summary.valid === 0}
                                    className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                                >
                                    Commit Import ({summary.valid})
                                </button>
                            </div>
                        </div>
                    </div>

                    {errors.length > 0 && (
                        <div className="border-b border-slate-200 bg-red-50 px-6 py-4">
                            <h4 className="text-sm font-medium text-red-800">Errors</h4>
                            <ul className="mt-2 list-disc list-inside text-sm text-red-700">
                                {errors.map((error, index) => (
                                    <li key={index}>
                                        Row {error.row}: {error.message}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Row</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee Code</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Leave Code</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Leave Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Balance</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Remarks</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-slate-200">
                                {previewData.map((item) => (
                                    <tr key={item.row} className={item.valid ? '' : 'bg-red-50'}>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.row}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.employee_code}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.employee_name}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.leave_code}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.leave_name}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.opening_balance}</td>
                                        <td className="px-6 py-4 text-sm text-slate-900">{item.remarks}</td>
                                        <td className="px-6 py-4 text-sm">
                                            {item.valid ? (
                                                <span className="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    Valid
                                                </span>
                                            ) : (
                                                <span className="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                                    Invalid
                                                </span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
