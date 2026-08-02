import { Head,Link } from "@inertiajs/react";

import HRLayout from '@/Layouts/HRLayout';

export default function IndexComponent({ workflows }) {
    return (
        <HRLayout
            heading="Approval Workflows"
            subheading="Manage approval workflows for employee requests."
        >
            <Head title="Approval Workflows" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Workflows
                            </h2>
                            <Link
                                href={route("hr.approval.workflows.create")}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                + New Workflow
                            </Link>
                        </div>
                    </div>

                    {workflows.data.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Workflow Name
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Module
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
                                    {workflows.data.map((workflow) => (
                                        <tr
                                            key={workflow.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {workflow.workflow_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {workflow.module_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        workflow.status ===
                                                        "active"
                                                            ? "bg-green-100 text-green-700"
                                                            : "bg-gray-100 text-gray-700"
                                                    }`}
                                                >
                                                    {workflow.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                <Link
                                                    href={route(
                                                        "hr.approval.workflows.show",
                                                        workflow.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    href={route(
                                                        "hr.approval.workflows.edit",
                                                        workflow.id,
                                                    )}
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
                            <p className="text-slate-500">
                                No workflows found.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
