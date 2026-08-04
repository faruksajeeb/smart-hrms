import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Show({ ledger }) {
    return (
        <HRLayout
            heading="Ledger Entry Details"
            subheading="View leave ledger transaction details."
        >
            <Head title="Ledger Entry Details" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Ledger Entry #{ledger.id}
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {ledger.employee?.name} - {ledger.leave_type?.leave_name}
                                </p>
                            </div>
                            <a
                                href={route('hr.leave.ledgers.index')}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Back
                            </a>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Transaction Type</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.transaction_type}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Transaction Source</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.transaction_source || '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Transaction Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.transaction_date}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Effective Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.effective_date || '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Credit Days</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.credit_days ?? '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Debit Days</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.debit_days ?? '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Balance After</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.balance_after}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Transaction Reference</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.transaction_reference || '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Performed By</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.performedBy?.name || '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Approved By</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.approvedBy?.name || '-'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Processed By</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.processedBy?.name || '-'}</p>
                        </div>

                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">Remarks</h4>
                            <p className="mt-1 font-semibold text-slate-900">{ledger.remarks || '-'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
