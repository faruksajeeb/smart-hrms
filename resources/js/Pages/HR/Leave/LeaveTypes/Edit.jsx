import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Edit({ leaveType, errors = {} }) {
    const { data, setData, put, processing } = useForm({
        leave_name: leaveType.leave_name ?? '',
        leave_code: leaveType.leave_code ?? '',
        description: leaveType.description ?? '',
        is_paid: leaveType.is_paid ?? true,
        display_color: leaveType.display_color ?? '#3B82F6',
        display_order: leaveType.display_order ?? 0,
        status: leaveType.status ?? 'active',
    });

    function submit(e) {
        e.preventDefault();
        put(route('hr.leave.types.update', leaveType.id));
    }

    return (
        <HRLayout
            heading="Edit Leave Type"
            subheading="Update leave type configuration."
        >
            <Head title="Edit Leave Type" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Leave Type Information
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.leave_name}
                                onChange={(e) => setData('leave_name', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.leave_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.leave_name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.leave_code}
                                onChange={(e) => setData('leave_code', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.leave_code && (
                                <p className="mt-1 text-sm text-red-600">{errors.leave_code}</p>
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
                                Display Color
                            </label>
                            <input
                                type="color"
                                value={data.display_color}
                                onChange={(e) => setData('display_color', e.target.value)}
                                className="mt-1 h-10 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.display_color && (
                                <p className="mt-1 text-sm text-red-600">{errors.display_color}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Display Order
                            </label>
                            <input
                                type="number"
                                value={data.display_order}
                                onChange={(e) => setData('display_order', parseInt(e.target.value) || 0)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.display_order && (
                                <p className="mt-1 text-sm text-red-600">{errors.display_order}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.is_paid}
                                    onChange={(e) => setData('is_paid', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Is Paid</span>
                            </label>
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
                        href={route('hr.leave.types.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Update Leave Type'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
