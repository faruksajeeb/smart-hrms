import Pagination from '@/Components/Pagination';
import SearchSelect from '@/Components/SearchSelect';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import HRLayout from '@/Layouts/HRLayout';
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle2, Clock, Filter, Mail, Pencil, Search, UserPlus, UserX, Users } from 'lucide-react';
import { useState } from 'react';

const titleCase = (value) =>
    String(value ?? '')
        .replaceAll('_', ' ')
        .replace(/\b\w/g, (letter) => letter.toUpperCase());

const statusClass = (status) => {
    if (['active', 'rejoined'].includes(status)) {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (['left', 'terminated'].includes(status)) {
        return 'bg-rose-100 text-rose-700';
    }

    if (status === 'probation') {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-sky-100 text-sky-700';
};

export default function Index({ employees, filters, stats, options }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [status, setStatus] = useState(filters.status ?? '');
    const [department, setDepartment] = useState(filters.department ?? '');

    const submitSearch = (event) => {
        event.preventDefault();

        router.get(
            route('hr.employees.index'),
            { search, status, department },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const cards = [
        { label: 'Total Employees', value: stats.total, icon: Users },
        { label: 'Onboarding', value: stats.onboarding, icon: UserPlus },
        { label: 'Probation', value: stats.probation, icon: Clock },
        { label: 'Active', value: stats.active, icon: CheckCircle2 },
        { label: 'Separated', value: stats.separated, icon: UserX },
    ];

    return (
        <HRLayout
            heading="Employee Management"
            subheading="Manage onboarding, probation, leave, salary, separation, and rejoin workflows."
        >
            <Head title="Employee Management" />

            <div className="grid gap-3 md:grid-cols-5">
                {cards.map((card) => (
                    <section key={card.label} className="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div className="flex items-center justify-between">
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{card.label}</p>
                            <card.icon className="h-5 w-5 text-slate-400" />
                        </div>
                        <p className="mt-3 text-3xl font-semibold tracking-tight text-slate-950">{card.value}</p>
                    </section>
                ))}
            </div>

            <section className="mt-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div className="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <form onSubmit={submitSearch} className="grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_220px_auto]">
                        <div>
                            <label className="text-sm font-medium text-slate-700">Search Employees</label>
                            <div className="relative mt-2">
                                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                <TextInput
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    placeholder="Name, email, employee ID, department"
                                    className="block w-full rounded-2xl border-slate-200 bg-slate-50 py-3 pl-9 pr-4"
                                />
                            </div>
                        </div>
                        <SearchSelect
                            id="filter_status"
                            label="Status"
                            value={status}
                            onChange={setStatus}
                            options={options.employmentStatuses}
                            placeholder="All statuses"
                        />
                        <SearchSelect
                            id="filter_department"
                            label="Department"
                            value={department}
                            onChange={setDepartment}
                            options={options.departments}
                            placeholder="All departments"
                        />
                        <SecondaryButton type="submit" className="inline-flex h-fit items-center gap-2 rounded-2xl px-5 py-3">
                            <Filter className="h-4 w-4" />
                            Filter
                        </SecondaryButton>
                    </form>

                    <Link
                        href={route('hr.employees.create')}
                        className="inline-flex items-center justify-center gap-2 rounded-2xl bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-800"
                    >
                        <UserPlus className="h-4 w-4" />
                        Onboard Employee
                    </Link>
                </div>

                <div className="mt-8 overflow-x-auto">
                    <table className="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr className="text-left text-xs uppercase tracking-[0.2em] text-slate-400">
                                <th className="pb-4">Employee</th>
                                <th className="pb-4">Role</th>
                                <th className="pb-4">Status</th>
                                <th className="pb-4">Probation</th>
                                <th className="pb-4">Salary</th>
                                <th className="pb-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {employees.data.map((employee) => (
                                <tr key={employee.id}>
                                    <td className="py-4">
                                        <div className="flex items-center gap-3">
                                            {employee.photo_url ? (
                                                <img
                                                    src={employee.photo_url}
                                                    alt={employee.name}
                                                    className="h-11 w-11 shrink-0 rounded-full border border-slate-200 object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-500">
                                                    {employee.name?.charAt(0)?.toUpperCase() ?? '?'}
                                                </div>
                                            )}
                                            <div className="min-w-0">
                                                <Link
                                                    href={route('hr.employees.show', employee.id)}
                                                    className="font-semibold text-slate-900 transition hover:text-slate-600 hover:underline"
                                                >
                                                    {employee.name}
                                                </Link>
                                                <p className="flex items-center gap-1 text-sm text-slate-500">
                                                    <Mail className="h-3.5 w-3.5 shrink-0" />
                                                    <span className="truncate">{employee.employee_id} · {employee.email}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="py-4">
                                        <p className="text-sm font-medium text-slate-800">{employee.designation ?? 'Not set'}</p>
                                        <p className="text-sm text-slate-500">{employee.department ?? 'No department'}</p>
                                    </td>
                                    <td className="py-4">
                                        <span className={`rounded-full px-3 py-1 text-xs font-semibold uppercase ${statusClass(employee.employment_status)}`}>
                                            {titleCase(employee.employment_status)}
                                        </span>
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">
                                        {employee.probation_ends_on ?? 'Not set'}
                                    </td>
                                    <td className="py-4 text-sm text-slate-600">{employee.salary}</td>
                                    <td className="py-4">
                                        <div className="flex justify-end gap-2">
                                            <Link href={route('hr.employees.edit', employee.id)} className="inline-flex items-center gap-1.5 rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800">
                                                <Pencil className="h-4 w-4" />
                                                Edit
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {employees.data.length === 0 && (
                    <div className="mt-8 rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500">
                        No employees matched the current filters.
                    </div>
                )}

                <Pagination links={employees.links} />
            </section>
        </HRLayout>
    );
}
