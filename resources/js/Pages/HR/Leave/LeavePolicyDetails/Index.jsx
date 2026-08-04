import { Head, Link } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function IndexComponent({ policy }) {
    return (
        <HRLayout
                    heading="Leave Policy Details"
                    subheading="Manage leave policy details."
                >
                    <Head title="Leave Policy Details" />
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Policy Details
                        </h2>
                        <Link
                            href={route('hr.leave.policies.details.create', policy.id)}
                            className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                        >
                            + Add Leave Type Rule
                        </Link>
                    </div>
                </div>
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

                {policy.details?.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Leave Type
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Entitlement
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Accrual
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Carry Forward
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Delegate
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Delegate Ack
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-slate-200">
                                {policy.details.map((detail) => (
                                    <tr key={detail.id} className="hover:bg-slate-50">
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {detail.leave_type?.leave_name || '-'}
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
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {detail.delegate_required ? 'Yes' : 'No'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {detail.delegate_acknowledgement_required ? 'Yes' : 'No'}
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
                                        <td className="px-6 py-4 text-sm font-medium space-x-2">
                                            <Link
                                                href={route('hr.leave.policies.details.edit', [policy.id, detail.id])}
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
                        <p className="text-slate-500">No policy details found.</p>
                    </div>
                )}
            </div>
        </div>
        </HRLayout>
    );
}
