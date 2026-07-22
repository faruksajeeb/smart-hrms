import { useEffect } from 'react';
import SecondaryButton from '@/Components/SecondaryButton';

export default function SummaryModal({ summary, onClose }) {
    useEffect(() => {
        if (summary) {
            document.body.style.overflow = 'hidden';
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [summary]);

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
            <div className="absolute inset-0 bg-gray-500/75" onClick={onClose} />

            <div className="relative w-full max-w-2xl transform overflow-hidden rounded-lg bg-white shadow-xl transition-all">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-950">
                        Assignment Summary
                    </h3>
                    <p className="mt-1 text-sm text-slate-500">
                        Bulk shift and weekly off assignment completed.
                    </p>
                </div>

                <div className="p-6">
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                        <SummaryCard label="Total Selected" value={summary.total_selected} />
                        <SummaryCard label="Assigned" value={summary.success_count} color="emerald" />
                        <SummaryCard label="Skipped" value={summary.skipped_count} color="amber" />
                        <SummaryCard label="Failed" value={summary.failed_count} color="rose" />
                    </div>

                    {(summary.skipped?.length > 0 || summary.failures?.length > 0) && (
                        <div className="mt-6 max-h-64 space-y-4 overflow-y-auto">
                            {summary.skipped?.length > 0 && (
                                <div>
                                    <h4 className="text-sm font-semibold uppercase tracking-wide text-slate-400">
                                        Skipped Employees
                                    </h4>
                                    <div className="mt-2 divide-y divide-slate-100 rounded-xl border border-slate-200">
                                        {summary.skipped.map((item, index) => (
                                            <div key={index} className="flex items-center justify-between px-4 py-3">
                                                <p className="text-sm font-medium text-slate-700">
                                                    Employee ID: {item.employee_id}
                                                </p>
                                                <p className="text-sm text-slate-500">
                                                    {item.reason}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {summary.failures?.length > 0 && (
                                <div>
                                    <h4 className="text-sm font-semibold uppercase tracking-wide text-slate-400">
                                        Failed Employees
                                    </h4>
                                    <div className="mt-2 divide-y divide-slate-100 rounded-xl border border-rose-200 bg-rose-50">
                                        {summary.failures.map((item, index) => (
                                            <div key={index} className="flex items-center justify-between px-4 py-3">
                                                <p className="text-sm font-medium text-slate-700">
                                                    Employee ID: {item.employee_id}
                                                </p>
                                                <p className="text-sm text-rose-600">
                                                    {item.reason}
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                <div className="flex justify-end border-t border-slate-200 px-6 py-4">
                    <SecondaryButton onClick={onClose}>Close</SecondaryButton>
                </div>
            </div>
        </div>
    );
}

function SummaryCard({ label, value, color = 'slate' }) {
    const colorClasses = {
        slate: 'bg-slate-50 text-slate-900',
        emerald: 'bg-emerald-50 text-emerald-700',
        amber: 'bg-amber-50 text-amber-700',
        rose: 'bg-rose-50 text-rose-700',
    };

    return (
        <div className={`rounded-xl px-4 py-3 ${colorClasses[color]}`}>
            <p className="text-xs font-medium uppercase tracking-wide opacity-70">
                {label}
            </p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}
