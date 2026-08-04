import { Head, Link } from '@inertiajs/react';
import EmployeeLayout from '@/Layouts/EmployeeLayout';

export default function IndexComponent({ applications, statuses = [], filters = {} }) {
    const getStatusBadge = (status) => {
        const colors = {
            pending: 'bg-yellow-100 text-yellow-800',
            accepted: 'bg-green-100 text-green-800',
            declined: 'bg-red-100 text-red-800',
        };
        return colors[status] || 'bg-gray-100 text-gray-800';
    };

    return (
        <EmployeeLayout
            heading="My Delegations"
            subheading="View and manage your acting person assignments."
        >
            <Head title="My Delegations" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Status
                            </label>
                            <select
                                value={filters.status || ''}
                                onChange={(e) => {
                                    const params = new URLSearchParams(window.location.search);
                                    if (e.target.value) {
                                        params.set('status', e.target.value);
                                    } else {
                                        params.delete('status');
                                    }
                                    window.location.search = params.toString();
                                }}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Statuses</option>
                                {statuses.map((status) => (
                                    <option key={status.value} value={status.value}>
                                        {status.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Application No
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Employee
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Leave Type
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Dates
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
                                {applications.data.length > 0 ? (
                                    applications.data.map((application) => (
                                        <tr key={application.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                                {application.application_no}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {application.employee?.name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {application.leave_type?.leave_name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {application.start_date} to {application.end_date}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadge(application.delegate_status)}`}>
                                                    {application.delegate_status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link
                                                    href={route('employee.leave.delegations.show', application.id)}
                                                    className="text-sky-700 hover:text-sky-900"
                                                >
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-sm text-slate-500">
                                            No delegations found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {applications.links && applications.links.length > 0 ? (
                        <div className="border-t border-slate-200 px-6 py-4 flex items-center justify-center gap-2">
                            {applications.links.map((link, index) => (
                                <a key={index} href={link.url || '#'} dangerouslySetInnerHTML={{__html: link.label}} className={`px-3 py-1 rounded-lg text-sm ${link.active ? 'bg-sky-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'}`} />
                            ))}
                        </div>
                    ) : null}
                </div>
            </div>
        </EmployeeLayout>
    );
}
