import { Head, usePage } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function Summary({ employees = [], leaveTypes = [], selectedEmployeeId, selectedLeaveTypeId, summary }) {
    const handleFilterChange = (key, value) => {
        const params = new URLSearchParams(window.location.search);
        if (value) {
            params.set(key, value);
        } else {
            params.delete(key);
        }
        window.location.search = params.toString();
    };

    return (
        <HRLayout
            heading="Leave Balance Summary"
            subheading="View employee leave balance summary."
        >
            <Head title="Leave Balance Summary" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Select Employee and Leave Type
                        </h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={selectedEmployeeId || ''}
                                onChange={(e) => handleFilterChange('employee_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Employee</option>
                                {employees.map((employee) => (
                                    <option key={employee.id} value={employee.id}>
                                        {employee.employee_id} - {employee.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Leave Type <span className="text-red-500">*</span>
                            </label>
                            <select
                                value={selectedLeaveTypeId || ''}
                                onChange={(e) => handleFilterChange('leave_type_id', e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">Select Leave Type</option>
                                {leaveTypes.map((type) => (
                                    <option key={type.id} value={type.id}>
                                        {type.leave_name}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                {summary && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Leave Balance Summary
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                {summary.employee?.name} - {summary.leave_type?.leave_name}
                            </p>
                        </div>
                        <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Entitlement</h4>
                                <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.entitlement}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Opening Balance</h4>
                                <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.opening}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Accruals</h4>
                                <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.accruals}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Used</h4>
                                <p className="mt-1 text-2xl font-semibold text-red-600">{summary.used}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Cancelled</h4>
                                <p className="mt-1 text-2xl font-semibold text-green-600">{summary.cancelled}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Expired</h4>
                                <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.expired}</p>
                            </div>
                            <div>
                                <h4 className="text-sm font-medium text-slate-500">Adjustments</h4>
                                <p className="mt-1 text-2xl font-semibold text-slate-900">{summary.adjustments}</p>
                            </div>
                            <div className="lg:col-span-2">
                                <h4 className="text-sm font-medium text-slate-500">Available Balance</h4>
                                <p className="mt-1 text-3xl font-bold text-sky-700">{summary.available}</p>
                            </div>
                        </div>
                        <div className="border-t border-slate-200 px-6 py-4">
                            <a
                                href={`/hr/leave/ledgers?employee_id=${summary.employee.id}&leave_type_id=${summary.leave_type.id}`}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                View Ledger
                            </a>
                        </div>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
