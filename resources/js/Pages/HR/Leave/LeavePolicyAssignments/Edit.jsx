import { Head, useForm } from '@inertiajs/react';

import HRLayout from '@/Layouts/HRLayout';

export default function Edit({ assignment, policies = [], companies = [], branches = [], designations = [], employmentTypes = [], users = [], errors = {} }) {
    const { data, setData, put, processing } = useForm({
        leave_policy_id: assignment.leave_policy_id ?? '',
        company_id: assignment.company_id ?? '',
        branch_id: assignment.branch_id ?? '',
        division_id: assignment.division_id ?? '',
        department_id: assignment.department_id ?? '',
        section_id: assignment.section_id ?? '',
        unit_id: assignment.unit_id ?? '',
        designation_id: assignment.designation_id ?? '',
        employment_type: assignment.employment_type ?? '',
        user_id: assignment.user_id ?? '',
        effective_from: assignment.effective_from ?? '',
        effective_to: assignment.effective_to ?? '',
        remarks: assignment.remarks ?? '',
        status: assignment.status ?? 'active',
    });

    function submit(e) {
        e.preventDefault();
        put(route('hr.leave.assignments.update', assignment.id));
    }

    return (
        <HRLayout
            heading="Edit Leave Policy Assignment"
            subheading="Update policy assignment configuration."
        >
            <Head title="Edit Leave Policy Assignment" />
            <form onSubmit={submit} className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            Assignment Information
                        </h3>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Policy <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={data.leave_policy_id}
                                onChange={(e) => setData('leave_policy_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Policy</option>
                                {policies.map((policy) => (
                                    <option key={policy.id} value={policy.id}>
                                        {policy.policy_name}
                                    </option>
                                ))}
                            </select>
                            {errors.leave_policy_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.leave_policy_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                            </label>
                            <select
                                value={data.user_id}
                                onChange={(e) => setData('user_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Employee</option>
                                {users.map((user) => (
                                    <option key={user.id} value={user.id}>
                                        {user.employee_id} - {user.name}
                                    </option>
                                ))}
                            </select>
                            {errors.user_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.user_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Company
                            </label>
                            <select
                                value={data.company_id}
                                onChange={(e) => setData('company_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Company</option>
                                {companies.map((company) => (
                                    <option key={company.id} value={company.id}>
                                        {company.name}
                                    </option>
                                ))}
                            </select>
                            {errors.company_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.company_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Branch
                            </label>
                            <select
                                value={data.branch_id}
                                onChange={(e) => setData('branch_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Branch</option>
                                {branches.map((branch) => (
                                    <option key={branch.id} value={branch.id}>
                                        {branch.name}
                                    </option>
                                ))}
                            </select>
                            {errors.branch_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.branch_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Designation
                            </label>
                            <select
                                value={data.designation_id}
                                onChange={(e) => setData('designation_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Designation</option>
                                {designations.map((designation) => (
                                    <option key={designation.id} value={designation.id}>
                                        {designation.name}
                                    </option>
                                ))}
                            </select>
                            {errors.designation_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.designation_id}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employment Type
                            </label>
                            <select
                                value={data.employment_type}
                                onChange={(e) => setData('employment_type', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Employment Type</option>
                                {employmentTypes.map((type) => (
                                    <option key={type.id} value={type.name}>
                                        {type.name}
                                    </option>
                                ))}
                            </select>
                            {errors.employment_type && (
                                <p className="mt-1 text-sm text-red-600">{errors.employment_type}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective From <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                value={data.effective_from}
                                onChange={(e) => setData('effective_from', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.effective_from && (
                                <p className="mt-1 text-sm text-red-600">{errors.effective_from}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Effective To
                            </label>
                            <input
                                type="date"
                                value={data.effective_to}
                                onChange={(e) => setData('effective_to', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.effective_to && (
                                <p className="mt-1 text-sm text-red-600">{errors.effective_to}</p>
                            )}
                        </div>

                        <div className="lg:col-span-2">
                            <label className="block text-sm font-medium text-slate-700">
                                Remarks
                            </label>
                            <textarea
                                value={data.remarks}
                                onChange={(e) => setData('remarks', e.target.value)}
                                rows="3"
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                            {errors.remarks && (
                                <p className="mt-1 text-sm text-red-600">{errors.remarks}</p>
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
                        href={route('hr.leave.assignments.index')}
                        className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                    >
                        {processing ? 'Saving...' : 'Update Assignment'}
                    </button>
                </div>
            </form>
        </HRLayout>
    );
}
