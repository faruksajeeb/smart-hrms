import { Head, Link } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function IndexComponent({ policies }) {
    return (
        <HRLayout
            heading="Leave Policies"
            subheading="Manage employee leave policies."
        >
            <Head title="Leave Policies" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Leave Policies
                            </h2>
                            <Link
                                href={route("hr.leave.policies.create")}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800"
                            >
                                + New Policy
                            </Link>
                        </div>
                    </div>

                    {policies.data.length > 0 ? (
                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-slate-200">
                                <thead className="bg-slate-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Policy Name
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">
                                            Code
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
                                    {policies.data.map((policy) => (
                                        <tr
                                            key={policy.id}
                                            className="hover:bg-slate-50"
                                        >
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {policy.policy_name}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {policy.policy_code}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {policy.effective_from}
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-900">
                                                {policy.effective_to || "-"}
                                            </td>
                                            <td className="px-6 py-4 text-sm">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                                        policy.status ===
                                                        "active"
                                                            ? "bg-green-100 text-green-700"
                                                            : "bg-gray-100 text-gray-700"
                                                    }`}
                                                >
                                                    {policy.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm font-medium space-x-2">
                                                <Link
                                                    href={route(
                                                        "hr.leave.policies.show",
                                                        policy.id,
                                                    )}
                                                    className="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-100"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    href={route(
                                                        "hr.leave.policies.details.index",
                                                        policy.id,
                                                    )}
                                                    className="rounded-lg border border-sky-300 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-50"
                                                >
                                                    Manage Rules
                                                </Link>
                                                <Link
                                                    href={route(
                                                        "hr.leave.policies.edit",
                                                        policy.id,
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
                                No leave policies found.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </HRLayout>
    );
}
