import { Head, Link } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

const STATUS_COLORS = {
    draft: 'bg-gray-100 text-gray-800',
    submitted: 'bg-blue-100 text-blue-800',
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-red-100 text-red-800',
    withdrawn: 'bg-orange-100 text-orange-800',
    expired: 'bg-gray-100 text-gray-800',
};

export default function IndexComponent({
    kpis = {},
    pendingApprovals = [],
    teamOnLeaveToday = [],
    upcomingLeaves = [],
    month,
}) {
    return (
        <HRLayout
            heading="Manager Leave Dashboard"
            subheading="Monitor your team's leave activity and approvals."
        >
            <Head title="Manager Leave Dashboard" />
            <div className="space-y-6">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Pending Approvals</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{kpis.pending_approvals}</p>
                        <Link
                            href={route('hr.approval.requests.pending')}
                            className="mt-3 inline-flex items-center text-sm font-medium text-sky-700 hover:text-sky-800"
                        >
                            Review now →
                        </Link>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">On Leave Today</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{kpis.on_leave_today}</p>
                        <p className="mt-1 text-xs text-slate-500">Team members</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Upcoming Leaves</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{kpis.upcoming_leaves}</p>
                        <p className="mt-1 text-xs text-slate-500">Next 30 days</p>
                    </div>
                    <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <p className="text-sm font-medium text-slate-500">Leave Days This Month</p>
                        <p className="mt-2 text-3xl font-semibold text-slate-900">{kpis.leave_days_this_month}</p>
                        <p className="mt-1 text-xs text-slate-500">{month || 'Current month'}</p>
                    </div>
                </div>

                {teamOnLeaveToday.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">Employees on Leave Today</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">Employee</th>
                                        <th className="px-6 py-3 font-medium">Leave Type</th>
                                        <th className="px-6 py-3 font-medium">Date</th>
                                        <th className="px-6 py-3 font-medium">Days</th>
                                        <th className="px-6 py-3 font-medium">Session</th>
                                        <th className="px-6 py-3 font-medium">Acting Person</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {teamOnLeaveToday.map((item, idx) => (
                                        <tr key={idx} className="hover:bg-slate-50">
                                            <td className="px-6 py-4">
                                                <div>
                                                    <p className="font-medium text-slate-900">{item.employee_name}</p>
                                                    <p className="text-xs text-slate-500">{item.employee_code}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className="inline-flex rounded-full bg-sky-100 px-2 py-1 text-xs font-medium text-sky-800">
                                                    {item.leave_type}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">{item.date}</td>
                                            <td className="px-6 py-4 text-slate-900">{item.leave_days}</td>
                                            <td className="px-6 py-4 text-slate-600">{item.session || 'Full Day'}</td>
                                            <td className="px-6 py-4 text-slate-600">{item.delegate || '-'}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {pendingApprovals.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">Pending Leave Approvals</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">Application No.</th>
                                        <th className="px-6 py-3 font-medium">Employee</th>
                                        <th className="px-6 py-3 font-medium">Leave Type</th>
                                        <th className="px-6 py-3 font-medium">Start Date</th>
                                        <th className="px-6 py-3 font-medium">End Date</th>
                                        <th className="px-6 py-3 font-medium">Days</th>
                                        <th className="px-6 py-3 font-medium">Submitted</th>
                                        <th className="px-6 py-3 font-medium">Current Level</th>
                                        <th className="px-6 py-3 font-medium">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {pendingApprovals.map((approval) => {
                                        const requester = approval.requester;
                                        const reference = approval.reference;
                                        const leaveApplication = reference && typeof reference === 'object' ? reference : null;
                                        const currentStep = approval.currentStep;
                                        const workflowLevel = currentStep?.workflowLevel;

                                        return (
                                            <tr key={approval.id} className="hover:bg-slate-50">
                                                <td className="px-6 py-4 font-medium text-slate-900">
                                                    {leaveApplication?.application_no || `#${approval.id}`}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div>
                                                        <p className="font-medium text-slate-900">{requester?.name || 'Unknown'}</p>
                                                        <p className="text-xs text-slate-500">{requester?.employee_id || ''}</p>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {leaveApplication?.leaveType?.leave_name || '-'}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">{leaveApplication?.start_date || '-'}</td>
                                                <td className="px-6 py-4 text-slate-600">{leaveApplication?.end_date || '-'}</td>
                                                <td className="px-6 py-4 text-slate-900">{leaveApplication?.total_days || '-'}</td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    {approval.submitted_at ? new Date(approval.submitted_at).toLocaleDateString() : '-'}
                                                </td>
                                                <td className="px-6 py-4 text-slate-600">
                                                    Level {approval.current_level}
                                                    {workflowLevel ? ` - ${workflowLevel.approval_type}` : ''}
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex gap-2">
                                                        <Link
                                                            href={route('hr.approval.requests.show', approval.id)}
                                                            className="text-sky-700 hover:text-sky-800"
                                                        >
                                                            View
                                                        </Link>
                                                        <Link
                                                            href={route('hr.leave.applications.show', leaveApplication?.id)}
                                                            className="text-slate-600 hover:text-slate-800"
                                                        >
                                                            Application
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {upcomingLeaves.length > 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">Upcoming Team Leaves</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-sm">
                                <thead className="bg-slate-50 text-slate-600">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">Employee</th>
                                        <th className="px-6 py-3 font-medium">Leave Type</th>
                                        <th className="px-6 py-3 font-medium">From</th>
                                        <th className="px-6 py-3 font-medium">To</th>
                                        <th className="px-6 py-3 font-medium">Days</th>
                                        <th className="px-6 py-3 font-medium">Session</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {upcomingLeaves.slice(0, 15).map((leave, idx) => (
                                        <tr key={idx} className="hover:bg-slate-50">
                                            <td className="px-6 py-4">
                                                <div>
                                                    <p className="font-medium text-slate-900">{leave.employee_name}</p>
                                                    <p className="text-xs text-slate-500">{leave.employee_code}</p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-slate-600">{leave.leave_type}</td>
                                            <td className="px-6 py-4 text-slate-600">{leave.start_date}</td>
                                            <td className="px-6 py-4 text-slate-600">{leave.end_date}</td>
                                            <td className="px-6 py-4 text-slate-900">{leave.total_days}</td>
                                            <td className="px-6 py-4 text-slate-600">
                                                {leave.is_half_day ? (leave.half_day_session || 'Half Day') : 'Full Day'}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                )}

                {pendingApprovals.length === 0 && teamOnLeaveToday.length === 0 && upcomingLeaves.length === 0 && (
                    <div className="rounded-2xl border border-slate-200 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                        No leave activity to display for the selected period.
                    </div>
                )}
            </div>
        </HRLayout>
    );
}