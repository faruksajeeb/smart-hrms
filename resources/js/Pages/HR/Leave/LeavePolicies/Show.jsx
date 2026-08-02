import { Head } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Show({ policy }) {
    return (
        <HRLayout
            heading="Leave Policy Details"
            subheading={policy.policy_name}
        >
            <Head title="Leave Policy Details" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {policy.policy_name}
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {policy.policy_code}
                                </p>
                            </div>
                            <a
                                href={route('hr.leave.policies.index')}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Back
                            </a>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Description</h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {policy.description || '-'}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Effective From</h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {policy.effective_from}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Effective To</h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {policy.effective_to || '-'}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Status</h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {policy.status}
                            </p>
                        </div>
                    </div>
                </div>

                {policy.details?.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <div className="flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Policy Rules
                                </h3>
                                <a
                                    href={route('hr.leave.policies.details.index', policy.id)}
                                    className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                                >
                                    Manage Rules
                                </a>
                            </div>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Leave Type</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Entitlement</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Accrual</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Carry Forward</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-slate-200">
                                    {policy.details.map((detail) => (
                                        <tr key={detail.id} className="hover:bg-slate-50">
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {detail.leaveType?.leave_name || '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {detail.annual_entitlement ?? '-'}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {detail.accrual_method}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {detail.carry_forward_allowed ? 'Yes' : 'No'}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        detail.status === 'active'
                                                            ? 'bg-green-100 text-green-700'
                                                            : 'bg-gray-100 text-gray-700'
                                                    }`}
                                                >
                                                    {detail.status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
