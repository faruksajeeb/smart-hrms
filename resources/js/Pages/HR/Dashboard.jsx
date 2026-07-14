import HRLayout from '@/Layouts/HRLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import DashboardCharts from '@/Components/DashboardCharts';

export default function Dashboard({ summary, quickActions }) {
    const { authPermissions = [] } = usePage().props;

    const cards = [
        { label: 'Employees Tracked', value: summary.employeesTracked },
        { label: 'Leave Approvals', value: summary.leaveApprovals },
        { label: 'Payroll Run', value: summary.payrollRuns },
    ];

    const modules = [
        {
            name: 'Employee Records',
            permission: 'manage employees',
            route: 'hr.employees.index',
            description: 'Keep records current and accurate across teams.',
        },
        {
            name: 'Master Data',
            permission: 'master-data.view-master-data',
            route: 'hr.master-data.index',
            description: 'Maintain company, branch, division, department, designation, and policy setup lists.',
        },
        {
            name: 'Payroll',
            permission: 'manage payroll',
            route: 'hr.payroll',
            description: 'Review compensation operations with the right guardrails.',
        },
        {
            name: 'Leave Requests',
            permission: 'manage leave requests',
            route: 'hr.leave',
            description: 'Approve leave efficiently while watching team coverage.',
        },
    ].filter((module) => authPermissions.includes(module.permission));

    return (
        <HRLayout
            heading="HR Dashboard"
            subheading="Operational control for employee records, attendance, payroll, and leave."
        >
            <Head title="HR Dashboard" />

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


            <div className="mt-8">
                <DashboardCharts />
            </div>

            <section className="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-lg font-semibold text-slate-950">
                            Essential HR Menus
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            Permission-aware shortcuts for daily HR operations.
                        </p>
                    </div>
                </div>

                <div className="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    {modules.map((module) => (
                        <Link
                            key={module.name}
                            href={route(module.route)}
                            className="rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-slate-300 hover:bg-white"
                        >
                            <p className="text-base font-semibold text-slate-900">
                                {module.name}
                            </p>
                            <p className="mt-2 text-sm leading-6 text-slate-600">
                                {module.description}
                            </p>
                        </Link>
                    ))}
                </div>
            </section>
        </HRLayout>
    );
}
