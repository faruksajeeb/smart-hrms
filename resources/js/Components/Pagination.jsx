import { Link } from '@inertiajs/react';

export default function Pagination({ links = [] }) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <div className="mt-6 flex flex-wrap items-center gap-2">
            {links.map((link) =>
                link.url ? (
                    <Link
                        key={`${link.label}-${link.url}`}
                        href={link.url}
                        preserveScroll
                        className={`rounded-xl px-4 py-2 text-sm transition ${
                            link.active
                                ? 'bg-slate-950 text-white'
                                : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={`${link.label}-disabled`}
                        className="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-sm text-slate-400"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </div>
    );
}
