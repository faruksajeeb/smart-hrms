import { Link, Head } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function IndexComponent({ requests }) {
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
                                Approval Requests
                            </h2>
                            <div className="flex gap-3">
                                <Link
                                    href={route("hr.approval.requests.pending")}
                                    className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Pending Approvals
                                </Link>
                                <Link
                                    href={route("hr.approval.requests.history")}
                                    className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    History
                                </Link>
                            </div>
                        </div>
                    </div>

                    {requests.data.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Request ID
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Module
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Requester
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
                                    {requests.data.map((request) => (
                                        <tr
                                            key={request.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                #{request.id}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {request.module_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {request.requester?.name}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        request.current_status ===
                                                        "pending"
                                                            ? "bg-yellow-100 text-yellow-700"
                                                            : request.current_status ===
                                                                "approved"
                                                              ? "bg-green-100 text-green-700"
                                                              : "bg-red-100 text-red-700"
                                                    }`}
                                                >
                                                    {request.current_status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium">
                                                <Link
                                                    href={route(
                                                        "approval.requests.show",
                                                        request.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    View
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
                                No approval requests found.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
