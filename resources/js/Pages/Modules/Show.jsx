import AdminLayout from '@/Layouts/AdminLayout';
import EmployeeLayout from '@/Layouts/EmployeeLayout';
import HRLayout from '@/Layouts/HRLayout';
import { Head } from '@inertiajs/react';

const layouts = {
    admin: AdminLayout,
    hr: HRLayout,
    employee: EmployeeLayout,
};

export default function Show({ layout, title, description, highlights }) {
    const Layout = layouts[layout] ?? EmployeeLayout;

    return (
        <Layout heading={title} subheading={description}>
            <Head title={title} />

            <div className="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-950">
                        Operational Snapshot
                    </h2>
                    <p className="mt-3 max-w-2xl text-sm leading-7 text-slate-600">
                        {description}
                    </p>

                    <div className="mt-6 grid gap-4 md:grid-cols-2">
                        {highlights.map((highlight) => (
                            <div
                                key={highlight}
                                className="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                            >
                                <p className="text-sm font-medium leading-6 text-slate-700">
                                    {highlight}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                    <h2 className="text-lg font-semibold">Access Guardrails</h2>
                    <div className="mt-6 space-y-3 text-sm leading-6 text-slate-300">
                        <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            Role middleware protects the entire section before the page is rendered.
                        </p>
                        <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            Permission checks trim navigation and keep sensitive tools out of view.
                        </p>
                        <p className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">
                            Backend authorization remains the source of truth even if the frontend is tampered with.
                        </p>
                    </div>
                </section>
            </div>
        </Layout>
    );
}
