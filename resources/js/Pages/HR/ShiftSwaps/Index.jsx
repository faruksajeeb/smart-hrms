import { useState } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

const statusStyles = {
    pending: 'bg-amber-100 text-amber-800',
    accepted: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-600',
};

function ScheduleLabel({ schedule }) {
    if (!schedule) return null;
    return (
        <span>
            {schedule.work_date.slice(0, 10)} &middot; {schedule.shift.name} (
            {schedule.shift.start_time.slice(0, 5)}&ndash;{schedule.shift.end_time.slice(0, 5)})
        </span>
    );
}

export default function ShiftSwapsIndex({ requests, mySchedules, canApprove }) {
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        requester_schedule_id: '',
        target_user_id: '',
        reason: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('shift-swaps.store'), {
            onSuccess: () => {
                reset();
                setShowForm(false);
            },
        });
    }

    function accept(id) {
        router.post(route('shift-swaps.accept', id));
    }

    function review(id, status) {
        router.post(route('shift-swaps.review', id), { status });
    }

    function cancelRequest(id) {
        if (confirm('Cancel this swap request?')) {
            router.delete(route('shift-swaps.cancel', id));
        }
    }

    return (
        <AuthenticatedLayout header={<h2 className="text-xl font-semibold text-gray-800">Shift swap requests</h2>}>
            <Head title="Shift swaps" />

            <div className="py-8">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4 flex justify-end">
                        <button
                            onClick={() => setShowForm((s) => !s)}
                            className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                        >
                            Request a swap
                        </button>
                    </div>

                    {showForm && (
                        <form onSubmit={submit} className="mb-6 grid grid-cols-1 gap-4 rounded-lg bg-white p-4 shadow sm:grid-cols-3">
                            <div>
                                <label className="block text-sm text-gray-600">Shift to give up</label>
                                <select
                                    value={data.requester_schedule_id}
                                    onChange={(e) => setData('requester_schedule_id', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                >
                                    <option value="">Select&hellip;</option>
                                    {mySchedules.map((s) => (
                                        <option key={s.id} value={s.id}>
                                            {s.work_date.slice(0, 10)} &middot; {s.shift.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.requester_schedule_id && (
                                    <p className="mt-1 text-xs text-red-600">{errors.requester_schedule_id}</p>
                                )}
                            </div>

                            <div className="sm:col-span-2">
                                <label className="block text-sm text-gray-600">Reason (optional)</label>
                                <input
                                    type="text"
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    className="mt-1 w-full rounded-md border-gray-300 text-sm"
                                    placeholder="Leave blank to make it available to anyone"
                                />
                            </div>

                            <div className="sm:col-span-3">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                                >
                                    Submit request
                                </button>
                            </div>
                        </form>
                    )}

                    <div className="overflow-hidden rounded-lg bg-white shadow">
                        <table className="min-w-full divide-y divide-gray-200 text-sm">
                            <thead className="bg-gray-50 text-left text-xs font-medium uppercase text-gray-500">
                                <tr>
                                    <th className="px-4 py-3">Requested by</th>
                                    <th className="px-4 py-3">Giving up</th>
                                    <th className="px-4 py-3">Taken by</th>
                                    <th className="px-4 py-3">Status</th>
                                    <th className="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {requests.map((r) => (
                                    <tr key={r.id}>
                                        <td className="px-4 py-3">{r.requester.name}</td>
                                        <td className="px-4 py-3">
                                            <ScheduleLabel schedule={r.requester_schedule} />
                                        </td>
                                        <td className="px-4 py-3">{r.target_user ? r.target_user.name : '—'}</td>
                                        <td className="px-4 py-3">
                                            <span className={`rounded-full px-2 py-1 text-xs font-medium capitalize ${statusStyles[r.status]}`}>
                                                {r.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 space-x-2">
                                            {r.status === 'pending' && !r.target_user && (
                                                <button onClick={() => accept(r.id)} className="text-indigo-600 hover:underline">
                                                    Accept
                                                </button>
                                            )}
                                            {canApprove && r.status === 'accepted' && (
                                                <>
                                                    <button onClick={() => review(r.id, 'approved')} className="text-green-600 hover:underline">
                                                        Approve
                                                    </button>
                                                    <button onClick={() => review(r.id, 'rejected')} className="text-red-600 hover:underline">
                                                        Reject
                                                    </button>
                                                </>
                                            )}
                                            {r.status === 'pending' && (
                                                <button onClick={() => cancelRequest(r.id)} className="text-gray-500 hover:underline">
                                                    Cancel
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                                {requests.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-4 py-6 text-center text-gray-400">
                                            No swap requests yet.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}