import { Head, useForm, Link } from '@inertiajs/react';
import EmployeeLayout from '@/Layouts/EmployeeLayout';

export default function ShowComponent({ application }) {
    const { data, setData, post, processing, errors } = useForm({
        remarks: '',
    });

    const submitAccept = (e) => {
        e.preventDefault();
        post(route('employee.leave.delegations.accept', application.id), {
            onSuccess: () => {},
        });
    };

    const submitDecline = (e) => {
        e.preventDefault();
        post(route('employee.leave.delegations.decline', application.id), {
            onSuccess: () => {},
        });
    };

    return (
        <EmployeeLayout
            heading="Delegation Details"
            subheading="Review and respond to this delegation request."
        >
            <Head title="Delegation Details" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-lg font-semibold text-slate-900">
                                    {application.application_no}
                                </h3>
                                <p className="mt-1 text-sm text-slate-500">
                                    {application.leave_type?.leave_name}
                                </p>
                            </div>
                            <div className="flex items-center gap-3">
                                <Link
                                    href={route('employee.leave.delegations.index')}
                                    className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Back
                                </Link>
                            </div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Employee</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.employee?.name}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Leave Type</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.leave_type?.leave_name}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Start Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.start_date}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">End Date</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.end_date}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Total Days</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.total_days}</p>
                        </div>

                        <div>
                            <h4 className="text-sm font-medium text-slate-500">Status</h4>
                            <span className={`mt-1 inline-flex rounded-full px-2 py-1 text-xs font-semibold ${
                                application.delegate_status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                application.delegate_status === 'accepted' ? 'bg-green-100 text-green-800' :
                                'bg-red-100 text-red-800'
                            }`}>
                                {application.delegate_status}
                            </span>
                        </div>

                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">Reason</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.reason || '-'}</p>
                        </div>

                        <div className="lg:col-span-2">
                            <h4 className="text-sm font-medium text-slate-500">Remarks</h4>
                            <p className="mt-1 font-semibold text-slate-900">{application.remarks || '-'}</p>
                        </div>
                    </div>
                </div>

                {application.delegate_status === 'pending' && (
                    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div className="border-b border-slate-200 px-6 py-4">
                            <h2 className="text-lg font-semibold text-slate-900">
                                Respond to Delegation
                            </h2>
                        </div>
                        <div className="p-6 space-y-6">
                            <form onSubmit={submitAccept} className="space-y-4">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Remarks (Optional)
                                    </label>
                                    <textarea
                                        value={data.remarks}
                                        onChange={(e) => setData('remarks', e.target.value)}
                                        rows={3}
                                        className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                        placeholder="Add any remarks for accepting this delegation..."
                                    />
                                </div>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center rounded-xl bg-green-700 px-4 py-2 text-sm font-semibold text-white hover:bg-green-800 disabled:opacity-50"
                                    >
                                        Accept Delegation
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            const form = document.getElementById('decline-form');
                                            if (form) form.submit();
                                        }}
                                        className="inline-flex items-center rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800"
                                    >
                                        Decline Delegation
                                    </button>
                                </div>
                            </form>

                            <form id="decline-form" onSubmit={submitDecline} className="space-y-4 hidden">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Remarks (Required)
                                    </label>
                                    <textarea
                                        value={data.remarks}
                                        onChange={(e) => setData('remarks', e.target.value)}
                                        rows={3}
                                        className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                        placeholder="Please provide a reason for declining..."
                                        required
                                    />
                                    {errors.remarks && (
                                        <p className="mt-1 text-sm text-red-600">{errors.remarks}</p>
                                    )}
                                </div>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => {
                                            const form = document.getElementById('decline-form');
                                            if (form) form.classList.add('hidden');
                                        }}
                                        className="inline-flex items-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="inline-flex items-center rounded-xl bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800 disabled:opacity-50"
                                    >
                                        Confirm Decline
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}
            </div>
        </EmployeeLayout>
    );
}
