import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import HRLayout from "@/Layouts/HRLayout";
import Swal from "sweetalert2";

export default function Index({
    assignments,
    filters = {},
    employees = [],
    policies = [],
}) {
    const [search, setSearch] = useState(filters.search ?? "");

    const [employeeId, setEmployeeId] = useState(filters.employee_id ?? "");

    const [policyId, setPolicyId] = useState(filters.policy_id ?? "");

    const filter = () => {
        router.get(
            route("hr.weekly-off-assignments.index"),
            {
                search,
                employee_id: employeeId,
                policy_id: policyId,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };


    const deleteAssignment = (id) => {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to recover this assignment!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    router.delete(route("hr.weekly-off-assignments.destroy", id), {
                        preserveScroll: true,
                        onSuccess: () => {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: "Assignment deleted successfully.",
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        },
                    });
                }
            });
        };

    return (
        <HRLayout
            heading="Employee Weekly Off Assignment"
            subheading="Manage employee weekly off assignments and assignment history."
        >
            <Head title="Employee Weekly Off Assignment" />

            <div className="space-y-6">
                {/* Header */}

                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-xl font-semibold text-slate-900">
                            Weekly Off Assignment
                        </h2>

                        <p className="mt-1 text-sm text-slate-500">
                            Assign weekly off policy to employees.
                        </p>
                    </div>

                    <Link
                        href={route(
                            "hr.weekly-off-assignments.create",
                        )}
                        className="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700"
                    >
                        Assign Weekly Off
                    </Link>
                </div>

                {/* Filters */}

                <div className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="grid grid-cols-1 gap-4 lg:grid-cols-4">
                        {/* Search */}

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Search
                            </label>

                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Employee name or ID..."
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            />
                        </div>

                        {/* Employee */}

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Employee
                            </label>

                            <select
                                value={employeeId}
                                onChange={(e) => setEmployeeId(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Employees</option>

                                {employees.map((employee) => (
                                    <option
                                        key={employee.id}
                                        value={employee.id}
                                    >
                                        {employee.employee_id} -{" "}
                                        {employee.name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Weekly Off Policy */}

                        <div>
                            <label className="block text-sm font-medium text-slate-700">
                                Weekly Off Policy
                            </label>

                            <select
                                value={policyId}
                                onChange={(e) => setPolicyId(e.target.value)}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Policies</option>

                                {policies.map((policy) => (
                                    <option key={policy.id} value={policy.id}>
                                        {policy.policy_name}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Filter Button */}

                        <div className="flex items-end gap-2">
                            <button
                                onClick={filter}
                                className="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                Filter
                            </button>

                            <Link
                                href={route(
                                    "hr.weekly-off-assignments.index",
                                )}
                                className="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100"
                            >
                                Reset
                            </Link>
                        </div>
                    </div>
                </div>
                {/* Assignment Table */}

                <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead className="bg-slate-50">
                            <tr className="text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                <th className="px-5 py-3">Employee</th>

                                <th className="px-5 py-3">Weekly Off Policy</th>

                                <th className="px-5 py-3">Effective From</th>

                                <th className="px-5 py-3">Effective To</th>

                                <th className="px-5 py-3">Assignment</th>

                                <th className="px-5 py-3">Status</th>

                                <th className="px-5 py-3 text-right">
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody className="divide-y divide-slate-100">
                            {assignments.data.length > 0 ? (
                                assignments.data.map((assignment) => (
                                    <tr
                                        key={assignment.id}
                                        className="hover:bg-slate-50"
                                    >
                                        {/* Employee */}

                                        <td className="px-5 py-4">
                                            <div className="font-semibold text-slate-900">
                                                {
                                                    assignment.employee
                                                        ?.employee_id
                                                }
                                            </div>

                                            <div className="text-sm text-slate-600">
                                                {assignment.employee?.name}
                                            </div>

                                            <div className="text-xs text-slate-400">
                                                {
                                                    assignment.employee
                                                        ?.department?.name
                                                }
                                            </div>
                                        </td>

                                        {/* Policy */}

                                        <td className="px-5 py-4">
                                            <div className="font-medium text-slate-900">
                                                {
                                                    assignment.weekly_off_policy
                                                        ?.policy_name
                                                }
                                            </div>

                                            <div className="text-xs text-slate-500">
                                                {
                                                    assignment.weekly_off_policy
                                                        ?.description
                                                }
                                            </div>
                                        </td>

                                        {/* Effective From */}

                                        <td className="px-5 py-4 text-sm">
                                            {assignment.effective_from}
                                        </td>

                                        {/* Effective To */}

                                        <td className="px-5 py-4 text-sm">
                                            {assignment.effective_to ??
                                                "Present"}
                                        </td>

                                        {/* Assignment Type */}

                                        <td className="px-5 py-4">
                                            <span className="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                                {assignment.assignment_type}
                                            </span>
                                        </td>

                                        {/* Status */}

                                        <td className="px-5 py-4">
                                            {assignment.effective_to ===
                                            null ? (
                                                <span className="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                    Current
                                                </span>
                                            ) : (
                                                <span className="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">
                                                    Expired
                                                </span>
                                            )}
                                        </td>

                                        {/* Actions */}

                                        <td className="px-5 py-4">
                                            <div className="flex justify-end gap-2">
                                                <Link
                                                    href={route(
                                                        "hr.weekly-off-assignments.history",
                                                        assignment.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    History
                                                </Link>

                                                <Link
                                                    href={route(
                                                        "hr.weekly-off-assignments.change",
                                                        assignment.id,
                                                    )}
                                                    className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                                                >
                                                    Change
                                                </Link>

                                                <button
                                                    onClick={() =>
                                                        deleteAssignment(
                                                            assignment.id,
                                                        )
                                                    }
                                                    className="text-red-600 hover:text-red-900"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td
                                        colSpan={7}
                                        className="py-16 text-center text-slate-500"
                                    >
                                        No weekly off assignment found.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
                {/* Pagination */}

                {assignments.links && assignments.links.length > 3 && (
                    <div className="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-6 py-4 shadow-sm">
                        <div className="text-sm text-slate-500">
                            Showing{" "}
                            <span className="font-semibold">
                                {assignments.from ?? 0}
                            </span>{" "}
                            to{" "}
                            <span className="font-semibold">
                                {assignments.to ?? 0}
                            </span>{" "}
                            of{" "}
                            <span className="font-semibold">
                                {assignments.total}
                            </span>{" "}
                            records
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {assignments.links.map((link, index) => (
                                <Link
                                    key={index}
                                    href={link.url ?? "#"}
                                    preserveScroll
                                    preserveState
                                    className={`rounded-lg px-3 py-2 text-sm transition ${
                                        link.active
                                            ? "bg-indigo-600 text-white"
                                            : link.url
                                              ? "border border-slate-300 bg-white text-slate-700 hover:bg-slate-100"
                                              : "cursor-not-allowed border border-slate-200 bg-slate-100 text-slate-400"
                                    }`}
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
