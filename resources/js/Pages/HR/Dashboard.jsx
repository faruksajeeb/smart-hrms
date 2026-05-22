import HRLayout from '@/Layouts/HRLayout';
import { Head, usePage } from '@inertiajs/react';
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
            description: 'Keep records current and accurate across teams.',
        },
        {
            name: 'Payroll',
            permission: 'manage payroll',
            description: 'Review compensation operations with the right guardrails.',
        },
        {
            name: 'Leave Requests',
            permission: 'manage leave requests',
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
        </HRLayout>
    );
}
