import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Create({ policy, leaveTypes = [], errors = {} }) {
    const { data, setData, post, processing } = useForm({
        leave_type_id: '',
        annual_entitlement: '',
        accrual_method: 'none',
        monthly_accrual: '',
        carry_forward_allowed: false,
        maximum_carry_forward: '',
        encashment_allowed: false,
        maximum_encashment: '',
        maximum_consecutive_days: '',
        minimum_days_per_application: 1,
        maximum_days_per_application: '',
        half_day_allowed: true,
        hourly_leave_allowed: false,
        attachment_required: false,
        medical_certificate_required: false,
        notice_period_days: 0,
        minimum_service_months: 0,
        probation_allowed: true,
        include_weekly_off: false,
        include_holiday: false,
        sandwich_rule: false,
        allow_negative_balance: false,
        gender_restriction: 'any',
        marital_status_restriction: 'any',
        applicable_after_confirmation: false,
        status: 'active',
    });

    function submit(e) {
        e.preventDefault();
        post(route('hr.leave.policies.details.store', policy.id));
    }

    return (
        <HRLayout
            heading="Add Policy Rule"
            subheading={`Configure leave type rules for ${policy.policy_name}`}
        >
            <Head title="Add Policy Rule" />
            <form onSubmit={submit} className="space-y-6">
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
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Policy Rule Configuration
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
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
                                Annual Entitlement (Days)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                value={data.annual_entitlement}
                                onChange={(e) => setData('annual_entitlement', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.annual_entitlement && (
                                <p className="mt-1 text-sm text-red-600">{errors.annual_entitlement}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Accrual Method <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.accrual_method}
                                onChange={(e) => setData('accrual_method', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="none">None</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                            {errors.accrual_method && (
                                <p className="mt-1 text-sm text-red-600">{errors.accrual_method}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Monthly Accrual (Days)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                value={data.monthly_accrual}
                                onChange={(e) => setData('monthly_accrual', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.monthly_accrual && (
                                <p className="mt-1 text-sm text-red-600">{errors.monthly_accrual}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Minimum Days Per Application
                            </label>
                            <input
                                type="number"
                                value={data.minimum_days_per_application}
                                onChange={(e) => setData('minimum_days_per_application', parseInt(e.target.value) || 1)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.minimum_days_per_application && (
                                <p className="mt-1 text-sm text-red-600">{errors.minimum_days_per_application}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Maximum Days Per Application
                            </label>
                            <input
                                type="number"
                                value={data.maximum_days_per_application}
                                onChange={(e) => setData('maximum_days_per_application', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.maximum_days_per_application && (
                                <p className="mt-1 text-sm text-red-600">{errors.maximum_days_per_application}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Notice Period (Days)
                            </label>
                            <input
                                type="number"
                                value={data.notice_period_days}
                                onChange={(e) => setData('notice_period_days', parseInt(e.target.value) || 0)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.notice_period_days && (
                                <p className="mt-1 text-sm text-red-600">{errors.notice_period_days}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Minimum Service (Months)
                            </label>
                            <input
                                type="number"
                                value={data.minimum_service_months}
                                onChange={(e) => setData('minimum_service_months', parseInt(e.target.value) || 0)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.minimum_service_months && (
                                <p className="mt-1 text-sm text-red-600">{errors.minimum_service_months}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.carry_forward_allowed}
                                    onChange={(e) => setData('carry_forward_allowed', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Carry Forward Allowed</span>
                            </label>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Maximum Carry Forward (Days)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                value={data.maximum_carry_forward}
                                onChange={(e) => setData('maximum_carry_forward', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.maximum_carry_forward && (
                                <p className="mt-1 text-sm text-red-600">{errors.maximum_carry_forward}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.encashment_allowed}
                                    onChange={(e) => setData('encashment_allowed', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Encashment Allowed</span>
                            </label>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Maximum Encashment (Days)
                            </label>
                            <input
                                type="number"
                                step="0.5"
                                value={data.maximum_encashment}
                                onChange={(e) => setData('maximum_encashment', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.maximum_encashment && (
                                <p className="mt-1 text-sm text-red-600">{errors.maximum_encashment}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Maximum Consecutive Days
                            </label>
                            <input
                                type="number"
                                value={data.maximum_consecutive_days}
                                onChange={(e) => setData('maximum_consecutive_days', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.maximum_consecutive_days && (
                                <p className="mt-1 text-sm text-red-600">{errors.maximum_consecutive_days}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.half_day_allowed}
                                    onChange={(e) => setData('half_day_allowed', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Half Day Allowed</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.hourly_leave_allowed}
                                    onChange={(e) => setData('hourly_leave_allowed', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Hourly Leave Allowed</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.attachment_required}
                                    onChange={(e) => setData('attachment_required', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Attachment Required</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.medical_certificate_required}
                                    onChange={(e) => setData('medical_certificate_required', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Medical Certificate Required</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.probation_allowed}
                                    onChange={(e) => setData('probation_allowed', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Probation Allowed</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.include_weekly_off}
                                    onChange={(e) => setData('include_weekly_off', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Include Weekly Off</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.include_holiday}
                                    onChange={(e) => setData('include_holiday', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Include Holiday</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.sandwich_rule}
                                    onChange={(e) => setData('sandwich_rule', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Sandwich Rule</span>
                            </label>
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.allow_negative_balance}
                                    onChange={(e) => setData('allow_negative_balance', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Allow Negative Balance</span>
                            </label>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Gender Restriction <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.gender_restriction}
                                onChange={(e) => setData('gender_restriction', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="any">Any</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                            {errors.gender_restriction && (
                                <p className="mt-1 text-sm text-red-600">{errors.gender_restriction}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Marital Status Restriction <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.marital_status_restriction}
                                onChange={(e) => setData('marital_status_restriction', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="any">Any</option>
                                <option value="single">Single</option>
                                <option value="married">Married</option>
                            </select>
                            {errors.marital_status_restriction && (
                                <p className="mt-1 text-sm text-red-600">{errors.marital_status_restriction}</p>
                            )}
                        </div>

                        <div className="flex items-center gap-6">
                            <label className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    checked={data.applicable_after_confirmation}
                                    onChange={(e) => setData('applicable_after_confirmation', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <span className="text-sm text-slate-700">Applicable After Confirmation</span>
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
                        href={route('hr.leave.policies.details.index', policy.id)}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Add Rule'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
