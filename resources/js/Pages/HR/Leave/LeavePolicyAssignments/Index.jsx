import { Head, Link } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function IndexComponent({ assignments, filters = {}, companies = [], branches = [], divisions = [], policies = [], users = [] }) {
    return (
        <HRLayout
                    heading="Leave Policy Assignments"
                    subheading="Manage employee leave policy assignments."
                >
                    <Head title="Leave Policy Assignments" />
        <div className="space-y-6">
            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Policy Assignments
                        </h2>
                        <Link
                            href={route('hr.leave.assignments.create')}
                            className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                        >
                            + New Assignment
                        </Link>
                    </div>
                </div>

                <div className="border-b border-slate-200 px-6 py-4">
                    <form method="GET" className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                        <div>
                            <label className="block text-xs font-medium text-slate-700">Employee</label>
                            <select name="employee" defaultValue={filters.employee || ''} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">All Employees</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>{user.name} ({user.employee_id})</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700">Company</label>
                            <select name="company" defaultValue={filters.company || ''} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">All Companies</option>
                                {companies.map((company) => (
                                    <option key={company.id} value={company.id}>{company.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700">Branch</label>
                            <select name="branch" defaultValue={filters.branch || ''} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">All Branches</option>
                                {branches.map((branch) => (
                                    <option key={branch.id} value={branch.id}>{branch.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700">Cluster / Division</label>
                            <select name="division" defaultValue={filters.division || ''} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">All Divisions</option>
                                {divisions.map((division) => (
                                    <option key={division.id} value={division.id}>{division.name}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-slate-700">Policy</label>
                            <select name="policy" defaultValue={filters.policy || ''} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">All Policies</option>
                                {policies.map((policy) => (
                                    <option key={policy.id} value={policy.id}>{policy.policy_name}</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex items-end gap-2">
                            <button type="submit" className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800">Filter</button>
                            <Link href={route('hr.leave.assignments.index')} className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100">Reset</Link>
                        </div>
                    </form>
                </div>

                {assignments.data.length > 0 ? (
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Policy
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Scope
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Employee
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Effective From
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                        Effective To
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
                                {assignments.data.map((assignment) => (
                                    <tr key={assignment.id} className="hover:bg-slate-50">
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {assignment.policy?.policy_name || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {assignment.company?.name || assignment.designation?.name || assignment.employment_type || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {assignment.employee?.name || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {assignment.effective_from}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-slate-900">
                                            {assignment.effective_to || '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                    assignment.status === 'active'
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-gray-100 text-gray-700'
                                                }`}
                                            >
                                                {assignment.status}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-sm font-medium space-x-2">
                                            <Link
                                                href={route('hr.leave.assignments.edit', assignment.id)}
                                                className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                            >
                                                Edit
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="py-10 text-center">
                        <p className="text-slate-500">No policy assignments found.</p>
                    </div>
                )}
            </div>
        </div>
        </HRLayout>
    );
}
