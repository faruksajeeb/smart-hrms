import { Head, Link, router } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function HistoryComponent({ employee, history }) {
    return (
        <HRLayout
            heading="Employee Transfer"
            subheading="Manage employee transfers across organizational units."
        >
            <Head title="Employee Transfer History" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    Employee
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {employee.employee_id} - {employee.name}
                                </p>
                            </div>
                            <Link
                                href={route("hr.transfers.index")}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Back
                            </Link>
                        </div>
                    </div>
                </div>

                {history.length > 0 ? (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h3 className="text-lg font-semibold text-slate-900">
                                Timeline
                            </h3>
                        </div>
                        <div className="p-6">
                            <div className="flow-root">
                                <ul className="-mb-8">
                                    {history.map((item, index) => (
                                        <li key={item.id}>
                                            <div className="relative pb-8">
                                                {index !==
                                                    history.length - 1 && (
                                                    <span
                                                        className="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-200"
                                                        aria-hidden="true"
                                                    />
                                                )}
                                                <div className="relative flex items-start space-x-3">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                                                        <span className="text-sm font-semibold">
                                                            {index + 1}
                                                        </span>
                                                    </div>
                                                    <div className="flex-1 rounded-xl border border-slate-200 p-4">
                                                        <div className="flex items-center justify-between">
                                                            <div>
                                                                <p className="text-sm font-medium text-slate-900">
                                                                    {item
                                                                        .fromCompany
                                                                        ?.name ??
                                                                        "-"}{" "}
                                                                    →{" "}
                                                                    {item
                                                                        .toCompany
                                                                        ?.name ??
                                                                        "-"}
                                                                </p>
                                                                <p className="text-sm text-slate-500">
                                                                    {item
                                                                        .fromDepartment
                                                                        ?.name ??
                                                                        "-"}{" "}
                                                                    →{" "}
                                                                    {item
                                                                        .toDepartment
                                                                        ?.name ??
                                                                        "-"}
                                                                </p>
                                                            </div>
                                                            <div className="text-right text-sm text-slate-500">
                                                                <p>
                                                                    From:{" "}
                                                                    {
                                                                        item.effective_from
                                                                    }
                                                                </p>
                                                                <p>
                                                                    To:{" "}
                                                                    {item.effective_to ??
                                                                        "Present"}
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div className="mt-3 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                                                            <span>
                                                                Reason:{" "}
                                                                {item.transfer_reason
                                                                    .split("_")
                                                                    .map(
                                                                        (
                                                                            word,
                                                                        ) =>
                                                                            word
                                                                                .charAt(
                                                                                    0,
                                                                                )
                                                                                .toUpperCase() +
                                                                            word.slice(
                                                                                1,
                                                                            ),
                                                                    )
                                                                    .join(" ")}
                                                            </span>
                                                            {item.approver && (
                                                                <span>
                                                                    Approved By:{" "}
                                                                    {
                                                                        item
                                                                            .approver
                                                                            .name
                                                                    }
                                                                </span>
                                                            )}
                                                            {item.creator && (
                                                                <span>
                                                                    Changed By:{" "}
                                                                    {
                                                                        item
                                                                            .creator
                                                                            .name
                                                                    }
                                                                </span>
                                                            )}
                                                        </div>
                                                        {item.remarks && (
                                                            <p className="mt-2 text-sm text-slate-600">
                                                                Remarks:{" "}
                                                                {item.remarks}
                                                            </p>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm py-10 text-center">
                        <p className="text-slate-500">
                            No transfer history found.
                        </p>
                    </div>
                )}
            </div>
        </HRLayout>
    );
}
