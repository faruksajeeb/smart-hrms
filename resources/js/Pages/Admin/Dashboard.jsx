import AdminLayout from '@/Layouts/AdminLayout';
import { Head, usePage } from '@inertiajs/react';
import DashboardCharts from '@/Components/DashboardCharts';

export default function Dashboard({ summary, quickActions }) {
    const { authPermissions = [] } = usePage().props;

    const cards = [
        { label: 'Active Users', value: summary.activeUsers, tone: 'sky' },
        { label: 'Configured Roles', value: summary.openRoles, tone: 'emerald' },
        {
            label: 'Permissions',
            value: summary.permissionCount,
            tone: 'amber',
        },
    ];

    const secureModules = [
        {
            name: 'User Management',
            description: 'Create HR accounts and assign secure access boundaries.',
            permission: 'manage users',
        },
        {
            name: 'Role Management',
            description: 'Tune reusable access bundles for workforce operations.',
            permission: 'manage roles',
        },
        {
            name: 'Permission Management',
            description: 'Control the lowest-level capabilities that protect each module.',
            permission: 'manage permissions',
        },
        {
            name: 'Reports',
            description: 'Review sensitive workforce and operational metrics.',
            permission: 'view reports',
        },
    ].filter((module) => authPermissions.includes(module.permission));

    return (
        <AdminLayout
            heading="Admin Dashboard"
            subheading="Platform-wide visibility, user access governance, and executive oversight."
        >
            <Head title="Admin Dashboard" />

            <div className="grid gap-6 lg:grid-cols-3">
                {cards.map((card) => (
                    <section
                        key={card.label}
                        className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <p className="text-sm font-medium text-slate-500">{card.label}</p>
                        <p className="mt-4 text-4xl font-semibold tracking-tight text-slate-950">
                            {card.value}
                        </p>
                    </section>
                ))}
            </div>
{/* 
            <div className="mt-8 grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div className="flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-semibold text-slate-950">
                                Permission-Aware Modules
                            </h2>
                            <p className="mt-1 text-sm text-slate-500">
                                What this role can actively govern right now.
                            </p>
                        </div>
                    </div>

                    <div className="mt-6 grid gap-4 md:grid-cols-2">
                        {secureModules.map((module) => (
                            <div
                                key={module.name}
                                className="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                            >
                                <p className="text-base font-semibold text-slate-900">
                                    {module.name}
                                </p>
                                <p className="mt-2 text-sm leading-6 text-slate-600">
                                    {module.description}
                                </p>
                            </div>
                        ))}
                    </div>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
                    <h2 className="text-lg font-semibold">Admin Priorities</h2>
                    <div className="mt-6 space-y-3">
                        {quickActions.map((item) => (
                            <div
                                key={item}
                                className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200"
                            >
                                {item}
                            </div>
                        ))}
                    </div>
                </section>
            </div> */}

            <div className="mt-8">
                <DashboardCharts />
            </div>
        </AdminLayout>
    );
}
