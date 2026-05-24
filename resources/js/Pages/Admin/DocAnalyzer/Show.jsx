import DangerButton from '@/Components/DangerButton';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect } from 'react';

const statusStyles = {
    pending: 'bg-amber-100 text-amber-800',
    processing: 'bg-sky-100 text-sky-800',
    completed: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-rose-100 text-rose-700',
};

export default function Show({ analysis }) {
    const shouldPoll =
        !analysis.is_finished &&
        (analysis.status === 'pending' || analysis.status === 'processing');

    useEffect(() => {
        if (!shouldPoll) {
            return undefined;
        }

        const interval = window.setInterval(() => {
            router.reload({
                only: ['analysis', 'flash'],
                preserveScroll: true,
            });
        }, 3000);

        return () => window.clearInterval(interval);
    }, [shouldPoll, analysis.status]);

    const destroyAnalysis = () => {
        if (
            !window.confirm(
                'Delete this analysis and its uploaded document? This cannot be undone.',
            )
        ) {
            return;
        }

        router.delete(route('admin.doc-analyzer.destroy', analysis.id));
    };

    return (
        <AdminLayout
            heading="Analysis result"
            subheading="Review the AI response generated from your document and instructions."
        >
            <Head title={`Analysis: ${analysis.original_filename}`} />

            <div className="mb-6">
                <Link
                    href={route('admin.doc-analyzer.index')}
                    className="text-sm font-medium text-slate-600 transition hover:text-slate-950"
                >
                    ← Back to Document Analyzer
                </Link>
            </div>

            <div className="grid gap-6 lg:grid-cols-3">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                    <p className="text-xs uppercase tracking-[0.2em] text-slate-400">
                        Document
                    </p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">
                        {analysis.original_filename}
                    </h2>
                    <div className="mt-4">
                        <span
                            className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${
                                statusStyles[analysis.status] ??
                                'bg-slate-100 text-slate-700'
                            }`}
                        >
                            {analysis.status}
                        </span>
                    </div>
                    <p className="mt-4 text-sm text-slate-600">
                        Provider:{' '}
                        <span className="font-medium text-slate-900">
                            {analysis.ai_provider_label}
                        </span>
                    </p>
                    <p className="mt-6 text-sm text-slate-500">
                        Created{' '}
                        {analysis.created_at
                            ? new Date(analysis.created_at).toLocaleString()
                            : '—'}
                    </p>
                    {analysis.completed_at && (
                        <p className="mt-2 text-sm text-slate-500">
                            Completed{' '}
                            {new Date(analysis.completed_at).toLocaleString()}
                        </p>
                    )}
                    <DangerButton
                        type="button"
                        onClick={destroyAnalysis}
                        className="mt-8 rounded-xl px-4 py-2 text-sm normal-case tracking-normal"
                    >
                        Delete analysis
                    </DangerButton>
                </section>

                <section className="space-y-6 lg:col-span-2">
                    <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">
                            Your instructions
                        </h3>
                        <p className="mt-4 whitespace-pre-wrap text-sm leading-7 text-slate-700">
                            {analysis.prompt}
                        </p>
                    </div>

                    <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <h3 className="text-sm font-semibold uppercase tracking-[0.2em] text-slate-400">
                            AI response
                        </h3>

                        {!analysis.is_finished && (
                            <div className="mt-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                                Analysis in progress. This page refreshes
                                automatically every few seconds.
                            </div>
                        )}

                        {analysis.status === 'failed' && (
                            <div className="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                {analysis.error_message ??
                                    'The analysis could not be completed.'}
                            </div>
                        )}

                        {analysis.status === 'completed' && (
                            <div className="mt-4 whitespace-pre-wrap rounded-2xl bg-slate-50 px-5 py-4 text-sm leading-7 text-slate-800">
                                {analysis.response}
                            </div>
                        )}
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
