import InputError from '@/Components/InputError';
import Pagination from '@/Components/Pagination';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

const statusStyles = {
    pending: 'bg-amber-100 text-amber-800',
    processing: 'bg-sky-100 text-sky-800',
    completed: 'bg-emerald-100 text-emerald-700',
    failed: 'bg-rose-100 text-rose-700',
};

function formatBytes(bytes) {
    if (!bytes) {
        return '0 B';
    }

    const units = ['B', 'KB', 'MB'];
    const index = Math.min(
        Math.floor(Math.log(bytes) / Math.log(1024)),
        units.length - 1,
    );
    const value = bytes / 1024 ** index;

    return `${value.toFixed(index === 0 ? 0 : 1)} ${units[index]}`;
}

const providerDescriptions = {
    openai: 'ChatGPT models via OpenAI API.',
    google: 'Gemini models via Google AI Studio.',
};

export default function Index({ analyses, providers = [], defaultProvider }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        document: null,
        prompt: 'Summarize the document',
        ai_provider: defaultProvider ?? providers[0]?.value ?? '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('admin.doc-analyzer.store'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () =>
                reset('document', 'prompt', 'ai_provider'),
        });
    };

    return (
        <AdminLayout
            heading="Document Analyzer"
            subheading="Upload HR documents, describe what you need, and review AI-generated insights saved to your workspace."
        >
            <Head title="Document Analyzer" />

            <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">
                    New analysis
                </h2>
                <p className="mt-2 text-sm text-slate-500">
                    Supported formats: PDF, DOCX, TXT, MD, and CSV (max 10 MB).
                </p>

                {providers.length === 0 ? (
                    <div className="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                        No AI provider is configured. Add{' '}
                        <code className="rounded bg-amber-100 px-1">OPENAI_API_KEY</code>{' '}
                        or{' '}
                        <code className="rounded bg-amber-100 px-1">GOOGLE_AI_API_KEY</code>{' '}
                        to your <code className="rounded bg-amber-100 px-1">.env</code> file.
                    </div>
                ) : (
                <form onSubmit={submit} className="mt-6 space-y-5">
                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            AI provider
                        </label>
                        <div className="mt-3 grid gap-3 sm:grid-cols-2">
                            {providers.map((provider) => (
                                <label
                                    key={provider.value}
                                    className={`cursor-pointer rounded-2xl border px-4 py-4 transition ${
                                        data.ai_provider === provider.value
                                            ? 'border-slate-950 bg-slate-950 text-white'
                                            : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-slate-300'
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        name="ai_provider"
                                        value={provider.value}
                                        checked={data.ai_provider === provider.value}
                                        onChange={(e) =>
                                            setData('ai_provider', e.target.value)
                                        }
                                        className="sr-only"
                                    />
                                    <p className="font-semibold">{provider.label}</p>
                                    <p
                                        className={`mt-1 text-xs ${
                                            data.ai_provider === provider.value
                                                ? 'text-slate-300'
                                                : 'text-slate-500'
                                        }`}
                                    >
                                        {providerDescriptions[provider.value]}
                                    </p>
                                </label>
                            ))}
                        </div>
                        <InputError message={errors.ai_provider} className="mt-2" />
                    </div>

                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            Document
                        </label>
                        <input
                            type="file"
                            accept=".pdf,.docx,.txt,.md,.csv,application/pdf,text/plain,text/csv"
                            onChange={(e) =>
                                setData('document', e.target.files[0] ?? null)
                            }
                            className="mt-2 block w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 file:mr-4 file:rounded-xl file:border-0 file:bg-slate-950 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800"
                        />
                        <InputError message={errors.document} className="mt-2" />
                    </div>

                    <div>
                        <label className="text-sm font-medium text-slate-700">
                            Instructions
                        </label>
                        <textarea
                            value={data.prompt}
                            onChange={(e) => setData('prompt', e.target.value)}
                            rows={5}
                            placeholder="Example: Summarize key employment terms, notice period, and compensation details."
                            className="mt-2 block w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-slate-400 focus:ring-slate-400"
                        />
                        <InputError message={errors.prompt} className="mt-2" />
                    </div>

                    <PrimaryButton
                        disabled={processing}
                        className="rounded-2xl px-5 py-3 text-sm normal-case tracking-normal"
                    >
                        {processing ? 'Analyzing…' : 'Analyze document'}
                    </PrimaryButton>
                </form>
                )}
            </section>

            <section className="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 className="text-lg font-semibold text-slate-950">
                    Recent analyses
                </h2>

                <div className="mt-6 overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                <th className="pb-4">Document</th>
                                <th className="pb-4">Provider</th>
                                <th className="pb-4">Instructions</th>
                                <th className="pb-4">Status</th>
                                <th className="pb-4">Created</th>
                                <th className="pb-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {analyses.data.map((analysis) => (
                                <tr key={analysis.id}>
                                    <td className="py-4">
                                        <p className="font-semibold text-slate-900">
                                            {analysis.original_filename}
                                        </p>
                                        <p className="text-sm text-slate-500">
                                            {formatBytes(analysis.file_size)}
                                        </p>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {analysis.ai_provider_label}
                                    </td>
                                    <td className="max-w-xs py-4 text-sm text-slate-600">
                                        <p className="line-clamp-2">
                                            {analysis.prompt}
                                        </p>
                                    </td>
                                    <td className="py-4">
                                        <span
                                            className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${
                                                statusStyles[analysis.status] ??
                                                'bg-slate-100 text-slate-700'
                                            }`}
                                        >
                                            {analysis.status}
                                        </span>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {analysis.created_at
                                            ? new Date(
                                                  analysis.created_at,
                                              ).toLocaleString()
                                            : '—'}
                                    </td>
                                    <td className="py-4 text-right">
                                        <Link
                                            href={route(
                                                'admin.doc-analyzer.show',
                                                analysis.id,
                                            )}
                                            className="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {analyses.data.length === 0 && (
                    <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        No analyses yet. Upload a document to get started.
                    </div>
                )}

                <Pagination links={analyses.links} />
            </section>
        </AdminLayout>
    );
}
