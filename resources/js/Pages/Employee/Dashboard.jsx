import EmployeeLayout from '@/Layouts/EmployeeLayout';
import { Head, usePage } from '@inertiajs/react';
import DashboardCharts from '@/Components/DashboardCharts';

export default function Dashboard({ summary, quickActions }) {
    const { authPermissions = [] } = usePage().props;

    const cards = [
        { label: 'Attendance Status', value: summary.attendanceStatus },
        { label: 'Leave Balance', value: summary.leaveBalance },
        { label: 'Next Payroll', value: summary.nextPayroll },
    ];

    const modules = [
        {
            name: 'Attendance',
            permission: 'manage attendance',
            description: 'See your check-ins and fix exceptions before payroll closes.',
        },
        {
            name: 'Leave Center',
            permission: 'manage leave requests',
            description: 'Submit requests and follow approval updates in one place.',
        },
    ].filter((module) => authPermissions.includes(module.permission));

    return (
        <EmployeeLayout
            heading="Employee Dashboard"
            subheading="Your personal workspace for attendance, leave, and employment essentials."
        >
            <Head title="Employee Dashboard" />

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

            <div className="mt-8 grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-slate-950">
                        Your Available Tools
                    </h2>
                    <div className="mt-6 grid gap-4">
                        {modules.map((module) => (
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

                <section className="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h2 className="text-lg font-semibold text-amber-950">
                        What You Can Do Next
                    </h2>
                    <div className="mt-6 space-y-3">
                        {quickActions.map((item) => (
                            <div
                                key={item}
                                className="rounded-2xl border border-amber-100 bg-white px-4 py-3 text-sm text-amber-900"
                            >
                                {item}
                            </div>
                        ))}
                    </div>
                </section>
            </div>

            <div className="mt-8">
                <DashboardCharts />
            </div>
        </EmployeeLayout>
    );
}
