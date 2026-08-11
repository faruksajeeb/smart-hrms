import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import EmployeeLayout from '@/Layouts/EmployeeLayout';

export default function ShowComponent({ application, activePolicy = null, policyDetails = [] }) {
    const { post, processing } = useForm();
    const [showPolicyRules, setShowPolicyRules] = useState(false);

    const getStatusBadge = (status) => {
        const colors = {
            draft: 'bg-gray-100 text-gray-800',
            submitted: 'bg-blue-100 text-blue-800',
            pending: 'bg-yellow-100 text-yellow-800',
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
            cancelled: 'bg-red-100 text-red-800',
            withdrawn: 'bg-orange-100 text-orange-800',
            expired: 'bg-gray-100 text-gray-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    const getDelegateStatusBadge = (status) => {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800',
            accepted: 'bg-green-100 text-green-800',
            declined: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    return (
        <EmployeeLayout
            heading="Leave Application Details"
            subheading="View your leave application details."
        >
            <Head title="Leave Application Details" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {application.application_no}
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {application.leave_type?.leave_name}
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Link
                                    href={route('employee.leave.applications.index')}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Back
                                </Link>
                                {application.can_edit && (
                                    <Link
                                        href={route('employee.leave.applications.edit', application)}
                                        className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                                    >
                                        Edit
                                    </Link>
                                )}
                                {application.can_submit && (
                                    <button
                                        onClick={() => {
                                            if (confirm('Submit this leave application for approval?')) {
                                                post(route('employee.leave.applications.submit', application));
                                            }
                                        }}
                                        disabled={processing}
                                        className="inline-flex items-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-50"
                                    >
                                        Submit
                                    </button>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Application No</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.application_no}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Status</h4>
                            <span className={`mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadge(application.status)}`}>
                                {application.status}
                            </span>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Leave Type</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.leave_type?.leave_name}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Leave Policy</h4>
                            <p className="mt-1 font-semibold text-slate-900" id="leave-policy-view">
                                {application.leave_policy?.policy_name}
                                
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Application Type</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.application_type}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Start Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.start_date}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">End Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.end_date}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Total Days</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.total_days}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Requested Days</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.requested_days}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Half Day</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.is_half_day ? 'Yes' : 'No'}</p>
                        </div>

                        {application.is_half_day && (
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Session</h4>
                                <p className="mt-1 font-semibold text-slate-900">{application.half_day_session}</p>
                            </div>
                        )}

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Emergency Leave</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.is_emergency ? 'Yes' : 'No'}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Acting Person</h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {application.delegate?.name || '-'}
                            </p>
                        </div>

                        {application.delegate_status && (
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Delegate Status</h4>
                                <span className={`mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getDelegateStatusBadge(application.delegate_status)}`}>
                                    {application.delegate_status}
                                </span>
                            </div>
                        )}

                        {application.delegate_responded_at && (
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Delegate Responded At</h4>
                                <p className="mt-1 font-semibold text-slate-900">
                                    {new Date(application.delegate_responded_at).toLocaleString()}
                                </p>
                            </div>
                        )}

                        {application.delegate_remarks && (
                            <div className="lg:col-span-2">
                                <h4 className="text-sm font-medium text-slate-500">Delegate Remarks</h4>
                                <p className="mt-1 font-semibold text-slate-900">{application.delegate_remarks}</p>
                            </div>
                        )}

                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">Reason</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.reason || '-'}</p>
                        </div>

                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">Remarks</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.remarks || '-'}</p>
                        </div>
                    </div>
                </div>

                {activePolicy && (
                    <div  className="rounded-2xl border border-sky-200 bg-white shadow-sm">
                        <div className="border-b border-sky-200 px-6 py-4">
                            <h4 className="text-sm font-medium text-sky-900">Applicable Leave Policy For You</h4>
                            <p className="mt-1 text-sm text-sky-700">{activePolicy.policy_name}</p>
                            <p className="text-xs text-sky-600">
                                {activePolicy.policy_code} | Effective: {activePolicy.effective_from} {activePolicy.effective_to ? `to ${activePolicy.effective_to}` : ""}
                            </p>
                        </div>

                        {policyDetails.length > 0 && (
                            <div className="p-5">
                                <button
                                    type="button"
                                    onClick={() => setShowPolicyRules(!showPolicyRules)}
                                    className="flex w-full items-center justify-between rounded-lg bg-sky-100 px-4 py-3 text-left text-sm font-semibold text-sky-900 hover:bg-sky-200"
                                >
                                    <span>Policy Rules ({policyDetails.length})</span>
                                    <svg
                                        className={`h-5 w-5 transform transition-transform ${showPolicyRules ? 'rotate-180' : ''}`}
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {showPolicyRules && (
                                    <div className="mt-3 overflow-x-auto rounded-xl border border-sky-100">
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
                                                                {detail.delegate_required && (
                                                                    <span className="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-medium text-indigo-700">Delegate</span>
                                                                )}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                        </table>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                )}

                {application.days && application.days.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Leave Days
                            </h2>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Date</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Day Type</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Session</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Holiday</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Weekly Off</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Counts as Leave</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Leave Days</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-slate-200">
                                    {application.days.map((day) => (
                                        <tr key={day.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-900">{day.leave_date}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.day_type}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.session || '-'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.is_holiday ? 'Yes' : 'No'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.is_weekly_off ? 'Yes' : 'No'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.counts_as_leave ? 'Yes' : 'No'}</td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{day.leave_days}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {application.attachments && application.attachments.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Attachments
                            </h2>
                        </div>
                        <div className="p-6">
                            <ul className="space-y-2">
                                {application.attachments.map((attachment) => (
                                    <li key={attachment.id} className="flex items-center justify-between">
                                        <div className="flex items-center gap-3">
                                            <a href={route('employee.employee.leave.applications.attachments.download', [application.id, attachment.id])} className="text-sky-700 hover:text-sky-900">
                                                {attachment.original_file_name || attachment.file_name}
                                            </a>
                                            <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${
                                                attachment.status === 'verified' ? 'bg-green-100 text-green-700' :
                                                attachment.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                                'bg-yellow-100 text-yellow-700'
                                            }`}>
                                                {attachment.status}
                                            </span>
                                        </div>
                                        <span className="text-sm text-slate-500">{(attachment.file_size / 1024).toFixed(1)} KB</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                )}
            </div>
        </EmployeeLayout>
    );
}
