import { Link, Head } from "@inertiajs/react";
import HRLayout from "@/Layouts/HRLayout";

export default function ShowComponent({ request }) {
    return (
        <HRLayout
            heading="Approval Requests"
            subheading="View details of a specific approval request."
        >
            <Head title={`Approval Request #${request.id}`} />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <div>
                            <h3 className="text-lg font-semibold text-slate-900">
                                Request #{request.id}
                            </h3>
                            <p className="mt-1 text-sm text-slate-500">
                                {request.module_name}
                            </p>
                        </div>
                        <Link
                            href={route('approval.requests.index')}
                            className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                        >
                            Back
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Requester
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {request.requester?.name}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Status
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {request.current_status}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Submitted At
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {request.submitted_at || '-'}
                        </p>
                    </div>

                    <div>
                        <h4 className="text-sm font-medium text-slate-500">
                            Completed At
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {request.completed_at || '-'}
                        </p>
                    </div>

                    <div className="lg:col-span-2">
                        <h4 className="text-sm font-medium text-slate-500">
                            Remarks
                        </h4>
                        <p className="mt-1 font-semibold text-slate-900">
                            {request.remarks || '-'}
                        </p>
                    </div>
                </div>
            </div>

            <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div className="border-b border-slate-200 px-6 py-4">
                    <h3 className="text-lg font-semibold text-slate-900">
                        Approval Timeline
                    </h3>
                </div>
                <div className="p-6">
                    {request.steps && request.steps.length > 0 ? (
                        <div className="flow-root">
                            <ul className="-mb-8">
                                {request.steps.map((step, index) => (
                                    <li key={step.id}>
                                        <div className="relative pb-8">
                                            {index !== request.steps.length - 1 && (
                                                <span
                                                    className="absolute left-5 top-5 -ml-px h-full w-0.5 bg-slate-200"
                                                    aria-hidden="true"
                                                />
                                            )}
                                            <div className="relative flex items-start space-x-3">
                                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-700">
                                                    <span className="text-sm font-semibold">
                                                        {step.level_no}
                                                    </span>
                                                </div>
                                                <div className="flex-1 rounded-xl border border-slate-200 p-4">
                                                    <div className="flex items-center justify-between">
                                                        <div>
                                                            <p className="text-sm font-medium text-slate-900">
                                                                {step.approver?.name || 'Unknown'}
                                                            </p>
                                                            <p className="text-sm text-slate-500">
                                                                {step.status}
                                                            </p>
                                                        </div>
                                                        <div className="text-right text-sm text-slate-500">
                                                            {step.approved_at && (
                                                                <p>{step.approved_at}</p>
                                                            )}
                                                        </div>
                                                    </div>
                                                    {step.remarks && (
                                                        <p className="mt-2 text-sm text-slate-600">
                                                            Remarks: {step.remarks}
                                                        </p>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ) : (
                        <p className="text-slate-500">No approval steps found.</p>
                    )}
                </div>
            </div>
        </div>
        </HRLayout>
    );
}
