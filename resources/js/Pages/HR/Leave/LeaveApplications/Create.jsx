import { Head, useForm } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function CreateComponent({ leaveTypes = [], policies = [], assignments = [] }) {
    const { data, setData, post, processing, errors } = useForm({
        leave_type_id: '',
        leave_policy_id: '',
        start_date: '',
        end_date: '',
        is_half_day: false,
        half_day_session: 'morning',
        is_emergency: false,
        reason: '',
        attachments: [],
    });

    const selectedLeaveType = leaveTypes.find((t) => t.id === parseInt(data.leave_type_id));
    const selectedPolicy = policies.find((p) => p.id === parseInt(data.leave_policy_id));
    const startDate = data.start_date;
    const endDate = data.end_date;

    const handleFileChange = (e) => {
        const files = Array.from(e.target.files || []);
        const newAttachments = files.map((file) => ({
            file_name: file.name,
            file_path: URL.createObjectURL(file),
            file_size: file.size,
            mime_type: file.type,
        }));
        setData('attachments', [...data.attachments, ...newAttachments]);
    };

    const removeAttachment = (index) => {
        const newAttachments = data.attachments.filter((_, i) => i !== index);
        setData('attachments', newAttachments);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('hr.leave.applications.store'), {
            onSuccess: () => {},
        });
    };

    return (
        <HRLayout
            heading="Apply for Leave"
            subheading="Submit a new leave application."
        >
            <Head title="Apply for Leave" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">
                            Leave Application Form
                        </h2>
                    </div>
                    <form onSubmit={submit} className="p-6 space-y-6">
                        <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Leave Type <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={data.leave_type_id}
                                    onChange={(e) => setData('leave_type_id', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                >
                                    <option value="">Select Leave Type</option>
                                    {leaveTypes.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.leave_name}
                                        </option>
                                    ))}
                                </select>
                                {errors.leave_type_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.leave_type_id}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Leave Policy <span className="text-red-500">*</span>
                                </label>
                                <select
                                    value={data.leave_policy_id}
                                    onChange={(e) => setData('leave_policy_id', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                >
                                    <option value="">Select Policy</option>
                                    {policies.map((policy) => (
                                        <option key={policy.id} value={policy.id}>
                                            {policy.policy_name}
                                        </option>
                                    ))}
                                </select>
                                {errors.leave_policy_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.leave_policy_id}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    Start Date <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={data.start_date}
                                    onChange={(e) => setData('start_date', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.start_date && (
                                    <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700">
                                    End Date <span className="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    value={data.end_date}
                                    onChange={(e) => setData('end_date', e.target.value)}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.end_date && (
                                    <p className="mt-1 text-sm text-red-600">{errors.end_date}</p>
                                )}
                            </div>

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_half_day"
                                    checked={data.is_half_day}
                                    onChange={(e) => setData('is_half_day', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <label htmlFor="is_half_day" className="text-sm font-medium text-slate-700">
                                    Half Day
                                </label>
                            </div>

                            {data.is_half_day && (
                                <div>
                                    <label className="block text-sm font-medium text-slate-700">
                                        Session <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={data.half_day_session}
                                        onChange={(e) => setData('half_day_session', e.target.value)}
                                        className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                    >
                                        <option value="morning">Morning</option>
                                        <option value="afternoon">Afternoon</option>
                                    </select>
                                </div>
                            )}

                            <div className="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    id="is_emergency"
                                    checked={data.is_emergency}
                                    onChange={(e) => setData('is_emergency', e.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                <label htmlFor="is_emergency" className="text-sm font-medium text-slate-700">
                                    Emergency Leave
                                </label>
                            </div>

                            <div className="lg:col-span-2">
                                <label className="block text-sm font-medium text-slate-700">
                                    Reason
                                </label>
                                <textarea
                                    value={data.reason}
                                    onChange={(e) => setData('reason', e.target.value)}
                                    rows={3}
                                    className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                                />
                                {errors.reason && (
                                    <p className="mt-1 text-sm text-red-600">{errors.reason}</p>
                                )}
                            </div>

                            <div className="lg:col-span-2">
                                <label className="block text-sm font-medium text-slate-700">
                                    Attachments
                                </label>
                                <input
                                    type="file"
                                    multiple
                                    onChange={handleFileChange}
                                    className="mt-1 block w-full text-sm text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-sky-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-sky-700 hover:file:bg-sky-100"
                                />
                                {data.attachments.length > 0 && (
                                    <ul className="mt-2 space-y-1">
                                        {data.attachments.map((file, index) => (
                                            <li key={index} className="flex items-center justify-between text-sm text-slate-600">
                                                <span>{file.file_name}</span>
                                                <button
                                                    type="button"
                                                    onClick={() => removeAttachment(index)}
                                                    className="text-red-600 hover:text-red-800"
                                                >
                                                    Remove
                                                </button>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        </div>

                        <div className="flex items-center justify-end gap-3">
                            <Link
                                href={route('hr.leave.applications.index')}
                                className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center rounded-xl bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 disabled:opacity-50"
                            >
                                Save as Draft
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </HRLayout>
    );
}
