import { Head, useForm, Link } from '@inertiajs/react';
import EmployeeLayout from '@/Layouts/EmployeeLayout';

export default function CreateComponent({ leaveTypes = [], allLeaveTypes = [], activePolicy = null, policyDetails = [], leaveBalances = [], policyError = null, user = {} }) {
    const { data, setData, post, processing, errors } = useForm({
        leave_type_id: '',
        start_date: '',
        end_date: '',
        is_half_day: false,
        half_day_session: 'morning',
        is_emergency: false,
        reason: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('employee.leave.applications.store'), {
            onSuccess: () => {},
        });
    };

    return (
        <EmployeeLayout
            heading="Apply for Leave"
            subheading="Submit a new leave application."
        >
            <Head title="Apply for Leave" />
            <div className="space-y-6">
                {policyError && (
                    <div className="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-700">
                        {policyError}
                    </div>
                )}

                {activePolicy && (
                    <div className="rounded-2xl border border-sky-200 bg-sky-50">
                        <div className="border-b border-sky-200 px-5 py-4">
                            <h4 className="text-sm font-medium text-sky-900">Applicable Leave Policy For You</h4>
                            <p className="mt-1 text-sm text-sky-700">{activePolicy.policy_name}</p>
                            <p className="text-xs text-sky-600">
                                {activePolicy.policy_code} | Effective: {activePolicy.effective_from} {activePolicy.effective_to ? `to ${activePolicy.effective_to}` : ""}
                            </p>
                        </div>

                        {policyDetails.length > 0 && (
                            <div className="p-5">
                                <h5 className="text-xs font-semibold uppercase text-sky-800 mb-3">Policy Rules</h5>
                                <div className="overflow-x-auto rounded-xl border border-sky-100">
                                    <table className="min-w-full divide-y divide-sky-100 text-xs">
                                        <thead className="bg-sky-50">
                                            <tr>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Leave Type</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Entitlement</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Accrual</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Min / Max</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Notice</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Gender</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Marital</th>
                                                <th className="px-3 py-2 text-left font-semibold text-sky-900">Flags</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-sky-100 bg-white/70">
                                            {policyDetails.map((detail) => (
                                                <tr key={detail.id} className="hover:bg-sky-50/60">
                                                    <td className="px-3 py-2 font-medium text-sky-900">{detail.leave_type?.leave_name || 'Unknown'}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.annual_entitlement}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.accrual_method}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.minimum_days_per_application} - {detail.maximum_days_per_application || '∞'}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.notice_period_days}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.gender_restriction}</td>
                                                    <td className="px-3 py-2 text-sky-700">{detail.marital_status_restriction}</td>
                                                    <td className="px-3 py-2 text-sky-700">
                                                        <div className="flex flex-wrap gap-1">
                                                            {detail.carry_forward_allowed && (
                                                                <span className="rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700">Carry Forward</span>
                                                            )}
                                                            {detail.encashment_allowed && (
                                                                <span className="rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700">Encashment</span>
                                                            )}
                                                            {detail.half_day_allowed && (
                                                                <span className="rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-medium text-sky-700">Half Day</span>
                                                            )}
                                                            {detail.attachment_required && (
                                                                <span className="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">Attachment</span>
                                                            )}
                                                            {detail.medical_certificate_required && (
                                                                <span className="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">Medical Cert</span>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Leave Balance
                        </h2>
                        <p className="text-sm text-slate-500">
                            Employee: {user.name || 'Current User'}
                        </p>
                    </div>
                    <div className="p-6">
                        {leaveBalances.length > 0 ? (
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {leaveBalances.map((balance) => (
                                    <div key={balance.leave_type_id} className="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                        <div className="text-sm font-medium text-slate-500">{balance.leave_name}</div>
                                        <div className="mt-1 text-2xl font-bold text-sky-700">
                                            {balance.balance !== null ? balance.balance : 'Undefined'}
                                        </div>
                                        <div className="text-xs text-slate-500">Days Available</div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-sm text-slate-500">No leave balance records found.</p>
                        )}
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Leave Application Form
                        </h2>
                    </div>
                    <form onSubmit={submit} className="p-6 space-y-6">
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
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
                                    Start Date <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={data.start_date}
                                    onChange={(e) => setData('start_date', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.start_date && (
                                    <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    End Date <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={data.end_date}
                                    onChange={(e) => setData('end_date', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.end_date && (
                                    <p className="mt-1 text-sm text-red-600">{errors.end_date}</p>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_half_day"
                                    checked={data.is_half_day}
                                    onChange={(e) => setData('is_half_day', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <label htmlFor="is_half_day" className="text-sm font-medium text-slate-700">
                                    Half Day
                                </label>
                            </div>

                            {data.is_half_day && (
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Session <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={data.half_day_session}
                                        onChange={(e) => setData('half_day_session', e.target.value)}
                                        className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                    >
                                        <option value="morning">Morning</option>
                                        <option value="afternoon">Afternoon</option>
                                    </select>
                                </div>
                            )}

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_emergency"
                                    checked={data.is_emergency}
                                    onChange={(e) => setData('is_emergency', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <label htmlFor="is_emergency" className="text-sm font-medium text-slate-700">
                                    Emergency Leave
                                </label>
                            </div>

                            <div className="lg:col-span-2">
                                <label className="block text-sm font-medium text-slate-700">
                                    Reason
                                </label>
                                <textarea
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    rows={3}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.reason && (
                                    <p className="mt-1 text-sm text-red-600">{errors.reason}</p>
                                )}
                            </div>
                            <div className="flex items-center justify-end gap-3">
                                <Link
                                    href={route('employee.leave.applications.index')}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                                >
                                    Save as Draft
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </EmployeeLayout>
    );
}
