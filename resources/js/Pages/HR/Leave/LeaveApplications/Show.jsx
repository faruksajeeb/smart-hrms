import { Head, Link } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function ShowComponent({ application }) {
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

    return (
        <HRLayout
            heading="Leave Application Details"
            subheading="View leave application details."
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
                                    {application.employee?.name} - {application.leaveType?.leave_name}
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Link
                                    href={route('hr.leave.applications.index')}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Back
                                </Link>
                                {application.canEdit() && (
                                    <Link
                                        href={route('hr.leave.applications.edit', application)}
                                        className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                                    >
                                        Edit
                                    </Link>
                                )}
                                {application.canSubmit() && (
                                    <Link
                                        href={route('hr.leave.applications.submit', application)}
                                        className="inline-flex items-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800"
                                    >
                                        Submit
                                    </Link>
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
                            <h4 className="text-sm font-medium text-slate-500">Employee</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.employee?.name}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Leave Type</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.leaveType?.leave_name}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Leave Policy</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.leavePolicy?.policy_name}</p>
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
                                        <a href={`/storage/${attachment.file_path}`} target="_blank" className="text-sky-700 hover:text-sky-900">
                                            {attachment.file_name}
                                        </a>
                                        <span className="text-sm text-slate-500">{attachment.mime_type}</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
