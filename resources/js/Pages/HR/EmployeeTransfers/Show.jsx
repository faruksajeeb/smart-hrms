import { Head, Link, router } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function ShowComponent({ transfer }) {
    const statusBadge = (value) => {
        const map = {
            draft: "bg-gray-100 text-gray-700",
            pending: "bg-yellow-100 text-yellow-700",
            approved: "bg-green-100 text-green-700",
            rejected: "bg-red-100 text-red-700",
        };

        return (
            <span
                className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${map[value] || "bg-gray-100 text-gray-700"}`}
            >
                {value.charAt(0).toUpperCase() + value.slice(1)}
            </span>
        );
    };

    return (
        <HRLayout
            heading="Employee Transfer"
            subheading="Manage employee transfers across organizational units."
        >
            <Head title="Employee Transfer" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Transfer Information
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {transfer.employee?.employee_id} -{" "}
                                    {transfer.employee?.name}
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                {statusBadge(transfer.approval_status)}
                                <Link
                                    href={route("hr.transfers.index")}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Back
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Employee
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.employee?.name}
                            </p>
                            <p className="text-sm text-slate-500">
                                {transfer.employee?.employee_id}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Transfer Reason
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.transfer_reason
                                    .split("_")
                                    .map(
                                        (word) =>
                                            word.charAt(0).toUpperCase() +
                                            word.slice(1),
                                    )
                                    .join(" ")}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Effective From
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.effective_from}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Effective To
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.effective_to ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Remarks
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.remarks ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Approved By
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.approver?.name ?? "-"}
                            </p>
                            {transfer.approved_at && (
                                <p className="text-sm text-slate-500">
                                    {transfer.approved_at}
                                </p>
                            )}
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Created By
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.creator?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Last Updated By
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.updater?.name ?? "-"}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            From
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            Previous organizational assignment.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Company
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_company?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Branch
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_branch?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Cluster
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_cluster?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Division
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_division?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Department
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_department?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Section
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_section?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Unit
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.from_unit?.name ?? "-"}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h3 className="text-lg font-semibold text-slate-900">
                            To
                        </h3>
                        <p className="mt-1 text-sm text-slate-500">
                            New organizational assignment after approval.
                        </p>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Company
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_company?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Branch
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_branch?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Cluster
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_cluster?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Division
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_division?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Department
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_department?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Section
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_section?.name ?? "-"}
                            </p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">
                                Unit
                            </h4>
                            <p className="mt-1 font-semibold text-slate-900">
                                {transfer.to_unit?.name ?? "-"}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </HRLayout>
    );
}
