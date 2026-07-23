import { Head, Link, router } from "@inertiajs/react";
import { useState } from "react";
import HRLayout from "@/Layouts/HRLayout";
import Swal from "sweetalert2";

export default function Index({
    assignments,
    filters = {},
    employees = [],
    managers = [],
}) {
    const [employeeId, setEmployeeId] = useState(filters.employee_id ?? "");
    const [managerId, setManagerId] = useState(filters.manager_id ?? "");

    const filter = () => {
        router.get(
            route("hr.reporting-manager-assignments.index"),
            {
                employee_id: employeeId,
                manager_id: managerId,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        router.get(route("hr.reporting-manager-assignments.index"), {}, {
            preserveState: true,
            replace: true,
        });
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
                router.delete(route("hr.reporting-manager-assignments.destroy", id), {
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

    const hasFilters = employeeId || managerId;

    return (
        <HRLayout
            heading={`Reporting Manager Assignments`}
            subheading="Manage reporting manager assignments for employees."
        >
            <Head title="Reporting Manager Assignments" />

            <div className="space-y-6">
                {/* Filter Section */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-sm font-semibold uppercase tracking-wider text-slate-500">
                            Filters
                        </h3>
                    </div>
                    <div className="p-6">
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Employee
                                </label>
                                <select
                                    value={employeeId}
                                    onChange={(e) => setEmployeeId(e.target.value)}
                                    className="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
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
                                    Reporting Manager
                                </label>
                                <select
                                    value={managerId}
                                    onChange={(e) => setManagerId(e.target.value)}
                                    className="mt-1 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                                >
                                    <option value="">All Managers</option>
                                    {managers.map((manager) => (
                                        <option key={manager.id} value={manager.id}>
                                            {manager.employee_id} - {manager.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="mt-4 flex items-center justify-end gap-3">
                            {hasFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="text-sm font-medium text-slate-500 hover:text-slate-700"
                                >
                                    Clear Filters
                                </button>
                            )}
                            <button
                                type="button"
                                onClick={filter}
                                className="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                Apply Filters
                            </button>
                        </div>

                        </div>
                        
                    </div>
                </div>

                {/* Toolbar */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Assignments
                            </h2>
                            <Link
                                href={route("hr.reporting-manager-assignments.create")}
                                className="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                + Assign Manager
                            </Link>
                        </div>

                        {/* Assignment List */}
                        {assignments.data.length > 0 ? (
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Employee
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Manager
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Effective From
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Effective To
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Type
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Remarks
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-slate-200">
                                    {assignments.data.map((assignment) => (
                                        <tr
                                            key={assignment.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                                {assignment.employee?.name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                                {assignment.manager?.name}
                                            </td>
                                            <td className="px-5 py-4 text-sm">
                                                {assignment.effective_from}
                                            </td>
                                            <td className="px-5 py-4 text-sm">
                                                {assignment.effective_to ?? "Present"}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {assignment.assignment_type
                                                    .charAt(0)
                                                    .toUpperCase() +
                                                    assignment.assignment_type
                                                        .slice(1)
                                                        .toLowerCase()}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {assignment.remarks ?? "—"}
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                <Link
                                                    href={route(
                                                        "hr.reporting-manager-assignments.history",
                                                        assignment.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    History
                                                </Link>

                                                <Link
                                                    href={route(
                                                        "hr.reporting-manager-assignments.change",
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
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        ) : (
                            <div className="py-10 text-center">
                                <p className="text-slate-500">
                                    No assignments found.
                                </p>
                            </div>
                        )}

                        {/* Pagination */}
                        {assignments.last_page > 1 && (
                            <div className="mt-6 flex flex-wrap items-center justify-between gap-4">
                                <div className="text-sm text-slate-600">
                                    Showing
                                    <span className="font-medium">
                                        {assignments.from}
                                    </span>
                                    to
                                    <span className="font-medium">
                                        {assignments.to}
                                    </span>
                                    of
                                    <span className="font-medium">
                                        {assignments.total}
                                    </span>
                                    assignments
                                </div>

                                <div className="flex flex-wrap gap-2">
                                    {assignments.links.map((link, index) => (
                                        <button
                                            key={index}
                                            disabled={!link.url}
                                            onClick={() =>
                                                router.visit(link.url)
                                            }
                                            disabled={!link.url}
                                            className={`px-3 py-1 mx-1 text-sm leading-5 border rounded transition-colors ${
                                                link.active
                                                    ? "bg-indigo-600 border-transparent text-white"
                                                    : "border-transparent hover:bg-gray-100 hover:text-gray-700"
                                            } ${
                                                !link.url
                                                    ? "opacity-25 pointer-events-none"
                                                    : ""
                                            }`}
                                        >
                                            {link.label}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
