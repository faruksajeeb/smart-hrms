import { Head, Link, useForm } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function IndexComponent({ applications, employees = [], leaveTypes = [], statuses = [], filters = {} }) {
    const { delete: destroy } = useForm();

    const handleFilterChange = (key, value) => {
        const params = new URLSearchParams(window.location.search);
        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }
        window.location.search = params.toString();
    };

    const confirmDelete = (id) => {
        if (confirm('Are you sure you want to delete this application?')) {
            destroy(route('hr.leave.applications.destroy', id));
        }
    };

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
            heading="Leave Applications"
            subheading="Manage employee leave applications."
        >
            <Head title="Leave Applications" />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-3">
                        <Link
                            href={route('hr.leave.applications.create')}
                            className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                        >
                            + Apply Leave
                        </Link>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Filters
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                            </label>
                            <select
                                value={filters.employee_id || ''}
                                onChange={(e) => handleFilterChange('employee_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Employees</option>
                                {employees.map((employee) => (
                                    <option key={employee.id} value={employee.id}>
                                        {employee.employee_id} - {employee.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Type
                            </label>
                            <select
                                value={filters.leave_type_id || ''}
                                onChange={(e) => handleFilterChange('leave_type_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Leave Types</option>
                                {leaveTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.leave_name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Status
                            </label>
                            <select
                                value={filters.status || ''}
                                onChange={(e) => handleFilterChange('status', e.target.value)}
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
                                        Days
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
                                                {application.leaveType?.leave_name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {application.start_date} to {application.end_date}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {application.total_days}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${getStatusBadge(application.status)}`}>
                                                    {application.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex items-center gap-2">
                                                    <Link
                                                        href={route('hr.leave.applications.show', application)}
                                                        className="text-sky-700 hover:text-sky-900"
                                                    >
                                                        View
                                                    </Link>
                                                    {application.canEdit() && (
                                                        <Link
                                                            href={route('hr.leave.applications.edit', application)}
                                                            className="text-indigo-700 hover:text-indigo-900"
                                                        >
                                                            Edit
                                                        </Link>
                                                    )}
                                                    {application.canSubmit() && (
                                                        <button
                                                            onClick={() => {
                                                                if (confirm('Submit this leave application for approval?')) {
                                                                    window.location.href = route('hr.leave.applications.submit', application);
                                                                }
                                                            }}
                                                            className="text-green-700 hover:text-green-900"
                                                        >
                                                            Submit
                                                        </button>
                                                    )}
                                                    {application.canCancel() && (
                                                        <button
                                                            onClick={() => confirmDelete(application.id)}
                                                            className="text-red-700 hover:text-red-900"
                                                        >
                                                            Cancel
                                                        </button>
                                                    )}
                                                    {application.canDelete() && (
                                                        <button
                                                            onClick={() => confirmDelete(application.id)}
                                                            className="text-red-700 hover:text-red-900"
                                                        >
                                                            Delete
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="7" className="px-6 py-12 text-center text-sm text-slate-500">
                                            No leave applications found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {applications.links && (
                        <div className="border-t border-slate-200 px-6 py-4">
                            {applications.links}
                        </div>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
