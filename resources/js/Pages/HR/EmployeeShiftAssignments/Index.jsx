import { useEffect, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import HRLayout from "@/Layouts/HRLayout";

export default function Index({
    assignments,
    employees,
}) {
    const [showCreateModal, setShowCreateModal] = useState(false);

    return (
        <HRLayout
            heading={`Shift Assignments`}
            subheading="Manage shift assignments for employees."
        >
            <Head title="Shift Assignments" />

            <div className="space-y-6">
                {/* Toolbar */}
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="p-6">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Assignments
                            </h2>
                            <Link
                                href={route("hr.shift-assignments.create")}
                                className="inline-flex items-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                            >
                                + Assign Shift
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
                                            Effective From
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Effective To
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Shift
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
                                        <tr key={assignment.id} className="hover:bg-slate-50">
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                                {assignment.employee?.name}
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
                                            <td className="px-6 py-4 flex items-center space-x-3">
                                                <div className="flex-shrink-0 h-8 w-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-medium">
                                                    {assignment.shift?.shift_code?.substring(0, 2) ?? "??"
                                                    }
                                                </div>
                                                <div>
                                                    <p className="text-sm font-medium text-slate-900">
                                                        {assignment.shift?.shift_name ?? "Unknown"}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        Code: {assignment.shift?.shift_code}
                                                    </p>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {assignment.assignment_type
                                                    .charAt(0)
                                                    .toUpperCase() + assignment.assignment_type.slice(1)
                                                    .toLowerCase()}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {assignment.remarks ?? "—"}
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                <Link
                                                    href={route(
                                                        "hr.shift-assignments.history",
                                                        assignment.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    History
                                                </Link>

                                                <Link
                                                    href={route(
                                                        "hr.shift-assignments.change",
                                                        assignment.id,
                                                    )}
                                                    className="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-700"
                                                >
                                                    Change
                                                </Link>
                                                <button
                                                    onClick={() => {
                                                        if (
                                                            window.confirm(
                                                                "Are you sure you want to delete this assignment?"
                                                            )
                                                        ) {
                                                            router.delete(
                                                                route("hr.shift-assignments.destroy", assignment.id),
                                                                {
                                                                    preserveScroll: true,
                                                                    onSuccess: () => {
                                                                        // Optionally show a success toast; for now, rely on flash message
                                                                    },
                                                                }
                                                            );
                                                        }
                                                    }}
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
                                            onClick={() => router.visit(link.url)}
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
                                            {label}
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