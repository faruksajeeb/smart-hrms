import { Head, Link } from '@inertiajs/react';
import HRLayout from '@/Layouts/HRLayout';

export default function IndexComponent({ attachments, applications = [], filters = {} }) {
    return (
        <HRLayout
            heading="Leave Attachments"
            subheading="Review and verify leave attachments."
        >
            <Head title="Leave Attachments" />
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-6 py-4">
                        <h2 className="text-lg font-semibold text-slate-900">Filters</h2>
                    </div>
                    <div className="grid grid-cols-1 gap-6 p-6 lg:grid-cols-2">
                        <div>
                            <label className="block text-sm font-medium text-slate-700">Leave Application</label>
                            <select
                                value={filters.application_id || ''}
                                onChange={(e) => {
                                    const params = new URLSearchParams(window.location.search);
                                    if (e.target.value) {
                                        params.set('application_id', e.target.value);
                                    } else {
                                        params.delete('application_id');
                                    }
                                    window.location.search = params.toString();
                                }}
                                className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2"
                            >
                                <option value="">All Applications</option>
                                {applications.map((app) => (
                                    <option key={app.id} value={app.id}>
                                        {app.application_no}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Application</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Employee</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">File Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Size</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-slate-200">
                                {attachments.data.length > 0 ? (
                                    attachments.data.map((attachment) => (
                                        <tr key={attachment.id}>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                                {attachment.application?.application_no || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {attachment.application?.employee?.name || '-'}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {attachment.original_file_name || attachment.file_name}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {attachment.mime_type}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                                {(attachment.file_size / 1024).toFixed(1)} KB
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className={`inline-flex rounded-full px-2 py-1 text-xs font-medium ${
                                                    attachment.status === 'verified' ? 'bg-green-100 text-green-700' :
                                                    attachment.status === 'rejected' ? 'bg-red-100 text-red-700' :
                                                    'bg-yellow-100 text-yellow-700'
                                                }`}>
                                                    {attachment.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div className="flex items-center gap-2">
                                                    <Link href={route('hr.leave.attachments.download', attachment.id)} className="text-sky-700 hover:text-sky-900">
                                                        Download
                                                    </Link>
                                                    {attachment.status === 'pending' && (
                                                        <>
                                                            <form action={route('hr.leave.attachments.verify', attachment.id)} method="POST" className="inline">
                                                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                                                <button type="submit" className="text-green-700 hover:text-green-900">
                                                                    Verify
                                                                </button>
                                                            </form>
                                                            <form action={route('hr.leave.attachments.reject', attachment.id)} method="POST" className="inline" onSubmit={(e) => {
                                                                const remarks = prompt('Enter rejection reason:');
                                                                if (remarks === null) e.preventDefault();
                                                                else {
                                                                    const input = document.createElement('input');
                                                                    input.type = 'hidden';
                                                                    input.name = 'remarks';
                                                                    input.value = remarks;
                                                                    e.target.appendChild(input);
                                                                }
                                                            }}>
                                                                <input type="hidden" name="_token" value={document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''} />
                                                                <button type="submit" className="text-red-700 hover:text-red-900">
                                                                    Reject
                                                                </button>
                                                            </form>
                                                        </>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="7" className="px-6 py-12 text-center text-sm text-slate-500">
                                            No attachments found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    {attachments.links && attachments.links.length > 0 ? (
                        <div className="border-t border-slate-200 px-6 py-4 flex items-center justify-center gap-2">
                            {attachments.links.map((link, index) => (
                                <a key={index} href={link.url || '#'} dangerouslySetInnerHTML={{__html: link.label}} className={`px-3 py-1 rounded-lg text-sm ${link.active ? 'bg-sky-700 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'}`} />
                            ))}
                        </div>
                    ) : null}
                </div>
            </div>
        </HRLayout>
    );
}
