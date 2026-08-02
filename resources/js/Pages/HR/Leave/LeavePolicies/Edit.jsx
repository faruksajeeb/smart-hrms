import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Edit({ policy, errors = {} }) {
    const { data, setData, put, processing } = useForm({
        policy_name: policy.policy_name ?? '',
        policy_code: policy.policy_code ?? '',
        description: policy.description ?? '',
        effective_from: policy.effective_from ?? '',
        effective_to: policy.effective_to ?? '',
        status: policy.status ?? 'active',
    });

    function submit(e) {
        e.preventDefault();
        put(route('hr.leave.policies.update', policy.id));
    }

    return (
        <HRLayout
            heading="Edit Leave Policy"
            subheading="Update leave policy configuration."
        >
            <Head title="Edit Leave Policy" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Policy Information
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Policy Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.policy_name}
                                onChange={(e) => setData('policy_name', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.policy_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.policy_name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Policy Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.policy_code}
                                onChange={(e) => setData('policy_code', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.policy_code && (
                                <p className="mt-1 text-sm text-red-600">{errors.policy_code}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective From <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                value={data.effective_from}
                                onChange={(e) => setData('effective_from', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.effective_from && (
                                <p className="mt-1 text-sm text-red-600">{errors.effective_from}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective To
                            </label>
                            <input
                                type="date"
                                value={data.effective_to}
                                onChange={(e) => setData('effective_to', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.effective_to && (
                                <p className="mt-1 text-sm text-red-600">{errors.effective_to}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Description
                            </label>
                            <textarea
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                rows="3"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.description && (
                                <p className="mt-1 text-sm text-red-600">{errors.description}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Status <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            {errors.status && (
                                <p className="mt-1 text-sm text-red-600">{errors.status}</p>
                            )}
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-end gap-3">
                    <a
                        href={route('hr.leave.policies.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Update Policy'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
