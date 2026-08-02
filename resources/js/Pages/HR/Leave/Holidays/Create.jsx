import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Create({ errors = {} }) {
    const { data, setData, post, processing } = useForm({
        holiday_name: '',
        holiday_code: '',
        holiday_date: '',
        holiday_type: 'company',
        is_recurring: false,
        description: '',
        status: 'active',
        scopes: [],
    });

    function submit(e) {
        e.preventDefault();
        post(route('hr.leave.holidays.store'));
    }

    return (
        <HRLayout
            heading="Create Holiday"
            subheading="Add a new holiday to the calendar."
        >
            <Head title="Create Holiday" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Holiday Information
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Holiday Name <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.holiday_name}
                                onChange={(e) => setData('holiday_name', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.holiday_name && (
                                <p className="mt-1 text-sm text-red-600">{errors.holiday_name}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Holiday Code <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                value={data.holiday_code}
                                onChange={(e) => setData('holiday_code', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.holiday_code && (
                                <p className="mt-1 text-sm text-red-600">{errors.holiday_code}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Holiday Date <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                value={data.holiday_date}
                                onChange={(e) => setData('holiday_date', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.holiday_date && (
                                <p className="mt-1 text-sm text-red-600">{errors.holiday_date}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Holiday Type <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.holiday_type}
                                onChange={(e) => setData('holiday_type', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="national">National</option>
                                <option value="religious">Religious</option>
                                <option value="company">Company</option>
                                <option value="branch">Branch</option>
                                <option value="optional">Optional</option>
                            </select>
                            {errors.holiday_type && (
                                <p className="mt-1 text-sm text-red-600">{errors.holiday_type}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.is_recurring}
                                    onChange={(e) => setData('is_recurring', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Is Recurring</span>
                            </label>
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
                        href={route('hr.leave.holidays.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Create Holiday'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
